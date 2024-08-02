<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = ['nome','cpf','email','senha','telefone','data_nascimento','data_cadastro','status','observacoes'];

    // Constantes para o campo status
    const STATUS_ATIVO = 1;
    const STATUS_INATIVO = 2;
    const STATUS_BLOQUEADO = 3;

    // Definir os tipos de cast para garantir o formato dos campos
    protected $casts = [
        'data_nascimento' => 'date',
        'data_cadastro' => 'date',
        'status' => 'integer',
    ];

    public function contas()
    {
        return $this->hasMany(ContaPagamento::class);
    }

    public function transacoes()
    {
        return $this->hasMany(Transacao::class);
    }

    public function senhasAntigas()
    {
        return $this->hasMany(SenhaAntiga::class);
    }
}
