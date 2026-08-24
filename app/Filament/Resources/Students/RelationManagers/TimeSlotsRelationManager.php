<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Models\TimeSlot;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Gerenciador dos horários semanais fixos matriculados para o aluno.
 */
class TimeSlotsRelationManager extends RelationManager
{
    protected static string $relationship = 'timeSlots';

    protected static ?string $title = 'Horários Matriculados na Semana';

    protected static ?string $modelLabel = 'Horário';

    protected static ?string $pluralModelLabel = 'Horários';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('day_name')
                    ->label('Dia da Semana')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('formatted_time_range')
                    ->label('Faixa de Horário'),
                TextColumn::make('occupancy')
                    ->label('Ocupação / Vagas')
                    ->state(function (TimeSlot $record): string {
                        $occupied = $record->activeStudents()->count();
                        return "{$occupied} / {$record->capacity} vagas";
                    })
                    ->badge()
                    ->color(fn (TimeSlot $record): string => $record->activeStudents()->count() >= $record->capacity ? 'danger' : 'success'),
                TextColumn::make('pivot.enrolled_at')
                    ->label('Matriculado em')
                    ->date('d/m/Y'),
                TextColumn::make('pivot.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'ativo' ? 'success' : 'gray')
                    ->formatStateUsing(fn (?string $state): string => ucfirst($state ?? 'ativo')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('attachTimeSlots')
                    ->label('Adicionar Horários')
                    ->icon(Heroicon::OutlinedClock)
                    ->color('primary')
                    ->modalHeading('Adicionar Horários do Aluno')
                    ->modalDescription('Escolha o dia da semana e marque os horários para matricular o aluno.')
                    ->modalWidth('lg')
                    ->form([
                        Select::make('day_of_week')
                            ->label('1. Selecione o Dia da Semana')
                            ->options([
                                1 => 'Segunda-feira',
                                2 => 'Terça-feira',
                                3 => 'Quarta-feira',
                                4 => 'Quinta-feira',
                                5 => 'Sexta-feira',
                                6 => 'Sábado',
                                0 => 'Domingo',
                            ])
                            ->default(1)
                            ->live()
                            ->required()
                            ->native(false),
                        CheckboxList::make('time_slot_ids')
                            ->label('2. Escolha os Horários de Treino')
                            ->options(function (Get $get, $livewire): array {
                                $day = $get('day_of_week') ?? 1;
                                $alreadyEnrolled = $livewire->ownerRecord->timeSlots()
                                    ->wherePivot('status', 'ativo')
                                    ->pluck('time_slots.id')
                                    ->toArray();

                                return TimeSlot::query()
                                    ->where('active', true)
                                    ->where('day_of_week', (int) $day)
                                    ->orderBy('start_time')
                                    ->get()
                                    ->mapWithKeys(function (TimeSlot $slot) use ($alreadyEnrolled) {
                                        $occupied = $slot->activeStudents()->count();
                                        $isFull = $occupied >= $slot->capacity;
                                        $isEnrolled = in_array($slot->id, $alreadyEnrolled);

                                        $tag = $isEnrolled ? ' — [Já Matriculado]' : ($isFull ? ' — [LOTADO]' : " — {$occupied}/{$slot->capacity} vagas");
                                        return [$slot->id => "{$slot->formatted_time_range}{$tag}"];
                                    })
                                    ->toArray();
                            })
                            ->descriptions(function (Get $get): array {
                                $day = $get('day_of_week') ?? 1;
                                return TimeSlot::query()
                                    ->where('active', true)
                                    ->where('day_of_week', (int) $day)
                                    ->get()
                                    ->mapWithKeys(function (TimeSlot $slot) {
                                        $occupied = $slot->activeStudents()->count();
                                        $remaining = max(0, $slot->capacity - $occupied);
                                        return [$slot->id => "{$remaining} vaga(s) disponível(is)"];
                                    })
                                    ->toArray();
                            })
                            ->disableOptionWhen(function (string $value, $livewire): bool {
                                $alreadyEnrolled = $livewire->ownerRecord->timeSlots()
                                    ->wherePivot('status', 'ativo')
                                    ->pluck('time_slots.id')
                                    ->toArray();

                                if (in_array((int) $value, $alreadyEnrolled)) {
                                    return true;
                                }

                                $slot = TimeSlot::find($value);
                                return ! $slot || $slot->activeStudents()->count() >= $slot->capacity;
                            })
                            ->columns(2)
                            ->gridDirection('row')
                            ->required(),
                    ])
                    ->action(function (array $data, $livewire): void {
                        $student = $livewire->ownerRecord;
                        $slotIds = $data['time_slot_ids'] ?? [];
                        $added = 0;

                        foreach ($slotIds as $slotId) {
                            $slot = TimeSlot::find($slotId);
                            if ($slot && $slot->activeStudents()->count() < $slot->capacity) {
                                $existing = $student->timeSlots()->where('time_slot_id', $slotId)->first();
                                if ($existing) {
                                    $student->timeSlots()->updateExistingPivot($slotId, [
                                        'status' => 'ativo',
                                        'enrolled_at' => now()->toDateString(),
                                    ]);
                                } else {
                                    $student->timeSlots()->attach($slotId, [
                                        'status' => 'ativo',
                                        'enrolled_at' => now()->toDateString(),
                                    ]);
                                }
                                $added++;
                            }
                        }

                        if ($added > 0) {
                            Notification::make()
                                ->title("Matrícula realizada com sucesso!")
                                ->body("{$added} horário(s) adicionado(s) para o aluno.")
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Remover Matrícula')
                    ->modalHeading('Remover Aluno deste Horário')
                    ->modalDescription('O aluno deixará de ocupar uma vaga neste horário fixo.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
