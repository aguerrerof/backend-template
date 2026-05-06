<?php

namespace App\Services\Authentication;

use App\Exceptions\UserCouldNotBeDeletedException;
use App\Exceptions\UserWasAlreadyDeletedException;
use App\Helpers\CustomLog;
use App\Helpers\ServiceResponse;
use App\Models\DeletedUser;
use App\Models\RecurringOrder;
use App\Models\Shopify\ShopifyResponse;
use App\Models\UserCard;
use App\Models\UserDevice;
use App\Models\UserMapping;
use App\Services\Shop\ShopService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;

class AuthService implements AuthenticationService
{
    public function __construct(
        private readonly ShopService $shopService,
        private readonly Auth $firebaseAuth,
    ) {
    }

    public function verifyAndCreateEmailShopify(Request $request): array
    {
        try {
            $firebaseId = $request->attributes->get('firebase_uid');
            $email = $request->attributes->get('firebase_email');
            if (!$email && $request->query('email')) {
                $email = $request->query('email');
                $this->firebaseAuth->updateUser($firebaseId, ['email' => $email]);
            }

            if (!isset($email)) {
                throw new Exception('Email no encontrado en token');
            }

            $userRecord = $this->firebaseAuth->getUser($firebaseId);
            $name = $userRecord->displayName;

            $response = $this->getCustomerFromShopifyByEmail($email);
            $customer = $response->getData()['customer'] ?? null;

            if (!is_null($customer)) {
                $shopifyUUId = $customer['id'];
                if ($this->checkIfUserIsAlreadyDeleted($email, $shopifyUUId)) {
                    throw new UserWasAlreadyDeletedException(
                        __('custom.user_is_already_deleted'),
                        0,
                        null,
                        __('custom.user_is_already_deleted'),
                    );
                }
                $this->setUserMappings($firebaseId, $shopifyUUId);
                return ServiceResponse::success($customer, 'usuario vinculado exitosamente.');
            }
            return $this->createUser($email, $firebaseId, $name);
        } catch (Exception $e) {
            CustomLog::saveLog(
                'ERROR',
                'Falló en el proceso de creación de usuario',
                [
                    'error_message' => $e->getMessage(),
                ],
            );
            if ($e instanceof UserWasAlreadyDeletedException) {
                throw $e;
            }
            return ServiceResponse::error('Fallo en el proceso de creación de usuario', $e->getMessage());
        }
    }

    public function checkEmail(Request $request): array
    {
        try {
            $email = $request->query('email');

            if (empty($email) || !is_string($email) || !str_contains($email, '@')) {
                throw new Exception('Email vacío o con formato incorrecto');
            }

            $user = $this->firebaseAuth->getUserByEmail($email);

            $data = [
                'isRegistered' => true,
                'uid' => $user->uid,
            ];

            return ServiceResponse::success($data, 'verificación de email correcta.');
        } catch (Exception $e) {
            CustomLog::saveLog(
                'ERROR',
                'Falló en el proceso de verificacion de email',
                [
                    'error_message' => $e->getMessage(),
                ],
            );
            return ServiceResponse::error('Falló en el proceso de verificación de email', $e->getMessage());
        }
    }

    private function createUser(string $email, string $firebaseId, ?string $name): array
    {
        $queryCreateUser = '
                mutation customerCreate($input: CustomerInput!) {
                    customerCreate(input: $input) {
                    userErrors {
                        field
                        message
                    }
                    customer {
                        id
                        email
                        phone
                        taxExempt
                        firstName
                        lastName
                        amountSpent {
                        amount
                        currencyCode
                        }
                    }
                    }
                }
                ';

        $responseCreateUser = $this->shopService->query(
            $queryCreateUser,
            [
                'input' => [
                    'email' => trim($email),
                    'firstName' => trim($name),
                ],
            ],
            useAdminApi: true,
            key: 'customerCreate',
        );

        if ($responseCreateUser->hasErrors()) {
            throw new Exception('GraphQL errors: ' . $responseCreateUser->getFullErrorMessage());
        }
        $customerId = $responseCreateUser->getData()['customer']['id'] ?? null;

        if (!isset($customerId)) {
            throw new Exception('customerId no encontrado');
        }
        $this->setUserMappings($firebaseId, $customerId);
        return ServiceResponse::success($customerId, 'Usuario creado exitosamente.');
    }

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
    ): void {
        $this->deleteUserInFirebase($firebaseUUId);
        try {
            DB::beginTransaction();
            UserCard::query()
                ->where('user_id', '=', $shopifyId)
                ->delete();
            RecurringOrder::query()
                ->where('user_id', '=', $shopifyId)
                ->delete();
            UserMapping::query()
                ->where('firebase_id', '=', $firebaseUUId)
                ->delete();
            UserDevice::query()
                ->where('shopify_id', '=', $shopifyId)
                ->delete();
            DeletedUser::create([
                'email' => $email,
                'shopify_id' => $shopifyId,
                'created_at' => Carbon::now(),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
    }

    /**
     * @param string $firebaseUUId
     * @return void
     * @throws UserCouldNotBeDeletedException
     */
    public function deleteUserInFirebase(string $firebaseUUId): void
    {
        try {
            $this->firebaseAuth->deleteUser($firebaseUUId);
        } catch (FirebaseException|AuthException $e) {
            throw new UserCouldNotBeDeletedException(
                $e->getMessage(),
                $e->getCode(),
                null,
                __('custom.error_trying_to_process_request'),
            );
        }
    }

    /**
     * @param string $email
     * @param string $shopifyId
     * @return bool
     */
    public function checkIfUserIsAlreadyDeleted(string $email, string $shopifyId): bool
    {
        return DeletedUser::query()
            ->where('email', $email)
            ->where('shopify_id', $shopifyId)
            ->exists();
    }

    public function reactivateUser(
        string $email,
        string $firebaseUUId,
    ): array {
        $deletedUser = DeletedUser::query()->where('email', $email)->firstOrFail();
        $response = $this->getCustomerFromShopifyByEmail($email);
        $customer = $response->getData()['customer'] ?? null;
        $this->setUserMappings($firebaseUUId, $customer['id']);
        $deletedUser->delete();
        return $customer;
    }

    /**
     * @param string $email
     * @return ShopifyResponse|null
     * @throws Exception
     */
    public function getCustomerFromShopifyByEmail(string $email): ?ShopifyResponse
    {
        $query = '
                query($identifier: CustomerIdentifierInput!) {
                  customer: customerByIdentifier(identifier: $identifier) {
                    id
                  }
                }
              ';

        $response = $this->shopService->query(
            $query,
            [
                'identifier' => [
                    'emailAddress' => trim($email),
                ],
            ],
            useAdminApi: true,
        );

        if ($response->hasErrors()) {
            throw new Exception('GraphQL errors: ' . $response->getFullErrorMessage());
        }
        return $response;
    }

    public function setUserMappings(string $firebaseId, string $shopifyId): void
    {
        $userMapping = UserMapping::query()
            ->where('firebase_id', '=', $firebaseId)
            ->first();

        if (is_null($userMapping)) {
            UserMapping::create([
                'shopify_user_id' => $shopifyId,
                'firebase_id' => $firebaseId,
                'created_at' => Carbon::now(),
            ]);
        } else {
            $userMapping->update(['shopify_user_id' => $shopifyId]);
        }
    }

    public function linkNewDevice(string $shopifyId, string $deviceId, string $firebaseToken): void
    {
        CustomLog::saveLog(
            'INFO',
            'New device link request',
            ['shopify_id' => $shopifyId, 'device_id' => $deviceId, 'firebase_token' => $firebaseToken],
        );
        /** @var ?UserDevice $userDevice */
        $userDevice = UserDevice::query()
            ->where('device_id', '=', $deviceId)
            ->first();
        if (is_null($userDevice)) {
            $userDevice = new UserDevice();
            $userDevice->device_id = $deviceId;
            $userDevice->created_at = Carbon::now();
        } else {
            $userDevice->updated_at = Carbon::now();
        }
        $userDevice->shopify_id = $shopifyId;
        $userDevice->firebase_token = $firebaseToken;
        $userDevice->saveOrFail();
    }

    public function unlinkDevice(string $shopifyId, string $deviceId): void
    {
        CustomLog::saveLog(
            'INFO',
            'New device unlink request',
            ['shopify_id' => $shopifyId, 'device_id' => $deviceId],
        );
        UserDevice::query()
            ->where('device_id', '=', $deviceId)
            ->firstOrFail()
            ->delete();
    }
}
