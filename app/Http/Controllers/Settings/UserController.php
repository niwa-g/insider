<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
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
            ->with('roles:id,name')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Settings/Users/Index', [
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'can' => [
                'update' => $request->user()?->can('users.update') ?? false,
            ],
        ]);
    }

    public function updateRoles(UpdateUserRolesRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->organization_id === $request->user()->organization_id, 404);

        $user->syncRoles($request->validated('role_names', []));

        return redirect()
            ->route('settings.users.index')
            ->with('success', 'User roles updated.');
    }
}
