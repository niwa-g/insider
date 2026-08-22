<?php

namespace App\Http\Controllers\Settings;

use App\Domains\Identity\Models\Team;
use App\Http\Controllers\Controller;
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
        ]);
    }
}
