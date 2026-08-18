<?php

namespace App\Filament\Resources\TimeSlots\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/**
 * Formulário para cadastro e edição de horários semanais fixos.
 */
class TimeSlotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('day_of_week')
                    ->label('Dia da Semana')
                    ->options([
                        0 => 'Domingo',
                        1 => 'Segunda-feira',
                        2 => 'Terça-feira',
                        3 => 'Quarta-feira',
                        4 => 'Quinta-feira',
                        5 => 'Sexta-feira',
                        6 => 'Sábado',
                    ])
                    ->required()
                    ->native(false),
                TimePicker::make('start_time')
                    ->label('Horário de Início')
                    ->seconds(false)
                    ->required(),
                TimePicker::make('end_time')
                    ->label('Horário de Término')
                    ->seconds(false)
                    ->required(),
                TextInput::make('capacity')
                    ->label('Capacidade Máxima (Alunos)')
                    ->numeric()
                    ->default(10)
                    ->minValue(1)
                    ->required(),
                Toggle::make('active')
                    ->label('Horário Ativo')
                    ->helperText('Desative para suspender este horário da grade semanal')
                    ->default(true)
                    ->required(),
            ]);
    }
}
