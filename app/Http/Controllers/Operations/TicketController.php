<?php

namespace App\Http\Controllers\Operations;

use App\Domains\Assets\Models\Application;
use App\Domains\Crm\Models\Client;
use App\Domains\Delivery\Models\Project;
use App\Domains\Operations\Actions\AddTicketComment;
use App\Domains\Operations\Actions\DeleteTicket;
use App\Domains\Operations\Actions\UpdateTicketStatus;
use App\Domains\Operations\Actions\UpsertTicket;
use App\Domains\Operations\Enums\TicketPriority;
use App\Domains\Operations\Enums\TicketStatus;
use App\Domains\Operations\Enums\TicketType;
use App\Domains\Operations\Models\SlaPolicy;
use App\Domains\Operations\Models\Ticket;
use App\Domains\Operations\Models\TicketComment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreTicketCommentRequest;
use App\Http\Requests\Operations\UpdateTicketStatusRequest;
use App\Http\Requests\Operations\UpsertTicketRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = Ticket::query()
            ->with(['client:id,company_name', 'assignee:id,name', 'slaPolicy:id,name'])
            ->when($request->string('search')->toString(), fn ($q, $s) => $q->where(function ($inner) use ($s) {
                $inner->where('subject', 'ilike', "%{$s}%")->orWhere('number', 'ilike', "%{$s}%");
            }))
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when($request->string('priority')->toString(), fn ($q, $p) => $q->where('priority', $p))
            ->when($request->string('client_id')->toString(), fn ($q, $id) => $q->where('client_id', $id))
            ->when($request->string('assignee_id')->toString(), fn ($q, $id) => $q->where('assignee_id', $id))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Operations/Tickets/Index', [
            'tickets' => $tickets,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'priority' => $request->string('priority')->toString(),
                'client_id' => $request->string('client_id')->toString(),
                'assignee_id' => $request->string('assignee_id')->toString(),
            ],
            'statuses' => TicketStatus::options(),
            'priorities' => TicketPriority::options(),
            'types' => TicketType::options(),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'assignees' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'can' => [
                'create' => $request->user()?->can('tickets.create') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Ticket::class);

        return Inertia::render('Operations/Tickets/Form', [
            'ticket' => null,
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name', 'client_id']),
            'applications' => Application::query()->orderBy('name')->get(['id', 'name', 'client_id']),
            'slaPolicies' => SlaPolicy::query()->orderBy('name')->get(['id', 'name', 'is_default']),
            'statuses' => TicketStatus::options(),
            'priorities' => TicketPriority::options(),
            'types' => TicketType::options(),
            'users' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'preselectedClientId' => $request->string('client_id')->toString() ?: null,
        ]);
    }

    public function store(UpsertTicketRequest $request, UpsertTicket $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $ticket = $action->handle(null, $data, $request->user());

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created.');
    }

    public function show(Request $request, Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'client:id,company_name,client_code',
            'project:id,name',
            'application:id,name',
            'slaPolicy:id,name',
            'requester:id,name',
            'assignee:id,name',
            'comments.user:id,name',
        ]);

        return Inertia::render('Operations/Tickets/Show', [
            'ticket' => [
                ...$ticket->toArray(),
                'status' => $ticket->getRawOriginal('status'),
                'priority' => $ticket->getRawOriginal('priority'),
                'type' => $ticket->getRawOriginal('type'),
                'comments' => $ticket->comments->map(fn ($c) => [
                    ...$c->toArray(),
                    'user' => $c->user,
                ]),
            ],
            'statuses' => TicketStatus::options(),
            'can' => [
                'update' => $request->user()?->can('update', $ticket) ?? false,
                'delete' => $request->user()?->can('delete', $ticket) ?? false,
                'comment' => $request->user()?->can('create', TicketComment::class) ?? false,
            ],
        ]);
    }

    public function edit(Request $request, Ticket $ticket): Response
    {
        $this->authorize('update', $ticket);

        return Inertia::render('Operations/Tickets/Form', [
            'ticket' => [
                ...$ticket->toArray(),
                'status' => $ticket->getRawOriginal('status'),
                'priority' => $ticket->getRawOriginal('priority'),
                'type' => $ticket->getRawOriginal('type'),
            ],
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name', 'client_id']),
            'applications' => Application::query()->orderBy('name')->get(['id', 'name', 'client_id']),
            'slaPolicies' => SlaPolicy::query()->orderBy('name')->get(['id', 'name', 'is_default']),
            'statuses' => TicketStatus::options(),
            'priorities' => TicketPriority::options(),
            'types' => TicketType::options(),
            'users' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'preselectedClientId' => $ticket->client_id,
        ]);
    }

    public function update(UpsertTicketRequest $request, Ticket $ticket, UpsertTicket $action): RedirectResponse
    {
        $action->handle($ticket, $request->validated(), $request->user());

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket updated.');
    }

    public function destroy(Ticket $ticket, DeleteTicket $action): RedirectResponse
    {
        $this->authorize('delete', $ticket);
        $action->handle($ticket);

        return redirect()->route('tickets.index')->with('success', 'Ticket deleted.');
    }

    public function updateStatus(UpdateTicketStatusRequest $request, Ticket $ticket, UpdateTicketStatus $action): RedirectResponse
    {
        $action->handle($ticket, $request->validated('status'), $request->user());

        return back()->with('success', 'Status updated.');
    }
}
