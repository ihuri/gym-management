<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

/**
 * Tabela de Gestão de Mensalidades com Filtros e Ação Rápida de Baixa.
 */
class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('due_date', 'desc')
            ->columns([
                TextColumn::make('student.name')
                    ->label('Aluno')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('plan.name')
                    ->label('Plano')
                    ->placeholder('Sem plano')
                    ->sortable(),
                TextColumn::make('reference_month')
                    ->label('Competência')
                    ->date('m/Y')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Pago em')
                    ->date('d/m/Y')
                    ->placeholder('Não pago')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Método')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'dinheiro' => 'Dinheiro',
                        'pix' => 'PIX',
                        'cartao_credito' => 'Cartão de Crédito',
                        'cartao_debito' => 'Cartão de Débito',
                        default => '—',
                    }),
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
            ->filters([
                TrashedFilter::make(),
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
                            ->body("Pagamento do aluno {$record->student->name} confirmado.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Payment $record): bool => $record->status !== 'pago'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
