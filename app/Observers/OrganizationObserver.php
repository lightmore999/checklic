<?php

namespace App\Observers;

use App\Models\Organization;
use App\Traits\Loggable;

class OrganizationObserver
{
    use Loggable;

    public function created(Organization $organization): void
    {
        $this->logCreate($organization);
    }

    public function updated(Organization $organization): void
    {
        $changes = $organization->getChanges();
        if (empty($changes)) return;

        $oldData = [];
        foreach (array_keys($changes) as $field) {
            $oldData[$field] = $organization->getOriginal($field);
        }

        $this->logUpdate($organization, $oldData);
    }

    public function deleted(Organization $organization): void
    {
        $this->logDelete($organization);
    }

    public function restored(Organization $organization): void
    {
        $this->log('restore', $organization, null, $organization->toArray());
    }

    public function forceDeleted(Organization $organization): void
    {
        $this->log('force_delete', $organization, $organization->toArray(), null);
    }
}