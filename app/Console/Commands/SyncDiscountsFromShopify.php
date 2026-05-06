<?php

namespace App\Console\Commands;

use App\Models\Discount;
use App\Services\Shop\ShopService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncDiscountsFromShopify extends Command
{
    protected $signature = 'app:sync-discounts-from-shopify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all the discounts from shopify to the local database';

    public function __construct(private readonly ShopService $shopService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('Retrieving all discounts from shopify');
            $discounts = $this->shopService->getAllDiscounts();
            foreach ($discounts as $discount) {
                $discount['created_at'] = Carbon::now();
                if (!Discount::query()->where('code', '=', $discount['code'])->exists()) {
                    DB::table('discounts')->updateOrInsert($discount);
                }
            }
            $this->info(sprintf('All discounts were synced, total: %s', count($discounts)));
            return 0;
        } catch (\Exception $exception) {
            $this->error(
                sprintf(
                    'An unexpected error occurred while trying to sync discounts, error: %s',
                    $exception->getMessage(),
                ),
            );
            return 1;
        }
    }
}
