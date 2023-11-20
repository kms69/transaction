<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Create a user
        $user = User::factory()->create();
        return [
            'amount' => $this->faker->randomFloat(2, 50, 500),
            'payer' => $this->faker->name,
            'due_on' => $this->faker->dateTimeBetween('+1 week', '+2 weeks')->format('Y-m-d'),
            'vat' => $this->faker->randomFloat(2, 5, 25),
            'is_vat_inclusive' => $this->faker->boolean,
            'status' => 'Outstanding',
            'user_id' => $user->id, // You may adjust this based on your application logic.
        ];
    }
}
