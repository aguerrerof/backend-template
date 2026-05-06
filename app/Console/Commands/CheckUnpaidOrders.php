<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\UserDevice;
use App\Services\Google\PushNotificationService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CheckUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-unpaid-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to users who have not paid their orders';

    public function __construct(
        private readonly PushNotificationService $notificationService,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $title = __('orders.order_not_paid_title');
        $body = __('orders.order_not_paid_body');
        Order::query()->where('order->financial_status', '<>', Order::STATUS_PAID)
            ->chunk(100, function (Collection $collection) use ($title, $body) {
                $collection->each(function (Order $order) use ($title, $body) {
                    $tokens = $this->getUserTokens($order);
                    foreach ($tokens as $token) {
                        $response = $this->notificationService->sendNotification(
                            [$token],
                            $title,
                            $body,
                            [
                                'type' => 'ORDER_DETAIL',
                                'route' => 'orderDetail',
                                'id' => (string)$order->id,
                            ]
                        );
                        $this->info(json_encode($response));
                    }
                });
            });
    }

    /**
     * @param Order $order
     * @return mixed[]
     */
    public function getUserTokens(Order $order): array
    {
        return UserDevice::query()
            ->where('shopify_id', '=', $order->user_id)
            ->pluck('firebase_token')
            ->filter()
            ->toArray();
    }
}
