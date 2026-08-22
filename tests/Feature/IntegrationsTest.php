<?php

use App\Domains\Identity\Models\Organization;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Integrations\Enums\SyncLogStatus;
use App\Domains\Integrations\Models\Integration;
use App\Domains\Integrations\Models\IntegrationExternalRef;
use App\Domains\Integrations\Models\IntegrationSyncLog;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function seedIntegrationsUser(array $permissions): User
{
    $org = Organization::factory()->create();

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $role = Role::findOrCreate('Integrations Tester', 'web');
    $role->syncPermissions($permissions);

    $user = User::factory()->create([
        'organization_id' => $org->id,
        'email' => fake()->unique()->safeEmail(),
    ]);
    $user->assignRole($role);

    app()->instance('currentOrganizationId', $org->id);

    return $user;
}

it('lists integrations for authorized users', function () {
    $user = seedIntegrationsUser(['integrations.view']);

    Integration::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->get('/integrations')
        ->assertOk();
});

it('denies integration list without permission', function () {
    $user = seedIntegrationsUser(['dashboard.view']);

    $this->actingAs($user)
        ->get('/integrations')
        ->assertForbidden();
});

it('creates integration with encrypted credentials and no secrets in response', function () {
    $user = seedIntegrationsUser([
        'integrations.view',
        'integrations.create',
    ]);

    $response = $this->actingAs($user)
        ->post('/integrations', [
            'name' => 'Test AWS',
            'provider' => 'aws',
            'category' => 'cloud',
            'config' => ['region' => 'us-west-2', 'account_id' => '999999999999'],
            'credentials' => [
                'access_key' => 'AKIATESTKEY123',
                'secret_key' => 'super-secret-value',
            ],
        ]);

    $response->assertRedirect();

    $integration = Integration::query()->where('name', 'Test AWS')->first();
    expect($integration)->not->toBeNull();
    expect($integration->credentials_encrypted)->not->toBeNull();
    expect($integration->credentials_encrypted)->not->toContain('super-secret-value');
    expect($integration->credentials_encrypted)->not->toContain('AKIATESTKEY123');

    $show = $this->actingAs($user)->get("/integrations/{$integration->id}");
    $show->assertOk();
    $content = $show->getContent();
    expect($content)->not->toContain('super-secret-value');
    expect($content)->not->toContain('AKIATESTKEY123');
    expect($content)->not->toContain('credentials_encrypted');
});

it('tests connection with fake adapter', function () {
    $user = seedIntegrationsUser([
        'integrations.view',
        'integrations.update',
    ]);

    $integration = Integration::factory()->create([
        'organization_id' => $user->organization_id,
        'provider' => 'aws',
        'category' => 'cloud',
    ]);

    $this->actingAs($user)
        ->post("/integrations/{$integration->id}/test")
        ->assertRedirect();

    expect(IntegrationSyncLog::query()->where('integration_id', $integration->id)->where('action', 'test')->exists())->toBeTrue();
});

it('sync creates external refs and succeeded sync log', function () {
    $user = seedIntegrationsUser([
        'integrations.view',
        'integrations.sync',
    ]);

    $integration = Integration::factory()->connected()->aws()->create([
        'organization_id' => $user->organization_id,
    ]);

    $this->actingAs($user)
        ->post("/integrations/{$integration->id}/sync")
        ->assertRedirect();

    expect(IntegrationExternalRef::query()->where('integration_id', $integration->id)->count())->toBeGreaterThan(0);

    $log = IntegrationSyncLog::query()
        ->where('integration_id', $integration->id)
        ->where('action', 'sync')
        ->latest('created_at')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->status)->toBe(SyncLogStatus::Succeeded);
    expect($integration->fresh()->status)->toBe(IntegrationStatus::Connected);
});

it('denies sync without permission', function () {
    $user = seedIntegrationsUser(['integrations.view']);

    $integration = Integration::factory()->connected()->create([
        'organization_id' => $user->organization_id,
    ]);

    $this->actingAs($user)
        ->post("/integrations/{$integration->id}/sync")
        ->assertForbidden();
});

it('scopes integrations to organization', function () {
    $user = seedIntegrationsUser(['integrations.view']);
    $otherOrg = Organization::factory()->create();

    $mine = Integration::factory()->create(['organization_id' => $user->organization_id, 'name' => 'Mine']);
    Integration::factory()->create(['organization_id' => $otherOrg->id, 'name' => 'Other Org']);

    $response = $this->actingAs($user)->get('/integrations');
    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain('Mine');
    expect($content)->not->toContain('Other Org');
});
