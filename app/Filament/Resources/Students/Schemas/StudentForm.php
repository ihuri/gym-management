<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Formulário de Cadastro e Edição de Alunos.
 */
class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('photo_path')
                    ->label('Foto do Perfil')
                    ->image()
                    ->directory('students')
                    ->avatar()
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->label('Nome Completo')
                    ->placeholder('Nome do aluno')
                    ->required()
                    ->maxLength(255),
                TextInput::make('cpf')
                    ->label('CPF')
                    ->placeholder('000.000.000-00')
                    ->mask('999.999.999-99')
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->placeholder('aluno@exemplo.com')
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Telefone / WhatsApp')
                    ->placeholder('(00) 00000-0000')
                    ->mask('(99) 99999-9999')
                    ->required(),
                DatePicker::make('birth_date')
                    ->label('Data de Nascimento')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->required(),
                DatePicker::make('enrollment_date')
                    ->label('Data da Matrícula')
                    ->default(now())
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->required(),
                Select::make('plan_id')
                    ->label('Plano Contratado')
                    ->relationship('plan', 'name', fn ($query) => $query->where('active', true))
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('payment_day')
                    ->label('Dia de Vencimento')
                    ->helperText('Dia do mês preferencial para cobrança (1 a 31)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31)
                    ->default(5)
                    ->required(),
                Select::make('status')
                    ->label('Situação da Matrícula')
                    ->options([
                        'ativo' => 'Ativo',
                        'inativo' => 'Inativo',
                        'suspenso' => 'Suspenso',
                    ])
                    ->default('ativo')
                    ->native(false)
                    ->required(),
                TextInput::make('address')
                    ->label('Endereço Completo')
                    ->placeholder('Rua, número, complemento, bairro, cidade')
                    ->columnSpanFull(),
            ]);
    }
}
