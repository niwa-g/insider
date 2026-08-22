<?php

namespace Database\Factories\Crm;

use App\Domains\Crm\Enums\ContactStatus;
use App\Domains\Crm\Models\Client;
use App\Domains\Crm\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'name' => fake()->name(),
            'position' => fake()->jobTitle(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'department' => fake()->optional()->word(),
            'is_primary' => false,
            'is_billing' => false,
            'is_technical' => false,
            'status' => ContactStatus::Active,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => ['is_primary' => true]);
    }
}
