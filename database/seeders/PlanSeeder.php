<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Seeder para criar os planos padrão de treino da academia.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::firstOrCreate(
            ['name' => 'Plano Normal'],
            [
                'price' => 60.00,
                'duration_months' => 12,
                'description' => 'Plano anual padrão com mensalidade de R$ 60,00',
                'active' => true,
            ]
        );
    }
}
