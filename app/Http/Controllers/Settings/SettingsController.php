<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('settings.view'), 403);

        $user = $request->user();

        return Inertia::render('Settings/Index', [
            'sections' => [
                [
                    'name' => 'Users',
                    'description' => 'Organization members, roles, and status',
                    'href' => '/settings/users',
                    'permission' => 'users.view',
                ],
                [
                    'name' => 'Teams',
                    'description' => 'Departments and team membership',
                    'href' => '/settings/teams',
                    'permission' => 'teams.view',
                ],
                [
                    'name' => 'Roles',
                    'description' => 'Role definitions and permission counts',
                    'href' => '/settings/roles',
                    'permission' => 'roles.view',
                ],
                [
                    'name' => 'Audit log',
                    'description' => 'Immutable record of sensitive actions',
                    'href' => '/settings/audit',
                    'permission' => 'audit.view',
                ],
            ],
            'can' => [
                'users' => $user?->can('users.view') ?? false,
                'teams' => $user?->can('teams.view') ?? false,
                'roles' => $user?->can('roles.view') ?? false,
                'audit' => $user?->can('audit.view') ?? false,
            ],
        ]);
    }
}
