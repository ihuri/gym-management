<?php

namespace App\Filament\Widgets;

use App\Models\TimeSlot;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

/**
 * Gráfico de barras comparando a ocupação atual de alunos vs capacidade total por dia da semana.
 */
class TimeSlotOccupancyChartWidget extends ChartWidget
{
    protected ?string $heading = 'Ocupação da Grade por Dia da Semana';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $days = [
            1 => 'Segunda',
            2 => 'Terça',
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado',
            0 => 'Domingo',
        ];

        $enrolledData = [];
        $capacityData = [];
        $labels = [];

        foreach ($days as $dayNumber => $dayLabel) {
            $labels[] = $dayLabel;

            // Capacidade total dos horários cadastrados para este dia
            $dayCapacity = TimeSlot::where('day_of_week', $dayNumber)
                ->where('active', true)
                ->sum('capacity');

            // Alunos ativos matriculados em horários deste dia
            $dayEnrolled = DB::table('student_time_slot')
                ->join('time_slots', 'student_time_slot.time_slot_id', '=', 'time_slots.id')
                ->where('time_slots.day_of_week', $dayNumber)
                ->where('time_slots.active', true)
                ->where('student_time_slot.status', 'ativo')
                ->count();

            $enrolledData[] = $dayEnrolled;
            $capacityData[] = $dayCapacity;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Alunos Matriculados',
                    'data' => $enrolledData,
                    'backgroundColor' => '#9333ea', // Roxo / Purple
                ],
                [
                    'label' => 'Capacidade Total de Vagas',
                    'data' => $capacityData,
                    'backgroundColor' => '#64748b', // Slate / Gray
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
