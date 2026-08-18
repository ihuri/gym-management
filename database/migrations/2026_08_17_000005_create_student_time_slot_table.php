<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a migration da tabela pivot entre alunos e horários semanais.
     */
    public function up(): void
    {
        Schema::create('student_time_slot', function (Blueprint $table) {
            $table->id(); // Identificador único da matrícula no horário
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete(); // Chave estrangeira do aluno
            $table->foreignId('time_slot_id')->constrained('time_slots')->cascadeOnDelete(); // Chave estrangeira do horário
            $table->string('status')->default('ativo'); // Status da vaga do aluno: 'ativo', 'cancelado'
            $table->date('enrolled_at')->nullable(); // Data em que o aluno foi vinculado a este horário
            $table->timestamps(); // created_at e updated_at

            // Garante que o mesmo aluno não seja matriculado em duplicidade no mesmo horário
            $table->unique(['student_id', 'time_slot_id']);
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_time_slot');
    }
};
