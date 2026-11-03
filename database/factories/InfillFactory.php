<?php

namespace Database\Factories;

use App\Models\Infill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Infill>
 */
class InfillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'მინ' . fake()->unique()->numberBetween(1000, 9999),
            'description' => 'მინა, 6მმ. გამჭვირვალე',
            'unique_code' => fake()->unique()->numerify('000######'),
        ];
    }
}
