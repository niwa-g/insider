<?php

namespace App\Http\Controllers\Integrations;

use App\Domains\Integrations\Models\Integration;
use App\Domains\Integrations\Models\IntegrationSyncLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IntegrationSyncLogController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', IntegrationSyncLog::class);

        $logs = IntegrationSyncLog::query()
            ->with(['integration:id,name,provider', 'triggeredBy:id,name'])
            ->when($request->string('integration_id')->toString(), fn ($q, $id) => $q->where('integration_id', $id))
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Integrations/SyncLogs/Index', [
            'logs' => $logs,
            'filters' => ['integration_id' => $request->string('integration_id')->toString()],
            'integrations' => Integration::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
