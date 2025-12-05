<?php

namespace Shieldforce\CheckoutPayment\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ImageUrlRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        /*try {
            $headers = @get_headers($value, 1);

            if ($headers === false || strpos($headers[0], '200') === false) {
                $fail("A URL fornecida em {$attribute} não está acessível.");
                return;
            }

            $contentType = $headers['Content-Type'] ?? null;

            if (is_array($contentType)) {
                $contentType = $contentType[0];
            }

            if (!$contentType || !str_starts_with($contentType, 'image/')) {
                $fail("A URL fornecida em {$attribute} não é uma imagem válida.");
            }
        } catch (\Throwable $e) {
            $fail("Não foi possível validar a imagem em {$attribute}.");
        }*/
    }
}

