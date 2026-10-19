<?php

use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\Team;
use App\Domains\Platform\Models\AuditLog;
use App\Domains\Platform\Services\AuditLogger;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            ->has('can.organization')
            ->has('can.users')
        );
});

it('denies settings index without settings.view', function () {
    $user = seedSettingsUser(['dashboard.view']);

    $this->actingAs($user)
        ->get('/settings')
        ->assertForbidden();
});

it('shows and updates company profile', function () {
    Storage::fake('public');
    $user = seedSettingsUser(['settings.view']);

    $this->actingAs($user)
        ->get('/settings/organization')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Settings/Organization/Edit'));

    $this->actingAs($user)
        ->post('/settings/organization', [
            'name' => 'Cypherhost Ops',
            'legal_name' => 'Cypherhost Pte Ltd',
            'slug' => 'cypherhost-ops',
            'timezone' => 'Asia/Singapore',
            'currency' => 'USD',
            'email' => 'ops@example.com',
            'primary_color' => '#123456',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])
        ->assertRedirect('/settings/organization');

    $org = Organization::query()->findOrFail($user->organization_id);
    expect($org->name)->toBe('Cypherhost Ops')
        ->and($org->legal_name)->toBe('Cypherhost Pte Ltd')
        ->and($org->logo_path)->not->toBeNull();
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

it('creates updates and deletes users', function () {
    $user = seedSettingsUser(['users.view', 'users.create', 'users.update', 'users.delete']);
    Permission::findOrCreate('dashboard.view', 'web');
    Role::findOrCreate('Developer', 'web')->givePermissionTo('dashboard.view');

    $this->actingAs($user)
        ->post('/settings/users', [
            'name' => 'New Hire',
            'email' => 'newhire@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'status' => 'active',
            'role_names' => ['Developer'],
            'team_ids' => [],
        ])
        ->assertRedirect('/settings/users');

    $created = User::query()->where('email', 'newhire@example.com')->firstOrFail();
    expect($created->hasRole('Developer'))->toBeTrue();

    $this->actingAs($user)
        ->put("/settings/users/{$created->id}", [
            'name' => 'New Hire Updated',
            'email' => 'newhire@example.com',
            'status' => 'inactive',
            'role_names' => ['Developer'],
            'team_ids' => [],
        ])
        ->assertRedirect('/settings/users');

    expect($created->fresh()->name)->toBe('New Hire Updated')
        ->and($created->fresh()->status)->toBe('inactive');

    $this->actingAs($user)
        ->delete("/settings/users/{$created->id}")
        ->assertRedirect('/settings/users');

    expect(User::query()->where('email', 'newhire@example.com')->exists())->toBeFalse();
});

it('creates updates and deletes teams', function () {
    $user = seedSettingsUser(['teams.view', 'teams.create', 'teams.update', 'teams.delete']);

    $this->actingAs($user)
        ->post('/settings/teams', [
            'name' => 'Ops',
            'code' => 'OPS',
            'status' => 'active',
            'user_ids' => [$user->id],
        ])
        ->assertRedirect('/settings/teams');

    $team = Team::query()->where('code', 'OPS')->firstOrFail();
    expect($team->users()->where('users.id', $user->id)->exists())->toBeTrue();

    $this->actingAs($user)
        ->put("/settings/teams/{$team->id}", [
            'name' => 'Operations',
            'code' => 'OPS',
            'status' => 'active',
            'user_ids' => [],
        ])
        ->assertRedirect('/settings/teams');

    expect($team->fresh()->name)->toBe('Operations');

    $this->actingAs($user)
        ->delete("/settings/teams/{$team->id}")
        ->assertRedirect('/settings/teams');

    expect(Team::query()->whereKey($team->id)->exists())->toBeFalse();
});

it('creates updates and deletes roles with permission matrix', function () {
    $user = seedSettingsUser(['roles.view', 'roles.create', 'roles.update', 'roles.delete']);
    Permission::findOrCreate('dashboard.view', 'web');
    Permission::findOrCreate('clients.view', 'web');

    $this->actingAs($user)
        ->post('/settings/roles', [
            'name' => 'Support Lead',
            'permissions' => ['dashboard.view', 'clients.view'],
        ])
        ->assertRedirect('/settings/roles');

    $role = Role::findByName('Support Lead', 'web');
    expect($role->hasPermissionTo('clients.view'))->toBeTrue();

    $this->actingAs($user)
        ->put("/settings/roles/{$role->id}", [
            'name' => 'Support Lead',
            'permissions' => ['dashboard.view'],
        ])
        ->assertRedirect('/settings/roles');

    expect($role->fresh()->hasPermissionTo('clients.view'))->toBeFalse();

    $this->actingAs($user)
        ->delete("/settings/roles/{$role->id}")
        ->assertRedirect('/settings/roles');

    expect(Role::query()->where('name', 'Support Lead')->exists())->toBeFalse();
});

it('protects super admin role from deletion', function () {
    $user = seedSettingsUser(['roles.view', 'roles.delete']);
    $role = Role::findOrCreate('Super Admin', 'web');

    $this->actingAs($user)
        ->delete("/settings/roles/{$role->id}")
        ->assertRedirect('/settings/roles');

    expect(Role::query()->where('name', 'Super Admin')->exists())->toBeTrue();
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
