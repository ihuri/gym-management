<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Gerenciador do Histórico Financeiro e Mensalidades do Aluno.
 */
class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Histórico de Mensalidades';

    protected static ?string $modelLabel = 'Mensalidade';

    protected static ?string $pluralModelLabel = 'Mensalidades';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('plan_id')
                    ->label('Plano de Referência')
                    ->relationship('plan', 'name')
                    ->default(fn ($livewire) => $livewire->ownerRecord->plan_id)
                    ->searchable()
                    ->preload(),
                DatePicker::make('reference_month')
                    ->label('Mês de Competência')
                    ->default(now()->startOfMonth())
                    ->displayFormat('m/Y')
                    ->native(false)
                    ->required(),
                TextInput::make('amount')
                    ->label('Valor Cobrado')
                    ->numeric()
                    ->prefix('R$')
                    ->default(fn ($livewire) => $livewire->ownerRecord->plan?->price)
                    ->required(),
                TextInput::make('paid_amount')
                    ->label('Valor Pago')
                    ->numeric()
                    ->prefix('R$'),
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
                    ->label('Status')
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
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date', 'desc')
            ->recordTitleAttribute('reference_month')
            ->columns([
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
                //
            ])
            ->headerActions([
                CreateAction::make()->label('Nova Cobrança'),
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
                            ->title('Mensalidade recebida com sucesso!')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Payment $record): bool => $record->status !== 'pago'),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
