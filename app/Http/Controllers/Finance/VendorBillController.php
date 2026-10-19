<?php

namespace App\Http\Controllers\Finance;

use App\Domains\Catalog\Models\ClientService;
use App\Domains\Crm\Models\Client;
use App\Domains\Finance\Actions\RecordVendorBillPayment;
use App\Domains\Finance\Actions\UpsertVendorBill;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Enums\VendorBillStatus;
use App\Domains\Finance\Models\Vendor;
use App\Domains\Finance\Models\VendorBill;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\RecordVendorBillPaymentRequest;
use App\Http\Requests\Finance\UpsertVendorBillRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorBillController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', VendorBill::class);

        $bills = VendorBill::query()
            ->with(['vendor:id,name', 'client:id,company_name', 'clientService:id,name'])
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when($request->string('vendor_id')->toString(), fn ($q, $id) => $q->where('vendor_id', $id))
            ->latest('due_date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Finance/VendorBills/Index', [
            'bills' => $bills->through(fn (VendorBill $bill) => [
                ...$bill->toArray(),
                'status' => $bill->getRawOriginal('status'),
                'amount_paid' => $bill->amountPaid(),
                'balance_due' => $bill->balanceDue(),
            ]),
            'filters' => [
                'status' => $request->string('status')->toString(),
                'vendor_id' => $request->string('vendor_id')->toString(),
            ],
            'statuses' => VendorBillStatus::options(),
            'vendors' => Vendor::query()->orderBy('name')->get(['id', 'name']),
            'can' => [
                'create' => $request->user()?->can('create', VendorBill::class) ?? false,
                'update' => $request->user()?->can('financial_entries.update') ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', VendorBill::class);

        return Inertia::render('Finance/VendorBills/Form', [
            'bill' => null,
            'vendors' => Vendor::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'clientServices' => ClientService::query()->orderBy('name')->get(['id', 'name', 'client_id', 'vendor_id', 'cost']),
        ]);
    }

    public function store(UpsertVendorBillRequest $request, UpsertVendorBill $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $bill = $action->handle(null, $data);

        return redirect()->route('vendor-bills.show', $bill)->with('success', 'Vendor bill created.');
    }

    public function show(VendorBill $vendorBill): Response
    {
        $this->authorize('view', $vendorBill);
        $vendorBill->load([
            'vendor:id,name,email',
            'client:id,company_name',
            'clientService:id,name',
            'payments.recordedBy:id,name',
            'financialEntries',
        ]);

        return Inertia::render('Finance/VendorBills/Show', [
            'bill' => [
                ...$vendorBill->toArray(),
                'status' => $vendorBill->getRawOriginal('status'),
                'amount_paid' => $vendorBill->amountPaid(),
                'balance_due' => $vendorBill->balanceDue(),
                'payments' => $vendorBill->payments->map(fn ($p) => [
                    ...$p->toArray(),
                    'method' => $p->getRawOriginal('method'),
                ])->all(),
                'cost_entries' => $vendorBill->financialEntries->map(fn ($e) => [
                    'id' => $e->id,
                    'description' => $e->description,
                    'amount' => $e->amount,
                    'status' => $e->getRawOriginal('status'),
                    'occurred_on' => $e->occurred_on?->toDateString(),
                ])->all(),
            ],
            'paymentMethods' => PaymentMethod::options(),
            'can' => [
                'update' => request()->user()?->can('update', $vendorBill) ?? false,
            ],
        ]);
    }

    public function pay(
        RecordVendorBillPaymentRequest $request,
        VendorBill $vendorBill,
        RecordVendorBillPayment $action,
    ): RedirectResponse {
        try {
            $action->handle($vendorBill, $request->validated());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }

        return back()->with('success', 'Vendor payment recorded.');
    }

    public function payInFull(VendorBill $vendorBill, RecordVendorBillPayment $action): RedirectResponse
    {
        $this->authorize('update', $vendorBill);
        $balance = $vendorBill->balanceDue();
        if ($balance <= 0) {
            return back()->withErrors(['payment' => 'Bill has no remaining balance.']);
        }

        try {
            $action->handle($vendorBill, [
                'amount' => $balance,
                'method' => PaymentMethod::Other->value,
                'paid_on' => now()->toDateString(),
                'notes' => 'Marked paid in full',
            ]);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }

        return back()->with('success', 'Vendor bill paid in full.');
    }
}
