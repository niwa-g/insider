<?php

use App\Domains\Automation\Enums\AutomationRunStatus;
use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Automation\Models\AutomationRun;
use App\Domains\Automation\Models\AutomationSchedule;
use App\Domains\Automation\Services\RenewalScanService;
use App\Domains\Finance\Enums\RenewalStatus;
use App\Domains\Finance\Models\Renewal;
use App\Domains\Identity\Models\Organization;
use App\Domains\Operations\Models\OpsNotification;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function seedAutomationUser(array $permissions): User
{
    $org = Organization::factory()->create();

    $allAutomation = [
        'automation.view',
        'automation.create',
        'automation.update',
        'automation.delete',
        'automation.run',
    ];

    foreach (array_unique([...$permissions, ...$allAutomation, 'dashboard.view']) as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $role = Role::findOrCreate('Automation Tester', 'web');
    $role->syncPermissions($permissions);

    $adminRole = Role::findOrCreate('Administrator', 'web');
    $adminRole->givePermissionTo($allAutomation);

    $user = User::factory()->create([
        'organization_id' => $org->id,
        'email' => fake()->unique()->safeEmail(),
    ]);
    $user->assignRole($role);

    app()->instance('currentOrganizationId', $org->id);

    return $user;
}

it('lists automation rules for authorized users', function () {
    $user = seedAutomationUser(['automation.view']);

    AutomationRule::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->get('/automation/rules')
        ->assertOk();
});

it('denies automation rules list without permission', function () {
    $user = seedAutomationUser(['dashboard.view']);

    $this->actingAs($user)
        ->get('/automation/rules')
        ->assertForbidden();
});

it('creates automation rule', function () {
    $user = seedAutomationUser(['automation.view', 'automation.create']);

    $this->actingAs($user)
        ->post('/automation/rules', [
            'name' => 'Test renewal rule',
            'trigger' => 'renewal_expiring',
            'conditions' => ['days_ahead' => 7],
            'actions' => [['type' => 'notify_users', 'role' => 'Administrator']],
            'notify_in_app' => true,
            'is_enabled' => true,
        ])
        ->assertRedirect();

    expect(AutomationRule::query()->where('name', 'Test renewal rule')->exists())->toBeTrue();
});

it('run renewal scan creates run log and notifies when renewal within window', function () {
    $user = seedAutomationUser(['automation.view', 'automation.run']);

    AutomationRule::factory()->create([
        'organization_id' => $user->organization_id,
        'name' => 'Renewal notify',
        'trigger' => 'renewal_expiring',
        'conditions' => ['days_ahead' => 14],
        'actions' => [['type' => 'notify_users', 'role' => 'Administrator']],
        'is_enabled' => true,
    ]);

    $admin = User::factory()->create(['organization_id' => $user->organization_id]);
    $admin->assignRole(Role::findByName('Administrator', 'web'));

    Renewal::query()->create([
        'organization_id' => $user->organization_id,
        'title' => 'Expiring domain',
        'renewal_type' => 'domain',
        'renewal_date' => now()->addDays(10),
        'status' => RenewalStatus::Upcoming,
        'owner_id' => $admin->id,
    ]);

    app(RenewalScanService::class)->scanOrganization($user->organization_id);

    expect(AutomationRun::query()->where('organization_id', $user->organization_id)->exists())->toBeTrue();
    expect(OpsNotification::query()->where('organization_id', $user->organization_id)->count())->toBeGreaterThan(0);
});

it('toggle disable skips rule execution', function () {
    $user = seedAutomationUser(['automation.view', 'automation.update', 'automation.run']);

    $rule = AutomationRule::factory()->create([
        'organization_id' => $user->organization_id,
        'is_enabled' => true,
    ]);

    $this->actingAs($user)
        ->post("/automation/rules/{$rule->id}/toggle")
        ->assertRedirect();

    expect($rule->fresh()->is_enabled)->toBeFalse();

    $this->actingAs($user)
        ->post("/automation/rules/{$rule->id}/run")
        ->assertRedirect();

    $run = AutomationRun::query()->where('automation_rule_id', $rule->id)->latest()->first();
    expect($run)->not->toBeNull();
    expect($run->status)->toBe(AutomationRunStatus::Skipped);
});

it('scopes automation rules to organization', function () {
    $user = seedAutomationUser(['automation.view']);
    $otherOrg = Organization::factory()->create();

    AutomationRule::factory()->create(['organization_id' => $user->organization_id, 'name' => 'Mine']);
    AutomationRule::factory()->create(['organization_id' => $otherOrg->id, 'name' => 'Other']);

    $response = $this->actingAs($user)->get('/automation/rules');
    $response->assertOk();
    expect($response->getContent())->toContain('Mine');
    expect($response->getContent())->not->toContain('Other');
});

it('lists automation schedules for authorized users', function () {
    $user = seedAutomationUser(['automation.view']);

    AutomationSchedule::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->get('/automation/schedules')
        ->assertOk();
});

it('manual run schedule succeeds', function () {
    $user = seedAutomationUser(['automation.view', 'automation.run']);

    $schedule = AutomationSchedule::factory()->create([
        'organization_id' => $user->organization_id,
        'job_type' => 'scan_renewals',
        'is_enabled' => true,
    ]);

    $this->actingAs($user)
        ->post("/automation/schedules/{$schedule->id}/run")
        ->assertRedirect();

    expect(AutomationRun::query()->where('automation_schedule_id', $schedule->id)->exists())->toBeTrue();
});
