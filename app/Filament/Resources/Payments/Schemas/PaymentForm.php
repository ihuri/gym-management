<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Formulário para cadastro e edição de mensalidades/pagamentos.
 */
class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->label('Aluno')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('plan_id')
                    ->label('Plano')
                    ->relationship('plan', 'name')
                    ->searchable()
                    ->preload(),
                DatePicker::make('reference_month')
                    ->label('Mês de Competência')
                    ->displayFormat('m/Y')
                    ->native(false)
                    ->required(),
                TextInput::make('amount')
                    ->label('Valor Cobrado')
                    ->numeric()
                    ->prefix('R$')
                    ->minValue(0)
                    ->required(),
                TextInput::make('paid_amount')
                    ->label('Valor Pago')
                    ->numeric()
                    ->prefix('R$')
                    ->minValue(0),
                DatePicker::make('due_date')
                    ->label('Data de Vencimento')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->required(),
                DatePicker::make('paid_at')
                    ->label('Data do Pagamento')
                    ->displayFormat('d/m/Y')
                    ->native(false),
                Select::make('payment_method')
                    ->label('Forma de Pagamento')
                    ->options([
                        'dinheiro' => 'Dinheiro',
                        'pix' => 'PIX',
                        'cartao_credito' => 'Cartão de Crédito',
                        'cartao_debito' => 'Cartão de Débito',
                    ])
                    ->native(false),
                Select::make('status')
                    ->label('Status da Mensalidade')
                    ->options([
                        'pendente' => 'Pendente',
                        'pago' => 'Pago',
                        'atrasado' => 'Atrasado',
                        'cancelado' => 'Cancelado',
                    ])
                    ->default('pendente')
                    ->native(false)
                    ->required(),
                Textarea::make('notes')
                    ->label('Observações')
                    ->placeholder('Anotações internas sobre o pagamento')
                    ->columnSpanFull(),
            ]);
    }
}
