<?php

namespace App\Http\Controllers\Finance;

use App\Domains\Catalog\Models\ClientService;
use App\Domains\Crm\Models\Client;
use App\Domains\Finance\Actions\DeleteInvoice;
use App\Domains\Finance\Actions\UpdateInvoiceStatus;
use App\Domains\Finance\Actions\UpsertInvoice;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Finance\Models\Invoice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertInvoiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::query()
            ->with('client:id,company_name')
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when($request->string('client_id')->toString(), fn ($q, $id) => $q->where('client_id', $id))
            ->latest('issue_date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Finance/Invoices/Index', [
            'invoices' => $invoices->through(fn (Invoice $i) => [
                ...$i->toArray(),
                'status' => $i->getRawOriginal('status'),
            ]),
            'filters' => [
                'status' => $request->string('status')->toString(),
                'client_id' => $request->string('client_id')->toString(),
            ],
            'statuses' => InvoiceStatus::options(),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'can' => [
                'create' => $request->user()?->can('invoices.create') ?? false,
                'update' => $request->user()?->can('invoices.update') ?? false,
                'delete' => $request->user()?->can('invoices.delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Invoice::class);

        return Inertia::render('Finance/Invoices/Form', [
            'invoice' => null,
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'clientServices' => ClientService::query()->orderBy('name')->get(['id', 'name', 'client_id', 'selling_price']),
            'preselectedClientId' => $request->string('client_id')->toString() ?: null,
        ]);
    }

    public function store(UpsertInvoiceRequest $request, UpsertInvoice $action): RedirectResponse
    {
        $data = $request->validated();
        $lines = $data['lines'];
        unset($data['lines']);
        $data['organization_id'] = $request->user()->organization_id;
        $data['tax_amount'] = $data['tax_amount'] ?? 0;
        $invoice = $action->handle(null, $data, $lines);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created.');
    }

    public function show(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);
        $invoice->load(['client:id,company_name,client_code', 'lines.clientService:id,name']);

        return Inertia::render('Finance/Invoices/Show', [
            'invoice' => [
                ...$invoice->toArray(),
                'status' => $invoice->getRawOriginal('status'),
                'lines' => $invoice->lines->toArray(),
            ],
            'statuses' => InvoiceStatus::options(),
            'can' => [
                'update' => request()->user()?->can('update', $invoice) ?? false,
                'delete' => request()->user()?->can('delete', $invoice) ?? false,
            ],
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        $this->authorize('update', $invoice);
        $invoice->load('lines');

        return Inertia::render('Finance/Invoices/Form', [
            'invoice' => [
                ...$invoice->toArray(),
                'status' => $invoice->getRawOriginal('status'),
                'lines' => $invoice->lines->map(fn ($l) => [
                    'description' => $l->description,
                    'quantity' => $l->quantity,
                    'unit_price' => $l->unit_price,
                    'client_service_id' => $l->client_service_id,
                ])->all(),
            ],
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'clientServices' => ClientService::query()->orderBy('name')->get(['id', 'name', 'client_id', 'selling_price']),
            'preselectedClientId' => null,
        ]);
    }

    public function update(UpsertInvoiceRequest $request, Invoice $invoice, UpsertInvoice $action): RedirectResponse
    {
        $data = $request->validated();
        $lines = $data['lines'];
        unset($data['lines']);
        $data['tax_amount'] = $data['tax_amount'] ?? 0;
        $action->handle($invoice, $data, $lines);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice, DeleteInvoice $action): RedirectResponse
    {
        $this->authorize('delete', $invoice);
        $action->handle($invoice);

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted.');
    }

    public function send(Invoice $invoice, UpdateInvoiceStatus $action): RedirectResponse
    {
        $this->authorize('update', $invoice);
        $action->handle($invoice, InvoiceStatus::Sent);

        return back()->with('success', 'Invoice marked as sent.');
    }

    public function pay(Invoice $invoice, UpdateInvoiceStatus $action): RedirectResponse
    {
        $this->authorize('update', $invoice);
        $action->handle($invoice, InvoiceStatus::Paid);

        return back()->with('success', 'Invoice marked as paid.');
    }

    public function void(Invoice $invoice, UpdateInvoiceStatus $action): RedirectResponse
    {
        $this->authorize('update', $invoice);
        $action->handle($invoice, InvoiceStatus::Void);

        return back()->with('success', 'Invoice voided.');
    }
}
