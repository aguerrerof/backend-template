<?php

namespace App\Helpers;

class ServiceResponse
{
    public static function success($data = null, string $message = 'Operación exitosa'): array
    {
        return [
            'data' => $data,
            'message' => $message,
            'error' => null,
            'devError' => null,
        ];
    }

    public static function error(string $message = 'Error en la operación', string $devError = ''): array
    {
        return [
            'data' => null,
            'message' => null,
            'error' => $message,
            'devError' => $devError,
        ];
    }
}
