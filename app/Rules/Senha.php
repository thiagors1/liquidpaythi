<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Senha implements Rule
{
    protected $message;

    /**
     * Determine se a regra de validação passa.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!preg_match('/[A-Z]/', $value)) {
            $this->message = 'uma letra maiúscula';
        }

        if (!preg_match('/[a-z]/', $value)) {
            $this->message .= $this->message ? ', ' : '' ;
            $this->message .= 'uma letra minúscula';
        }
        
        if (!preg_match('/\d/', $value)) {
            $this->message .= $this->message ? ', ' : '' ;
            $this->message .=  'um número';
        }
        
        if (!preg_match('/[\W_]/', $value)) {
            $this->message .= $this->message ? ', ' : '' ;
            $this->message .=  'um caractere especial';
        }
        
        if($this->message){
            $this->message = 'A senha deve conter pelo menos '.$this->message.'.';
            return false;
        }
        
        return true;
    }

    /**
     * Obtenha a mensagem de erro da validação.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
