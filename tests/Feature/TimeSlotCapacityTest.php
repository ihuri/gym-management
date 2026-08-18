<?php

use App\Models\Plan;
use App\Models\ScheduleClosure;
use App\Models\Student;
use App\Models\TimeSlot;

test('calcula ocupacao de vagas e identifica horario lotado', function () {
    $slot = TimeSlot::create([
        'day_of_week' => 1, // Segunda
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'capacity' => 2,
        'active' => true,
    ]);

    $plan = Plan::create([
        'name' => 'Plano Teste',
        'price' => 60.00,
        'duration_months' => 12,
        'active' => true,
    ]);

    $student1 = Student::factory()->create(['plan_id' => $plan->id, 'status' => 'ativo']);
    $student2 = Student::factory()->create(['plan_id' => $plan->id, 'status' => 'ativo']);

    expect($slot->activeStudents()->count())->toBe(0);

    // Matricula primeiro aluno
    $student1->timeSlots()->attach($slot->id, ['status' => 'ativo', 'enrolled_at' => now()]);
    expect($slot->activeStudents()->count())->toBe(1);

    // Matricula segundo aluno (lotando capacidade)
    $student2->timeSlots()->attach($slot->id, ['status' => 'ativo', 'enrolled_at' => now()]);
    expect($slot->activeStudents()->count())->toBe(2);
});

test('suporta fechamento recorrente de agenda por dia da semana', function () {
    $closure = ScheduleClosure::create([
        'type' => 'recorrente',
        'day_of_week' => 6, // Sábado
        'reason' => 'Sem funcionamento aos sábados',
    ]);

    expect($closure->day_name)->toBe('Todos os Sábados')
        ->and($closure->type)->toBe('recorrente')
        ->and($closure->time_slot_id)->toBeNull();
});
