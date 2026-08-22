<?php

use App\Domains\Crm\Enums\ClientStatus;
use App\Domains\Crm\Models\Client;
use App\Domains\Delivery\Models\Project;
use App\Domains\Finance\Enums\FinancialEntryStatus;
use App\Domains\Finance\Enums\FinancialEntryType;
use App\Domains\Finance\Models\FinancialEntry;
use App\Domains\Identity\Models\Organization;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function seedDashboardUser(): User
{
    $org = Organization::factory()->create();

    $permissions = [
        'dashboard.view',
        'clients.view',
        'projects.view',
        'financial_entries.view',
        'financial_entries.post',
        'renewals.view',
        'tickets.view',
        'tasks.view',
    ];

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $role = Role::findOrCreate('Administrator', 'web');
    $role->syncPermissions(Permission::all());

    $user = User::factory()->create([
        'organization_id' => $org->id,
    ]);
    $user->assignRole($role);

    app()->instance('currentOrganizationId', $org->id);

    return $user;
}

it('returns operational dashboard structure for admin', function () {
    $user = seedDashboardUser();

    Client::factory()->create([
        'organization_id' => $user->organization_id,
        'status' => ClientStatus::Active,
    ]);

    Project::factory()->create([
        'organization_id' => $user->organization_id,
    ]);

    FinancialEntry::factory()->create([
        'organization_id' => $user->organization_id,
        'entry_type' => FinancialEntryType::Revenue,
        'status' => FinancialEntryStatus::Posted,
        'amount' => 500,
        'occurred_on' => now()->startOfMonth(),
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Index')
            ->has('kpis', fn ($kpis) => $kpis
                ->where('clients', 1)
                ->where('active_clients', 1)
                ->etc()
            )
            ->has('attention.renewals')
            ->has('attention.overdue_tasks')
            ->has('attention.at_risk_projects')
            ->has('attention.sla_breaches')
            ->has('recentAudit')
        );
});

it('computes non-zero monthly revenue on dashboard', function () {
    $user = seedDashboardUser();

    FinancialEntry::factory()->create([
        'organization_id' => $user->organization_id,
        'entry_type' => FinancialEntryType::Revenue,
        'status' => FinancialEntryStatus::Posted,
        'amount' => 1200,
        'occurred_on' => now(),
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->where('kpis.monthly_revenue', 1200)
        );
});
