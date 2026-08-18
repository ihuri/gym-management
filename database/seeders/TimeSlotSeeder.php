<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

/**
 * Seeder para popular a grade de horários semanais da academia.
 * Segunda a Quinta, das 05h da manhã às 20h da noite, de 1 em 1 hora, com 15 vagas.
 */
class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        // 1 = Segunda-feira, 2 = Terça-feira, 3 = Quarta-feira, 4 = Quinta-feira
        $daysOfWeek = [1, 2, 3, 4];

        // Horários de início de 1 em 1 hora das 05:00 até as 19:00 (término às 20:00)
        $startHours = range(5, 19);

        foreach ($daysOfWeek as $day) {
            foreach ($startHours as $hour) {
                $startTime = sprintf('%02d:00:00', $hour);
                $endTime = sprintf('%02d:00:00', $hour + 1);

                TimeSlot::firstOrCreate(
                    [
                        'day_of_week' => $day,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                    ],
                    [
                        'capacity' => 15, // 15 pessoas por horário
                        'active' => true,
                    ]
                );
            }
        }
    }
}
