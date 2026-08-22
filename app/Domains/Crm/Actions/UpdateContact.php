<?php

namespace App\Domains\Crm\Actions;

use App\Domains\Crm\Models\Contact;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class UpdateContact
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Contact $contact, array $data): Contact
    {
        return DB::transaction(function () use ($contact, $data) {
            if (! empty($data['is_primary'])) {
                Contact::query()
                    ->where('client_id', $contact->client_id)
                    ->whereKeyNot($contact->id)
                    ->update(['is_primary' => false]);
            }

            $old = $contact->toArray();
            $contact->update($data);

            $this->auditLogger->log('contact.updated', $contact, $old, $contact->fresh()->toArray());
            ActivityHelper::log('Contact updated', $contact);

            return $contact->fresh('client');
        });
    }
}
