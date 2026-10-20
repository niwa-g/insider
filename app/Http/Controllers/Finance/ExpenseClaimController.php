<?php

namespace App\Http\Controllers\Finance;

use App\Domains\Catalog\Models\ClientService;
use App\Domains\Crm\Models\Client;
use App\Domains\Finance\Actions\PayExpenseClaim;
use App\Domains\Finance\Actions\ReviewExpenseClaim;
use App\Domains\Finance\Actions\UpsertExpenseClaim;
use App\Domains\Finance\Enums\ExpenseCategory;
use App\Domains\Finance\Enums\ExpenseClaimStatus;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Models\ExpenseClaim;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\PayExpenseClaimRequest;
use App\Http\Requests\Finance\RejectExpenseClaimRequest;
use App\Http\Requests\Finance\StoreExpenseClaimRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseClaimController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ExpenseClaim::class);

        $user = $request->user();
        $scope = $request->string('scope')->toString(); // mine | pending | all
        $canApprove = $user?->can('financial_entries.post') ?? false;

        if ($scope === '') {
            $scope = $canApprove ? 'pending' : 'mine';
        }

        $claims = ExpenseClaim::query()
            ->with(['claimant:id,name', 'approver:id,name', 'client:id,company_name'])
            ->when($scope === 'mine', fn ($q) => $q->where('claimant_id', $user->id))
            ->when($scope === 'pending', fn ($q) => $q->where('status', ExpenseClaimStatus::Pending))
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->latest('incurred_on')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Finance/ExpenseClaims/Index', [
            'claims' => $claims->through(fn (ExpenseClaim $claim) => [
                ...$claim->toArray(),
                'status' => $claim->getRawOriginal('status'),
                'category' => $claim->getRawOriginal('category'),
            ]),
            'filters' => [
                'scope' => $scope,
                'status' => $request->string('status')->toString(),
            ],
            'statuses' => ExpenseClaimStatus::options(),
            'can' => [
                'create' => $user?->can('create', ExpenseClaim::class) ?? false,
                'approve' => $canApprove,
                'pay' => $canApprove,
                'currentUserId' => $user->id,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', ExpenseClaim::class);

        return Inertia::render('Finance/ExpenseClaims/Form', [
            'categories' => ExpenseCategory::options(),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'clientServices' => ClientService::query()->orderBy('name')->get(['id', 'name', 'client_id']),
        ]);
    }

    public function store(StoreExpenseClaimRequest $request, UpsertExpenseClaim $action): RedirectResponse
    {
        $data = $request->safe()->except('receipt');
        $data['organization_id'] = $request->user()->organization_id;
        $data['claimant_id'] = $request->user()->id;

        $claim = $action->handle($data, $request->file('receipt'));

        return redirect()
            ->route('expense-claims.show', $claim)
            ->with('success', 'Expense claim submitted for approval.');
    }

    public function show(ExpenseClaim $expenseClaim): Response
    {
        $this->authorize('view', $expenseClaim);
        $expenseClaim->load([
            'claimant:id,name,email',
            'approver:id,name',
            'client:id,company_name',
            'clientService:id,name',
            'financialEntry:id,description,amount,status,occurred_on',
        ]);

        return Inertia::render('Finance/ExpenseClaims/Show', [
            'claim' => [
                ...$expenseClaim->toArray(),
                'status' => $expenseClaim->getRawOriginal('status'),
                'category' => $expenseClaim->getRawOriginal('category'),
                'receipt_url' => $expenseClaim->receiptUrl(),
            ],
            'paymentMethods' => PaymentMethod::options(),
            'can' => [
                'approve' => request()->user()?->can('approve', $expenseClaim) ?? false,
                'pay' => request()->user()?->can('pay', $expenseClaim) ?? false,
                'delete' => request()->user()?->can('delete', $expenseClaim) ?? false,
                'isClaimant' => request()->user()?->id === $expenseClaim->claimant_id,
            ],
        ]);
    }

    public function approve(ExpenseClaim $expenseClaim, ReviewExpenseClaim $action): RedirectResponse
    {
        $this->authorize('approve', $expenseClaim);

        try {
            $action->approve($expenseClaim);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['claim' => $e->getMessage()]);
        }

        return back()->with('success', 'Expense claim approved.');
    }

    public function reject(
        RejectExpenseClaimRequest $request,
        ExpenseClaim $expenseClaim,
        ReviewExpenseClaim $action,
    ): RedirectResponse {
        try {
            $action->reject($expenseClaim, $request->validated('rejection_reason'));
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['claim' => $e->getMessage()]);
        }

        return back()->with('success', 'Expense claim rejected.');
    }

    public function cancel(ExpenseClaim $expenseClaim, ReviewExpenseClaim $action): RedirectResponse
    {
        if ($expenseClaim->claimant_id !== request()->user()->id) {
            abort(403);
        }

        try {
            $action->cancel($expenseClaim);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['claim' => $e->getMessage()]);
        }

        return redirect()
            ->route('expense-claims.index', ['scope' => 'mine'])
            ->with('success', 'Expense claim cancelled.');
    }

    public function pay(
        PayExpenseClaimRequest $request,
        ExpenseClaim $expenseClaim,
        PayExpenseClaim $action,
    ): RedirectResponse {
        try {
            $action->handle($expenseClaim, $request->validated());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['claim' => $e->getMessage()]);
        }

        return back()->with('success', 'Reimbursement paid. Cost entry posted.');
    }

    public function destroy(ExpenseClaim $expenseClaim): RedirectResponse
    {
        $this->authorize('delete', $expenseClaim);
        $expenseClaim->delete();

        return redirect()
            ->route('expense-claims.index', ['scope' => 'mine'])
            ->with('success', 'Expense claim deleted.');
    }
}
