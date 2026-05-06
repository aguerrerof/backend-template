<?php

namespace App\Services\Authentication;

use App\Exceptions\UserCouldNotBeDeletedException;
use App\Exceptions\UserWasAlreadyDeletedException;
use Illuminate\Http\Request;

interface AuthenticationService
{
    public function verifyAndCreateEmailShopify(Request $request): array;

    public function checkEmail(Request $request): array;

    /**
     * @param string $shopifyId
     * @param string $firebaseUUId
     * @param string $email
     * @return void
     * @throws UserCouldNotBeDeletedException
     * @throws UserWasAlreadyDeletedException
     */
    public function deleteUser(
        string $shopifyId,
        string $firebaseUUId,
        string $email,
    ): void;

    public function reactivateUser(
        string $email,
        string $firebaseUUId,
    ): array;

    public function linkNewDevice(
        string $shopifyId,
        string $deviceId,
        string $firebaseToken,
    ): void;

    public function unlinkDevice(
        string $shopifyId,
        string $deviceId,
    ): void;
}
