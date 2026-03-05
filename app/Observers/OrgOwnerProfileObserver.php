<?php

namespace App\Observers;

use App\Models\OrgOwnerProfile;
use App\Traits\Loggable;

class OrgOwnerProfileObserver
{
    use Loggable;

    public function created(OrgOwnerProfile $profile): void
    {
        $this->logCreate($profile);
    }

    public function updated(OrgOwnerProfile $profile): void
    {
        $changes = $profile->getChanges();
        if (empty($changes)) return;

        $oldData = [];
        foreach (array_keys($changes) as $field) {
            $oldData[$field] = $profile->getOriginal($field);
        }

        $this->logUpdate($profile, $oldData);
    }

    public function deleted(OrgOwnerProfile $profile): void
    {
        $this->logDelete($profile);
    }

    public function restored(OrgOwnerProfile $profile): void
    {
        $this->log('restore', $profile, null, $profile->toArray());
    }

    public function forceDeleted(OrgOwnerProfile $profile): void
    {
        $this->log('force_delete', $profile, $profile->toArray(), null);
    }
}