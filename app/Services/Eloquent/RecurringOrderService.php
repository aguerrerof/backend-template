<?php

namespace App\Services\Eloquent;

use App\Models\Domain\UpdateRecurringOrder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface RecurringOrderService
{
    /**
     * @param UpdateRecurringOrder $updateRecurringOrder
     * @return void
     * @throws ModelNotFoundException
     */
    public function updateRecurringOrder(UpdateRecurringOrder $updateRecurringOrder): void;
}
