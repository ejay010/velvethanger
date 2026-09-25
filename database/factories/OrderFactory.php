<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => 'pending',
            'total_amount' => fake()->numberBetween(2500, 20000),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_address' => fake()->streetAddress().', Nassau, Bahamas',
        ];
    }
}
