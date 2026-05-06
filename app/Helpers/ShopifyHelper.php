<?php

namespace App\Helpers;

class ShopifyHelper
{
    public static function gidToAdminUrl(string $gid): ?string
    {
        if (!preg_match('/^gid:\/\/shopify\/(\w+)\/(\d+)$/', $gid, $matches)) {
            return null;
        }

        $resource = strtolower($matches[1]);
        $id = $matches[2];
        return sprintf(
            '%s/admin/%ss/%s',
            config('services.shopify.store_url'),
            $resource,
            $id
        );
    }
}
