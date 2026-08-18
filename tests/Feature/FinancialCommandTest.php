<?php

use App\Models\Plan;
use App\Models\Student;
use App\Models\User;

test('executa o comando app:check-payments-and-notify com sucesso', function () {
    User::factory()->create();

    $plan = Plan::create([
        'name' => 'Plano Normal',
        'price' => 60.00,
        'duration_months' => 12,
        'active' => true,
    ]);

    Student::factory()->count(2)->create([
        'status' => 'ativo',
        'plan_id' => $plan->id,
    ]);

    $this->artisan('app:check-payments-and-notify')
        ->expectsOutputToContain('Rotina financeira concluída com sucesso.')
        ->assertSuccessful();
});
