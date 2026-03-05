<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LimitController;
use App\Http\Controllers\DelegatedLimitController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\UserOrganizationLogController;

// Главная страница
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        if ($user->isManager()) {
            return redirect()->route('manager.dashboard');
        }
        
        if ($user->isOrgOwner()) {
            return redirect()->route('owner.dashboard');
        }
        
        if ($user->isOrgMember()) {
            return redirect()->route('member.profile');
        }
        
        return 'Вы авторизованы! Роль: ' . $user->role;
    }
    
    return redirect()->route('login');
});


// Аутентификация
Route::middleware('guest')->group(function () {
    Route::get('login', 'App\Http\Controllers\Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', 'App\Http\Controllers\Auth\LoginController@login');
});

// Выход
Route::post('logout', 'App\Http\Controllers\Auth\LoginController@logout')->name('logout');

// ============================
// АДМИНИСТРАТОР
// ============================
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Главная панель админа
    Route::get('/dashboard', 'App\Http\Controllers\AdminController@dashboard')->name('dashboard');
    
    // Управление менеджерами
    Route::get('/managers', 'App\Http\Controllers\ManagerController@index')->name('managers.index');
    Route::get('/managers/create', 'App\Http\Controllers\ManagerController@create')->name('managers.create');
    Route::post('/managers/store', 'App\Http\Controllers\ManagerController@store')->name('managers.store');
    Route::get('/managers/{id}', 'App\Http\Controllers\ManagerController@show')->name('managers.show');
    Route::get('/managers/{id}/edit', 'App\Http\Controllers\ManagerController@edit')->name('managers.edit');
    Route::put('/managers/{id}/update', 'App\Http\Controllers\ManagerController@update')->name('managers.update');
    Route::post('/managers/{id}/toggle-status', 'App\Http\Controllers\ManagerController@toggleStatus')->name('managers.toggle-status');
    Route::delete('/managers/{id}/delete', 'App\Http\Controllers\ManagerController@destroy')->name('managers.delete');
    
    // Управление организациями (использует общий метод)
    Route::get('/organizations', 'App\Http\Controllers\OrganizationController@index')->name('organizations.list');
    Route::get('/organization/create', 'App\Http\Controllers\OrganizationController@create')->name('organization.create');
    Route::post('/organization/store', 'App\Http\Controllers\OrganizationController@store')->name('organization.store');
    Route::get('/organization/{id}', 'App\Http\Controllers\OrganizationController@show')->name('organization.show');
    Route::get('/organization/{id}/edit', 'App\Http\Controllers\OrganizationController@edit')->name('organization.edit');
    Route::put('/organization/{id}', 'App\Http\Controllers\OrganizationController@update')->name('organization.update');
    Route::post('/organization/{id}/toggle-status', 'App\Http\Controllers\OrganizationController@toggleStatus')->name('organization.toggle-status');
    Route::post('/organization/{id}/extend-subscription', 'App\Http\Controllers\OrganizationController@extendSubscription')->name('organization.extend-subscription');
    Route::delete('/organization/{id}/delete', 'App\Http\Controllers\OrganizationController@destroy')->name('organization.delete');
    
    // Управление сотрудниками организаций
    Route::prefix('organization/{organizationId}/member')->name('org-members.')->group(function () {
        Route::get('/create', 'App\Http\Controllers\OrgMemberController@create')->name('create');
        Route::post('/store', 'App\Http\Controllers\OrgMemberController@store')->name('store');
        Route::get('/{memberId}', 'App\Http\Controllers\OrgMemberController@show')->name('show');
        Route::get('/{memberId}/edit', 'App\Http\Controllers\OrgMemberController@edit')->name('edit');
        Route::put('/{memberId}/update', 'App\Http\Controllers\OrgMemberController@update')->name('update');
        Route::post('/{memberId}/change-password', 'App\Http\Controllers\OrgMemberController@changePassword')->name('change-password');
        Route::post('/{memberId}/toggle-status', 'App\Http\Controllers\OrgMemberController@toggleStatus')->name('toggle-status');
        Route::delete('/{memberId}/delete', 'App\Http\Controllers\OrgMemberController@destroy')->name('delete');
    });
    
    // Управление пользователями
    Route::put('/users/{id}/toggle-status', 'App\Http\Controllers\AdminController@toggleUserStatus')->name('users.toggle-status');
    Route::delete('/users/{id}', 'App\Http\Controllers\AdminController@deleteUser')->name('users.delete');
});

// ============================
// МЕНЕДЖЕР
// ============================
Route::middleware(['auth'])->prefix('manager')->name('manager.')->group(function () {
    
    // Дашборд менеджера
    Route::get('/dashboard', 'App\Http\Controllers\ManagerController@dashboard')->name('dashboard');
    
    // Профиль менеджера
    Route::get('/profile', 'App\Http\Controllers\ManagerController@profile')->name('profile');
    Route::get('/profile/edit', 'App\Http\Controllers\ManagerController@editProfile')->name('profile.edit');
    Route::post('/profile/update', 'App\Http\Controllers\ManagerController@updateProfile')->name('profile.update');
    
    // Организации менеджера (использует ТОТ ЖЕ общий метод)
    Route::get('/organizations', 'App\Http\Controllers\OrganizationController@index')->name('organizations.list');
    Route::get('/organization/create', 'App\Http\Controllers\OrganizationController@create')->name('organization.create');
    Route::post('/organization/store', 'App\Http\Controllers\OrganizationController@store')->name('organization.store');
    Route::get('/organization/{id}', 'App\Http\Controllers\OrganizationController@show')->name('organization.show');
    Route::get('/organization/{id}/edit', 'App\Http\Controllers\OrganizationController@edit')->name('organization.edit');
    Route::put('/organization/{id}', 'App\Http\Controllers\OrganizationController@update')->name('organization.update');
    Route::post('/organization/{id}/toggle-status', 'App\Http\Controllers\OrganizationController@toggleStatus')->name('organization.toggle-status');
    Route::post('/organization/{id}/extend-subscription', 'App\Http\Controllers\OrganizationController@extendSubscription')->name('organization.extend-subscription');
    
    // Управление сотрудниками организаций менеджера
    Route::prefix('organization/{organizationId}/member')->name('org-members.')->group(function () {
        Route::get('/create', 'App\Http\Controllers\OrgMemberController@create')->name('create');
        Route::post('/store', 'App\Http\Controllers\OrgMemberController@store')->name('store');
        Route::get('/{memberId}', 'App\Http\Controllers\OrgMemberController@show')->name('show');
        Route::get('/{memberId}/edit', 'App\Http\Controllers\OrgMemberController@edit')->name('edit');
        Route::put('/{memberId}/update', 'App\Http\Controllers\OrgMemberController@update')->name('update');
        Route::post('/{memberId}/change-password', 'App\Http\Controllers\OrgMemberController@changePassword')->name('change-password');
        Route::post('/{memberId}/toggle-status', 'App\Http\Controllers\OrgMemberController@toggleStatus')->name('toggle-status');
        Route::delete('/{memberId}/delete', 'App\Http\Controllers\OrgMemberController@destroy')->name('delete');
    });
});

// ============================
// ВЛАДЕЛЕЦ ОРГАНИЗАЦИИ
// ============================
Route::middleware(['auth'])->prefix('owner')->name('owner.')->group(function () {
    
    // Дашборд владельца
    Route::get('/dashboard', 'App\Http\Controllers\OrganizationController@ownerDashboard')->name('dashboard');
    
    // Организация владельца
    Route::get('/organization', 'App\Http\Controllers\OrganizationController@ownerOrganization')->name('organization');
    Route::get('/organization/{id}', 'App\Http\Controllers\OrganizationController@ownerShow')->name('organization.show');
    
    // Управление сотрудниками организаций (полные права)
    Route::prefix('organization/{organizationId}/member')->name('org-members.')->group(function () {
        Route::get('/create', 'App\Http\Controllers\OrgMemberController@create')->name('create');
        Route::post('/store', 'App\Http\Controllers\OrgMemberController@store')->name('store');
        Route::get('/{memberId}', 'App\Http\Controllers\OrgMemberController@show')->name('show');
        Route::get('/{memberId}/edit', 'App\Http\Controllers\OrgMemberController@edit')->name('edit');
        Route::put('/{memberId}/update', 'App\Http\Controllers\OrgMemberController@update')->name('update');
        Route::post('/{memberId}/change-password', 'App\Http\Controllers\OrgMemberController@changePassword')->name('change-password');
        Route::post('/{memberId}/toggle-status', 'App\Http\Controllers\OrgMemberController@toggleStatus')->name('toggle-status');
        Route::delete('/{memberId}/delete', 'App\Http\Controllers\OrgMemberController@destroy')->name('delete');
    });
});

// ============================
// СОТРУДНИК ОРГАНИЗАЦИИ
// ============================
Route::middleware(['auth'])->prefix('member')->name('member.')->group(function () {
    
    // Дашборд сотрудника
    Route::get('/dashboard', 'App\Http\Controllers\OrgMemberController@dashboard')->name('dashboard');
    
    // Профиль сотрудника
    Route::get('/profile', 'App\Http\Controllers\OrgMemberController@profile')->name('profile');
    Route::get('/profile/edit', 'App\Http\Controllers\OrgMemberController@editProfile')->name('profile.edit');
    Route::post('/profile/update', 'App\Http\Controllers\OrgMemberController@updateProfile')->name('profile.update');
});

// ============================
// ОТЧЕТЫ
// ============================
Route::middleware('auth')->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::patch('/reports/{report}/cancel', [ReportController::class, 'cancel'])->name('reports.cancel');
    Route::post('/reports/bulk/store', [App\Http\Controllers\ReportController::class, 'bulkStore'])->name('reports.bulk.store');
    Route::post('/reports/preview', [App\Http\Controllers\ReportController::class, 'previewExcel'])->name('reports.preview');
});

// ============================
// ЛИМИТЫ (с проверкой ролей)
// ============================
Route::middleware(['auth'])->group(function () {
    
    // Общие маршруты для лимитов (доступны админу и менеджеру)
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::get('/limits', [LimitController::class, 'index'])->name('limits.index');
        Route::get('/limits/create', [LimitController::class, 'create'])->name('limits.create');
        Route::post('/limits', [LimitController::class, 'store'])->name('limits.store');
        Route::get('/limits/bulk-create', [LimitController::class, 'bulkCreate'])->name('limits.bulk-create');
        Route::post('/limits/bulk-store', [LimitController::class, 'bulkStore'])->name('limits.bulk-store');
        Route::get('/users/{user}/limits', [LimitController::class, 'userLimits'])->name('users.limits');
    });
    
    // Только админ может редактировать/удалять лимиты
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/limits/{limit}/edit', [LimitController::class, 'edit'])->name('limits.edit');
        Route::put('/limits/{limit}', [LimitController::class, 'update'])->name('limits.update');
        Route::delete('/limits/{limit}', [LimitController::class, 'destroy'])->name('limits.destroy');
    });
    
    // Делегирование лимитов (доступно всем аутентифицированным)
    Route::post('/limits/{limit}/delegate', [LimitController::class, 'delegate'])->name('limits.delegate');
});

// ============================
// ДЕЛЕГИРОВАННЫЕ ЛИМИТЫ
// ============================
Route::middleware(['auth'])->group(function () {
    Route::post('/delegated-limits', [DelegatedLimitController::class, 'store'])->name('delegated-limits.store');
    Route::delete('/delegated-limits/{delegatedLimit}', [DelegatedLimitController::class, 'destroy'])->name('delegated-limits.destroy');
});

// ============================
// ПОИСК ПОЛЬЗОВАТЕЛЕЙ
// ============================
Route::middleware(['auth'])->get('/users/search', [UserController::class, 'search'])->name('users.search');

// ============================
// ПОДПИСКИ
// ============================
Route::middleware(['auth'])->group(function () {
    
    // Общие маршруты для подписок (доступны админу и менеджеру)
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::get('/subscriptions', [App\Http\Controllers\SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/create', [App\Http\Controllers\SubscriptionController::class, 'create'])->name('subscriptions.create');
        Route::post('/subscriptions', [App\Http\Controllers\SubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::get('/subscriptions/{subscription}', [App\Http\Controllers\SubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::get('/subscriptions/{subscription}/edit', [App\Http\Controllers\SubscriptionController::class, 'edit'])->name('subscriptions.edit');
        Route::put('/subscriptions/{subscription}', [App\Http\Controllers\SubscriptionController::class, 'update'])->name('subscriptions.update');
        
        // Действия с подписками
        Route::post('/subscriptions/{subscription}/extend', [App\Http\Controllers\SubscriptionController::class, 'extend'])->name('subscriptions.extend');
        Route::post('/subscriptions/{subscription}/activate', [App\Http\Controllers\SubscriptionController::class, 'activate'])->name('subscriptions.activate');
        Route::post('/subscriptions/{subscription}/suspend', [App\Http\Controllers\SubscriptionController::class, 'suspend'])->name('subscriptions.suspend');
        Route::post('/subscriptions/{subscription}/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    });
    
    // Только админ может удалять подписки
    Route::middleware(['role:admin'])->group(function () {
        Route::delete('/subscriptions/{subscription}', [App\Http\Controllers\SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
    });
    
    // API маршруты для подписок (доступны всем аутентифицированным)
    Route::get('/api/users/{user}/subscription/check', [App\Http\Controllers\SubscriptionController::class, 'checkUserSubscription'])->name('api.users.subscription.check');
    Route::get('/api/subscriptions/stats', [App\Http\Controllers\SubscriptionController::class, 'stats'])->name('api.subscriptions.stats');
    
    // НОВЫЙ МАРШРУТ: Получение всех подписок пользователя
    Route::get('/api/users/{user}/subscriptions', [App\Http\Controllers\SubscriptionController::class, 'getUserSubscriptions'])->name('api.users.subscriptions');
});

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Логи действий
    Route::get('/logs', [UserOrganizationLogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{log}', [UserOrganizationLogController::class, 'show'])->name('logs.show');
    Route::get('/logs/export/csv', [UserOrganizationLogController::class, 'export'])->name('logs.export');
    Route::post('/logs/clean', [UserOrganizationLogController::class, 'clean'])->name('logs.clean');
    Route::get('/logs/statistics', [UserOrganizationLogController::class, 'statistics'])->name('logs.statistics');
});