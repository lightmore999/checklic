<?php

namespace App\Observers;

use App\Models\Limit;
use App\Traits\LoggableLimit;

class LimitObserver
{
    use LoggableLimit;

    public function created(Limit $limit): void
    {
        $this->logCreateLimit($limit);
    }

    public function updated(Limit $limit): void
    {
        $changes = $limit->getChanges();
        if (empty($changes)) return;

        // Отдельно обрабатываем изменение количества
        if (isset($changes['quantity']) || isset($changes['used_quantity'])) {
            $oldQuantity = $limit->getOriginal('quantity') - $limit->getOriginal('used_quantity');
            $newQuantity = $limit->quantity - $limit->used_quantity;
            
            if ($oldQuantity !== $newQuantity) {
                $this->logQuantityChange($limit, $oldQuantity, $newQuantity);
                return;
            }
        }

        // Для остальных изменений
        $oldData = [];
        foreach (array_keys($changes) as $field) {
            $oldData[$field] = $limit->getOriginal($field);
        }

        $this->logUpdateLimit($limit, $oldData);
    }

    public function deleted(Limit $limit): void
    {
        $this->logDeleteLimit($limit);
    }

    public function restored(Limit $limit): void
    {
        $this->logLimit('restore', $limit, null, $limit->toArray());
    }

    public function forceDeleted(Limit $limit): void
    {
        $this->logLimit('force_delete', $limit, $limit->toArray(), null);
    }
}