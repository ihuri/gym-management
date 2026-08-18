<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model que representa os Horários Semanais Fixos da Academia com controle de capacidade.
 */
class TimeSlot extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'day_of_week', // Dia da semana (0=Domingo a 6=Sábado)
        'start_time',  // Horário de início
        'end_time',    // Horário de término
        'capacity',    // Limite máximo de alunos no horário
        'active',      // Se o horário está ativo na grade
    ];

    /**
     * Conversão de tipos nativos dos atributos (Casts).
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'capacity' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * Relacionamento: Todos os alunos matriculados neste horário.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_time_slot')
            ->withPivot(['id', 'status', 'enrolled_at'])
            ->withTimestamps();
    }

    /**
     * Relacionamento: Apenas os alunos com matrícula ativa neste horário.
     */
    public function activeStudents(): BelongsToMany
    {
        return $this->students()->wherePivot('status', 'ativo');
    }

    /**
     * Relacionamento: Fechamentos pontuais agendados para este horário específico.
     */
    public function closures(): HasMany
    {
        return $this->hasMany(ScheduleClosure::class);
    }

    /**
     * Acessor: Retorna o nome em português do dia da semana.
     */
    public function getDayNameAttribute(): string
    {
        return match ($this->day_of_week) {
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
            default => 'Desconhecido',
        };
    }

    /**
     * Acessor: Retorna a faixa de horário formatada (ex: "07:00 - 08:00").
     */
    public function getFormattedTimeRangeAttribute(): string
    {
        $start = substr($this->start_time, 0, 5);
        $end = substr($this->end_time, 0, 5);

        return "{$start} - {$end}";
    }

    /**
     * Acessor: Rótulo completo para exibição em selects e listagens.
     */
    public function getLabelAttribute(): string
    {
        return "{$this->day_name} ({$this->formatted_time_range})";
    }
}
