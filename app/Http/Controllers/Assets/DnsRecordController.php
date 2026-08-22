<?php

namespace App\Http\Controllers\Assets;

use App\Domains\Assets\Actions\DeleteDnsRecord;
use App\Domains\Assets\Actions\UpsertDnsRecord;
use App\Domains\Assets\Enums\DnsRecordStatus;
use App\Domains\Assets\Enums\DnsRecordType;
use App\Domains\Assets\Models\DnsRecord;
use App\Domains\Assets\Models\Domain;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\UpsertDnsRecordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DnsRecordController extends Controller
{
    public function create(Request $request, Domain $domain): Response
    {
        $this->authorize('create', DnsRecord::class);

        return Inertia::render('Assets/DnsRecords/Form', [
            'dnsRecord' => null,
            'domain' => $domain->only(['id', 'name']),
            'types' => DnsRecordType::options(),
            'statuses' => DnsRecordStatus::options(),
        ]);
    }

    public function store(UpsertDnsRecordRequest $request, Domain $domain, UpsertDnsRecord $action): RedirectResponse
    {
        $data = $request->validated();
        $data['domain_id'] = $domain->id;
        $data['organization_id'] = $request->user()->organization_id;
        $action->handle(null, $data);

        return redirect()->route('domains.show', $domain)->with('success', 'DNS record created.');
    }

    public function edit(Domain $domain, DnsRecord $dnsRecord): Response
    {
        $this->authorize('update', $dnsRecord);

        return Inertia::render('Assets/DnsRecords/Form', [
            'dnsRecord' => [
                ...$dnsRecord->toArray(),
                'type' => $dnsRecord->getRawOriginal('type'),
                'status' => $dnsRecord->getRawOriginal('status'),
            ],
            'domain' => $domain->only(['id', 'name']),
            'types' => DnsRecordType::options(),
            'statuses' => DnsRecordStatus::options(),
        ]);
    }

    public function update(UpsertDnsRecordRequest $request, Domain $domain, DnsRecord $dnsRecord, UpsertDnsRecord $action): RedirectResponse
    {
        $action->handle($dnsRecord, $request->validated());

        return redirect()->route('domains.show', $domain)->with('success', 'DNS record updated.');
    }

    public function destroy(Domain $domain, DnsRecord $dnsRecord, DeleteDnsRecord $action): RedirectResponse
    {
        $this->authorize('delete', $dnsRecord);
        $action->handle($dnsRecord);

        return redirect()->route('domains.show', $domain)->with('success', 'DNS record deleted.');
    }
}
