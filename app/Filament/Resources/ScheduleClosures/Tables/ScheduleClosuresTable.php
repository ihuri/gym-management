<?php

namespace App\Filament\Resources\ScheduleClosures\Tables;

use App\Models\ScheduleClosure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Tabela de Listagem dos Fechamentos de Agenda.
 */
class ScheduleClosuresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('scope')
                    ->label('Escopo do Bloqueio')
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
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
