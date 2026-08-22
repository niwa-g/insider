<?php

namespace App\Http\Controllers\Operations;

use App\Domains\Crm\Models\Client;
use App\Domains\Delivery\Models\Project;
use App\Domains\Operations\Actions\DeleteDocument;
use App\Domains\Operations\Actions\UpsertDocument;
use App\Domains\Operations\Enums\DocumentStatus;
use App\Domains\Operations\Enums\DocumentVisibility;
use App\Domains\Operations\Models\Document;
use App\Domains\Operations\Models\Ticket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\UpsertDocumentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Document::class);

        $documents = Document::query()
            ->with(['client:id,company_name', 'uploader:id,name'])
            ->when($request->string('search')->toString(), fn ($q, $s) => $q->where('title', 'ilike', "%{$s}%"))
            ->when($request->string('client_id')->toString(), fn ($q, $id) => $q->where('client_id', $id))
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Operations/Documents/Index', [
            'documents' => $documents,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'client_id' => $request->string('client_id')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'statuses' => DocumentStatus::options(),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'can' => [
                'create' => $request->user()?->can('documents.create') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Document::class);

        return Inertia::render('Operations/Documents/Form', [
            'document' => null,
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name', 'client_id']),
            'tickets' => Ticket::query()->orderByDesc('created_at')->limit(50)->get(['id', 'number', 'subject']),
            'visibilities' => DocumentVisibility::options(),
            'statuses' => DocumentStatus::options(),
        ]);
    }

    public function store(UpsertDocumentRequest $request, UpsertDocument $action): RedirectResponse
    {
        $data = $request->validated();
        unset($data['file']);
        $data['organization_id'] = $request->user()->organization_id;
        $data['uploaded_by'] = $request->user()->id;
        $data['disk'] = 'local';

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store("documents/{$request->user()->organization_id}", 'local');
            $data['path'] = $path;
            $data['original_name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getMimeType();
            $data['size_bytes'] = $file->getSize();
        }

        $document = $action->handle(null, $data);

        return redirect()->route('documents.index')->with('success', 'Document uploaded.');
    }

    public function edit(Request $request, Document $document): Response
    {
        $this->authorize('update', $document);

        return Inertia::render('Operations/Documents/Form', [
            'document' => [
                ...$document->toArray(),
                'visibility' => $document->getRawOriginal('visibility'),
                'status' => $document->getRawOriginal('status'),
            ],
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name', 'client_id']),
            'tickets' => Ticket::query()->orderByDesc('created_at')->limit(50)->get(['id', 'number', 'subject']),
            'visibilities' => DocumentVisibility::options(),
            'statuses' => DocumentStatus::options(),
        ]);
    }

    public function update(UpsertDocumentRequest $request, Document $document, UpsertDocument $action): RedirectResponse
    {
        $data = $request->validated();
        unset($data['file']);

        if ($request->hasFile('file')) {
            if ($document->path && Storage::disk($document->disk)->exists($document->path)) {
                Storage::disk($document->disk)->delete($document->path);
            }
            $file = $request->file('file');
            $data['path'] = $file->store("documents/{$request->user()->organization_id}", 'local');
            $data['original_name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getMimeType();
            $data['size_bytes'] = $file->getSize();
        }

        $action->handle($document, $data);

        return redirect()->route('documents.index')->with('success', 'Document updated.');
    }

    public function destroy(Document $document, DeleteDocument $action): RedirectResponse
    {
        $this->authorize('delete', $document);
        $action->handle($document);

        return redirect()->route('documents.index')->with('success', 'Document deleted.');
    }
}
