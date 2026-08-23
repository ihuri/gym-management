<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Widget de Tabela com os últimos lançamentos de mensalidade e ação rápida de baixa no Dashboard.
 */
class LatestPaymentsTableWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Últimas Mensalidades e Cobranças';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Payment::query()->with(['student', 'plan'])->latest('due_date')->limit(6))
            ->paginated(false)
            ->columns([
                TextColumn::make('student.name')
                    ->label('Aluno')
                    ->searchable(),
                TextColumn::make('reference_month')
                    ->label('Competência')
                    ->date('m/Y'),
                TextColumn::make('amount')
                    ->label('Valor')
                    ->money('BRL'),
                TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->date('d/m/Y'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pago' => 'success',
                        'pendente' => 'warning',
                        'atrasado' => 'danger',
                        'cancelado' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->recordActions([
                Action::make('markAsPaid')
                    ->label('Dar Baixa')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->modalHeading('Dar Baixa na Mensalidade')
                    ->modalDescription('Informe a forma de pagamento e detalhes para confirmar o recebimento.')
                    ->form([
                        Select::make('payment_method')
                            ->label('Tipo de Pagamento')
                            ->options([
                                'pix' => 'PIX',
                                'dinheiro' => 'Dinheiro',
                                'cartao_credito' => 'Cartão de Crédito',
                                'cartao_debito' => 'Cartão de Débito',
                            ])
                            ->default(fn (Payment $record): string => $record->payment_method ?? 'pix')
                            ->required()
                            ->native(false),
                        TextInput::make('paid_amount')
                            ->label('Valor Recebido')
                            ->numeric()
                            ->prefix('R$')
                            ->default(fn (Payment $record) => $record->amount)
                            ->required(),
                        DatePicker::make('paid_at')
                            ->label('Data do Pagamento')
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->required(),
                    ])
                    ->action(function (Payment $record, array $data): void {
                        $record->update([
                            'status' => 'pago',
                            'paid_at' => $data['paid_at'] ?? now(),
                            'paid_amount' => $data['paid_amount'] ?? $record->amount,
                            'payment_method' => $data['payment_method'],
                        ]);

                        Notification::make()
                            ->title('Mensalidade recebida!')
                            ->body("Pagamento de {$record->student->name} confirmado.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Payment $record): bool => $record->status !== 'pago'),
            ]);
    }
}
