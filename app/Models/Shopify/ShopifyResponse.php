<?php

namespace App\Models\Shopify;

readonly class ShopifyResponse
{
    private bool $success;
    private ?array $errors;
    private ?array $data;

    public function __construct(array $response)
    {
        $this->success = (!isset($response['errors']) && !isset($response['userErrors']));
        $this->errors = (array)($response['errors'] ?? $response['userErrors'] ?? []);
        $this->data = $response['data'] ?? null;
    }

    public function hasErrors(): bool
    {
        return !$this->success;
    }

    public function getFullErrorMessage(string $separator = ' | '): ?string
    {
        if (!isset($this->errors) && !isset($this->data['errors'])) {
            return null;
        }
        $errors = $this->errors;
        if (empty($errors) && isset($this->data['errors'])) {
            $errors = $this->data['errors'];
        }
        if (is_array($errors)) {
            $flatten = function ($items) use (&$flatten) {
                $flat = [];
                foreach ($items as $item) {
                    if (is_array($item)) {
                        $flat = array_merge($flat, $flatten($item));
                    } else {
                        $flat[] = (string)$item;
                    }
                }
                return $flat;
            };

            $flattenedErrors = $flatten($errors);

            return implode($separator, $flattenedErrors);
        }

        return (string)$errors;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }

    public function getData(): ?array
    {
        return $this->data;
    }
}
