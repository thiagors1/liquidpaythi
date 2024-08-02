<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transacao extends Model
{
    use HasFactory, SoftDeletes;

    // Nome da tabela
    protected $table = 'transacoes';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['conta_pagamento_id','cliente_id','tipo','valor','descricao','data_transacao','saldo_apos_transacao','metodo_pagamento','referencia_externa','status'];

    // Constantes para o campo tipo
    const TIPO_CREDITO = 1;
    const TIPO_DEBITO = 2;

    // Constantes para o campo status
    const STATUS_PENDENTE = 1;
    const STATUS_CONCLUIDA = 2;
    const STATUS_FALHADA = 99;

    // Definir os tipos de cast para garantir o formato dos campos
    protected $casts = [
        'valor' => 'decimal:2',
        'saldo_apos_transacao' => 'decimal:2',
        'data_transacao' => 'datetime',
        'tipo' => 'integer',
        'status' => 'integer',
    ];

    // Relacionamento com a tabela `conta_pagamentos`
    public function contaPagamento()
    {
        return $this->belongsTo(ContaPagamento::class);
    }

    // Relacionamento com a tabela `clientes`
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function getTiposTratadoAttribute()
    {
        $tipos = [
            self::TIPO_CREDITO => 'crédito',
            self::TIPO_DEBITO => 'débito'
        ];

        return $tipos[$this->tipo] ?? 'desconhecido';
    }
}
