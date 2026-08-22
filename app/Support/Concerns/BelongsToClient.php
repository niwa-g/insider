<?php

namespace App\Support\Concerns;

use App\Domains\Crm\Models\Client;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToClient
{
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
