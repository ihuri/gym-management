<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a migration para criação da grade de horários semanais.
     */
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id(); // Identificador único do horário
            $table->unsignedTinyInteger('day_of_week'); // Dia da semana: 0=Domingo, 1=Segunda, 2=Terça, 3=Quarta, 4=Quinta, 5=Sexta, 6=Sábado
            $table->time('start_time'); // Horário de início da aula/treino (ex: 07:00:00)
            $table->time('end_time'); // Horário de término da aula/treino (ex: 08:00:00)
            $table->unsignedInteger('capacity')->default(10); // Capacidade máxima de alunos simultâneos no horário
            $table->boolean('active')->default(true); // Se o horário está ativo na grade semanal da academia
            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
