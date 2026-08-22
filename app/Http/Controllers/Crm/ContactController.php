<?php

namespace App\Http\Controllers\Crm;

use App\Domains\Crm\Actions\CreateContact;
use App\Domains\Crm\Actions\DeleteContact;
use App\Domains\Crm\Actions\UpdateContact;
use App\Domains\Crm\Enums\ContactStatus;
use App\Domains\Crm\Models\Client;
use App\Domains\Crm\Models\Contact;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreContactRequest;
use App\Http\Requests\Crm\UpdateContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Contact::class);

        $contacts = Contact::query()
            ->with('client:id,company_name,client_code')
            ->when($request->string('search')->toString(), function ($query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            })
            ->when($request->string('client_id')->toString(), fn ($q, $id) => $q->where('client_id', $id))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Crm/Contacts/Index', [
            'contacts' => $contacts,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'client_id' => $request->string('client_id')->toString(),
            ],
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'can' => [
                'create' => $request->user()?->can('contacts.create') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Contact::class);

        return Inertia::render('Crm/Contacts/Form', [
            'contact' => null,
            'statuses' => ContactStatus::options(),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'preselectedClientId' => $request->string('client_id')->toString() ?: null,
        ]);
    }

    public function store(StoreContactRequest $request, CreateContact $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;

        $contact = $action->handle($data);

        return redirect()
            ->route('clients.show', $contact->client_id)
            ->with('success', 'Contact created.');
    }

    public function edit(Contact $contact): Response
    {
        $this->authorize('update', $contact);

        $payload = $contact->toArray();
        $payload['status'] = $contact->getRawOriginal('status');

        return Inertia::render('Crm/Contacts/Form', [
            'contact' => $payload,
            'statuses' => ContactStatus::options(),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'preselectedClientId' => $contact->client_id,
        ]);
    }

    public function update(UpdateContactRequest $request, Contact $contact, UpdateContact $action): RedirectResponse
    {
        $action->handle($contact, $request->validated());

        return redirect()
            ->route('clients.show', $contact->client_id)
            ->with('success', 'Contact updated.');
    }

    public function destroy(Contact $contact, DeleteContact $action): RedirectResponse
    {
        $this->authorize('delete', $contact);
        $clientId = $contact->client_id;
        $action->handle($contact);

        return redirect()
            ->route('clients.show', $clientId)
            ->with('success', 'Contact deleted.');
    }
}
