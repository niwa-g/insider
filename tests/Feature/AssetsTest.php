<?php

use App\Domains\Assets\Models\Application;
use App\Domains\Assets\Models\Domain;
use App\Domains\Assets\Models\Environment;
use App\Domains\Crm\Models\Client;
use App\Domains\Finance\Models\Renewal;
use App\Domains\Identity\Models\Organization;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function seedAssetsUser(array $permissions): User
{
    $org = Organization::factory()->create();

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $role = Role::findOrCreate('Assets Tester', 'web');
    $role->syncPermissions($permissions);

    $user = User::factory()->create([
        'organization_id' => $org->id,
        'email' => fake()->unique()->safeEmail(),
    ]);
    $user->assignRole($role);

    app()->instance('currentOrganizationId', $org->id);

    return $user;
}

it('lists applications for authorized users', function () {
    $user = seedAssetsUser([
        'dashboard.view',
        'applications.view',
        'applications.create',
        'applications.update',
    ]);

    Application::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->get('/applications')
        ->assertOk();
});

it('creates an application and environment', function () {
    $user = seedAssetsUser([
        'dashboard.view',
        'applications.view',
        'applications.create',
        'applications.update',
        'environments.view',
        'environments.create',
    ]);

    $client = Client::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->post('/applications', [
            'client_id' => $client->id,
            'name' => 'Ops Dashboard',
            'status' => 'active',
        ])
        ->assertRedirect();

    $application = Application::query()->where('name', 'Ops Dashboard')->first();
    expect($application)->not->toBeNull();

    $this->actingAs($user)
        ->post('/environments', [
            'application_id' => $application->id,
            'name' => 'Production',
            'slug' => 'production',
            'status' => 'active',
        ])
        ->assertRedirect();

    expect(Environment::query()->where('application_id', $application->id)->exists())->toBeTrue();
});

it('creates a domain and syncs renewal', function () {
    $user = seedAssetsUser([
        'dashboard.view',
        'domains.view',
        'domains.create',
        'domains.update',
    ]);

    $client = Client::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->post('/domains', [
            'client_id' => $client->id,
            'name' => 'renew-me.example',
            'expires_at' => now()->addMonth()->toDateString(),
            'auto_renew' => true,
            'status' => 'active',
        ])
        ->assertRedirect();

    $domain = Domain::query()->where('name', 'renew-me.example')->first();
    expect($domain)->not->toBeNull();

    expect(Renewal::query()
        ->where('renewable_type', Domain::class)
        ->where('renewable_id', $domain->id)
        ->where('renewal_type', 'domain')
        ->exists())->toBeTrue();
});

it('scopes domains to the current organization', function () {
    $user = seedAssetsUser(['dashboard.view', 'domains.view']);
    $otherOrg = Organization::factory()->create();

    Domain::factory()->create([
        'organization_id' => $user->organization_id,
        'name' => 'mine.example',
    ]);

    $otherClient = Client::factory()->create(['organization_id' => $otherOrg->id]);
    Domain::withoutGlobalScopes()->create([
        'organization_id' => $otherOrg->id,
        'client_id' => $otherClient->id,
        'name' => 'theirs.example',
        'status' => 'active',
    ]);

    expect(Domain::query()->pluck('name')->all())->toBe(['mine.example']);
});

it('denies application create without permission', function () {
    $user = seedAssetsUser(['dashboard.view', 'applications.view']);
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->post('/applications', [
            'client_id' => $client->id,
            'name' => 'Blocked',
            'status' => 'planned',
        ])
        ->assertForbidden();
});

it('loads the application relationships page', function () {
    $user = seedAssetsUser([
        'dashboard.view',
        'applications.view',
        'applications.create',
    ]);

    $application = Application::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->get("/applications/{$application->id}/relationships")
        ->assertOk();
});
