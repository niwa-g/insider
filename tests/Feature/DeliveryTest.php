<?php

use App\Domains\Crm\Models\Client;
use App\Domains\Delivery\Enums\ProjectHealthStatus;
use App\Domains\Delivery\Enums\TaskStatus;
use App\Domains\Delivery\Models\Project;
use App\Domains\Delivery\Models\Task;
use App\Domains\Identity\Models\Organization;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function seedDeliveryUser(string $role = 'Administrator'): User
{
    $org = Organization::factory()->create();

    foreach ([
        'dashboard.view',
        'projects.view',
        'projects.create',
        'projects.update',
        'projects.delete',
        'tasks.view',
        'tasks.create',
        'tasks.update',
        'tasks.delete',
        'project_templates.view',
        'project_templates.create',
        'project_templates.update',
        'project_templates.delete',
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

it('lists projects for authorized users', function () {
    $user = seedDeliveryUser();
    Project::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->get('/projects')
        ->assertOk();
});

it('creates a project', function () {
    $user = seedDeliveryUser();
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->post('/projects', [
            'client_id' => $client->id,
            'name' => 'Website Rebuild',
            'code' => 'WEB-01',
            'status' => 'planned',
            'priority' => 'medium',
            'start_date' => now()->toDateString(),
            'due_date' => now()->addWeeks(4)->toDateString(),
        ])
        ->assertRedirect();

    expect(Project::query()->where('code', 'WEB-01')->exists())->toBeTrue();
});

it('creates a task and syncs project health', function () {
    $user = seedDeliveryUser();
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);
    $project = Project::factory()->create([
        'organization_id' => $user->organization_id,
        'client_id' => $client->id,
        'health_score' => 100,
        'health_status' => ProjectHealthStatus::Healthy,
        'due_date' => now()->addMonth()->toDateString(),
    ]);

    $this->actingAs($user)
        ->post('/tasks', [
            'project_id' => $project->id,
            'title' => 'Overdue discovery',
            'status' => 'blocked',
            'priority' => 'high',
            'due_date' => now()->subDays(3)->toDateString(),
        ])
        ->assertRedirect();

    $task = Task::query()->where('title', 'Overdue discovery')->first();
    expect($task)->not->toBeNull()
        ->and($task->client_id)->toBe($client->id);

    $project->refresh();
    expect($project->health_score)->toBeLessThan(100)
        ->and($project->health_status)->not->toBe(ProjectHealthStatus::Healthy);
});

it('scopes projects to the current organization', function () {
    $user = seedDeliveryUser();
    $otherOrg = Organization::factory()->create();

    Project::factory()->create([
        'organization_id' => $user->organization_id,
        'name' => 'Mine',
    ]);

    $otherClient = Client::factory()->create(['organization_id' => $otherOrg->id]);
    Project::withoutGlobalScopes()->create([
        'organization_id' => $otherOrg->id,
        'client_id' => $otherClient->id,
        'name' => 'Theirs',
        'status' => 'active',
        'priority' => 'medium',
        'health_status' => 'healthy',
        'health_score' => 100,
    ]);

    expect(Project::query()->pluck('name')->all())->toBe(['Mine']);
});

it('marks a task status', function () {
    $user = seedDeliveryUser();
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);
    $project = Project::factory()->create([
        'organization_id' => $user->organization_id,
        'client_id' => $client->id,
    ]);
    $task = Task::factory()->create([
        'organization_id' => $user->organization_id,
        'client_id' => $client->id,
        'project_id' => $project->id,
        'status' => TaskStatus::Todo,
    ]);

    $this->actingAs($user)
        ->patch("/tasks/{$task->id}/status", [
            'status' => 'done',
        ])
        ->assertRedirect();

    expect($task->fresh()->status)->toBe(TaskStatus::Done)
        ->and($task->fresh()->completed_at)->not->toBeNull();
});

it('denies project create without permission', function () {
    $user = seedDeliveryUser();
    $user->syncRoles([]);
    $user->syncPermissions(['dashboard.view']);
    $client = Client::factory()->create(['organization_id' => $user->organization_id]);

    $this->actingAs($user)
        ->post('/projects', [
            'client_id' => $client->id,
            'name' => 'Blocked',
            'status' => 'planned',
            'priority' => 'medium',
        ])
        ->assertForbidden();
});
