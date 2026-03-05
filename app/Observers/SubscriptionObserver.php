<?php

namespace App\Observers;

use App\Models\Subscription;
use App\Traits\LoggableLimit;

class SubscriptionObserver
{
    use LoggableLimit;

    public function created(Subscription $subscription): void
    {
        $this->logCreateLimit($subscription);
    }

    public function updated(Subscription $subscription): void
    {
        $changes = $subscription->getChanges();
        if (empty($changes)) return;

        $oldData = [];
        foreach (array_keys($changes) as $field) {
            $oldData[$field] = $subscription->getOriginal($field);
        }

        // Специальная обработка для статуса
        if (isset($changes['status'])) {
            $this->logSubscriptionStatus(
                $subscription,
                $subscription->getOriginal('status'),
                $subscription->status
            );
            return;
        }

        // Специальная обработка для даты окончания
        if (isset($changes['ends_at'])) {
            $this->logSubscriptionExtend(
                $subscription,
                $subscription->getOriginal('ends_at'),
                $subscription->ends_at
            );
            return;
        }

        $this->logUpdateLimit($subscription, $oldData);
    }

    public function deleted(Subscription $subscription): void
    {
        $this->logDeleteLimit($subscription);
    }

    public function restored(Subscription $subscription): void
    {
        $this->logLimit('restore', $subscription, null, $subscription->toArray());
    }

    public function forceDeleted(Subscription $subscription): void
    {
        $this->logLimit('force_delete', $subscription, $subscription->toArray(), null);
    }
}