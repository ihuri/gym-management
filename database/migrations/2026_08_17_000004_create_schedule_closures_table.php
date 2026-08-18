<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a migration para criação da tabela de fechamentos pontuais e recorrentes de agenda.
     */
    public function up(): void
    {
        Schema::create('schedule_closures', function (Blueprint $table) {
            $table->id(); // Identificador único do fechamento
            $table->string('type')->default('data_especifica'); // 'data_especifica' (pontual) ou 'recorrente' (dia da semana fixo)
            $table->date('date')->nullable(); // Data específica (se type = 'data_especifica')
            $table->unsignedTinyInteger('day_of_week')->nullable(); // Dia da semana recorrente: 0=Domingo a 6=Sábado (se type = 'recorrente')
            $table->foreignId('time_slot_id')->nullable()->constrained('time_slots')->nullOnDelete(); // Horário específico. Se NULL = dia inteiro fechado
            $table->string('reason')->nullable(); // Motivo do fechamento (ex: "Sem funcionamento aos sábados", "Feriado")
            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_closures');
    }
};
