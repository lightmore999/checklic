<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Organization;
use App\Models\Manager;
use App\Models\OrgOwnerProfile;
use App\Models\OrgMemberProfile;
use App\Models\Limit;
use App\Models\DelegatedLimit;
use App\Models\Subscription;
use App\Observers\UserObserver;
use App\Observers\OrganizationObserver;
use App\Observers\ManagerObserver;
use App\Observers\OrgOwnerProfileObserver;
use App\Observers\OrgMemberProfileObserver;
use App\Observers\LimitObserver;
use App\Observers\DelegatedLimitObserver;
use App\Observers\SubscriptionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Регистрируем все Observers
        User::observe(UserObserver::class);
        Organization::observe(OrganizationObserver::class);
        Manager::observe(ManagerObserver::class);
        OrgOwnerProfile::observe(OrgOwnerProfileObserver::class);
        OrgMemberProfile::observe(OrgMemberProfileObserver::class);
        Limit::observe(LimitObserver::class);
        DelegatedLimit::observe(DelegatedLimitObserver::class);
        Subscription::observe(SubscriptionObserver::class);
    }
}