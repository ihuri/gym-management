<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                SelectFilter::make('status')
                    ->label('Filtrar por Status')
                    ->options([
                        'pendente' => 'Pendente',
                        'pago' => 'Pago',
                        'atrasado' => 'Atrasado',
                        'cancelado' => 'Cancelado',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('markAsPaid')
                    ->label('Dar Baixa')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Recebimento de Mensalidade')
                    ->modalDescription('Deseja marcar esta mensalidade como paga no valor integral hoje?')
                    ->action(function (Payment $record): void {
                        $record->update([
                            'status' => 'pago',
                            'paid_at' => now(),
                            'paid_amount' => $record->amount,
                            'payment_method' => $record->payment_method ?? 'pix',
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
