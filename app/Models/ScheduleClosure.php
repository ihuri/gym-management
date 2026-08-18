<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model que representa Fechamentos Pontuais da Academia (feriados, manutenções).
 */
class ScheduleClosure extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'date',         // Data específica do fechamento (ex: 2026-12-25)
        'time_slot_id', // ID do horário fechado. Se for NULL, fecha a academia o dia todo
        'reason',       // Motivo do fechamento (ex: "Feriado de Natal")
    ];

    /**
     * Conversão de tipos nativos dos atributos (Casts).
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Relacionamento: Fechamento pode pertencer a um horário específico.
     */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }
}
