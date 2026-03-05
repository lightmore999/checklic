<?php

namespace App\Observers;

use App\Models\Manager;
use App\Traits\Loggable;

class ManagerObserver
{
    use Loggable;

    public function created(Manager $manager): void
    {
        $this->logCreate($manager);
    }

    public function updated(Manager $manager): void
    {
        $changes = $manager->getChanges();
        if (empty($changes)) return;

        $oldData = [];
        foreach (array_keys($changes) as $field) {
            $oldData[$field] = $manager->getOriginal($field);
        }

        $this->logUpdate($manager, $oldData);
    }

    public function deleted(Manager $manager): void
    {
        $this->logDelete($manager);
    }

    public function restored(Manager $manager): void
    {
        $this->log('restore', $manager, null, $manager->toArray());
    }

    public function forceDeleted(Manager $manager): void
    {
        $this->log('force_delete', $manager, $manager->toArray(), null);
    }
}