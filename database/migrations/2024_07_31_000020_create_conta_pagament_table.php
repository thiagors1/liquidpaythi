<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContaPagamentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conta_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('nome_conta')->nullable(); // Nome da conta
            $table->tinyInteger('status')->default(1); // Status da conta (1: Ativo, 2: Suspenso, 3: Encerrado)
            $table->decimal('saldo', 10, 2)->default(0.00); // Saldo da conta
            $table->decimal('limite_credito', 10, 2)->nullable(); // Limite de crédito
            $table->text('descricao')->nullable(); // Descrição adicional
            $table->string('tipo')->nullable(); // Tipo de conta (pessoal, empresarial, etc.)
            $table->timestamps(); // Inclui created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conta_pagamentos');
    }
}
