<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaPagamento extends Model
{
    use HasFactory;

    // Nome da tabela
    protected $table = 'conta_pagamentos';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['cliente_id','nome_conta','status','saldo','limite_credito','descricao','tipo'];

    // Constantes para o campo status
    const STATUS_ATIVO = 1;
    const STATUS_SUSPENSO = 2;
    const STATUS_ENCERRADO = 3;

    // Definir os tipos de cast para garantir o formato dos campos
    protected $casts = [
        'saldo' => 'decimal:2',
        'limite_credito' => 'decimal:2',
        'status' => 'integer',
    ];

    // Relacionamento com a tabela `clientes`
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
