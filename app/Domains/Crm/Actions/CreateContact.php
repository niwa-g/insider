<?php

namespace App\Domains\Crm\Actions;

use App\Domains\Crm\Models\Contact;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class CreateContact
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Contact
    {
        return DB::transaction(function () use ($data) {
            if (! empty($data['is_primary']) && ! empty($data['client_id'])) {
                Contact::query()
                    ->where('client_id', $data['client_id'])
                    ->update(['is_primary' => false]);
            }

            $contact = Contact::query()->create($data);

            $this->auditLogger->log('contact.created', $contact, null, $contact->toArray());
            ActivityHelper::log('Contact created', $contact);

            return $contact->fresh('client');
        });
    }
}
