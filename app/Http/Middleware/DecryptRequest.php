<?php

namespace App\Http\Middleware;

use App\Http\Formatters\ApiResponseFormatter;
use Closure;
use Illuminate\Http\Request;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use Symfony\Component\HttpFoundation\Response;

readonly class DecryptRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $encryptedBody = $request->input('body');
        if (!is_array(
            $encryptedBody,
        ) || !isset($encryptedBody['encryptedData'], $encryptedBody['encryptedKey'], $encryptedBody['iv'])) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                'Information is not correctly encrypted',
                Response::HTTP_BAD_REQUEST,
            );
        }
        try {
            $privateKeyContent = file_get_contents(storage_path('app/mobile/private_key.pem'));
            $rsa = PublicKeyLoader::loadPrivateKey($privateKeyContent);
            $rsa = $rsa
                ->withPadding(RSA::ENCRYPTION_OAEP)
                ->withHash('sha1')
                ->withMGFHash('sha1');
            $aesKey = $rsa->decrypt(base64_decode($encryptedBody['encryptedKey']));
            if ($aesKey === false) {
                return response()->json([
                    'error' => 'Invalid encryption key provided.',
                ], 400);
            }
            $aesKey = substr($aesKey, 0, 32);
            $aes = new AES('cbc');
            $aes->setKey($aesKey);
            $aes->setIV(base64_decode($encryptedBody['iv']));
            $decryptedData = $aes->decrypt(base64_decode($encryptedBody['encryptedData']));
            if (!$decryptedData) {
                return ApiResponseFormatter::formatError(
                    __('custom.error_trying_to_process_request'),
                    'Invalid encrypted data or initialization vector.',
                    Response::HTTP_BAD_REQUEST,
                );
            }
            $decryptedJson = json_decode($decryptedData, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge($decryptedJson);
            } else {
                $request->merge(['decryptedData' => $decryptedData]);
            }
            $request->offsetUnset('body');
        } catch (\Exception $e) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                sprintf('An error occurred during request decryption. %s', $e->getMessage()),
                Response::HTTP_BAD_REQUEST,
            );
        }
        return $next($request);
    }
}
