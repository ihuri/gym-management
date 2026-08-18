<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a migration para criação da tabela de fechamentos pontuais de agenda.
     */
    public function up(): void
    {
        Schema::create('schedule_closures', function (Blueprint $table) {
            $table->id(); // Identificador único do fechamento
            $table->date('date'); // Data específica do fechamento (ex: 2026-12-25)
            $table->foreignId('time_slot_id')->nullable()->constrained('time_slots')->nullOnDelete(); // Horário específico afetado. Se NULL = dia inteiro fechado
            $table->string('reason')->nullable(); // Motivo do fechamento (ex: "Feriado Nacional", "Manutenção dos equipamentos")
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
