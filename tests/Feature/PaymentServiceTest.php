<?php

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Student;
use App\Models\User;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

test('calcula vencimento com ajuste automatico para o ultimo dia do mes curto', function () {
    $service = new PaymentService();

    $studentDia31 = Student::factory()->make(['payment_day' => 31]);
    $studentDia15 = Student::factory()->make(['payment_day' => 15]);

    // Fevereiro 2026 (28 dias)
    $fev2026 = Carbon::create(2026, 2, 1);
    $dueFev31 = $service->calculateDueDate($studentDia31, $fev2026);
    $dueFev15 = $service->calculateDueDate($studentDia15, $fev2026);

    expect($dueFev31->format('Y-m-d'))->toBe('2026-02-28')
        ->and($dueFev15->format('Y-m-d'))->toBe('2026-02-15');

    // Abril 2026 (30 dias)
    $abr2026 = Carbon::create(2026, 4, 1);
    $dueAbr31 = $service->calculateDueDate($studentDia31, $abr2026);

    expect($dueAbr31->format('Y-m-d'))->toBe('2026-04-30');
});

test('gera mensalidades em lote para alunos ativos com plano', function () {
    $plan = Plan::create([
        'name' => 'Plano Teste',
        'price' => 80.00,
        'duration_months' => 12,
        'active' => true,
    ]);

    Student::factory()->count(3)->create([
        'status' => 'ativo',
        'plan_id' => $plan->id,
        'payment_day' => 10,
    ]);

    // Aluno inativo não deve receber cobrança
    Student::factory()->create([
        'status' => 'inativo',
        'plan_id' => $plan->id,
    ]);

    $service = new PaymentService();
    $generated = $service->generateMonthlyPayments(Carbon::create(2026, 8, 1));

    expect($generated)->toBe(3)
        ->and(Payment::count())->toBe(3);

    $payment = Payment::first();
    expect((float) $payment->amount)->toBe(80.00)
        ->and($payment->status)->toBe('pendente')
        ->and($payment->due_date->format('Y-m-d'))->toBe('2026-08-10');
});

test('evita duplicidade de mensalidade no mesmo mes de competencia', function () {
    $plan = Plan::create([
        'name' => 'Plano Teste',
        'price' => 60.00,
        'duration_months' => 12,
        'active' => true,
    ]);

    $student = Student::factory()->create([
        'status' => 'ativo',
        'plan_id' => $plan->id,
        'payment_day' => 5,
    ]);

    $service = new PaymentService();
    $firstRun = $service->generateMonthlyPayments(Carbon::create(2026, 8, 1));
    $secondRun = $service->generateMonthlyPayments(Carbon::create(2026, 8, 1));

    expect($firstRun)->toBe(1)
        ->and($secondRun)->toBe(0)
        ->and(Payment::count())->toBe(1);
});

test('atualiza mensalidades vencidas para atrasado', function () {
    $plan = Plan::create([
        'name' => 'Plano Teste',
        'price' => 60.00,
        'duration_months' => 12,
        'active' => true,
    ]);

    $student = Student::factory()->create([
        'status' => 'ativo',
        'plan_id' => $plan->id,
    ]);

    // Mensalidade vencida ontem
    $overduePayment = Payment::create([
        'student_id' => $student->id,
        'plan_id' => $plan->id,
        'reference_month' => Carbon::now()->subMonth()->startOfMonth(),
        'amount' => 60.00,
        'due_date' => Carbon::yesterday(),
        'status' => 'pendente',
    ]);

    // Mensalidade que vence amanhã
    $futurePayment = Payment::create([
        'student_id' => $student->id,
        'plan_id' => $plan->id,
        'reference_month' => Carbon::now()->startOfMonth(),
        'amount' => 60.00,
        'due_date' => Carbon::tomorrow(),
        'status' => 'pendente',
    ]);

    $service = new PaymentService();
    $updated = $service->updateOverduePayments();

    expect($updated)->toBe(1);
    expect($overduePayment->fresh()->status)->toBe('atrasado');
    expect($futurePayment->fresh()->status)->toBe('pendente');
});

test('notifica administradores sobre mensalidades atrasadas', function () {
    $admin = User::factory()->create([
        'email' => 'admin@test.com',
    ]);

    $plan = Plan::create([
        'name' => 'Plano Teste',
        'price' => 60.00,
        'duration_months' => 12,
        'active' => true,
    ]);

    $student = Student::factory()->create([
        'status' => 'ativo',
        'plan_id' => $plan->id,
    ]);

    Payment::create([
        'student_id' => $student->id,
        'plan_id' => $plan->id,
        'reference_month' => Carbon::now()->subMonth()->startOfMonth(),
        'amount' => 60.00,
        'due_date' => Carbon::yesterday(),
        'status' => 'atrasado',
    ]);

    $service = new PaymentService();
    $notified = $service->notifyAdminsAboutOverduePayments();

    expect($notified)->toBe(1)
        ->and($admin->notifications()->count())->toBe(1);
});
