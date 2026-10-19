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
                    'name' => 'Company profile',
                    'description' => 'Logo, branding, address, and locale settings',
                    'href' => '/settings/organization',
                    'permission' => 'settings.view',
                ],
                [
                    'name' => 'Users',
                    'description' => 'Invite members, assign roles, and manage access',
                    'href' => '/settings/users',
                    'permission' => 'users.view',
                ],
                [
                    'name' => 'Teams',
                    'description' => 'Departments, teams, and membership',
                    'href' => '/settings/teams',
                    'permission' => 'teams.view',
                ],
                [
                    'name' => 'Roles',
                    'description' => 'Role definitions and permission matrix',
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
                'organization' => $user?->can('settings.view') ?? false,
                'users' => $user?->can('users.view') ?? false,
                'teams' => $user?->can('teams.view') ?? false,
                'roles' => $user?->can('roles.view') ?? false,
                'audit' => $user?->can('audit.view') ?? false,
            ],
        ]);
    }
}
