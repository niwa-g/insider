<?php

namespace Database\Factories\Assets;

use App\Domains\Assets\Enums\ServerRole;
use App\Domains\Assets\Enums\ServerStatus;
use App\Domains\Assets\Models\Server;
use App\Domains\Crm\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Server> */
class ServerFactory extends Factory
{
    protected $model = Server::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'name' => fake()->words(2, true).' Server',
            'hostname' => fake()->domainName(),
            'ip_address' => fake()->ipv4(),
            'role' => ServerRole::Web,
            'os' => 'Ubuntu 22.04',
            'status' => ServerStatus::Active,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Server $server): void {
            if (! $server->organization_id && $server->client_id) {
                $server->organization_id = Client::query()->find($server->client_id)?->organization_id;
            }
        });
    }
}
