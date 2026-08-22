<?php

namespace App\Domains\Operations\Actions;

use App\Domains\Operations\Models\Ticket;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteTicket
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Ticket $model): void
    {
        DB::transaction(function () use ($model) {
            $snapshot = $model->toArray();
            $model->comments()->delete();
            $model->delete();
            $this->auditLogger->log('ticket.deleted', $model, $snapshot, null);
            ActivityHelper::log('Ticket deleted', $model);
        });
    }
}
