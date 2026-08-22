<?php

namespace App\Domains\Automation\Events;

use App\Domains\Operations\Models\Ticket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Ticket $ticket) {}
}
