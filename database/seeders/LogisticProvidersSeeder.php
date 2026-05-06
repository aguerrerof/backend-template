<?php

namespace Database\Seeders;

use App\Models\LogisticProvider;
use Illuminate\Database\Seeder;

class LogisticProvidersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'name' => 'DHL Express',
                'code' => 'DHL',
                'contact_email' => 'support@dhl.com',
                'contact_phone' => '+1 800 225 5345',
                'api_url' => 'https://api.dhl.com/v1/',
                'credentials' => [
                    'api_key' => 'test-dhl-key',
                    'secret' => 'test-dhl-secret',
                ],
                'config' => [
                    'sandbox' => true,
                    'supports_tracking' => true,
                    'supports_label' => true,
                ],
            ],
            [
                'name' => 'FedEx',
                'code' => 'FEDEX',
                'contact_email' => 'support@fedex.com',
                'contact_phone' => '+1 800 463 3339',
                'api_url' => 'https://apis.fedex.com/',
                'credentials' => [
                    'client_id' => 'test-fedex-id',
                    'client_secret' => 'test-fedex-secret',
                ],
                'config' => [
                    'sandbox' => true,
                    'supports_tracking' => true,
                    'supports_label' => true,
                ]
            ],
            [
                'name' => 'UPS',
                'code' => 'UPS',
                'contact_email' => 'support@ups.com',
                'contact_phone' => '+1 800 742 5877',
                'api_url' => 'https://onlinetools.ups.com/',
                'credentials' => [
                    'access_license' => 'test-ups-license',
                    'username' => 'testuser',
                    'password' => 'testpass',
                ],
                'config' => [
                    'sandbox' => true,
                    'supports_tracking' => true,
                ],
            ],
            [
                'name' => 'TransExpress Local',
                'code' => 'TRANSEX',
                'contact_email' => 'contacto@transexpress.local',
                'contact_phone' => '+593 2 555 1234',
                'api_url' => null,
                'credentials' => null,
                'config' => [
                    'supports_tracking' => false,
                    'manual_dispatch' => true,
                ],
            ],
        ];

        foreach ($providers as $provider) {
            LogisticProvider::updateOrCreate(
                ['code' => $provider['code']],
                $provider
            );
        }
    }
}
