<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para geração de Planos de Treino.
 *
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Plano Mensal', 'Plano Trimestral', 'Plano Semestral', 'Plano Anual']),
            'price' => fake()->randomElement([60.00, 80.00, 100.00, 120.00]),
            'duration_months' => fake()->randomElement([1, 3, 6, 12]),
            'description' => fake()->sentence(),
            'active' => true,
        ];
    }
}
