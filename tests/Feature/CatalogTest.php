<?php

use App\Domains\Catalog\Models\ClientService;
use App\Domains\Catalog\Models\ServiceCategory;
use App\Domains\Catalog\Models\ServiceType;
use App\Domains\Crm\Models\Client;
use App\Domains\Finance\Enums\RenewalStatus;
use App\Domains\Finance\Models\Renewal;
use App\Domains\Identity\Models\Organization;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function seedCatalogUser(string $role = 'Administrator'): User
{
    $org = Organization::factory()->create();

    foreach ([
        'dashboard.view',
        'service_catalog.view',
        'service_catalog.create',
        'service_catalog.update',
        'service_catalog.delete',
        'client_services.view',
        'client_services.create',
        'client_services.update',
        'client_services.delete',
        'renewals.view',
        'renewals.update',
    ] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $roleModel = Role::findOrCreate($role, 'web');
    $roleModel->syncPermissions(Permission::all());

    $user = User::factory()->create([
        'organization_id' => $org->id,
        'email' => fake()->unique()->safeEmail(),
    ]);
    $user->assignRole($roleModel);

    app()->instance('currentOrganizationId', $org->id);

    return $user;
}

it('lists service categories for authorized users', function () {
    $user = seedCatalogUser();
    ServiceCategory::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->get('/service-categories')
        ->assertOk();
});

it('creates a service type in the catalog', function () {
    $user = seedCatalogUser();
    $category = ServiceCategory::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->post('/service-types', [
            'service_category_id' => $category->id,
            'name' => 'Managed VPS',
            'code' => 'VPS-MGD',
            'billing_type' => 'recurring',
            'billing_cycle' => 'monthly',
            'default_cost' => 40,
            'default_selling_price' => 99,
            'status' => 'active',
        ])
        ->assertRedirect();

    expect(ServiceType::query()->where('code', 'VPS-MGD')->exists())->toBeTrue();
});

it('assigns a client service and syncs a renewal', function () {
    $user = seedCatalogUser();
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);
    $type = ServiceType::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->post('/client-services', [
            'client_id' => $client->id,
            'service_type_id' => $type->id,
            'name' => 'Prod VPS',
            'billing_type' => 'recurring',
            'billing_cycle' => 'monthly',
            'cost' => 40,
            'selling_price' => 99,
            'renewal_date' => now()->addDays(15)->toDateString(),
            'status' => 'active',
            'account_manager_id' => $user->id,
        ])
        ->assertRedirect();

    $service = ClientService::query()->where('name', 'Prod VPS')->first();
    expect($service)->not->toBeNull();

    $renewal = Renewal::query()
        ->where('renewable_type', ClientService::class)
        ->where('renewable_id', $service->id)
        ->first();

    expect($renewal)->not->toBeNull()
        ->and($renewal->title)->toBe('Prod VPS')
        ->and($renewal->status)->not->toBe(RenewalStatus::Cancelled);
});

it('scopes client services to the current organization', function () {
    $user = seedCatalogUser();
    $otherOrg = Organization::factory()->create();

    ClientService::factory()->create([
        'organization_id' => $user->organization_id,
        'name' => 'Mine',
    ]);

    $otherClient = Client::factory()->create(['organization_id' => $otherOrg->id]);
    $otherCategory = ServiceCategory::factory()->create(['organization_id' => $otherOrg->id]);
    $otherType = ServiceType::factory()->create([
        'organization_id' => $otherOrg->id,
        'service_category_id' => $otherCategory->id,
    ]);

    ClientService::withoutGlobalScopes()->create([
        'organization_id' => $otherOrg->id,
        'client_id' => $otherClient->id,
        'service_type_id' => $otherType->id,
        'name' => 'Theirs',
        'billing_type' => 'recurring',
        'billing_cycle' => 'monthly',
        'cost' => 10,
        'selling_price' => 20,
        'recurring_revenue' => 20,
        'status' => 'active',
    ]);

    expect(ClientService::query()->pluck('name')->all())->toBe(['Mine']);
});

it('marks a renewal as renewed and advances the next date', function () {
    $user = seedCatalogUser();
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);
    $type = ServiceType::factory()->create(['organization_id' => $user->organization_id]);
    $renewalDate = now()->addDays(5)->toDateString();

    $service = ClientService::factory()->create([
        'organization_id' => $user->organization_id,
        'client_id' => $client->id,
        'service_type_id' => $type->id,
        'renewal_date' => $renewalDate,
        'billing_cycle' => 'monthly',
        'status' => 'active',
    ]);

    app(\App\Domains\Finance\Actions\SyncRenewalFromClientService::class)->handle($service);
    $renewal = Renewal::query()->where('renewable_id', $service->id)->firstOrFail();

    $this->actingAs($user)
        ->post("/renewals/{$renewal->id}/renew")
        ->assertRedirect();

    $service->refresh();
    expect($service->renewal_date?->toDateString())
        ->toBe(\Carbon\Carbon::parse($renewalDate)->addMonth()->toDateString());
});

it('denies catalog create without permission', function () {
    $user = seedCatalogUser();
    $user->syncRoles([]);
    $user->syncPermissions(['dashboard.view']);

    $this->actingAs($user)
        ->post('/service-categories', [
            'name' => 'Blocked',
            'status' => 'active',
        ])
        ->assertForbidden();
});
