<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterClientesTableAddOptionalFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('telefone')->nullable()->after('email');
            $table->date('data_nascimento')->nullable()->after('telefone');
            $table->date('data_cadastro')->default(now())->after('data_nascimento');
            $table->tinyInteger('status')->default(1)->after('data_cadastro');
            $table->text('observacoes')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['telefone', 'data_nascimento', 'data_cadastro', 'status', 'observacoes']);
        });
    }
}
