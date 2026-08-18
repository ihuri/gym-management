<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model que representa Fechamentos Pontuais ou Recorrentes da Academia (feriados, manutenções, dias sem treino).
 */
class ScheduleClosure extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'type',         // Tipo: 'data_especifica' ou 'recorrente'
        'date',         // Data específica do fechamento (quando type = 'data_especifica')
        'day_of_week',  // Dia da semana recorrente: 0=Domingo a 6=Sábado (quando type = 'recorrente')
        'time_slot_id', // ID do horário fechado. Se for NULL, fecha a academia o dia todo
        'reason',       // Motivo do fechamento (ex: "Sem funcionamento aos sábados", "Feriado de Natal")
    ];

    /**
     * Conversão de tipos nativos dos atributos (Casts).
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'day_of_week' => 'integer',
        ];
    }

    /**
     * Relacionamento: Fechamento pode pertencer a um horário específico.
     */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    /**
     * Acessor: Retorna a descrição legível do dia da semana recorrente.
     */
    public function getDayNameAttribute(): ?string
    {
        if ($this->day_of_week === null) {
            return null;
        }

        return match ($this->day_of_week) {
            0 => 'Todos os Domingos',
            1 => 'Todas as Segundas-feiras',
            2 => 'Todas as Terças-feiras',
            3 => 'Todas as Quartas-feiras',
            4 => 'Todas as Quintas-feiras',
            5 => 'Todas as Sextas-feiras',
            6 => 'Todos os Sábados',
            default => 'Desconhecido',
        };
    }
}
