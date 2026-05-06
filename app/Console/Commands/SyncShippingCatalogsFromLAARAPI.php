<?php

namespace App\Console\Commands;

use App\Models\LogisticProvider;
use App\Models\ShippingCity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncShippingCatalogsFromLAARAPI extends Command
{
    protected $signature = 'sync:shipping-catalogs-laar';
    protected $description = 'Sincroniza catalogo de ciudades de la API de LaarCourier';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $logisticProvider = LogisticProvider::query()->where('code', '=', 'LAAR')->first();
        $cities = Http::get(sprintf('%s/ciudades', $logisticProvider->api_url))->json();

        foreach ($cities as $city) {
            ShippingCity::updateOrCreate(
                ['code' => $city['codigo']],
                ['name' => $city['nombre']],
            );
        }

        $this->info('Ciudades sincronizadas correctamente.');
    }
}
