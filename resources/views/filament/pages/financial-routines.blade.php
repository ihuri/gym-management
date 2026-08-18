<x-filament-panels::page>
    <x-filament::section
        heading="Automações e Cron Diário da Academia"
        description="Rotinas financeiras executadas automaticamente em segundo plano"
        icon="heroicon-o-information-circle"
    >
        <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
            <p>
                <strong>Cron Automático:</strong> O sistema está configurado para executar diariamente às <strong>06:00</strong> o comando <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-gray-800">app:check-payments-and-notify</code>.
            </p>
            <ul class="list-disc pl-5 space-y-1.5">
                <li><strong>Geração de Mensalidades:</strong> Cria automaticamente a cobrança do mês para todos os alunos ativos que ainda não possuam mensalidade criada.</li>
                <li><strong>Cálculo Inteligente de Vencimento:</strong> Respeita o dia preferencial do aluno e ajusta para o último dia do mês quando necessário (ex: dia 31 em meses de 28 ou 30 dias).</li>
                <li><strong>Verificação de Inadimplência:</strong> Identifica parcelas vencidas e altera o status para <span class="font-semibold text-danger-600 dark:text-danger-400">Atrasado</span>.</li>
                <li><strong>Notificações no Painel:</strong> Envia alertas no sino administrativo sobre pagamentos em atraso.</li>
            </ul>
        </div>
    </x-filament::section>
</x-filament-panels::page>
