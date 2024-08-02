<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContaPagamentoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Rotas públicas
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);

// Rotas protegidas
Route::middleware('auth.jwt')->group(function () {
    Route::put('clientes/{id}', [ClienteController::class, 'update']);
    Route::delete('clientes/{id}', [ClienteController::class, 'destroy']);
    Route::post('clientes/alterar-senha', [ClienteController::class, 'alterarSenha']);
    Route::get('clientes', [ClienteController::class, 'index']);
    Route::get('clientes/{id}', [ClienteController::class, 'show']);
});
// Cadastrar um cliente
Route::post('clientes', [ClienteController::class, 'store']);

Route::prefix('contas')->middleware('auth.jwt')->group(function () {
    Route::post('/adicionar', [ContaPagamentoController::class, 'adicionarConta']);
    Route::post('/adicionar-creditos', [ContaPagamentoController::class, 'adicionarCreditos']);
    Route::post('/transferir-saldo', [ContaPagamentoController::class, 'transferirSaldo']);
    Route::get('/extrato', [ContaPagamentoController::class, 'consultarExtrato']);
});
