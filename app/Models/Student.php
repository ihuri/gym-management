<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model que representa os Alunos cadastrados na Academia.
 */
class Student extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'name',            // Nome completo do aluno
        'cpf',             // CPF único do aluno
        'email',           // E-mail de contato
        'phone',           // Telefone celular / WhatsApp
        'birth_date',      // Data de nascimento
        'address',         // Endereço completo
        'photo_path',      // Caminho do arquivo da foto de perfil
        'status',          // Situação da matrícula: 'ativo', 'inativo', 'suspenso'
        'enrollment_date', // Data da matrícula
        'plan_id',         // ID do plano contratado
        'payment_day',     // Dia preferencial do vencimento da mensalidade (1 a 31)
    ];

    /**
     * Conversão de tipos nativos dos atributos (Casts).
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'enrollment_date' => 'date',
            'payment_day' => 'integer',
        ];
    }

    /**
     * Relacionamento: O aluno pertence a um Plano.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Relacionamento: O aluno está matriculado em vários horários da semana (via tabela pivot).
     */
    public function timeSlots(): BelongsToMany
    {
        return $this->belongsToMany(TimeSlot::class, 'student_time_slot')
            ->withPivot(['id', 'status', 'enrolled_at'])
            ->withTimestamps();
    }

    /**
     * Relacionamento: O aluno possui muitas cobranças/mensalidades geradas.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
