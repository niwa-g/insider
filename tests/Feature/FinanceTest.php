<?php

use App\Domains\Catalog\Enums\BillingType;
use App\Domains\Catalog\Enums\ClientServiceStatus;
use App\Domains\Catalog\Models\ClientService;
use App\Domains\Catalog\Models\ServiceType;
use App\Domains\Crm\Models\Client;
use App\Domains\Finance\Enums\FinancialEntryStatus;
use App\Domains\Finance\Enums\FinancialEntryType;
use App\Domains\Finance\Models\FinancialEntry;
use App\Domains\Finance\Services\MrrCalculator;
use App\Domains\Finance\Services\ProfitabilityCalculator;
use App\Domains\Identity\Models\Organization;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function seedFinanceUser(array $permissions): User
{
    $org = Organization::factory()->create();

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $role = Role::findOrCreate('Finance Tester', 'web');
    $role->syncPermissions($permissions);

    $user = User::factory()->create([
        'organization_id' => $org->id,
        'email' => fake()->unique()->safeEmail(),
    ]);
    $user->assignRole($role);

    app()->instance('currentOrganizationId', $org->id);

    return $user;
}

it('lists financial entries for authorized users', function () {
    $user = seedFinanceUser([
        'financial_entries.view',
        'financial_entries.create',
    ]);

    FinancialEntry::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->get('/financial-entries')
        ->assertOk();
});

it('creates a draft financial entry', function () {
    $user = seedFinanceUser([
        'financial_entries.view',
        'financial_entries.create',
    ]);
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->post('/financial-entries', [
            'client_id' => $client->id,
            'entry_type' => 'revenue',
            'category' => 'hosting',
            'description' => 'Test revenue entry',
            'amount' => 150,
            'occurred_on' => now()->toDateString(),
        ])
        ->assertRedirect();

    $entry = FinancialEntry::query()->where('description', 'Test revenue entry')->first();
    expect($entry)->not->toBeNull();
    expect($entry->status)->toBe(FinancialEntryStatus::Draft);
});

it('posts a draft entry', function () {
    $user = seedFinanceUser([
        'financial_entries.view',
        'financial_entries.post',
    ]);
    $entry = FinancialEntry::factory()->create([
        'organization_id' => $user->organization_id,
        'status' => FinancialEntryStatus::Draft,
    ]);

    $this->actingAs($user)
        ->post("/financial-entries/{$entry->id}/post")
        ->assertRedirect();

    expect($entry->fresh()->status)->toBe(FinancialEntryStatus::Posted);
});

it('void creates reversing entry and profit calculator ignores voided original', function () {
    $user = seedFinanceUser([
        'financial_entries.view',
        'financial_entries.post',
    ]);
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);

    $entry = FinancialEntry::factory()->posted()->create([
        'organization_id' => $user->organization_id,
        'client_id' => $client->id,
        'entry_type' => FinancialEntryType::Revenue,
        'amount' => 200,
        'occurred_on' => now()->toDateString(),
    ]);

    $calculator = app(ProfitabilityCalculator::class);
    expect($calculator->forOrganization($user->organization_id)['revenue'])->toBe(200.0);

    $this->actingAs($user)
        ->post("/financial-entries/{$entry->id}/void")
        ->assertRedirect();

    $entry->refresh();
    expect($entry->status)->toBe(FinancialEntryStatus::Void);
    expect(FinancialEntry::query()->where('void_of_id', $entry->id)->exists())->toBeTrue();

    $totals = $calculator->forOrganization($user->organization_id);
    expect($totals['revenue'])->toBe(0.0);
    expect($totals['cost'])->toBe(0.0);
    expect($totals['profit'])->toBe(0.0);
});

it('calculates MRR from active recurring client services', function () {
    $user = seedFinanceUser(['finance_reports.view']);
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);
    $type = ServiceType::factory()->create(['organization_id' => $user->organization_id]);

    ClientService::factory()->create([
        'organization_id' => $user->organization_id,
        'client_id' => $client->id,
        'service_type_id' => $type->id,
        'billing_type' => BillingType::Recurring,
        'status' => ClientServiceStatus::Active,
        'recurring_revenue' => 99,
        'selling_price' => 99,
    ]);

    ClientService::factory()->create([
        'organization_id' => $user->organization_id,
        'client_id' => $client->id,
        'service_type_id' => $type->id,
        'billing_type' => BillingType::Recurring,
        'status' => ClientServiceStatus::Active,
        'recurring_revenue' => 0,
        'selling_price' => 50,
    ]);

    $mrr = app(MrrCalculator::class)->forOrganization($user->organization_id);
    expect($mrr)->toBe(149.0);
});

it('scopes financial entries to organization', function () {
    $user = seedFinanceUser(['financial_entries.view']);
    $otherOrg = Organization::factory()->create();

    FinancialEntry::factory()->create(['organization_id' => $user->organization_id]);
    FinancialEntry::factory()->create(['organization_id' => $otherOrg->id]);

    expect(FinancialEntry::query()->count())->toBe(1);
});

it('denies financial entry access without permission', function () {
    $user = seedFinanceUser(['dashboard.view']);

    $this->actingAs($user)
        ->get('/financial-entries')
        ->assertForbidden();
});

it('downloads an invoice as pdf', function () {
    $user = seedFinanceUser(['invoices.view', 'invoices.create']);
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->post('/invoices', [
            'client_id' => $client->id,
            'issue_date' => now()->toDateString(),
            'discount_type' => 'flat',
            'discount_value' => 0,
            'tax_amount' => 0,
            'lines' => [
                [
                    'description' => 'Managed hosting',
                    'quantity' => 1,
                    'unit_price' => 150,
                    'client_service_id' => null,
                ],
            ],
        ])
        ->assertRedirect();

    $invoice = \App\Domains\Finance\Models\Invoice::query()
        ->where('client_id', $client->id)
        ->latest()
        ->firstOrFail();

    $this->actingAs($user)
        ->get("/invoices/{$invoice->id}/pdf")
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('downloads a receipt only after invoice is paid', function () {
    $user = seedFinanceUser(['invoices.view', 'invoices.create', 'invoices.update']);
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->post('/invoices', [
            'client_id' => $client->id,
            'issue_date' => now()->toDateString(),
            'discount_type' => 'flat',
            'discount_value' => 0,
            'tax_amount' => 0,
            'lines' => [
                [
                    'description' => 'Managed hosting',
                    'quantity' => 1,
                    'unit_price' => 150,
                    'client_service_id' => null,
                ],
            ],
        ])
        ->assertRedirect();

    $invoice = \App\Domains\Finance\Models\Invoice::query()
        ->where('client_id', $client->id)
        ->latest()
        ->firstOrFail();

    $this->actingAs($user)
        ->get("/invoices/{$invoice->id}/receipt")
        ->assertRedirect();

    $this->actingAs($user)
        ->post("/invoices/{$invoice->id}/send")
        ->assertRedirect();

    $this->actingAs($user)
        ->post("/invoices/{$invoice->id}/pay")
        ->assertRedirect();

    $this->actingAs($user)
        ->get("/invoices/{$invoice->id}/receipt")
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('loads finance reports page for authorized users', function () {
    $user = seedFinanceUser(['finance_reports.view']);

    $this->actingAs($user)
        ->get('/finance/reports')
        ->assertOk();
});

it('denies finance reports without permission', function () {
    $user = seedFinanceUser(['dashboard.view']);

    $this->actingAs($user)
        ->get('/finance/reports')
        ->assertForbidden();
});

it('applies invoice discount when syncing totals', function () {
    $user = seedFinanceUser(['invoices.view', 'invoices.create', 'invoices.update']);
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);
    (new \App\Domains\Finance\Actions\SeedDefaultDiscountReasons)->handle($user->organization_id);
    $reason = \App\Domains\Finance\Models\DiscountReason::query()
        ->where('organization_id', $user->organization_id)
        ->where('code', 'loyalty')
        ->firstOrFail();

    $this->actingAs($user)
        ->post('/invoices', [
            'client_id' => $client->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'discount_type' => 'flat',
            'discount_value' => 25,
            'discount_reason_id' => $reason->id,
            'tax_amount' => 10,
            'notes' => 'With discount',
            'lines' => [
                [
                    'description' => 'Managed hosting',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'client_service_id' => null,
                ],
            ],
        ])
        ->assertRedirect();

    $invoice = \App\Domains\Finance\Models\Invoice::query()
        ->where('client_id', $client->id)
        ->latest()
        ->firstOrFail();

    expect((float) $invoice->subtotal)->toBe(100.0)
        ->and($invoice->discount_type->value)->toBe('flat')
        ->and((float) $invoice->discount_value)->toBe(25.0)
        ->and((float) $invoice->discount_amount)->toBe(25.0)
        ->and($invoice->discount_reason_id)->toBe($reason->id)
        ->and((float) $invoice->tax_amount)->toBe(10.0)
        ->and((float) $invoice->total)->toBe(85.0);
});

it('applies percentage invoice discounts', function () {
    $user = seedFinanceUser(['invoices.view', 'invoices.create', 'invoices.update']);
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);
    (new \App\Domains\Finance\Actions\SeedDefaultDiscountReasons)->handle($user->organization_id);
    $reason = \App\Domains\Finance\Models\DiscountReason::query()
        ->where('organization_id', $user->organization_id)
        ->where('code', 'volume')
        ->firstOrFail();

    $this->actingAs($user)
        ->post('/invoices', [
            'client_id' => $client->id,
            'issue_date' => now()->toDateString(),
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'discount_reason_id' => $reason->id,
            'tax_amount' => 0,
            'lines' => [
                [
                    'description' => 'Managed hosting',
                    'quantity' => 1,
                    'unit_price' => 200,
                    'client_service_id' => null,
                ],
            ],
        ])
        ->assertRedirect();

    $invoice = \App\Domains\Finance\Models\Invoice::query()
        ->where('client_id', $client->id)
        ->latest()
        ->firstOrFail();

    expect($invoice->discount_type->value)->toBe('percentage')
        ->and((float) $invoice->discount_value)->toBe(10.0)
        ->and((float) $invoice->discount_amount)->toBe(20.0)
        ->and((float) $invoice->total)->toBe(180.0);
});

it('requires a discount reason when discount amount is set', function () {
    $user = seedFinanceUser(['invoices.view', 'invoices.create', 'invoices.update']);
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->post('/invoices', [
            'client_id' => $client->id,
            'issue_date' => now()->toDateString(),
            'discount_type' => 'flat',
            'discount_value' => 10,
            'lines' => [
                [
                    'description' => 'Hosting',
                    'quantity' => 1,
                    'unit_price' => 50,
                    'client_service_id' => null,
                ],
            ],
        ])
        ->assertSessionHasErrors('discount_reason_id');
});

it('seeds standard discount reasons and allows custom types', function () {
    $user = seedFinanceUser(['invoices.view', 'invoices.create', 'invoices.update']);
    (new \App\Domains\Finance\Actions\SeedDefaultDiscountReasons)->handle($user->organization_id);

    $this->actingAs($user)
        ->get('/discount-reasons')
        ->assertOk();

    $this->actingAs($user)
        ->post('/discount-reasons', [
            'name' => 'Partner referral',
            'code' => 'partner_referral',
            'description' => 'Discount for partner-referred clients',
            'status' => 'active',
            'sort_order' => 80,
        ])
        ->assertRedirect('/discount-reasons');

    expect(\App\Domains\Finance\Models\DiscountReason::query()
        ->where('organization_id', $user->organization_id)
        ->where('code', 'partner_referral')
        ->exists())->toBeTrue();

    $system = \App\Domains\Finance\Models\DiscountReason::query()
        ->where('organization_id', $user->organization_id)
        ->where('code', 'loyalty')
        ->firstOrFail();

    $this->actingAs($user)
        ->delete("/discount-reasons/{$system->id}")
        ->assertForbidden();
});
