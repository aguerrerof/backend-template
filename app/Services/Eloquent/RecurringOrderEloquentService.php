<?php

namespace App\Services\Eloquent;

use App\Helpers\RecurringOrderHelper;
use App\Models\Domain\UpdateRecurringOrder;
use App\Models\RecurrenceFrequency;
use App\Models\RecurringOrder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;

class RecurringOrderEloquentService implements RecurringOrderService
{
    /**
     * @param UpdateRecurringOrder $updateRecurringOrder
     * @return void
     * @throws ModelNotFoundException
     */
    public function updateRecurringOrder(UpdateRecurringOrder $updateRecurringOrder): void
    {
        /** @var RecurringOrder $recurringOrder */
        $recurringOrder = RecurringOrder::query()->findOrFail($updateRecurringOrder->getId());
        if ($updateRecurringOrder->getLineItems() !== null) {
            $recurringOrder->line_items = $this->checkLineItems(
                $recurringOrder->line_items,
                $updateRecurringOrder->getLineItems(),
            );
        }
        if ($updateRecurringOrder->getEmail() !== null) {
            $recurringOrder->email = $updateRecurringOrder->getEmail();
        }
        if ($updateRecurringOrder->getFrequency() !== null) {
            $recurrenceFrequency = RecurrenceFrequency::query()
                ->where('name', '=', $updateRecurringOrder->getFrequency())
                ->firstOrFail();
            $this->updateNextChargeDate($recurrenceFrequency, $recurringOrder);
        }
        if ($updateRecurringOrder->getUserCardId() !== null) {
            $recurringOrder->user_card_id = $updateRecurringOrder->getUserCardId();
        }
        if ($updateRecurringOrder->getShippingAddress() !== null) {
            $recurringOrder->shipping_address = $updateRecurringOrder->getShippingAddress();
        }
        if ($updateRecurringOrder->getNotes() !== null) {
            $recurringOrder->notes = $updateRecurringOrder->getNotes();
        }
        $recurringOrder->update();
    }

    private function checkLineItems(array $currentLineItems, array $newLineItems): array
    {
        $current = collect($currentLineItems)->keyBy('variantId');
        $new = collect($newLineItems)->keyBy('variantId');
        $result = [];
        foreach ($new as $variantId => $newItem) {
            if ($current->has($variantId)) {
                $oldItem = $current[$variantId];
                if ($oldItem != $newItem) {
                    $result[] = $newItem;
                } else {
                    $result[] = $oldItem;
                }
            } else {
                $result[] = $newItem;
            }
        }
        return array_values($result);
    }

    private function calculateNextChargeDate(
        RecurrenceFrequency $recurrenceFrequency,
        RecurringOrder $recurringOrder,
    ): Carbon {

        return RecurringOrderHelper::calculateNextChargeDateByFrequency(
            Carbon::parse($recurringOrder->next_charge_date),
            (int)$recurrenceFrequency->value,
        );
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model|RecurrenceFrequency $recurrenceFrequency
     * @param RecurringOrder $recurringOrder
     * @return void
     */
    public function updateNextChargeDate(
        RecurrenceFrequency $recurrenceFrequency,
        RecurringOrder $recurringOrder
    ): void {
        $recurringOrder->recurrence_frequency_id = $recurrenceFrequency->id;
        $recurringOrder->previous_charge_date = $recurringOrder->next_charge_date;
        $recurringOrder->next_charge_date = $this->calculateNextChargeDate(
            $recurrenceFrequency,
            $recurringOrder,
        );
    }

}
