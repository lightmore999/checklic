<?php

namespace App\Observers;

use App\Models\OrgMemberProfile;
use App\Traits\Loggable;

class OrgMemberProfileObserver
{
    use Loggable;

    public function created(OrgMemberProfile $profile): void
    {
        $this->logCreate($profile);
    }

    public function updated(OrgMemberProfile $profile): void
    {
        $changes = $profile->getChanges();
        if (empty($changes)) return;

        $oldData = [];
        foreach (array_keys($changes) as $field) {
            $oldData[$field] = $profile->getOriginal($field);
        }

        $this->logUpdate($profile, $oldData);
    }

    public function deleted(OrgMemberProfile $profile): void
    {
        $this->logDelete($profile);
    }

    public function restored(OrgMemberProfile $profile): void
    {
        $this->log('restore', $profile, null, $profile->toArray());
    }

    public function forceDeleted(OrgMemberProfile $profile): void
    {
        $this->log('force_delete', $profile, $profile->toArray(), null);
    }
}