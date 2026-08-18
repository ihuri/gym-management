<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model que representa Cobranças, Mensalidades e Histórico Financeiro dos Alunos.
 */
class Payment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'student_id',      // ID do aluno pagador
        'plan_id',         // ID do plano contratado
        'reference_month', // Mês de referência / competência (ex: 2026-08-01)
        'amount',          // Valor cobrado da mensalidade (R$)
        'paid_amount',     // Valor efetivamente recebido (R$)
        'due_date',        // Data limite de vencimento
        'paid_at',         // Data em que o pagamento foi realizado
        'payment_method',  // Forma de pagamento: 'dinheiro', 'pix', 'cartao_credito', 'cartao_debito'
        'status',          // Status: 'pendente', 'pago', 'atrasado', 'cancelado'
        'notes',           // Observações adicionais da recepção
    ];

    /**
     * Conversão de tipos nativos dos atributos (Casts).
     */
    protected function casts(): array
    {
        return [
            'reference_month' => 'date',
            'due_date' => 'date',
            'paid_at' => 'date',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    /**
     * Relacionamento: A cobrança pertence a um Aluno.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relacionamento: A cobrança está vinculada a um Plano.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
