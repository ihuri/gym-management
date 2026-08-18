<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Student;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

/**
 * Widget com indicadores executivos da academia no Dashboard principal.
 */
class GymOverviewStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // 1. Alunos Ativos e novos inscritos neste mês
        $activeStudentsCount = Student::where('status', 'ativo')->count();
        $newStudentsThisMonth = Student::whereMonth('enrollment_date', Carbon::now()->month)
            ->whereYear('enrollment_date', Carbon::now()->year)
            ->count();

        // 2. Faturamento recebido no mês corrente
        $revenueThisMonth = Payment::where('status', 'pago')
            ->whereMonth('paid_at', Carbon::now()->month)
            ->whereYear('paid_at', Carbon::now()->year)
            ->sum('paid_amount') ?? 0;

        // 3. Inadimplência
        $overdueCount = Payment::where('status', 'atrasado')->count();
        $overdueAmount = Payment::where('status', 'atrasado')->sum('amount') ?? 0;

        // 4. Taxa de ocupação global da grade semanal
        $totalCapacity = TimeSlot::where('active', true)->sum('capacity') ?: 1;
        $totalEnrolled = DB::table('student_time_slot')
            ->where('status', 'ativo')
            ->count();
        $occupancyRate = round(($totalEnrolled / $totalCapacity) * 100);

        return [
            Stat::make('Alunos Ativos', $activeStudentsCount)
                ->description("{$newStudentsThisMonth} novos matriculados este mês")
                ->descriptionIcon(Heroicon::OutlinedUserPlus)
                ->color('primary'),

            Stat::make('Receita Recebida (Mês)', 'R$ ' . number_format($revenueThisMonth, 2, ',', '.'))
                ->description('Mensalidades quitadas no mês atual')
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color('success'),

            Stat::make('Mensalidades em Atraso', "{$overdueCount} (R$ " . number_format($overdueAmount, 2, ',', '.') . ')')
                ->description('Valores vencidos pendentes de recebimento')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color($overdueCount > 0 ? 'danger' : 'success'),

            Stat::make('Taxa de Ocupação da Grade', "{$occupancyRate}%")
                ->description("{$totalEnrolled} vagas ocupadas de {$totalCapacity} disponíveis")
                ->descriptionIcon(Heroicon::OutlinedChartBar)
                ->color($occupancyRate > 80 ? 'danger' : ($occupancyRate > 50 ? 'warning' : 'success')),
        ];
    }
}
