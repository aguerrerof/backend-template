<?php

namespace App\Observers;

use App\Events\FulfillmentCreated;
use App\Models\Fulfillment;
use App\Models\FulfillmentLog;
use Illuminate\Support\Facades\Auth;

class FulfillmentObserver
{
    /**
     * Handle the Fulfillment "created" event.
     */
    public function created(Fulfillment $fulfillment): void
    {
        FulfillmentLog::create([
            'fulfillment_id' => $fulfillment->id,
            'user_id' => Auth::id(),
            'changes' => ['after' => $fulfillment->getAttributes()],
            'description' => 'Fulfillment creado',
        ]);
        event(new FulfillmentCreated($fulfillment));
    }

    public function updated(Fulfillment $fulfillment): void
    {
        FulfillmentLog::create([
            'fulfillment_id' => $fulfillment->id,
            'user_id' => Auth::id() ?? $fulfillment->user_id,
            'changes' => [
                'before' => $fulfillment->getOriginal(),
                'after' => $fulfillment->getAttributes(),
            ],
            'description' => 'Fulfillment actualizado',
        ]);
    }

    /**
     * Handle the Fulfillment "deleted" event.
     */
    public function deleted(Fulfillment $fulfillment): void
    {
        FulfillmentLog::create([
            'fulfillment_id' => $fulfillment->id,
            'user_id' => Auth::id() ?? $fulfillment->user_id,
            'changes' => ['before' => $fulfillment->getAttributes()],
            'description' => 'Fulfillment eliminado',
        ]);
    }

    public function restored(Fulfillment $fulfillment): void
    {
        FulfillmentLog::create([
            'fulfillment_id' => $fulfillment->id,
            'user_id' => Auth::id(),
            'changes' => ['after' => $fulfillment->getAttributes()],
            'description' => 'Fulfillment restaurado',
        ]);
    }

    /**
     * Handle the Fulfillment "force deleted" event.
     */
    public function forceDeleted(Fulfillment $fulfillment): void
    {
        //
    }
}
