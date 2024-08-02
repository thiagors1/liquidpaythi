<?php

namespace App\Services;

use App\Models\Cliente;
use App\Helpers\JWTHelper;
use Illuminate\Http\Request;

class ClienteService
{
    public function checkCliente(Request $request, $id = NULL)
    {
        $token = $request->header('Authorization');

        if (!$token || !($decoded = JWTHelper::decode($token))) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        if ($id) {
            if ($decoded->sub != $id) {
                return response()->json(['message' => 'Cliente inválido.'], 404);
            }
        } else {
            $id = $decoded->sub;
        }

        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente não encontrado.'], 404);
        }

        return $cliente;
    }
}
