<?php

namespace App\Http\Controllers\Finance;

use App\Domains\Catalog\Models\ClientService;
use App\Domains\Crm\Models\Client;
use App\Domains\Finance\Actions\DeleteFinancialEntry;
use App\Domains\Finance\Actions\PostFinancialEntry;
use App\Domains\Finance\Actions\UpsertFinancialEntry;
use App\Domains\Finance\Actions\VoidFinancialEntry;
use App\Domains\Finance\Enums\FinancialEntryCategory;
use App\Domains\Finance\Enums\FinancialEntryStatus;
use App\Domains\Finance\Enums\FinancialEntryType;
use App\Domains\Finance\Models\FinancialEntry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertFinancialEntryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinancialEntryController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FinancialEntry::class);

        $entries = FinancialEntry::query()
            ->with(['client:id,company_name', 'clientService:id,name'])
            ->when($request->string('entry_type')->toString(), fn ($q, $t) => $q->where('entry_type', $t))
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when($request->string('client_id')->toString(), fn ($q, $id) => $q->where('client_id', $id))
            ->when($request->string('from')->toString(), fn ($q, $d) => $q->whereDate('occurred_on', '>=', $d))
            ->when($request->string('to')->toString(), fn ($q, $d) => $q->whereDate('occurred_on', '<=', $d))
            ->latest('occurred_on')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Finance/FinancialEntries/Index', [
            'entries' => $entries->through(fn (FinancialEntry $e) => [
                ...$e->toArray(),
                'entry_type' => $e->getRawOriginal('entry_type'),
                'status' => $e->getRawOriginal('status'),
                'category' => $e->getRawOriginal('category'),
            ]),
            'filters' => [
                'entry_type' => $request->string('entry_type')->toString(),
                'status' => $request->string('status')->toString(),
                'client_id' => $request->string('client_id')->toString(),
                'from' => $request->string('from')->toString(),
                'to' => $request->string('to')->toString(),
            ],
            'entryTypes' => FinancialEntryType::options(),
            'statuses' => FinancialEntryStatus::options(),
            'categories' => FinancialEntryCategory::options(),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'can' => [
                'create' => $request->user()?->can('financial_entries.create') ?? false,
                'update' => $request->user()?->can('financial_entries.update') ?? false,
                'delete' => $request->user()?->can('financial_entries.delete') ?? false,
                'post' => $request->user()?->can('financial_entries.post') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', FinancialEntry::class);

        return Inertia::render('Finance/FinancialEntries/Form', [
            'entry' => null,
            'entryTypes' => FinancialEntryType::options(),
            'categories' => FinancialEntryCategory::options(),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'clientServices' => ClientService::query()->orderBy('name')->get(['id', 'name', 'client_id']),
            'preselectedType' => $request->string('entry_type')->toString() ?: 'revenue',
            'preselectedClientId' => $request->string('client_id')->toString() ?: null,
        ]);
    }

    public function store(UpsertFinancialEntryRequest $request, UpsertFinancialEntry $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $data['created_by'] = $request->user()->id;
        $action->handle(null, $data);

        return redirect()->route('financial-entries.index', ['entry_type' => $data['entry_type']])
            ->with('success', 'Entry created.');
    }

    public function edit(FinancialEntry $financialEntry): Response
    {
        $this->authorize('update', $financialEntry);

        return Inertia::render('Finance/FinancialEntries/Form', [
            'entry' => [
                ...$financialEntry->toArray(),
                'entry_type' => $financialEntry->getRawOriginal('entry_type'),
                'category' => $financialEntry->getRawOriginal('category'),
                'status' => $financialEntry->getRawOriginal('status'),
            ],
            'entryTypes' => FinancialEntryType::options(),
            'categories' => FinancialEntryCategory::options(),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'clientServices' => ClientService::query()->orderBy('name')->get(['id', 'name', 'client_id']),
            'preselectedType' => null,
            'preselectedClientId' => null,
        ]);
    }

    public function update(UpsertFinancialEntryRequest $request, FinancialEntry $financialEntry, UpsertFinancialEntry $action): RedirectResponse
    {
        $action->handle($financialEntry, $request->validated());

        return redirect()->route('financial-entries.index', ['entry_type' => $financialEntry->getRawOriginal('entry_type')])
            ->with('success', 'Entry updated.');
    }

    public function destroy(FinancialEntry $financialEntry, DeleteFinancialEntry $action): RedirectResponse
    {
        $this->authorize('delete', $financialEntry);
        $type = $financialEntry->getRawOriginal('entry_type');
        $action->handle($financialEntry);

        return redirect()->route('financial-entries.index', ['entry_type' => $type])
            ->with('success', 'Entry deleted.');
    }

    public function post(FinancialEntry $financialEntry, PostFinancialEntry $action): RedirectResponse
    {
        $this->authorize('post', $financialEntry);
        $action->handle($financialEntry);

        return back()->with('success', 'Entry posted.');
    }

    public function void(FinancialEntry $financialEntry, VoidFinancialEntry $action): RedirectResponse
    {
        $this->authorize('void', $financialEntry);
        $action->handle($financialEntry);

        return back()->with('success', 'Entry voided.');
    }
}
