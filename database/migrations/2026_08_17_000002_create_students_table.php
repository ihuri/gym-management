<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a migration para criação da tabela de alunos.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id(); // Identificador único do aluno
            $table->string('name'); // Nome completo do aluno
            $table->string('cpf', 14)->unique(); // CPF com máscara ou somente dígitos (único)
            $table->string('email')->nullable(); // E-mail para contato (opcional)
            $table->string('phone', 20); // Telefone / WhatsApp do aluno
            $table->date('birth_date'); // Data de nascimento
            $table->string('address')->nullable(); // Endereço residencial (opcional)
            $table->string('photo_path')->nullable(); // Caminho da foto do perfil (storage)
            $table->string('status')->default('ativo'); // Status da matrícula: 'ativo', 'inativo', 'suspenso'
            $table->date('enrollment_date'); // Data em que o aluno se matriculou na academia
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete(); // Chave estrangeira para o plano contratado
            $table->unsignedTinyInteger('payment_day')->default(1); // Dia do mês para vencimento da mensalidade (1 a 31)
            $table->timestamps(); // created_at e updated_at
            $table->softDeletes(); // Exclusão lógica (deleted_at) para manter histórico
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
