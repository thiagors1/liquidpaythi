<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Helpers\JWTHelper;
use App\Rules\Cpf;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login'); // Retorna a view do formulário de login
    }

    public function login(Request $request)
    {
        $login = $request->input('login');
        $senha = $request->input('senha');

        $typeLogin = 'cpf';
        if (strpos($login, '@') !== false) {
            $typeLogin = 'email';
        }

        // Validação dos campos de login
        $validator = Validator::make($request->all(), [
            'login' => ['required', 'string', ($typeLogin == 'cpf' ? new Cpf() : 'email')],
            'senha' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }


        // Tenta encontrar o usuário usando CPF ou email
        $user = Cliente::where('cpf', $login)
                        ->orWhere('email', $login)
                        ->first();

        // Verifica se o usuário existe e se a senha está correta
        if ($user && Hash::check($senha, $user->senha)) {
            // Gera o payload para o JWT
            $payload = [
                'sub' => $user->id,
                'nome' => $user->nome,
                'cpf' => $user->cpf,
                'email' => $user->email,
                'iat' => time(),
                'exp' => time() + (60 * 60), // Token expira em 1 hora
            ];

            // Gera o token JWT
            $token = JWTHelper::encode($payload);

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid CPF, email, or password',
        ], 401);
    }

    public function logout(Request $request)
    {
        // Implementar logout, geralmente invalida o token no lado do cliente
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function me(Request $request)
    {
        $token = $request->header('Authorization');

        if (!$token || !($decoded = JWTHelper::decode($token))) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        $cliente = Cliente::find($decoded->sub);

        if (!$cliente) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($cliente);
    }
}
