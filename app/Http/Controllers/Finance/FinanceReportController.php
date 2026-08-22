<?php

namespace App\Http\Controllers\Finance;

use App\Domains\Crm\Models\Client;
use App\Domains\Finance\Services\ProfitabilityCalculator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinanceReportController extends Controller
{
    public function index(Request $request, ProfitabilityCalculator $calculator): Response
    {
        abort_unless($request->user()?->can('finance_reports.view'), 403);

        $orgId = $request->user()->organization_id;
        $clientId = $request->string('client_id')->toString() ?: null;
        $from = $request->string('from')->toString() ?: now()->startOfYear()->toDateString();
        $to = $request->string('to')->toString() ?: now()->endOfMonth()->toDateString();

        $period = $calculator->forOrganization($orgId, $clientId, $from, $to);
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $monthly = $calculator->forOrganization($orgId, $clientId, $monthStart, $monthEnd);
        $ytdStart = now()->startOfYear()->toDateString();
        $ytd = $calculator->forOrganization($orgId, $clientId, $ytdStart, $to);

        return Inertia::render('Finance/Reports/Index', [
            'filters' => [
                'client_id' => $clientId ?? '',
                'from' => $from,
                'to' => $to,
            ],
            'mrr' => $calculator->mrr($orgId, $clientId),
            'period' => $period,
            'monthly' => $monthly,
            'ytd' => $ytd,
            'monthlyBreakdown' => $calculator->monthlyBreakdown($orgId, $clientId, $from, $to)->all(),
            'byClient' => $calculator->byClient($orgId, $from, $to),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
        ]);
    }
}
