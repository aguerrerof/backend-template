<?php

namespace App\Services\Shop;

use App\Exceptions\AddressAlreadyExistsException;
use App\Exceptions\AddressNotExistException;
use App\Exceptions\MissingCustomerAddressException;
use App\Helpers\CustomLog;
use App\Helpers\ServiceResponse;
use App\Models\Shopify\Address;
use App\Models\Shopify\ShopifyResponse;

class ShopifyAddressService implements AddressService
{
    public function __construct(
        private readonly ShopifyService $shopifyService,
    ) {
    }

    /**
     * @param string $customerId
     * @param Address $address
     * @return ShopifyResponse
     * @throws AddressAlreadyExistsException
     * @throws AddressNotExistException
     */
    public function create(string $customerId, Address $address): ShopifyResponse
    {
        $payload = [
            'customerId' => $customerId,
            'address' => [
                'firstName' => $address->getFirstName(),
                'address1' => $address->getAddressLine1(),
                'address2' => $address->getAddressLine2(),
                'city' => $address->getCity(),
                'provinceCode' => $address->getProvinceCode(),
                'phone' => $address->getPhone(),
                'countryCode' => $address->getCountryCode(),
            ],
            'setAsDefault' => $address->isDefault(),
        ];
        CustomLog::saveLog(
            'INFO',
            'Create Shopify Address',
            $payload,
        );
        $response = $this->shopifyService->query(
            '
        mutation customerAddressCreate($customerId: ID!, $address: MailingAddressInput!, $setAsDefault: Boolean) {
            customerAddressCreate(customerId: $customerId, address: $address, setAsDefault: $setAsDefault) {
                address {
                    id
                    firstName
                    address1
                    address2
                    city
                    provinceCode
                    countryCode
                    country
                    phone
                }
                userErrors {
                    field
                    message
                }
            }
        }',
            $payload,
            [],
            'graphql',
            true,
        );
        $data = $response->getData();
        $userErrors = $data['customerAddressCreate']['userErrors'] ?? null;
        if (!empty($userErrors)) {
            CustomLog::saveLog(
                'ERROR',
                'Create Shopify Address',
                $userErrors,
            );
            $errorMessage = $this->getCustomErrorFromExternalService($userErrors);
        }
        return new ShopifyResponse(
            [
                'data' => $data,
                'errors' => $errorMessage ?? null,
            ],
        );
    }

    private function getCustomErrorFromExternalService(array $userErrors): string
    {
        if (empty($userErrors)) {
            return 'Unknown error from external service.';
        }

        $messages = [];

        foreach ($userErrors as $error) {
            $fields = isset($error['field']) ? implode(', ', $error['field']) : '';
            $message = $error['message'] ?? 'Unknown error';
            if ($fields === 'address' && $message === 'Address already exists') {
                throw new AddressAlreadyExistsException(
                    $message,
                    0,
                    null,
                    __('custom.address_already_exists'),
                );
            }
            if ($message === 'Address does not exist') {
                throw new AddressNotExistException(
                    $message,
                    0,
                    null,
                    __('custom.address_not_exists'),
                );
            }
            $messages[] = $fields ? "$fields: $message" : $message;
        }
        return implode('; ', $messages);
    }

    /**
     * @param string $customerId
     * @param Address $address
     * @return array
     * @throws AddressAlreadyExistsException
     * @throws AddressNotExistException
     */
    public function update(string $customerId, Address $address): array
    {
        $payload = [
            'customerId' => $customerId,
            'addressId' => $address->getId(),
            'address' => [
                'firstName' => $address->getFirstName(),
                'phone' => $address->getPhone(),
            ],
            'setAsDefault' => $address->isDefault(),
        ];
        CustomLog::saveLog(
            'INFO',
            'Update Shopify Address',
            $payload,
        );
        $response = $this->shopifyService->query(
            '
        mutation customerAddressUpdate($customerId: ID!, $addressId: ID!, $address: MailingAddressInput!, $setAsDefault: Boolean) {
            customerAddressUpdate(customerId: $customerId, addressId: $addressId, address: $address, setAsDefault: $setAsDefault) {
                userErrors {
                    field
                    message
                }
            }
        }',
            $payload,
            [],
            'graphql',
            true,
            key: 'customerAddressUpdate',
        );
        $data = $response->getData();
        $userErrors = $data['userErrors'] ?? null;
        if (!empty($userErrors)) {
            CustomLog::saveLog(
                'ERROR',
                'Update Shopify Address',
                $userErrors,
            );
            $errorMessage = $this->getCustomErrorFromExternalService($userErrors);
            return ServiceResponse::error($errorMessage);
        }
        return ServiceResponse::success($data, 'La dirección se editó exitosamente.');
    }

    public function delete(string $customerId, string $addressId): array
    {
        CustomLog::saveLog(
            'INFO',
            sprintf('Delete Shopify Address: id %s - customer id %s', $addressId, $customerId),
            []
        );
        try {
            $response = $this->shopifyService->query(
                'mutation customerAddressDelete($customerId: ID!, $addressId: ID!) {
                        customerAddressDelete(customerId: $customerId, addressId: $addressId) {
                        userErrors {
                            field
                            message
                        }
                        }
                    }',
                [
                     'customerId' => $customerId,
                     'addressId' => $addressId,
                 ],
                [],
                'graphql',
                true,
                key: 'customerAddressDelete',
            );
            $data = $response->getData();
        } catch (\Throwable $error) {
            CustomLog::saveLog(
                'ERROR',
                sprintf('Error from delete Shopify Address: id %s - customer id %s', $addressId, $customerId),
                $error->getTrace()
            );
        }

        if (!empty($response->getErrors())) {
            CustomLog::saveLog(
                'ERROR',
                sprintf('Response from delete Shopify Address: id %s - customer id %s', $addressId, $customerId),
                $response->getErrors()
            );
            $errorMessage = $this->getCustomErrorFromExternalService($response->getErrors());
            return ServiceResponse::error($errorMessage);
        }
        return ServiceResponse::success($data, 'La dirección fué eliminada exitosamente.');
    }

    /**
     * @param string $customerId
     * @return ShopifyResponse
     * @throws MissingCustomerAddressException
     */
    public function getByUser(string $customerId): ShopifyResponse
    {
        $response = $this->shopifyService->query(
            'query getCustomerAddresses($id: ID!) {
                customer(id: $id) {
                  id
                  firstName
                  lastName
                  email
                  defaultAddress {
                    id
                  }
                  addresses {
                    id
                    firstName
                    address1
                    address2
                    city
                    provinceCode
                    country
                    zip
                    phone
                    name
                    company
                  }
                }
              }',
            [
                'id' => $customerId,
            ],
            [],
            'graphql',
            true,
        );
        $data = $response->getData();
        if (!isset($data['customer']['addresses']) || empty($data['customer']['addresses'])) {
            throw new MissingCustomerAddressException(
                __('custom.missing_customer_address'),
                0,
                null,
                'Customer must have at least one address.',
            );
        }
        $defaultAddressId = $data['customer']['defaultAddress']['id'] ?? null;
        $addressesData = $data['customer']['addresses'] ?? [];

        $newAddresses = array_map(function ($address) use ($defaultAddressId) {
            return array_merge($address, [
                'isDefault' => ($address['id'] === $defaultAddressId),
            ]);
        }, $addressesData);

        return new ShopifyResponse(
            [
                'data' => $newAddresses,
                'errors' => null,
            ],
        );
    }
}
