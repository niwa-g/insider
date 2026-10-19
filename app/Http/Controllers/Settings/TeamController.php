<?php

namespace App\Http\Controllers\Settings;

use App\Domains\Identity\Models\Department;
use App\Domains\Identity\Models\Team;
use App\Domains\Platform\Services\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreTeamRequest;
use App\Http\Requests\Settings\UpdateTeamRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('teams.view'), 403);

        $teams = Team::query()
            ->with(['department:id,name', 'users:id,name'])
            ->withCount('users')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Settings/Teams/Index', [
            'teams' => $teams,
            'can' => [
                'create' => $request->user()?->can('teams.create') ?? false,
                'update' => $request->user()?->can('teams.update') ?? false,
                'delete' => $request->user()?->can('teams.delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('teams.create'), 403);

        return Inertia::render('Settings/Teams/Form', [
            'team' => null,
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()
                ->where('organization_id', $request->user()->organization_id)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(StoreTeamRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();

        $team = Team::query()->create([
            'organization_id' => $request->user()->organization_id,
            'department_id' => ($data['department_id'] ?? null) ?: null,
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'status' => $data['status'],
        ]);

        $team->users()->sync($data['user_ids'] ?? []);

        $auditLogger->log('team.created', $team, null, $team->only(['name', 'code', 'status']));

        return redirect()
            ->route('settings.teams.index')
            ->with('success', 'Team created.');
    }

    public function edit(Request $request, Team $team): Response
    {
        abort_unless($request->user()?->can('teams.update'), 403);
        abort_unless($team->organization_id === $request->user()->organization_id, 404);

        $team->load('users:id');

        return Inertia::render('Settings/Teams/Form', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'code' => $team->code,
                'department_id' => $team->department_id,
                'status' => $team->status,
                'user_ids' => $team->users->pluck('id')->all(),
            ],
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()
                ->where('organization_id', $request->user()->organization_id)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(UpdateTeamRequest $request, Team $team, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($team->organization_id === $request->user()->organization_id, 404);

        $data = $request->validated();
        $old = $team->only(['name', 'code', 'status', 'department_id']);

        $team->update([
            'department_id' => ($data['department_id'] ?? null) ?: null,
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'status' => $data['status'],
        ]);

        $team->users()->sync($data['user_ids'] ?? []);

        $auditLogger->log('team.updated', $team, $old, $team->fresh()->only(['name', 'code', 'status', 'department_id']));

        return redirect()
            ->route('settings.teams.index')
            ->with('success', 'Team updated.');
    }

    public function destroy(Request $request, Team $team, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()?->can('teams.delete'), 403);
        abort_unless($team->organization_id === $request->user()->organization_id, 404);

        $auditLogger->log('team.deleted', $team, $team->only(['name', 'code']), null);
        $team->users()->detach();
        $team->delete();

        return redirect()
            ->route('settings.teams.index')
            ->with('success', 'Team deleted.');
    }

    /** @return array<string, string> */
    private function statuses(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ];
    }
}
