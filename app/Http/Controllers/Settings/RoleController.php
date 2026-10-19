<?php

namespace App\Http\Controllers\Settings;

use App\Domains\Platform\Services\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreRoleRequest;
use App\Http\Requests\Settings\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    private const PROTECTED_ROLES = ['Super Admin', 'Administrator'];

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('roles.view'), 403);

        $roles = Role::query()
            ->withCount('permissions')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Settings/Roles/Index', [
            'roles' => $roles->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => $role->permissions_count,
                'protected' => in_array($role->name, self::PROTECTED_ROLES, true),
            ]),
            'can' => [
                'create' => $request->user()?->can('roles.create') ?? false,
                'update' => $request->user()?->can('roles.update') ?? false,
                'delete' => $request->user()?->can('roles.delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('roles.create'), 403);

        return Inertia::render('Settings/Roles/Form', [
            'role' => null,
            'permissionGroups' => $this->permissionGroups(),
            'protected' => false,
        ]);
    }

    public function store(StoreRoleRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($data['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $auditLogger->log('role.created', $role, null, [
            'name' => $role->name,
            'permissions' => $data['permissions'] ?? [],
        ]);

        return redirect()
            ->route('settings.roles.index')
            ->with('success', 'Role created.');
    }

    public function edit(Request $request, Role $role): Response
    {
        abort_unless($request->user()?->can('roles.update'), 403);

        $role->load('permissions:id,name');

        return Inertia::render('Settings/Roles/Form', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->all(),
            ],
            'permissionGroups' => $this->permissionGroups(),
            'protected' => in_array($role->name, self::PROTECTED_ROLES, true),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();
        $protected = in_array($role->name, self::PROTECTED_ROLES, true);
        $old = [
            'name' => $role->name,
            'permissions' => $role->permissions()->pluck('name')->all(),
        ];

        if ($protected && $data['name'] !== $role->name) {
            return redirect()
                ->back()
                ->withErrors(['name' => 'Protected role names cannot be changed.']);
        }

        if (! $protected) {
            $role->name = $data['name'];
            $role->save();
        }

        $role->syncPermissions($data['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $auditLogger->log('role.updated', $role, $old, [
            'name' => $role->name,
            'permissions' => $data['permissions'] ?? [],
        ]);

        return redirect()
            ->route('settings.roles.index')
            ->with('success', 'Role updated.');
    }

    public function destroy(Request $request, Role $role, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()?->can('roles.delete'), 403);

        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return redirect()
                ->route('settings.roles.index')
                ->withErrors(['role' => 'Protected roles cannot be deleted.']);
        }

        $auditLogger->log('role.deleted', $role, ['name' => $role->name], null);
        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('settings.roles.index')
            ->with('success', 'Role deleted.');
    }

    /** @return array<string, list<string>> */
    private function permissionGroups(): array
    {
        return Permission::query()
            ->orderBy('name')
            ->pluck('name')
            ->groupBy(fn (string $name) => explode('.', $name)[0] ?? 'other')
            ->map(fn ($items) => $items->values()->all())
            ->sortKeys()
            ->all();
    }
}
