<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

/**
 * Serviço responsável pelas regras de negócio financeiras, cálculo inteligente de vencimentos,
 * geração em lote de mensalidades e notificações aos administradores.
 */
class PaymentService
{
    /**
     * Calcula a data de vencimento ajustando automaticamente para o último dia do mês quando necessário.
     * Exemplo: Aluno com dia 31 em Fevereiro (28 dias) terá vencimento em 28/02.
     */
    public function calculateDueDate(Student $student, ?CarbonInterface $referenceMonth = null): Carbon
    {
        $ref = $referenceMonth ? Carbon::parse($referenceMonth)->startOfMonth() : Carbon::now()->startOfMonth();
        $daysInMonth = $ref->daysInMonth;

        // Limita o dia do aluno entre 1 e o último dia do mês corrente (clamp)
        $paymentDay = min($student->payment_day ?? 5, $daysInMonth);

        return $ref->copy()->day($paymentDay);
    }

    /**
     * Gera as mensalidades do mês de referência para todos os alunos ativos que possuem plano.
     * Retorna a quantidade de mensalidades geradas.
     */
    public function generateMonthlyPayments(?CarbonInterface $referenceMonth = null): int
    {
        $refMonth = $referenceMonth ? Carbon::parse($referenceMonth)->startOfMonth() : Carbon::now()->startOfMonth();

        $activeStudents = Student::query()
            ->where('status', 'ativo')
            ->whereNotNull('plan_id')
            ->with('plan')
            ->get();

        $generatedCount = 0;

        foreach ($activeStudents as $student) {
            // Evita gerar duplicidade de cobrança no mesmo mês de competência
            $exists = Payment::query()
                ->where('student_id', $student->id)
                ->whereDate('reference_month', $refMonth->format('Y-m-d'))
                ->exists();

            if (! $exists && $student->plan) {
                Payment::create([
                    'student_id' => $student->id,
                    'plan_id' => $student->plan_id,
                    'reference_month' => $refMonth->format('Y-m-d'),
                    'amount' => $student->plan->price,
                    'due_date' => $this->calculateDueDate($student, $refMonth)->format('Y-m-d'),
                    'status' => 'pendente',
                ]);

                $generatedCount++;
            }
        }

        return $generatedCount;
    }

    /**
     * Atualiza o status de mensalidades pendentes vencidas para 'atrasado'.
     * Retorna a quantidade de mensalidades marcadas como atrasadas.
     */
    public function updateOverduePayments(): int
    {
        $today = Carbon::today()->format('Y-m-d');

        return Payment::query()
            ->where('status', 'pendente')
            ->whereDate('due_date', '<', $today)
            ->update([
                'status' => 'atrasado',
            ]);
    }

    /**
     * Notifica todos os administradores cadastrados via Filament Database Notifications
     * caso existam alunos com mensalidades em atraso.
     * Retorna a quantidade de mensalidades atrasadas encontradas.
     */
    public function notifyAdminsAboutOverduePayments(): int
    {
        $overdueCount = Payment::query()
            ->where('status', 'atrasado')
            ->count();

        if ($overdueCount === 0) {
            return 0;
        }

        $users = User::all();

        if ($users->isNotEmpty()) {
            Notification::make()
                ->title('Mensalidades em Atraso!')
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->iconColor('danger')
                ->warning()
                ->body("Existem {$overdueCount} mensalidade(s) com pagamento pendente/atrasado na academia.")
                ->actions([
                    Action::make('view')
                        ->label('Ver Mensalidades')
                        ->button()
                        ->url('/admin/payments'),
                ])
                ->sendToDatabase($users);
        }

        return $overdueCount;
    }
}
