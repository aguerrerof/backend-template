<?php

namespace App\Events;

use App\Models\Fulfillment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FulfillmentCreated implements ShouldQueue
{
    use Dispatchable;
    use SerializesModels;
    use Queueable;

    public function __construct(private readonly Fulfillment $fulfillment)
    {
        $this->delay = 5;
    }

    public function getFulfillment(): Fulfillment
    {
        return $this->fulfillment;
    }
}
