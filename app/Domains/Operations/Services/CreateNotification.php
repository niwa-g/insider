<?php

namespace App\Domains\Operations\Services;

use App\Domains\Operations\Models\OpsNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CreateNotification
{
    /** @param  array<string, mixed>  $data */
    public function handle(
        User $user,
        string $type,
        string $title,
        ?string $body = null,
        array $data = [],
        ?Model $notifiable = null,
    ): OpsNotification {
        return OpsNotification::query()->create([
            'organization_id' => $user->organization_id,
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data ?: null,
            'notifiable_type' => $notifiable ? $notifiable::class : null,
            'notifiable_id' => $notifiable?->getKey(),
        ]);
    }
}
