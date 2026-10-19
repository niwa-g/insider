<?php

namespace App\Http\Controllers\Settings;

use App\Domains\Identity\Models\Team;
use App\Domains\Platform\Services\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreUserRequest;
use App\Http\Requests\Settings\UpdateUserRequest;
use App\Http\Requests\Settings\UpdateUserRolesRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('users.view'), 403);

        $users = User::query()
            ->where('organization_id', $request->user()->organization_id)
            ->with(['roles:id,name', 'teams:id,name'])
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Settings/Users/Index', [
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'can' => [
                'create' => $request->user()?->can('users.create') ?? false,
                'update' => $request->user()?->can('users.update') ?? false,
                'delete' => $request->user()?->can('users.delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('users.create'), 403);

        return Inertia::render('Settings/Users/Form', [
            'user' => null,
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'teams' => Team::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(StoreUserRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();
        $orgId = $request->user()->organization_id;

        $user = User::query()->create([
            'organization_id' => $orgId,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'status' => $data['status'],
            'must_reset_password' => true,
        ]);

        $user->syncRoles($data['role_names'] ?? []);
        $this->syncTeams($user, $data['team_ids'] ?? [], $orgId);

        $auditLogger->log('user.created', $user, null, [
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
            'roles' => $data['role_names'] ?? [],
        ], $orgId);

        return redirect()
            ->route('settings.users.index')
            ->with('success', 'User created.');
    }

    public function edit(Request $request, User $user): Response
    {
        abort_unless($request->user()?->can('users.update'), 403);
        abort_unless($user->organization_id === $request->user()->organization_id, 404);

        $user->load(['roles:id,name', 'teams:id,name']);

        return Inertia::render('Settings/Users/Form', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'role_names' => $user->roles->pluck('name')->all(),
                'team_ids' => $user->teams->pluck('id')->all(),
            ],
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'teams' => Team::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($user->organization_id === $request->user()->organization_id, 404);

        $data = $request->validated();
        $old = $user->only(['name', 'email', 'status']);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => $data['status'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
            $payload['must_reset_password'] = true;
        }

        $user->update($payload);
        $user->syncRoles($data['role_names'] ?? []);
        $this->syncTeams($user, $data['team_ids'] ?? [], $user->organization_id);

        $auditLogger->log('user.updated', $user, $old, $user->fresh()->only(['name', 'email', 'status']));

        return redirect()
            ->route('settings.users.index')
            ->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()?->can('users.delete'), 403);
        abort_unless($user->organization_id === $request->user()->organization_id, 404);
        abort_if($user->id === $request->user()->id, 422, 'You cannot delete your own account.');

        $auditLogger->log('user.deleted', $user, [
            'name' => $user->name,
            'email' => $user->email,
        ], null);

        $user->delete();

        return redirect()
            ->route('settings.users.index')
            ->with('success', 'User deleted.');
    }

    public function updateRoles(UpdateUserRolesRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->organization_id === $request->user()->organization_id, 404);

        $user->syncRoles($request->validated('role_names', []));

        return redirect()
            ->route('settings.users.index')
            ->with('success', 'User roles updated.');
    }

    /** @param  list<string>  $teamIds */
    private function syncTeams(User $user, array $teamIds, string $organizationId): void
    {
        $validIds = Team::query()
            ->where('organization_id', $organizationId)
            ->whereIn('id', $teamIds)
            ->pluck('id')
            ->all();

        $user->teams()->sync($validIds);
    }

    /** @return array<string, string> */
    private function statuses(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'suspended' => 'Suspended',
        ];
    }
}
