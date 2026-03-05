<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Organization;
use App\Models\Manager;
use App\Models\OrgOwnerProfile;
use App\Models\OrgMemberProfile;
use App\Observers\UserObserver;
use App\Observers\OrganizationObserver;
use App\Observers\ManagerObserver;
use App\Observers\OrgOwnerProfileObserver;
use App\Observers\OrgMemberProfileObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Регистрируем все Observers
        User::observe(UserObserver::class);
        Organization::observe(OrganizationObserver::class);
        Manager::observe(ManagerObserver::class);
        OrgOwnerProfile::observe(OrgOwnerProfileObserver::class);
        OrgMemberProfile::observe(OrgMemberProfileObserver::class);
    }
}