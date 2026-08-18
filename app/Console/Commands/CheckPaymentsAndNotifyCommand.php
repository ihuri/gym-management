<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use Illuminate\Console\Command;

/**
 * Comando de rotina diária para processar pagamentos:
 * 1. Gera mensalidades do mês corrente se ainda não geradas.
 * 2. Atualiza pagamentos vencidos para o status 'atrasado'.
 * 3. Envia notificações via Filament para os administradores.
 */
class CheckPaymentsAndNotifyCommand extends Command
{
    /**
     * Assinatura do comando artisan.
     */
    protected $signature = 'app:check-payments-and-notify {--month= : Mês de competência customizado no formato YYYY-MM}';

    /**
     * Descrição do comando.
     */
    protected $description = 'Gera mensalidades do mês, atualiza status de pagamentos atrasados e notifica administradores';

    /**
     * Executa a rotina financeira.
     */
    public function handle(PaymentService $paymentService): int
    {
        $this->info('Iniciando rotina de verificação financeira da academia...');

        $monthOption = $this->option('month');
        $referenceMonth = $monthOption ? \Carbon\Carbon::parse($monthOption) : null;

        // 1. Gera mensalidades do mês
        $generated = $paymentService->generateMonthlyPayments($referenceMonth);
        $this->info("Mensalidades geradas: {$generated}");

        // 2. Atualiza pagamentos vencidos
        $overdueUpdated = $paymentService->updateOverduePayments();
        $this->info("Mensalidades atualizadas para 'atrasado': {$overdueUpdated}");

        // 3. Notifica administradores
        $totalOverdue = $paymentService->notifyAdminsAboutOverduePayments();
        if ($totalOverdue > 0) {
            $this->warn("Notificação enviada aos administradores sobre {$totalOverdue} mensalidade(s) em atraso.");
        } else {
            $this->info('Nenhuma mensalidade em atraso no momento.');
        }

        $this->info('Rotina financeira concluída com sucesso.');

        return Command::SUCCESS;
    }
}
