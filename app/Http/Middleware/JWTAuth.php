<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\JWTHelper;

class JWTAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        // Verifica se o token está presente
        if (!$token) {
            return response()->json(['erro' => 'Token não fornecido'], 400);
        }

        // Tenta decodificar o token
        try {
            $decoded = JWTHelper::decode($token);

            // Verifica se o token foi decodificado com sucesso
            if (!$decoded) {
                return response()->json(['erro' => 'Token inválido'], 401);
            }

            // Se necessário, adicione verificação adicional para a validade do token
            // Exemplo: Verifique se o token está expirado
            if (isset($decoded->exp) && $decoded->exp < time()) {
                return response()->json(['erro' => 'Token expirado'], 401);
            }

            // Token é válido
            // Continue com a lógica do seu código

        } catch (\Exception $e) {
            // Captura exceções específicas relacionadas ao JWT e retorna uma resposta adequada
            return response()->json(['erro' => 'Não autorizado'], 401);
        }

        $request->attributes->set('jwt.user', $decoded->sub);

        return $next($request);
    }
}
