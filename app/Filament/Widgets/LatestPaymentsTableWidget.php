<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Actions\Action;
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
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Recebimento')
                    ->modalDescription('Confirmar o recebimento integral desta mensalidade hoje?')
                    ->action(function (Payment $record): void {
                        $record->update([
                            'status' => 'pago',
                            'paid_at' => now(),
                            'paid_amount' => $record->amount,
                            'payment_method' => $record->payment_method ?? 'pix',
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
