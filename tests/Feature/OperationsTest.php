<?php

use App\Domains\Crm\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Operations\Models\KnowledgeArticle;
use App\Domains\Operations\Models\SlaPolicy;
use App\Domains\Operations\Models\SlaTarget;
use App\Domains\Operations\Models\Ticket;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function seedOperationsUser(array $permissions): User
{
    $org = Organization::factory()->create();

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $role = Role::findOrCreate('Operations Tester', 'web');
    $role->syncPermissions($permissions);

    $user = User::factory()->create([
        'organization_id' => $org->id,
        'email' => fake()->unique()->safeEmail(),
    ]);
    $user->assignRole($role);

    app()->instance('currentOrganizationId', $org->id);

    return $user;
}

it('lists tickets for authorized users', function () {
    $user = seedOperationsUser(['dashboard.view', 'tickets.view', 'tickets.create']);

    Ticket::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->get('/tickets')
        ->assertOk();
});

it('creates a ticket and applies SLA deadlines', function () {
    $user = seedOperationsUser(['dashboard.view', 'tickets.view', 'tickets.create']);

    $client = Client::factory()->create(['organization_id' => $user->organization_id]);

    $policy = SlaPolicy::factory()->create([
        'organization_id' => $user->organization_id,
        'is_default' => true,
    ]);

    SlaTarget::query()->create([
        'sla_policy_id' => $policy->id,
        'priority' => 'high',
        'respond_within_minutes' => 60,
        'resolve_within_minutes' => 480,
    ]);

    $this->actingAs($user)
        ->post('/tickets', [
            'client_id' => $client->id,
            'subject' => 'Email outage',
            'status' => 'open',
            'priority' => 'high',
            'type' => 'incident',
        ])
        ->assertRedirect();

    $ticket = Ticket::query()->where('subject', 'Email outage')->first();
    expect($ticket)->not->toBeNull();
    expect($ticket->respond_by)->not->toBeNull();
    expect($ticket->resolve_by)->not->toBeNull();
    expect($ticket->number)->toMatch('/^TCK-\d{4}$/');
});

it('adds a comment to a ticket', function () {
    $user = seedOperationsUser(['dashboard.view', 'tickets.view', 'tickets.update']);

    $ticket = Ticket::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->post("/tickets/{$ticket->id}/comments", [
            'body' => 'Looking into this now.',
            'is_internal' => false,
        ])
        ->assertRedirect();

    expect($ticket->fresh()->comments()->count())->toBe(1);
    expect($ticket->fresh()->first_responded_at)->not->toBeNull();
});

it('scopes tickets to the current organization', function () {
    $user = seedOperationsUser(['dashboard.view', 'tickets.view']);
    $otherOrg = Organization::factory()->create();

    Ticket::factory()->create([
        'organization_id' => $user->organization_id,
        'subject' => 'Mine',
    ]);

    $otherClient = Client::factory()->create(['organization_id' => $otherOrg->id]);
    Ticket::withoutGlobalScopes()->create([
        'organization_id' => $otherOrg->id,
        'client_id' => $otherClient->id,
        'number' => 'TCK-9999',
        'subject' => 'Theirs',
        'status' => 'open',
        'priority' => 'low',
        'type' => 'request',
    ]);

    expect(Ticket::query()->pluck('subject')->all())->toBe(['Mine']);
});

it('denies ticket create without permission', function () {
    $user = seedOperationsUser(['dashboard.view', 'tickets.view']);
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->post('/tickets', [
            'client_id' => $client->id,
            'subject' => 'Blocked',
            'status' => 'open',
            'priority' => 'low',
            'type' => 'request',
        ])
        ->assertForbidden();
});

it('lists knowledge articles', function () {
    $user = seedOperationsUser(['dashboard.view', 'knowledge.view']);

    KnowledgeArticle::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->get('/knowledge-articles')
        ->assertOk();
});

it('loads the timeline for authorized users', function () {
    $user = seedOperationsUser(['dashboard.view', 'timeline.view']);

    $this->actingAs($user)
        ->get('/timeline')
        ->assertOk();
});
