<?php

namespace App\Http\Controllers\Finance;

use App\Domains\Finance\Actions\DeleteDiscountReason;
use App\Domains\Finance\Actions\UpsertDiscountReason;
use App\Domains\Finance\Enums\DiscountReasonStatus;
use App\Domains\Finance\Models\DiscountReason;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertDiscountReasonRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiscountReasonController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', DiscountReason::class);

        return Inertia::render('Finance/DiscountReasons/Index', [
            'reasons' => DiscountReason::query()
                ->withCount('invoices')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(30)
                ->through(fn (DiscountReason $reason) => [
                    ...$reason->toArray(),
                    'status' => $reason->getRawOriginal('status'),
                ]),
            'can' => [
                'create' => $request->user()?->can('create', DiscountReason::class) ?? false,
                'update' => $request->user()?->can('invoices.update') ?? false,
                'delete' => $request->user()?->can('invoices.update') ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', DiscountReason::class);

        return Inertia::render('Finance/DiscountReasons/Form', [
            'reason' => null,
            'statuses' => DiscountReasonStatus::options(),
        ]);
    }

    public function store(UpsertDiscountReasonRequest $request, UpsertDiscountReason $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $data['sort_order'] = $data['sort_order'] ?? 50;
        $action->handle(null, $data);

        return redirect()->route('discount-reasons.index')->with('success', 'Discount reason created.');
    }

    public function edit(DiscountReason $discountReason): Response
    {
        $this->authorize('update', $discountReason);

        return Inertia::render('Finance/DiscountReasons/Form', [
            'reason' => [
                ...$discountReason->toArray(),
                'status' => $discountReason->getRawOriginal('status'),
            ],
            'statuses' => DiscountReasonStatus::options(),
        ]);
    }

    public function update(
        UpsertDiscountReasonRequest $request,
        DiscountReason $discountReason,
        UpsertDiscountReason $action,
    ): RedirectResponse {
        $action->handle($discountReason, $request->validated());

        return redirect()->route('discount-reasons.index')->with('success', 'Discount reason updated.');
    }

    public function destroy(DiscountReason $discountReason, DeleteDiscountReason $action): RedirectResponse
    {
        $this->authorize('delete', $discountReason);

        try {
            $action->handle($discountReason);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['reason' => $e->getMessage()]);
        }

        return redirect()->route('discount-reasons.index')->with('success', 'Discount reason deleted.');
    }
}
