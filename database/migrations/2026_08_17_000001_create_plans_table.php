<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a migration para criação da tabela de planos.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id(); // Identificador único do plano
            $table->string('name'); // Nome do plano (ex: Mensal 2x/sem, Anual)
            $table->decimal('price', 10, 2); // Valor da mensalidade em reais (R$)
            $table->unsignedTinyInteger('duration_months')->default(1); // Duração do contrato em meses (1 = Mensal, 3 = Trimestral, etc.)
            $table->text('description')->nullable(); // Descrição detalhada dos benefícios do plano
            $table->boolean('active')->default(true); // Se o plano está ativo para novas matrículas
            $table->timestamps(); // Data de criação e última atualização (created_at, updated_at)
            $table->softDeletes(); // Exclusão lógica (deleted_at) para preservar histórico financeiro
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
