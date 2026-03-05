<?php

namespace App\Observers;

use App\Models\DelegatedLimit;
use App\Traits\LoggableLimit;

class DelegatedLimitObserver
{
    use LoggableLimit;

    public function created(DelegatedLimit $delegatedLimit): void
    {
        $this->logCreateLimit($delegatedLimit);
        
        // Дополнительно логируем факт делегирования
        if ($delegatedLimit->limit) {
            $this->logDelegate(
                $delegatedLimit->limit,
                $delegatedLimit->user_id,
                $delegatedLimit->quantity,
                $delegatedLimit->batch_id
            );
        }
    }

    public function updated(DelegatedLimit $delegatedLimit): void
    {
        $changes = $delegatedLimit->getChanges();
        if (empty($changes)) return;

        // Отдельно обрабатываем изменение количества
        if (isset($changes['quantity']) || isset($changes['used_quantity'])) {
            $oldQuantity = $delegatedLimit->getOriginal('quantity') - $delegatedLimit->getOriginal('used_quantity');
            $newQuantity = $delegatedLimit->quantity - $delegatedLimit->used_quantity;
            
            if ($oldQuantity !== $newQuantity) {
                $this->logQuantityChange($delegatedLimit, $oldQuantity, $newQuantity);
                return;
            }
        }

        // Для остальных изменений
        $oldData = [];
        foreach (array_keys($changes) as $field) {
            $oldData[$field] = $delegatedLimit->getOriginal($field);
        }

        $this->logUpdateLimit($delegatedLimit, $oldData);
    }

    public function deleted(DelegatedLimit $delegatedLimit): void
    {
        $this->logDeleteLimit($delegatedLimit);
    }

    public function restored(DelegatedLimit $delegatedLimit): void
    {
        $this->logLimit('restore', $delegatedLimit, null, $delegatedLimit->toArray());
    }

    public function forceDeleted(DelegatedLimit $delegatedLimit): void
    {
        $this->logLimit('force_delete', $delegatedLimit, $delegatedLimit->toArray(), null);
    }
}