<?php

namespace App\Http\Controllers\Finance;

use App\Domains\Finance\Actions\UpsertVendor;
use App\Domains\Finance\Enums\VendorStatus;
use App\Domains\Finance\Models\Vendor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertVendorRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Vendor::class);

        return Inertia::render('Finance/Vendors/Index', [
            'vendors' => Vendor::query()
                ->withCount(['bills', 'clientServices'])
                ->orderBy('name')
                ->paginate(30)
                ->through(fn (Vendor $vendor) => [
                    ...$vendor->toArray(),
                    'status' => $vendor->getRawOriginal('status'),
                ]),
            'can' => [
                'create' => $request->user()?->can('create', Vendor::class) ?? false,
                'update' => $request->user()?->can('financial_entries.update') ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Vendor::class);

        return Inertia::render('Finance/Vendors/Form', [
            'vendor' => null,
            'statuses' => VendorStatus::options(),
        ]);
    }

    public function store(UpsertVendorRequest $request, UpsertVendor $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $action->handle(null, $data);

        return redirect()->route('vendors.index')->with('success', 'Vendor created.');
    }

    public function edit(Vendor $vendor): Response
    {
        $this->authorize('update', $vendor);

        return Inertia::render('Finance/Vendors/Form', [
            'vendor' => [
                ...$vendor->toArray(),
                'status' => $vendor->getRawOriginal('status'),
            ],
            'statuses' => VendorStatus::options(),
        ]);
    }

    public function update(UpsertVendorRequest $request, Vendor $vendor, UpsertVendor $action): RedirectResponse
    {
        $action->handle($vendor, $request->validated());

        return redirect()->route('vendors.index')->with('success', 'Vendor updated.');
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        $this->authorize('delete', $vendor);

        if ($vendor->bills()->exists() || $vendor->clientServices()->exists()) {
            return back()->withErrors([
                'vendor' => 'Vendor is in use by bills or client services. Deactivate it instead.',
            ]);
        }

        $vendor->delete();

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted.');
    }
}
