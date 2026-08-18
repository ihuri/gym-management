<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Student;
use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

/**
 * Seeder para gerar 15 alunos de teste com matrículas e horários vinculados.
 */
class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $plan = Plan::first() ?? Plan::create([
            'name' => 'Plano Normal',
            'price' => 60.00,
            'duration_months' => 12,
            'description' => 'Plano anual padrão com mensalidade de R$ 60,00',
            'active' => true,
        ]);

        $slots = TimeSlot::where('active', true)->get();

        Student::factory(15)->create([
            'plan_id' => $plan->id,
        ])->each(function (Student $student) use ($slots) {
            // Vincula o aluno a 2 ou 3 horários semanais fixos (respeitando capacidade)
            if ($slots->isNotEmpty()) {
                $randomSlots = $slots->random(min(3, $slots->count()));
                foreach ($randomSlots as $slot) {
                    if ($slot->activeStudents()->count() < $slot->capacity) {
                        $student->timeSlots()->attach($slot->id, [
                            'status' => 'ativo',
                            'enrolled_at' => now()->subDays(rand(1, 30)),
                        ]);
                    }
                }
            }
        });
    }
}
