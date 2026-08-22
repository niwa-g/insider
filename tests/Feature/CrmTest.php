<?php

use App\Domains\Crm\Models\Client;
use App\Domains\Crm\Models\Contact;
use App\Domains\Identity\Models\Organization;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function seedCrmUser(string $role = 'Administrator'): User
{
    $org = Organization::factory()->create();

    foreach ([
        'dashboard.view',
        'clients.view',
        'clients.create',
        'clients.update',
        'clients.delete',
        'contacts.view',
        'contacts.create',
        'contacts.update',
        'contacts.delete',
        'client_groups.view',
        'client_groups.create',
        'client_groups.update',
        'client_groups.delete',
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

it('lists clients for authorized users', function () {
    $user = seedCrmUser();
    Client::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->get('/clients')
        ->assertOk();
});

it('creates a client', function () {
    $user = seedCrmUser();

    $this->actingAs($user)
        ->post('/clients', [
            'company_name' => 'Acme Corp',
            'client_code' => 'ACM-100',
            'status' => 'active',
            'email' => 'hello@acme.example',
        ])
        ->assertRedirect();

    expect(Client::query()->where('client_code', 'ACM-100')->exists())->toBeTrue();
});

it('scopes clients to the current organization', function () {
    $user = seedCrmUser();
    $otherOrg = Organization::factory()->create();

    Client::factory()->create([
        'organization_id' => $user->organization_id,
        'company_name' => 'Mine',
    ]);
    Client::withoutGlobalScopes()->create([
        'organization_id' => $otherOrg->id,
        'company_name' => 'Theirs',
        'client_code' => 'OTH-1',
        'status' => 'active',
    ]);

    expect(Client::query()->pluck('company_name')->all())->toBe(['Mine']);
});

it('denies client create without permission', function () {
    $user = seedCrmUser();
    $user->syncRoles([]);
    $user->syncPermissions(['dashboard.view']);

    $this->actingAs($user)
        ->post('/clients', [
            'company_name' => 'Blocked',
            'client_code' => 'BLK-1',
            'status' => 'prospect',
        ])
        ->assertForbidden();
});

it('creates a contact for a client', function () {
    $user = seedCrmUser();
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->post('/contacts', [
            'client_id' => $client->id,
            'name' => 'Pat Lee',
            'email' => 'pat@example.com',
            'status' => 'active',
            'is_primary' => true,
            'is_billing' => false,
            'is_technical' => true,
        ])
        ->assertRedirect();

    expect(Contact::query()->where('email', 'pat@example.com')->where('is_primary', true)->exists())
        ->toBeTrue();
});

it('shows the client dashboard shell', function () {
    $user = seedCrmUser();
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->get("/clients/{$client->id}")
        ->assertOk();
});

it('includes related summary counts on client show', function () {
    $user = seedCrmUser();
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);

    \App\Domains\Delivery\Models\Project::factory()->create([
        'organization_id' => $user->organization_id,
        'client_id' => $client->id,
    ]);

    $this->actingAs($user)
        ->get("/clients/{$client->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('summary')
            ->has('related.projects', 1)
            ->where('summary.projects', 1)
            ->where('summary.contacts', 0)
        );
});
