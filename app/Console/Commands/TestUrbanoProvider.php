<?php

namespace App\Console\Commands;

use App\Models\Fulfillment;
use App\Services\Logistics\LogisticProviderResolver;
use App\Services\Logistics\Providers\UrbanoProvider;
use Illuminate\Console\Command;
use Throwable;

class TestUrbanoProvider extends Command
{
    protected $signature = 'app:test-urbano-provider 
                            {fulfillment_id* : One or many fulfillment IDs}
                            {--dd : Dump and die response from Urbano service}
                            {--payload-only : Print payload JSON for Postman and skip API call}';

    protected $description = 'Creates Urbano shipments from existing fulfillment records without using web flow';

    public function __construct(
        private readonly LogisticProviderResolver $resolver,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $ids = array_unique(array_map('intval', (array)$this->argument('fulfillment_id')));
        $shouldDump = (bool)$this->option('dd');
        $payloadOnly = (bool)$this->option('payload-only');
        $failed = 0;

        foreach ($ids as $id) {
            $fulfillment = Fulfillment::query()
                ->with(['order', 'logisticProvider'])
                ->find($id);

            if (!$fulfillment) {
                $this->error(sprintf('Fulfillment %d no existe.', $id));
                $failed++;
                continue;
            }

            $providerCode = $fulfillment->logisticProvider?->code ?? null;
            if ($providerCode !== 'UHD') {
                $this->warn(
                    sprintf(
                        'Fulfillment %d no es Urbano (provider code: %s).',
                        $id,
                        $providerCode ?? 'N/A',
                    ),
                );
                $failed++;
                continue;
            }

            try {
                $provider = $this->resolver->resolve($providerCode);
                if ($payloadOnly) {
                    if (!$provider instanceof UrbanoProvider) {
                        $this->error(sprintf('Provider de fulfillment %d no es UrbanoProvider.', $id));
                        $failed++;
                        continue;
                    }
                    $payload = $provider->getShipmentPayload($fulfillment);
                    $this->info(sprintf('Payload para fulfillment %d (JSON):', $id));
                    $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    $this->newLine();
                    $this->info('Para Postman (x-www-form-urlencoded):');
                    $this->line('Key: json');
                    $this->line('Value:');
                    $this->line(json_encode($payload, JSON_UNESCAPED_UNICODE));
                    continue;
                }

                $response = $provider->createShipment($fulfillment);
                if ($shouldDump) {
                    dd($response);
                }
                $this->info(sprintf('Fulfillment %d procesado.', $id));
                $this->line(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } catch (Throwable $exception) {
                $this->error(
                    sprintf(
                        'Error en fulfillment %d: %s',
                        $id,
                        $exception->getMessage(),
                    ),
                );
                $failed++;
            }
        }

        return $failed > 0 ? 1 : 0;
    }
}
