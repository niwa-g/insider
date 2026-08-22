<?php

namespace App\Http\Controllers\Assets;

use App\Domains\Assets\Actions\DeleteSslCertificate;
use App\Domains\Assets\Actions\UpsertSslCertificate;
use App\Domains\Assets\Enums\SslCertificateStatus;
use App\Domains\Assets\Models\Domain;
use App\Domains\Assets\Models\SslCertificate;
use App\Domains\Crm\Models\Client;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\UpsertSslCertificateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SslCertificateController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', SslCertificate::class);

        $certificates = SslCertificate::query()
            ->with(['client:id,company_name', 'domain:id,name'])
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('expires_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Assets/SslCertificates/Index', [
            'certificates' => $certificates,
            'filters' => ['status' => $request->string('status')->toString()],
            'statuses' => SslCertificateStatus::options(),
            'can' => ['create' => $request->user()?->can('domains.create') ?? false],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', SslCertificate::class);

        return Inertia::render('Assets/SslCertificates/Form', [
            'certificate' => null,
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'domains' => Domain::query()->orderBy('name')->get(['id', 'name', 'client_id']),
            'statuses' => SslCertificateStatus::options(),
            'preselectedClientId' => $request->string('client_id')->toString() ?: null,
        ]);
    }

    public function store(UpsertSslCertificateRequest $request, UpsertSslCertificate $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $action->handle(null, $data);

        return redirect()->route('ssl-certificates.index')->with('success', 'SSL certificate created.');
    }

    public function edit(SslCertificate $sslCertificate): Response
    {
        $this->authorize('update', $sslCertificate);

        return Inertia::render('Assets/SslCertificates/Form', [
            'certificate' => [
                ...$sslCertificate->toArray(),
                'status' => $sslCertificate->getRawOriginal('status'),
                'issued_at' => $sslCertificate->issued_at?->toDateString(),
                'expires_at' => $sslCertificate->expires_at?->toDateString(),
            ],
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'domains' => Domain::query()->orderBy('name')->get(['id', 'name', 'client_id']),
            'statuses' => SslCertificateStatus::options(),
            'preselectedClientId' => $sslCertificate->client_id,
        ]);
    }

    public function update(UpsertSslCertificateRequest $request, SslCertificate $sslCertificate, UpsertSslCertificate $action): RedirectResponse
    {
        $action->handle($sslCertificate, $request->validated());

        return redirect()->route('ssl-certificates.index')->with('success', 'SSL certificate updated.');
    }

    public function destroy(SslCertificate $sslCertificate, DeleteSslCertificate $action): RedirectResponse
    {
        $this->authorize('delete', $sslCertificate);
        $action->handle($sslCertificate);

        return redirect()->route('ssl-certificates.index')->with('success', 'SSL certificate deleted.');
    }
}
