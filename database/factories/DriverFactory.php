<?php

namespace Database\Factories;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('###-###-####'),
            'pay_rate' => fake()->randomFloat(2, 15, 75),
            'company_address' => fake()->address(),
            'hst_number' => fake()->numerify('#########').'RT'.fake()->numerify('####'),
            'status' => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => 'inactive']);
    }
}
