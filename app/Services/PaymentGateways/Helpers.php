<?php

namespace App\Services\PaymentGateways;

use InvalidArgumentException;
use RuntimeException;

readonly class Helpers
{
    public static function encryptAES_ECB(string $text, string $symmetricKey): string
    {
        if (strlen($symmetricKey) !== 32) {
            throw new InvalidArgumentException('Symmetric key must be 32 bytes for AES-256-ECB.');
        }
        $encrypted = openssl_encrypt(
            $text,
            'aes-256-ecb',
            $symmetricKey,
            OPENSSL_RAW_DATA,
            '',
        );

        if ($encrypted === false) {
            throw new RuntimeException('AES-256-ECB encryption failed: ' . openssl_error_string());
        }

        return base64_encode($encrypted);
    }

    public static function encryptRSA(string $text, string $publicKey): string
    {
        if (!str_contains($publicKey, 'BEGIN PUBLIC KEY')) {
            $publicKey = sprintf("-----BEGIN PUBLIC KEY-----\n%s\n-----END PUBLIC KEY-----", trim($publicKey));
        }
        $pubKeyResource = openssl_get_publickey($publicKey);

        if ($pubKeyResource === false) {
            throw new RuntimeException(sprintf('Error loading public key: %s', openssl_error_string()));
        }

        $encryptedData = '';
        $success = openssl_public_encrypt(
            $text,
            $encryptedData,
            $pubKeyResource,
        );

        if (!$success) {
            throw new RuntimeException(sprintf('Error encrypting data: %s', openssl_error_string()));
        }

        return base64_encode($encryptedData);
    }

    public static function generateSymmetricKey(): string
    {
        $length = 32;
        $strSource = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $strRepCount = ceil($length / strlen($strSource));
        $randStr = str_shuffle(str_repeat($strSource, $strRepCount));
        return substr($randStr, 0, $length);
    }
}
