<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ContaPagamento;
use App\Models\Transacao;
use App\Helpers\JWTHelper;
use App\Models\Cliente;
use App\Rules\Cpf;
use App\Services\ClienteService;
use App\Services\LiquidBankService;
use Illuminate\Support\Facades\Validator;

class ContaPagamentoController extends Controller
{
    protected $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    /**
     * Adiciona uma conta de pagamento para um cliente.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function adicionarConta(Request $request)
    {
        // Verifica se o cliente existe e é válido para exclusão
        $cliente = $this->clienteService->checkCliente($request);
        if (!($cliente instanceof Cliente)) {
            return $cliente; // Retorna a resposta de erro se a validação falhar
        }

        $conta = ContaPagamento::where('cliente_id', $cliente->id)->first();

        // Verifica se a conta já existe para o cliente
        if ($conta) {
            return response()->json([
                'message' => 'O cliente já possui uma conta de pagamento.',
                'conta' => $conta,
            ], 200);
        }

        // Cria uma nova conta de pagamento para o cliente
        $novaConta = ContaPagamento::create([
            'cliente_id' => $cliente->id,
            'saldo' => 0,
        ]);

        return response()->json([
            'message' => 'Conta de pagamento criada com sucesso.',
            'conta' => $novaConta,
        ], 201);
    }

    /**
     * Adiciona créditos à conta de pagamento do cliente.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function adicionarCreditos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:credit,debit,pix',
            'number' => 'required_if:type,credit,debit|nullable|digits:16',
            'brand' => 'required_if:type,credit,debit|nullable|in:visa,master',
            'valid' => 'required_if:type,credit,debit|nullable|date_format:m/y',
            'cvv' => 'required_if:type,credit,debit|nullable|digits:3',
            'chavepix' => 'required_if:type,pix|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Verifica se o cliente existe e é válido para exclusão
        $cliente = $this->clienteService->checkCliente($request);
        if (!($cliente instanceof Cliente)) {
            return $cliente; // Retorna a resposta de erro se a validação falhar
        }

        $valor = floatval($request->input('amount'));
        $conta = $cliente->contas->first();
        
        $metodo_pagamento = json_encode($request->except('cvv'));
        $transacao = [
            'conta_pagamento_id' => $conta->id,
            'cliente_id' => $cliente->id,
            'tipo' => Transacao::TIPO_CREDITO,
            'status' => Transacao::STATUS_CONCLUIDA,
            'valor' => $valor,
            'saldo_apos_transacao' => $conta->saldo,
            'metodo_pagamento' => $metodo_pagamento
        ];

        if (($valor * 100) % 2 != 0) {
            // Registrar a tentativa de transação falha
            $transacao['status'] = Transacao::STATUS_FALHADA;
            $transacao['descricao'] = 'Tentativa falhou via LiquidBank <br>Valor precisa ser Par!';
            Transacao::create($transacao);
            
            return response()->json([
                'message' => 'Falha ao adicionar créditos.',
                'descricao' => 'Valor precisa ser Par!'
            ], 400);
        }

        if($request->type != 'pix'){
            // Lógica para adicionar créditos via LiquidBank (simplificado)
            $response = (new LiquidBankService())->authorize($request->all());
            $transacao['referencia_externa'] = json_encode($response);

            if (isset($response['error'])) {
                // Registrar a tentativa de transação falha
                $transacao['status'] = Transacao::STATUS_FALHADA;
                $transacao['descricao'] = 'Tentativa falhou via LiquidBank <br>' . $response['error']['description'];
                Transacao::create($transacao);
    
                return response()->json([
                    'message' => 'Falha ao adicionar créditos.',
                    'descricao' => $transacao['descricao']
                ], 400);
            }
        }
        
        $conta->saldo += $valor;
        $conta->save();

        $transacao['descricao'] = 'Crédito adicionado via LiquidBank';
        $transacao['saldo_apos_transacao'] = $conta->saldo;
        Transacao::create($transacao);

        return response()->json([
            'message' => 'Créditos adicionados com sucesso.',
        ], 200);
    }

    /**
     * Realiza uma transferência de valores entre contas.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function transferirSaldo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'destinatario_cpf' => ['required', 'string', new Cpf],
            'valor' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Verifica se o cliente existe e é válido para exclusão
        $cliente = $this->clienteService->checkCliente($request);
        if (!($cliente instanceof Cliente)) {
            return $cliente; // Retorna a resposta de erro se a validação falhar
        }

        $valor = floatval($request->input('valor'));
        $contaOrigem = $cliente->contas->first();
        
        $destinatarioCpf = $request->input('destinatario_cpf');
        $contaDestino = ContaPagamento::whereHas('cliente', function ($query) use ($destinatarioCpf) {
            $query->where('cpf', $destinatarioCpf);
        })->first();

        if (!$contaDestino) {
            return response()->json([
                'message' => 'Destinatário não encontrado.',
            ], 404);
        }

        if ($contaOrigem->saldo < $valor) {
            return response()->json([
                'message' => 'Saldo insuficiente.',
            ], 400);
        }

        $metodo_pagamento = $request->all();
        $metodo_pagamento['type'] = 'transferencia';
        $metodo_pagamento = json_encode($metodo_pagamento);

        DB::transaction(function () use ($contaOrigem, $contaDestino, $valor, $metodo_pagamento) {
            $contaOrigem->saldo -= $valor;
            $contaOrigem->save();

            $contaDestino->saldo += $valor;
            $contaDestino->save();


            // Registrar a transação
            Transacao::create([
                'conta_pagamento_id' => $contaOrigem->id,
                'cliente_id' => $contaOrigem->cliente_id,
                'tipo' => 2,
                'valor' => $valor,
                'saldo_apos_transacao' => $contaOrigem->saldo,
                'status' => Transacao::STATUS_CONCLUIDA,
                'descricao' => 'Transferência para conta ID ' . $contaDestino->id,
                'metodo_pagamento' => $metodo_pagamento
            ]);
            
            Transacao::create([
                'conta_pagamento_id' => $contaDestino->id,
                'cliente_id' => $contaDestino->cliente_id,
                'tipo' => 1,
                'valor' => $valor,
                'saldo_apos_transacao' => $contaDestino->saldo,
                'status' => Transacao::STATUS_CONCLUIDA,
                'descricao' => 'Transferência recebida da conta ID ' . $contaOrigem->id,
                'metodo_pagamento' => $metodo_pagamento
            ]);
        });

        return response()->json([
            'message' => 'Transferência realizada com sucesso.',
        ], 200);
    }

    /**
     * Consulta o extrato da conta de pagamento.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function consultarExtrato(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inicio' => 'required|date',
            'fim' => 'required|date|after_or_equal:inicio',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Verifica se o cliente existe e é válido para exclusão
        $cliente = $this->clienteService->checkCliente($request);
        if (!($cliente instanceof Cliente)) {
            return $cliente; // Retorna a resposta de erro se a validação falhar
        }

        $inicio = $request->input('inicio');
        $fim = $request->input('fim');
        
        $conta = $cliente->contas->first();
        $conta['transacoes'] = Transacao::where('conta_pagamento_id', $conta->id)
            ->where('status', '<', Transacao::STATUS_FALHADA)
            ->whereBetween('data_transacao', [$inicio, $fim])
            ->get()
            ->map(function ($transacao) {
                return [
                    'id' => $transacao->id,
                    'tipo' => $transacao->tipos_tratado, 
                    'data_transacao' => $transacao->data_transacao,
                    'valor' => $transacao->valor
                ];
            });;

        return response()->json([
            'message' => 'Extrato consultado com sucesso.',
            'conta' => $conta,
        ], 200);
    }
}
