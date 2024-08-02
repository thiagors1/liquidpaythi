<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SenhaAntiga extends Model
{
    use HasFactory;

    // Defina o nome da tabela, se não for o padrão pluralizado
    protected $table = 'senhas_antigas';

    // Defina os campos que podem ser preenchidos em massa
    protected $fillable = ['cliente_id','senha'];

    // Relacionamento com a tabela `clientes`
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
