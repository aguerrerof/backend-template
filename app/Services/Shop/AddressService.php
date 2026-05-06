<?php

namespace App\Services\Shop;

use App\Exceptions\AddressAlreadyExistsException;
use App\Exceptions\MissingCustomerAddressException;
use App\Models\Shopify\Address;
use App\Models\Shopify\ShopifyResponse;

interface AddressService
{
    /**
     * @param string $customerId
     * @return ShopifyResponse
     * @throws MissingCustomerAddressException
     */
    public function getByUser(string $customerId): ShopifyResponse;

    /**
     * @param string $customerId
     * @param Address $address
     * @return ShopifyResponse
     * @throws AddressAlreadyExistsException
     */
    public function create(string $customerId, Address $address): ShopifyResponse;

    /**
     * @param string $customerId
     * @param Address $address
     * @return ShopifyResponse
     * @throws AddressAlreadyExistsException
     */
    public function update(string $customerId, Address $address): array;

    public function delete(string $customerId, string $addressId): array;
}
