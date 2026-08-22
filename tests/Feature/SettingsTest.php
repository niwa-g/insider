<?php

use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\Team;
use App\Domains\Platform\Models\AuditLog;
use App\Domains\Platform\Services\AuditLogger;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function seedSettingsUser(array $permissions): User
{
    $org = Organization::factory()->create();

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $role = Role::findOrCreate('Settings Tester', 'web');
    $role->syncPermissions($permissions);

    $user = User::factory()->create([
        'organization_id' => $org->id,
    ]);
    $user->assignRole($role);

    app()->instance('currentOrganizationId', $org->id);

    return $user;
}

it('allows settings index with settings.view permission', function () {
    $user = seedSettingsUser(['settings.view', 'users.view', 'teams.view', 'roles.view', 'audit.view']);

    $this->actingAs($user)
        ->get('/settings')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Settings/Index')
            ->has('sections')
            ->has('can')
        );
});

it('denies settings index without settings.view', function () {
    $user = seedSettingsUser(['dashboard.view']);

    $this->actingAs($user)
        ->get('/settings')
        ->assertForbidden();
});

it('lists org users with users.view', function () {
    $user = seedSettingsUser(['users.view']);

    User::factory()->create([
        'organization_id' => $user->organization_id,
        'name' => 'Teammate',
    ]);

    $this->actingAs($user)
        ->get('/settings/users')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Settings/Users/Index')
            ->has('users.data', 2)
        );
});

it('lists teams with teams.view', function () {
    $user = seedSettingsUser(['teams.view']);

    Team::withoutGlobalScopes()->create([
        'organization_id' => $user->organization_id,
        'name' => 'Ops',
        'code' => 'OPS',
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->get('/settings/teams')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Settings/Teams/Index')
            ->has('teams.data', 1)
        );
});

it('lists roles with permission counts', function () {
    $user = seedSettingsUser(['roles.view']);

    $this->actingAs($user)
        ->get('/settings/roles')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Settings/Roles/Index')
            ->has('roles')
        );
});

it('paginates audit log for org', function () {
    $user = seedSettingsUser(['audit.view']);

    app(AuditLogger::class)->log('test.action', $user, null, ['ok' => true], $user->organization_id);

    $this->actingAs($user)
        ->get('/settings/audit')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Settings/Audit/Index')
            ->has('logs.data', 1)
        );

    expect(AuditLog::query()->where('organization_id', $user->organization_id)->count())->toBe(1);
});

it('updates user roles with users.update permission', function () {
    $user = seedSettingsUser(['users.view', 'users.update']);
    Permission::findOrCreate('dashboard.view', 'web');
    Role::findOrCreate('Developer', 'web')->givePermissionTo('dashboard.view');

    $target = User::factory()->create([
        'organization_id' => $user->organization_id,
    ]);

    $this->actingAs($user)
        ->patch("/settings/users/{$target->id}/roles", [
            'role_names' => ['Developer'],
        ])
        ->assertRedirect('/settings/users');

    expect($target->fresh()->hasRole('Developer'))->toBeTrue();
});
