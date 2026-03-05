<?php

namespace App\Observers;

use App\Models\User;
use App\Traits\Loggable;

class UserObserver
{
    use Loggable;

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->logCreate($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Получаем только измененные поля
        $changes = $user->getChanges();
        
        if (empty($changes)) {
            return;
        }

        // Получаем оригинальные значения только для измененных полей
        $oldData = [];
        foreach (array_keys($changes) as $field) {
            $oldData[$field] = $user->getOriginal($field);
        }

        $this->logUpdate($user, $oldData);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->logDelete($user);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        $this->log('restore', $user, null, $user->toArray());
    }

    /**
     * Handle the User "forceDeleted" event.
     */
    public function forceDeleted(User $user): void
    {
        $this->log('force_delete', $user, $user->toArray(), null);
    }
}