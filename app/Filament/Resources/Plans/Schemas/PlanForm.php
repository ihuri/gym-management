<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/**
 * Esquema do Formulário de Criação/Edição de Planos.
 */
class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome do Plano')
                    ->placeholder('Ex: Mensal 2x/sem, Mensal Livre, Trimestral')
                    ->required()
                    ->maxLength(255),
                TextInput::make('price')
                    ->label('Valor da Mensalidade')
                    ->required()
                    ->numeric()
                    ->prefix('R$')
                    ->minValue(0),
                TextInput::make('duration_months')
                    ->label('Duração (em meses)')
                    ->helperText('Ex: 1 para Mensal, 3 para Trimestral, 12 para Anual')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
                Textarea::make('description')
                    ->label('Descrição / Benefícios')
                    ->placeholder('Regras ou detalhes do plano')
                    ->columnSpanFull(),
                Toggle::make('active')
                    ->label('Plano Ativo')
                    ->helperText('Se desativado, o plano não poderá ser vinculado a novos alunos')
                    ->default(true)
                    ->required(),
            ]);
    }
}
