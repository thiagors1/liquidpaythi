<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransacaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_pagamento_id')->constrained('conta_pagamentos')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->tinyInteger('tipo'); // Tipo de transação (1: crédito, 2: débito)
            $table->decimal('valor', 10, 2); // Valor da transação
            $table->text('descricao')->nullable(); // Descrição detalhada
            $table->timestamp('data_transacao')->useCurrent(); // Data e hora da transação
            $table->decimal('saldo_apos_transacao', 10, 2); // Saldo após a transação
            $table->string('metodo_pagamento')->nullable(); // Método de pagamento
            $table->string('referencia_externa')->nullable(); // Referência externa
            $table->tinyInteger('status')->default(1); // Status da transação (1: pendente, 2: concluída, 3: falhada)
            $table->softDeletes(); // Adiciona o campo deleted_at
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
        Schema::dropIfExists('transacaos');
    }
}
