<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Cpf implements Rule
{
    public function passes($attribute, $value)
    {
        // Adicione a lógica de validação de CPF
        return $this->isValidCpf($value);
    }

    public function message()
    {
        return  'O :attribute não é um CPF válido.';
    }

    private function isValidCpf($cpf)
    {
        // Implementar a lógica de validação de CPF
        return preg_match('/^[0-9]{11}$/', $cpf); // Apenas um exemplo básico
    }
}
