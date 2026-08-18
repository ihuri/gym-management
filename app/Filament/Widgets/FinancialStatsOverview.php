<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Widget de Estatísticas Financeiras em Tempo Real.
 */
class FinancialStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $activeStudents = Student::where('status', 'ativo')->count();
        $pendingPayments = Payment::where('status', 'pendente')->count();
        $overduePayments = Payment::where('status', 'atrasado')->count();
        $paidThisMonth = Payment::where('status', 'pago')
            ->whereMonth('paid_at', Carbon::now()->month)
            ->whereYear('paid_at', Carbon::now()->year)
            ->count();

        return [
            Stat::make('Alunos Ativos', $activeStudents)
                ->description('Matrículas ativas na academia')
                ->descriptionIcon(Heroicon::OutlinedUserGroup)
                ->color('primary'),

            Stat::make('Mensalidades Pendentes', $pendingPayments)
                ->description('Aguardando pagamento')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color('warning'),

            Stat::make('Mensalidades em Atraso', $overduePayments)
                ->description('Cobranças vencidas pendentes')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color('danger'),

            Stat::make('Pagas neste Mês', $paidThisMonth)
                ->description('Mensalidades quitadas')
                ->descriptionIcon(Heroicon::OutlinedCheckBadge)
                ->color('success'),
        ];
    }
}
