<?php

namespace App\Filament\Resources\ScheduleClosures\Tables;

use App\Models\ScheduleClosure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Tabela de Listagem dos Fechamentos de Agenda Pontuais e Recorrentes.
 */
class ScheduleClosuresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'recorrente' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'recorrente' => 'Recorrente',
                        default => 'Pontual',
                    }),
                TextColumn::make('target')
                    ->label('Bloqueio / Data')
                    ->state(function (ScheduleClosure $record): string {
                        if ($record->type === 'recorrente') {
                            return $record->day_name ?? 'Dia recorrente';
                        }
                        return $record->date ? $record->date->format('d/m/Y') : 'Data não informada';
                    })
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('date', $direction)),
                TextColumn::make('scope')
                    ->label('Horário / Escopo')
                    ->state(function (ScheduleClosure $record): string {
                        return $record->timeSlot
                            ? "{$record->timeSlot->day_name} ({$record->timeSlot->formatted_time_range})"
                            : 'Dia Inteiro Fechado';
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Dia Inteiro Fechado' ? 'danger' : 'warning'),
                TextColumn::make('reason')
                    ->label('Motivo')
                    ->searchable()
                    ->placeholder('Sem motivo informado'),
                TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Filtrar por Tipo')
                    ->options([
                        'data_especifica' => 'Pontual (Data)',
                        'recorrente' => 'Recorrente (Dia da Semana)',
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
