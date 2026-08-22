<?php

use App\Domains\Identity\Models\Department;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\Team;
use App\Domains\Platform\Services\AuditLogger;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function seedOrgWorld(): array
{
    $orgA = Organization::query()->create([
        'name' => 'Org A',
        'slug' => 'org-a',
        'status' => 'active',
    ]);

    $orgB = Organization::query()->create([
        'name' => 'Org B',
        'slug' => 'org-b',
        'status' => 'active',
    ]);

    Permission::findOrCreate('dashboard.view', 'web');
    $role = Role::findOrCreate('Administrator', 'web');
    $role->givePermissionTo('dashboard.view');

    $userA = User::factory()->create([
        'organization_id' => $orgA->id,
        'email' => 'a@example.com',
    ]);
    $userA->assignRole($role);

    $userB = User::factory()->create([
        'organization_id' => $orgB->id,
        'email' => 'b@example.com',
    ]);
    $userB->assignRole($role);

    return compact('orgA', 'orgB', 'userA', 'userB');
}

it('redirects guests from the dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('allows authenticated users to view the dashboard', function () {
    ['userA' => $user] = seedOrgWorld();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

it('scopes departments to the current organization', function () {
    ['orgA' => $orgA, 'orgB' => $orgB, 'userA' => $userA] = seedOrgWorld();

    Department::withoutGlobalScopes()->create([
        'organization_id' => $orgA->id,
        'name' => 'Engineering A',
        'code' => 'ENG-A',
        'status' => 'active',
    ]);

    Department::withoutGlobalScopes()->create([
        'organization_id' => $orgB->id,
        'name' => 'Engineering B',
        'code' => 'ENG-B',
        'status' => 'active',
    ]);

    app()->instance('currentOrganizationId', $userA->organization_id);

    expect(Department::query()->count())->toBe(1)
        ->and(Department::query()->first()->code)->toBe('ENG-A');
});

it('denies users without permission via policy-style permission check', function () {
    ['userA' => $user] = seedOrgWorld();
    $user->syncRoles([]);
    $user->syncPermissions([]);

    expect($user->can('dashboard.view'))->toBeFalse();
});

it('writes immutable audit log entries', function () {
    ['userA' => $user, 'orgA' => $org] = seedOrgWorld();

    $this->actingAs($user);

    $team = Team::withoutGlobalScopes()->create([
        'organization_id' => $org->id,
        'name' => 'Core',
        'code' => 'CORE',
        'status' => 'active',
    ]);

    $log = app(AuditLogger::class)->log('team.created', $team, null, ['name' => 'Core'], $org->id);

    expect($log->exists)->toBeTrue()
        ->and($log->delete())->toBeFalse()
        ->and($log->update(['action' => 'hacked']))->toBeFalse()
        ->and($log->fresh()->action)->toBe('team.created');
});
