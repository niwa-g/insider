<?php

namespace App\Http\Controllers\Finance;

use App\Domains\Finance\Actions\MarkRenewalRenewed;
use App\Domains\Finance\Enums\RenewalStatus;
use App\Domains\Finance\Models\Renewal;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RenewalController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Renewal::class);

        $bucket = $request->string('bucket')->toString();

        $query = Renewal::query()
            ->with(['client:id,company_name', 'owner:id,name'])
            ->when($request->string('search')->toString(), function ($q, string $search) {
                $q->where('title', 'ilike', "%{$search}%");
            })
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status));

        if ($bucket === 'overdue') {
            $query->whereDate('renewal_date', '<', now()->toDateString())
                ->whereNotIn('status', [RenewalStatus::Renewed->value, RenewalStatus::Cancelled->value]);
        } elseif ($bucket === '7') {
            $query->whereBetween('renewal_date', [now()->toDateString(), now()->addDays(7)->toDateString()]);
        } elseif ($bucket === '14') {
            $query->whereBetween('renewal_date', [now()->toDateString(), now()->addDays(14)->toDateString()]);
        } elseif ($bucket === '30') {
            $query->whereBetween('renewal_date', [now()->toDateString(), now()->addDays(30)->toDateString()]);
        } elseif ($bucket === '60') {
            $query->whereBetween('renewal_date', [now()->toDateString(), now()->addDays(60)->toDateString()]);
        } elseif ($bucket === '90') {
            $query->whereBetween('renewal_date', [now()->toDateString(), now()->addDays(90)->toDateString()]);
        }

        $counts = [
            'overdue' => Renewal::query()->whereDate('renewal_date', '<', now()->toDateString())
                ->whereNotIn('status', [RenewalStatus::Renewed->value, RenewalStatus::Cancelled->value])->count(),
            '7' => Renewal::query()->whereBetween('renewal_date', [now()->toDateString(), now()->addDays(7)->toDateString()])->count(),
            '14' => Renewal::query()->whereBetween('renewal_date', [now()->toDateString(), now()->addDays(14)->toDateString()])->count(),
            '30' => Renewal::query()->whereBetween('renewal_date', [now()->toDateString(), now()->addDays(30)->toDateString()])->count(),
            '60' => Renewal::query()->whereBetween('renewal_date', [now()->toDateString(), now()->addDays(60)->toDateString()])->count(),
            '90' => Renewal::query()->whereBetween('renewal_date', [now()->toDateString(), now()->addDays(90)->toDateString()])->count(),
        ];

        return Inertia::render('Finance/Renewals/Index', [
            'renewals' => $query->orderBy('renewal_date')->paginate(20)->withQueryString(),
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'bucket' => $bucket,
            ],
            'statuses' => RenewalStatus::options(),
            'counts' => $counts,
            'can' => [
                'update' => $request->user()?->can('renewals.update') ?? false,
            ],
        ]);
    }

    public function renew(Request $request, Renewal $renewal, MarkRenewalRenewed $action): RedirectResponse
    {
        $this->authorize('update', $renewal);

        $result = $action->handle(
            $renewal,
            $request->string('notes')->toString() ?: null,
            $request->boolean('create_invoice'),
        );

        if ($result['invoice']) {
            return redirect()
                ->route('invoices.show', $result['invoice'])
                ->with('success', 'Renewal marked as renewed and draft invoice created.');
        }

        if ($result['vendor_bill']) {
            return redirect()
                ->route('vendor-bills.show', $result['vendor_bill'])
                ->with('success', 'Renewal marked as renewed. Vendor bill and cost entry created.');
        }

        return back()->with('success', 'Renewal marked as renewed.');
    }
}
