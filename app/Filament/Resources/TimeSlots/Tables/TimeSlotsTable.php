<?php

namespace App\Filament\Resources\TimeSlots\Tables;

use App\Models\TimeSlot;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Tabela com a Grade Semanal e Indicador de Ocupação/Vagas.
 */
class TimeSlotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('day_of_week')
            ->columns([
                TextColumn::make('day_name')
                    ->label('Dia da Semana')
                    ->badge()
                    ->color('primary')
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('day_of_week', $direction)),
                TextColumn::make('formatted_time_range')
                    ->label('Horário')
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('start_time', $direction)),
                TextColumn::make('occupancy')
                    ->label('Ocupação / Vagas')
                    ->state(function (TimeSlot $record): string {
                        $occupied = $record->activeStudents()->count();
                        return "{$occupied} / {$record->capacity} vagas";
                    })
                    ->badge()
                    ->color(function (TimeSlot $record): string {
                        $occupied = $record->activeStudents()->count();
                        if ($occupied >= $record->capacity) {
                            return 'danger';
                        }
                        if ($occupied > ($record->capacity * 0.7)) {
                            return 'warning';
                        }
                        return 'success';
                    }),
                IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('day_of_week')
                    ->label('Filtrar por Dia')
                    ->options([
                        0 => 'Domingo',
                        1 => 'Segunda-feira',
                        2 => 'Terça-feira',
                        3 => 'Quarta-feira',
                        4 => 'Quinta-feira',
                        5 => 'Sexta-feira',
                        6 => 'Sábado',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
