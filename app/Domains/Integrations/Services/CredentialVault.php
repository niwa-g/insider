<?php

namespace App\Domains\Integrations\Services;

use App\Domains\Integrations\Models\Integration;
use Illuminate\Support\Facades\Crypt;

class CredentialVault
{
    /** @param  array<string, mixed>  $credentials */
    public function store(Integration $integration, array $credentials): void
    {
        if ($credentials === []) {
            return;
        }

        $integration->credentials_encrypted = Crypt::encryptString(json_encode($credentials));
        $integration->save();
    }

    /** @return array<string, mixed> */
    public function decrypt(Integration $integration): array
    {
        if (! $integration->credentials_encrypted) {
            return [];
        }

        $decoded = json_decode(Crypt::decryptString($integration->credentials_encrypted), true);

        return is_array($decoded) ? $decoded : [];
    }

    /** @return array<string, mixed> */
    public function toPublicArray(Integration $integration): array
    {
        return $integration->toPublicArray();
    }

    public function hasCredentials(Integration $integration): bool
    {
        return filled($integration->credentials_encrypted);
    }
}
