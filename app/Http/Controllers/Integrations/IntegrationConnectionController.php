<?php

namespace App\Http\Controllers\Integrations;

use App\Domains\Integrations\Actions\ConnectIntegration;
use App\Domains\Integrations\Actions\DisconnectIntegration;
use App\Domains\Integrations\Actions\SyncIntegration;
use App\Domains\Integrations\Actions\TestIntegrationConnection;
use App\Domains\Integrations\Models\Integration;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IntegrationConnectionController extends Controller
{
    public function test(Request $request, Integration $integration, TestIntegrationConnection $action): RedirectResponse
    {
        $this->authorize('update', $integration);

        $log = $action->handle($integration, $request->user());

        $message = $log->status->value === 'succeeded'
            ? 'Connection test succeeded.'
            : 'Connection test failed: '.$log->message;

        return back()->with($log->status->value === 'succeeded' ? 'success' : 'error', $message);
    }

    public function connect(Request $request, Integration $integration, ConnectIntegration $action): RedirectResponse
    {
        $this->authorize('update', $integration);

        $action->handle($integration, $request->user());

        return back()->with('success', 'Integration connected.');
    }

    public function disconnect(Request $request, Integration $integration, DisconnectIntegration $action): RedirectResponse
    {
        $this->authorize('update', $integration);

        $action->handle($integration, $request->user());

        return back()->with('success', 'Integration disconnected.');
    }

    public function sync(Request $request, Integration $integration, SyncIntegration $action): RedirectResponse
    {
        $this->authorize('sync', $integration);

        $log = $action->handle($integration, $request->user());

        $message = $log->status->value === 'succeeded'
            ? 'Sync completed.'
            : 'Sync failed: '.$log->message;

        return back()->with($log->status->value === 'succeeded' ? 'success' : 'error', $message);
    }
}
