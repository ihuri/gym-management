<?php

namespace App\Filament\Widgets;

use App\Models\TimeSlot;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Widget exibindo a grade de horários do dia atual com lista de alunos matriculados e contagem de vagas.
 */
class TodayScheduleWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public static function getHeading(): ?string
    {
        $today = Carbon::now();
        $days = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
        ];

        $dayName = $days[$today->dayOfWeek] ?? 'Hoje';
        $formattedDate = $today->format('d/m/Y');

        return "Grade de Horários de Hoje — {$dayName} ({$formattedDate})";
    }

    public function table(Table $table): Table
    {
        $currentDayOfWeek = Carbon::now()->dayOfWeek;

        return $table
            ->query(
                fn (): Builder => TimeSlot::query()
                    ->where('day_of_week', $currentDayOfWeek)
                    ->where('active', true)
                    ->with(['activeStudents'])
                    ->orderBy('start_time')
            )
            ->paginated(false)
            ->emptyStateHeading('Nenhum horário cadastrado para hoje')
            ->emptyStateDescription('A academia não possui horários na grade para este dia da semana.')
            ->columns([
                TextColumn::make('formatted_time_range')
                    ->label('Horário')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('occupancy')
                    ->label('Vagas / Lotação')
                    ->state(function (TimeSlot $record): string {
                        $occupied = $record->activeStudents->count();
                        return "{$occupied} / {$record->capacity} vagas";
                    })
                    ->badge()
                    ->color(function (TimeSlot $record): string {
                        $occupied = $record->activeStudents->count();
                        if ($occupied >= $record->capacity) {
                            return 'danger';
                        }
                        if ($occupied > ($record->capacity * 0.7)) {
                            return 'warning';
                        }
                        return 'success';
                    }),

                TextColumn::make('activeStudents.name')
                    ->label('Alunos Matriculados no Horário')
                    ->badge()
                    ->color('gray')
                    ->separator(', ')
                    ->limitList(5)
                    ->expandableLimitedList()
                    ->placeholder('Nenhum aluno matriculado neste horário'),
            ])
            ->recordActions([
                Action::make('viewStudents')
                    ->label('Ver Lista Completa')
                    ->modalHeading(fn (TimeSlot $record) => "Alunos no Horário {$record->formatted_time_range}")
                    ->modalContent(function (TimeSlot $record) {
                        $students = $record->activeStudents;
                        return view('filament.widgets.partials.today-students-modal', [
                            'students' => $students,
                            'slot' => $record,
                        ]);
                    }),
            ]);
    }
}
