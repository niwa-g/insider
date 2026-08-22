<?php

namespace App\Domains\Integrations\Data;

readonly class ExternalResourceDTO
{
    /** @param  array<string, mixed>  $payload */
    public function __construct(
        public string $externalType,
        public string $externalId,
        public ?string $externalName = null,
        public array $payload = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'external_type' => $this->externalType,
            'external_id' => $this->externalId,
            'external_name' => $this->externalName,
            'payload' => $this->payload,
        ];
    }
}
