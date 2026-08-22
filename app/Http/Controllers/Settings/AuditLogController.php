<?php

namespace App\Http\Controllers\Settings;

use App\Domains\Platform\Models\AuditLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('audit.view'), 403);

        $logs = AuditLog::query()
            ->where('organization_id', $request->user()->organization_id)
            ->with('user:id,name,email')
            ->when($request->string('search')->toString(), function ($q, string $search) {
                $q->where('action', 'ilike', "%{$search}%");
            })
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Settings/Audit/Index', [
            'logs' => $logs,
            'filters' => [
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }
}
