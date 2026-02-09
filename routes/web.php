<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
    Route::get('/managers/create', 'App\Http\Controllers\ManagerController@create')->name('managers.create');
    Route::post('/managers/store', 'App\Http\Controllers\ManagerController@store')->name('managers.store');
    Route::get('/managers/{id}', 'App\Http\Controllers\ManagerController@show')->name('managers.show');
    Route::get('/managers/{id}/edit', 'App\Http\Controllers\ManagerController@edit')->name('managers.edit');
    Route::put('/managers/{id}/update', 'App\Http\Controllers\ManagerController@update')->name('managers.update');
    Route::post('/managers/{id}/toggle-status', 'App\Http\Controllers\ManagerController@toggleStatus')->name('managers.toggle-status');
    Route::delete('/managers/{id}/delete', 'App\Http\Controllers\ManagerController@destroy')->name('managers.delete');
    
    // Управление организациями
    Route::get('/organization/create', 'App\Http\Controllers\OrganizationController@create')->name('organization.create');
    Route::post('/organization/store', 'App\Http\Controllers\OrganizationController@store')->name('organization.store');
    Route::get('/organization/{id}', 'App\Http\Controllers\OrganizationController@show')->name('organization.show');
    Route::get('/organization/{id}/edit', 'App\Http\Controllers\OrganizationController@edit')->name('organization.edit');
    Route::post('/organization/{id}/update', 'App\Http\Controllers\OrganizationController@update')->name('organization.update');
    Route::post('/organization/{id}/toggle-status', 'App\Http\Controllers\OrganizationController@toggleStatus')->name('organization.toggle-status');
    Route::post('/organization/{id}/extend-subscription', 'App\Http\Controllers\OrganizationController@extendSubscription')->name('organization.extend-subscription');
    Route::delete('/organization/{id}/delete', 'App\Http\Controllers\OrganizationController@destroy')->name('organization.delete');
    
    // Список организаций
    Route::get('/organizations', 'App\Http\Controllers\OrganizationController@index')->name('organizations.list');
    
    // Управление сотрудниками организаций
    Route::prefix('organization/{organizationId}/member')->name('org-members.')->group(function () {
        Route::get('/create', 'App\Http\Controllers\OrgMemberController@create')->name('create');
        Route::post('/store', 'App\Http\Controllers\OrgMemberController@store')->name('store');
        Route::get('/{memberId}', 'App\Http\Controllers\OrgMemberController@show')->name('show');
        Route::get('/{memberId}/edit', 'App\Http\Controllers\OrgMemberController@edit')->name('edit');
        Route::post('/{memberId}/update', 'App\Http\Controllers\OrgMemberController@update')->name('update');
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
    
    // Организации менеджера
    Route::prefix('organization')->name('organization.')->group(function () {
        Route::get('/create', 'App\Http\Controllers\OrganizationController@create')->name('create');
        Route::post('/store', 'App\Http\Controllers\OrganizationController@store')->name('store');
        Route::get('/{id}', 'App\Http\Controllers\OrganizationController@show')->name('show');
        Route::get('/{id}/edit', 'App\Http\Controllers\OrganizationController@edit')->name('edit');
        Route::post('/{id}/update', 'App\Http\Controllers\OrganizationController@update')->name('update');
    });
    
    // Список организаций менеджера
    Route::get('/organizations', 'App\Http\Controllers\OrganizationController@managerIndex')->name('organizations.list');
    
    // Управление сотрудниками организаций менеджера
    Route::prefix('organization/{organizationId}/member')->name('org-members.')->group(function () {
        Route::get('/create', 'App\Http\Controllers\OrgMemberController@create')->name('create');
        Route::post('/store', 'App\Http\Controllers\OrgMemberController@store')->name('store');
        Route::get('/{memberId}', 'App\Http\Controllers\OrgMemberController@show')->name('show');
        Route::get('/{memberId}/edit', 'App\Http\Controllers\OrgMemberController@edit')->name('edit');
        Route::post('/{memberId}/update', 'App\Http\Controllers\OrgMemberController@update')->name('update');
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
    
    // Просмотр сотрудников организации (только просмотр)
    Route::prefix('organization/{organizationId}/member')->name('org-members.')->group(function () {
        Route::get('/{memberId}', 'App\Http\Controllers\OrgMemberController@show')->name('show');
    });
});

// ============================
// СОТРУДНИК ОРГАНИЗАЦИИ
// ============================
Route::middleware(['auth'])->prefix('member')->name('member.')->group(function () {
    
    // Профиль сотрудника (без дашборда)
    Route::get('/profile', 'App\Http\Controllers\OrgMemberController@profile')->name('profile');
    Route::get('/profile/edit', 'App\Http\Controllers\OrgMemberController@editProfile')->name('profile.edit');
    Route::post('/profile/update', 'App\Http\Controllers\OrgMemberController@updateProfile')->name('profile.update');
});

// ============================
// ОТЧЕТЫ
// ============================
Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/create', 'App\Http\Controllers\ReportController@create')->name('create');
    Route::post('/store', 'App\Http\Controllers\ReportController@store')->name('store');
    Route::get('/', 'App\Http\Controllers\ReportController@index')->name('index');
    Route::get('/{id}', 'App\Http\Controllers\ReportController@show')->name('show');
    Route::get('/{id}/edit', 'App\Http\Controllers\ReportController@edit')->name('edit');
    Route::post('/{id}/update', 'App\Http\Controllers\ReportController@update')->name('update');
    Route::delete('/{id}/delete', 'App\Http\Controllers\ReportController@destroy')->name('destroy');
});