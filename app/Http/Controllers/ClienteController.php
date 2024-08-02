<?php

namespace App\Http\Controllers;

use App\Helpers\JWTHelper;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\SenhaAntiga;
use App\Rules\Cpf;
use App\Rules\Senha;
use App\Services\ClienteService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    protected $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    // Cadastrar um novo cliente
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'cpf' => ['required', 'string', 'unique:clientes', new Cpf], // Validar CPF
            'email' => 'required|string|email|unique:clientes',
            'senha' => ['required', 'string', 'min:8', new Senha],
            'telefone' => 'nullable|string',
            'data_nascimento' => 'nullable|date',
            'status' => 'nullable|integer|in:1,2,3',
            'observacoes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $request['senha'] = Hash::make($request->senha);
        $cliente = Cliente::create($request->all());

        $cliente->makeHidden('senha');
        return response()->json($cliente, 201);
    }

    // Consultar os detalhes de um cliente
    public function show($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente não encontrado.'], 404);
        }

        $cliente->makeHidden('senha');
        return response()->json($cliente);
    }

    // Atualizar informações do cliente
    public function update(Request $request, $id)
    {
        // Verifica se o cliente existe e é válido para exclusão
        $cliente = $this->clienteService->checkCliente($request, $id);
        if (!($cliente instanceof Cliente)) {
            return $cliente; // Retorna a resposta de erro se a validação falhar
        }

        // Validação dos dados de entrada
        $validator = Validator::make($request->all(), [
            'nome' => 'nullable|string|max:255',
            'cpf' => ['nullable', 'string', 'unique:clientes,cpf,' . $id, new Cpf],
            'email' => ['nullable', 'string', 'email', 'unique:clientes,email,' . $id],
            'telefone' => 'nullable|string',
            'data_nascimento' => 'nullable|date',
            'status' => 'nullable|integer|in:1,2,3',
            'observacoes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Verifica se o campo 'senha' está presente na solicitação
        if ($request->has('senha')) {
            return response()->json(['error' => 'Senha não deve ser incluída na atualização.'], 400);
        }

        $data = $request->except('senha');
        $cliente->update($data);

        $cliente->makeHidden('senha');
        return response()->json($cliente);
    }

    // Excluir um cliente
    public function destroy(Request $request, $id)
    {
       // Verifica se o cliente existe e é válido para exclusão
        $cliente = $this->clienteService->checkCliente($request, $id);
        if (!($cliente instanceof Cliente)) {
            return $cliente; // Retorna a resposta de erro se a validação falhar
        }

        $cliente->delete();

        return response()->json(['message' => 'Cliente excluído com sucesso.']);
    }

    // Alterar a senha do cliente
    public function alterarSenha(Request $request)
    {
        $cliente = $this->clienteService->checkCliente($request);
        if (!($cliente instanceof Cliente)) {
            return $cliente; // Retorna a resposta de erro se a validação falhar
        }
        
        $validator = Validator::make($request->all(), [
            'nova_senha' => ['required', 'string', 'min:8', new Senha],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Verifica se a nova senha já foi usada anteriormente
        $novaSenhaHash = Hash::make($request->nova_senha);
        if (Hash::check($request->nova_senha, $cliente->senha)) {
            return response()->json(['message' => 'Senha não pode ser reutilizada.'], 400);
        }

        $senhasAntigas = $cliente->senhasAntigas->pluck('senha')->toArray();

        foreach ($senhasAntigas as $senhaAntiga) {
            if (Hash::check($request->nova_senha, $senhaAntiga)) {
                return response()->json(['message' => 'Senha não pode ser reutilizada.'], 400);
            }
        }

        // Adiciona a nova senha à tabela de senhas antigas
        SenhaAntiga::create([
            'cliente_id' => $cliente->id,
            'senha' => $novaSenhaHash,
        ]);

        // Atualiza a senha do cliente
        $cliente->senha = $novaSenhaHash;
        $cliente->save();

        return response()->json(['message' => 'Senha alterada com sucesso!']);
    }

    // Listar todos os clientes
    public function index()
    {
        $clientes = Cliente::all();
        return response()->json($clientes);
    }
}
