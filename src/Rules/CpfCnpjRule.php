<?php

namespace Shieldforce\CheckoutPayment\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfCnpjRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = preg_replace('/\D/', '', $value);

        if (strlen($value) === 11) {
            if (! $this->validateCpf($value)) {
                $fail("O campo {$attribute} não é um CPF válido.");
            }
        } elseif (strlen($value) === 14) {
            if (! $this->validateCnpj($value)) {
                $fail("O campo {$attribute} não é um CNPJ válido.");
            }
        } else {
            $fail("O campo {$attribute} não é um CPF ou CNPJ válido.");
        }
    }

    private function validateCpf(string $cpf): bool
    {
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        } // números iguais não são válidos

        $sum = 0;
        for ($i = 0, $j = 10; $i < 9; $i++, $j--) {
            $sum += $cpf[$i] * $j;
        }
        $rest = $sum % 11;
        $digit1 = ($rest < 2) ? 0 : 11 - $rest;

        $sum = 0;
        for ($i = 0, $j = 11; $i < 10; $i++, $j--) {
            $sum += $cpf[$i] * $j;
        }
        $rest = $sum % 11;
        $digit2 = ($rest < 2) ? 0 : 11 - $rest;

        return $cpf[9] == $digit1 && $cpf[10] == $digit2;
    }

    private function validateCnpj(string $cnpj): bool
    {
        /*$cnpj = preg_replace('/\D/', '', $cnpj);

        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        $lengths = [5,6];
        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $lengths[$i % 2];
        }

        $rest = $sum % 11;
        $digit1 = ($rest < 2) ? 0 : 11 - $rest;

        $sum = 0;
        $lengths = [6,5];
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $lengths[$i % 2];
        }

        $rest = $sum % 11;
        $digit2 = ($rest < 2) ? 0 : 11 - $rest;

        return $cnpj[12] == $digit1 && $cnpj[13] == $digit2;*/

        return true;
    }
}
