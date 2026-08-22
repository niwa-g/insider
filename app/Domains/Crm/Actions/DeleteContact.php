<?php

namespace App\Domains\Crm\Actions;

use App\Domains\Crm\Models\Contact;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteContact
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Contact $contact): void
    {
        DB::transaction(function () use ($contact) {
            $snapshot = $contact->toArray();
            $contact->delete();

            $this->auditLogger->log('contact.deleted', $contact, $snapshot, null);
            ActivityHelper::log('Contact deleted', $contact, ['name' => $snapshot['name'] ?? null]);
        });
    }
}
