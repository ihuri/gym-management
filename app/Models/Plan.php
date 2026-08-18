<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model que representa os Planos de Treino da Academia (ex: Mensal, Trimestral).
 */
class Plan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'name',            // Nome do plano (ex: Mensal 2x/sem)
        'price',           // Valor da mensalidade (R$)
        'duration_months', // Duração do contrato em meses
        'description',     // Descrição e detalhes do plano
        'active',          // Se o plano está ativo para venda
    ];

    /**
     * Conversão de tipos nativos dos atributos (Casts).
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_months' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * Relacionamento: Um plano possui muitos alunos matriculados.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Relacionamento: Um plano possui muitos registros de cobrança/pagamentos gerados.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
