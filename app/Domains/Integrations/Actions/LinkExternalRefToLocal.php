<?php

namespace App\Domains\Integrations\Actions;

use App\Domains\Integrations\Models\IntegrationExternalRef;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Database\Eloquent\Model;

class LinkExternalRefToLocal
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(IntegrationExternalRef $ref, Model $local): IntegrationExternalRef
    {
        $old = $ref->toArray();

        $ref->update([
            'local_type' => $local::class,
            'local_id' => $local->getKey(),
        ]);

        $this->auditLogger->log('integration_external_ref.linked', $ref, $old, $ref->fresh()->toArray());
        ActivityHelper::log('External ref linked', $ref);

        return $ref->fresh();
    }
}
