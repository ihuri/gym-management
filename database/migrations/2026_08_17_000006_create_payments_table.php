<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a migration da tabela de pagamentos e mensalidades.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // Identificador único da fatura/mensalidade
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete(); // Chave estrangeira do aluno pagador
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete(); // Chave estrangeira do plano de referência
            $table->date('reference_month'); // Mês de competência da mensalidade (ex: 2026-08-01)
            $table->decimal('amount', 10, 2); // Valor cobrado da mensalidade em reais (R$)
            $table->decimal('paid_amount', 10, 2)->nullable(); // Valor efetivamente pago (com eventuais descontos ou juros)
            $table->date('due_date'); // Data limite de vencimento calculada
            $table->date('paid_at')->nullable(); // Data em que o pagamento foi realizado
            $table->string('payment_method')->nullable(); // Método de pagamento: 'dinheiro', 'pix', 'cartao_credito', 'cartao_debito'
            $table->string('status')->default('pendente'); // Status da fatura: 'pendente', 'pago', 'atrasado', 'cancelado'
            $table->text('notes')->nullable(); // Observações financeiras da recepção
            $table->timestamps(); // created_at e updated_at
            $table->softDeletes(); // Exclusão lógica (deleted_at) para integridade contábil
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
