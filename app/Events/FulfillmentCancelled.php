<?php

namespace App\Events;

use App\Models\Fulfillment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FulfillmentCancelled
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(private readonly Fulfillment $fulfillment)
    {
    }

    public function getFulfillment(): Fulfillment
    {
        return $this->fulfillment;
    }
}
