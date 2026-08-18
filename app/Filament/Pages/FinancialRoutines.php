<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FinancialStatsOverview;
use App\Services\PaymentService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Página Customizada para Execução e Monitoramento de Rotinas Financeiras e Automações.
 */
class FinancialRoutines extends Page
{
    protected string $view = 'filament.pages.financial-routines';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?string $navigationLabel = 'Rotinas e Automações';

    protected static ?string $title = 'Rotinas Financeiras e Automações';

    protected static string|UnitEnum|null $navigationGroup = 'Financeiro';

    protected static ?int $navigationSort = 2;

    protected function getHeaderWidgets(): array
    {
        return [
            FinancialStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('runFullRoutine')
                ->label('Executar Rotina Diária Completa')
                ->icon(Heroicon::OutlinedPlay)
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Executar Rotina Financeira Completa')
                ->modalDescription('O sistema irá gerar as cobranças do mês corrente, atualizar os vencimentos atrasados e enviar alertas aos administradores.')
                ->action(function (PaymentService $paymentService): void {
                    $generated = $paymentService->generateMonthlyPayments();
                    $overdue = $paymentService->updateOverduePayments();
                    $notified = $paymentService->notifyAdminsAboutOverduePayments();

                    Notification::make()
                        ->title('Rotina executada com sucesso!')
                        ->body("Geradas: {$generated} | Atualizadas em atraso: {$overdue} | Inadimplentes alertados: {$notified}")
                        ->success()
                        ->send();
                }),

            Action::make('generatePayments')
                ->label('Gerar Mensalidades do Mês')
                ->icon(Heroicon::OutlinedDocumentPlus)
                ->color('success')
                ->form([
                    DatePicker::make('month')
                        ->label('Mês de Competência')
                        ->default(now()->startOfMonth())
                        ->displayFormat('m/Y')
                        ->native(false)
                        ->required(),
                ])
                ->action(function (array $data, PaymentService $paymentService): void {
                    $month = Carbon::parse($data['month']);
                    $generated = $paymentService->generateMonthlyPayments($month);

                    Notification::make()
                        ->title("Mensalidades geradas para {$month->format('m/Y')}!")
                        ->body("{$generated} cobranças foram criadas para os alunos ativos.")
                        ->success()
                        ->send();
                }),

            Action::make('updateOverdue')
                ->label('Atualizar Inadimplentes')
                ->icon(Heroicon::OutlinedExclamationCircle)
                ->color('warning')
                ->action(function (PaymentService $paymentService): void {
                    $updated = $paymentService->updateOverduePayments();

                    Notification::make()
                        ->title('Status de pagamentos atualizado!')
                        ->body("{$updated} mensalidade(s) vencida(s) marcada(s) como 'atrasado'.")
                        ->info()
                        ->send();
                }),

            Action::make('sendAlerts')
                ->label('Notificar Administradores')
                ->icon(Heroicon::OutlinedBellAlert)
                ->color('danger')
                ->action(function (PaymentService $paymentService): void {
                    $count = $paymentService->notifyAdminsAboutOverduePayments();

                    if ($count > 0) {
                        Notification::make()
                            ->title('Notificações enviadas!')
                            ->body("Alerta de {$count} mensalidade(s) em atraso enviado para o sino de todos os administradores.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Sem pendências!')
                            ->body('Não há mensalidades em atraso para notificar no momento.')
                            ->info()
                            ->send();
                    }
                }),
        ];
    }
}
