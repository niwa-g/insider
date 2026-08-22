<?php

namespace App\Http\Controllers\Operations;

use App\Domains\Operations\Models\Ticket;
use App\Domains\Operations\Models\TicketComment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreTicketCommentRequest;
use App\Domains\Operations\Actions\AddTicketComment;
use Illuminate\Http\RedirectResponse;

class TicketCommentController extends Controller
{
    public function store(StoreTicketCommentRequest $request, Ticket $ticket, AddTicketComment $action): RedirectResponse
    {
        $this->authorize('view', $ticket);
        $action->handle($ticket, $request->validated(), $request->user());

        return back()->with('success', 'Comment added.');
    }

    public function destroy(TicketComment $ticket_comment): RedirectResponse
    {
        $this->authorize('delete', $ticket_comment);
        $ticket_comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
