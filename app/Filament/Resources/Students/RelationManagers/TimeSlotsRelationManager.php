<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Models\TimeSlot;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
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
                    ->color(function (TimeSlot $record): string {
                        $occupied = $record->activeStudents()->count();
                        return $occupied >= $record->capacity ? 'danger' : 'success';
                    }),
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
                AttachAction::make()
                    ->label('Matricular em Horário')
                    ->modalHeading('Vincular Aluno ao Horário')
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Selecione o Horário')
                            ->options(function () {
                                return TimeSlot::query()
                                    ->where('active', true)
                                    ->orderBy('day_of_week')
                                    ->orderBy('start_time')
                                    ->get()
                                    ->mapWithKeys(function (TimeSlot $slot) {
                                        $occupied = $slot->activeStudents()->count();
                                        $lotado = $occupied >= $slot->capacity ? ' [LOTADO]' : '';
                                        return [$slot->id => "{$slot->label} ({$occupied}/{$slot->capacity} vagas){$lotado}"];
                                    });
                            })
                            ->disableOptionWhen(function (string $value): bool {
                                $slot = TimeSlot::find($value);
                                if (! $slot) {
                                    return false;
                                }
                                return $slot->activeStudents()->count() >= $slot->capacity;
                            })
                            ->required(),
                        Hidden::make('status')->default('ativo'),
                        Hidden::make('enrolled_at')->default(now()->toDateString()),
                    ])
                    ->before(function (AttachAction $action, array $data) {
                        $slot = TimeSlot::find($data['recordId'] ?? null);
                        if ($slot && $slot->activeStudents()->count() >= $slot->capacity) {
                            Notification::make()
                                ->title('Horário Lotado!')
                                ->body('Este horário já atingiu a capacidade máxima de alunos.')
                                ->danger()
                                ->send();

                            $action->halt();
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
