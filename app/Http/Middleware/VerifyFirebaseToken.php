<?php

namespace App\Http\Middleware;

use App\Models\UserMapping;
use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Lcobucci\JWT\UnencryptedToken;

class VerifyFirebaseToken
{
    public function __construct(private readonly Auth $firebaseAuth)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $bearerToken = $request->bearerToken();
        if (!app()->isProduction() && is_null($bearerToken)) {
            $request->attributes->add([
                'shopify_uid' => $request->get('shopify_uid'),
                'firebase_uid' => $request->get('firebase_uid', ''),
                'firebase_email' => $request->get('email', ''),
            ]);
            return $next($request);
        } elseif (empty($bearerToken)) {
            return response()->json(['message' => 'JWT not provided'], 401);
        }
        try {
            /** @var UnencryptedToken $verifiedIdToken */
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($bearerToken);
            $firebaseId = $verifiedIdToken->claims()->get('user_id');
            $userMapping = $this->getUserMapping($firebaseId);
            $request->attributes->add([
                'firebase_uid' => $firebaseId,
                'shopify_uid' => $userMapping?->shopify_user_id,
                'firebase_email' => $verifiedIdToken->claims()->get('email'),
                'firebase_name' => $verifiedIdToken->claims()->get('name'),
                'firebase_picture' => $verifiedIdToken->claims()->get('picture'),
                'firebase_token_claims' => $verifiedIdToken->claims()->all(),
            ]);
            return $next($request);
        } catch (FailedToVerifyToken $e) {
            return response()->json(['message' => 'Invalid Token'], 401);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => sprintf('Error trying to validate token: %s', $e->getMessage()),
                ],
                500,
            );
        }
    }

    private function getUserMapping(string $userId): ?UserMapping
    {
        return UserMapping::query()
            ->where('firebase_id', '=', $userId)
            ->first();
    }
}
