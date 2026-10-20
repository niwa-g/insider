<?php

namespace Database\Factories\Finance;

use App\Domains\Finance\Enums\ExpenseCategory;
use App\Domains\Finance\Enums\ExpenseClaimStatus;
use App\Domains\Finance\Models\ExpenseClaim;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ExpenseClaim> */
class ExpenseClaimFactory extends Factory
{
    protected $model = ExpenseClaim::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'category' => ExpenseCategory::Other,
            'amount' => fake()->randomFloat(2, 10, 500),
            'currency' => 'USD',
            'incurred_on' => now()->toDateString(),
            'status' => ExpenseClaimStatus::Pending,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => ExpenseClaimStatus::Pending]);
    }

    public function approved(): static
    {
        return $this->state([
            'status' => ExpenseClaimStatus::Approved,
            'approved_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state([
            'status' => ExpenseClaimStatus::Paid,
            'approved_at' => now()->subDay(),
            'paid_on' => now()->toDateString(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => ExpenseClaimStatus::Rejected,
            'rejection_reason' => 'Insufficient documentation',
        ]);
    }
}
