<?php

namespace App\Filament\Resources\ScheduleClosures\Schemas;

use App\Models\TimeSlot;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Formulário para cadastro e edição de fechamentos de agenda.
 */
class ScheduleClosureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Data do Fechamento')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                Select::make('time_slot_id')
                    ->label('Horário Específico (Opcional)')
                    ->relationship('timeSlot', 'id')
                    ->getOptionLabelFromRecordUsing(fn (TimeSlot $record): string => $record->label)
                    ->placeholder('Deixe vazio para fechar o dia inteiro')
                    ->helperText('Se nenhum horário for selecionado, a academia inteira estará fechada nesta data')
                    ->searchable()
                    ->preload(),
                TextInput::make('reason')
                    ->label('Motivo do Fechamento')
                    ->placeholder('Ex: Feriado Nacional de Tiradentes, Manutenção dos Aparelhos')
                    ->maxLength(255),
            ]);
    }
}
