<?php

namespace App\Filament\Resources\ScheduleClosures\Schemas;

use App\Models\TimeSlot;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Formulário para cadastro de fechamentos pontuais e recorrentes de agenda.
 */
class ScheduleClosureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Radio::make('type')
                    ->label('Tipo de Fechamento')
                    ->options([
                        'data_especifica' => 'Data Específica (Feriado / Manutenção Pontual)',
                        'recorrente' => 'Recorrente (Todos os dias da semana selecionados)',
                    ])
                    ->default('data_especifica')
                    ->live()
                    ->required(),
                DatePicker::make('date')
                    ->label('Data do Fechamento')
                    ->visible(fn ($get): bool => $get('type') === 'data_especifica')
                    ->required(fn ($get): bool => $get('type') === 'data_especifica')
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                Select::make('day_of_week')
                    ->label('Dia da Semana Recorrente')
                    ->options([
                        0 => 'Todos os Domingos',
                        1 => 'Todas as Segundas-feiras',
                        2 => 'Todas as Terças-feiras',
                        3 => 'Todas as Quartas-feiras',
                        4 => 'Todas as Quintas-feiras',
                        5 => 'Todas as Sextas-feiras',
                        6 => 'Todos os Sábados',
                    ])
                    ->visible(fn ($get): bool => $get('type') === 'recorrente')
                    ->required(fn ($get): bool => $get('type') === 'recorrente')
                    ->native(false),
                Select::make('time_slot_id')
                    ->label('Horário Específico (Opcional)')
                    ->relationship('timeSlot', 'id')
                    ->getOptionLabelFromRecordUsing(fn (TimeSlot $record): string => $record->label)
                    ->placeholder('Deixe vazio para fechar o dia inteiro')
                    ->helperText('Se nenhum horário for selecionado, o dia inteiro estará bloqueado')
                    ->searchable()
                    ->preload(),
                TextInput::make('reason')
                    ->label('Motivo do Fechamento')
                    ->placeholder('Ex: Sem funcionamento aos sábados, Feriado de Natal')
                    ->maxLength(255),
            ]);
    }
}
