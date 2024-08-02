<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cpf')->unique();
            $table->string('email')->unique();
            $table->string('senha');
            $table->timestamps();
        });

        // Criação da tabela 'senhas_antigas'
        Schema::create('senhas_antigas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('senha'); // Senha hashada
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Exclusão da tabela 'senhas_antigas'
        Schema::dropIfExists('senhas_antigas');

        // Exclusão da tabela 'clientes'
        Schema::dropIfExists('clientes');
    }
}
