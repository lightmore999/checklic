@extends('layouts.app')

@section('title', 'История операций с лимитами')
@section('page-icon', 'bi-clock-history')

@section('content')
<div class="container-fluid py-4">
    <!-- Заголовок с градиентом -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-info text-white shadow-lg" 
                 style="background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-4" 
                                 style="width: 70px; height: 70px; font-size: 2rem; font-weight: 500; color: white; border: 3px solid rgba(255,255,255,0.3);">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <h1 class="h2 mb-2">История операций с лимитами</h1>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-white text-info px-3 py-2">
                                        <i class="bi bi-database me-1"></i>Всего: {{ $logs->total() }}
                                    </span>
                                    <span class="badge bg-white bg-opacity-25 px-3 py-2">
                                        <i class="bi bi-calendar me-1"></i>{{ now()->format('d.m.Y') }}
                                    </span>
                                </div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'manager.dashboard') }}" 
                                               class="text-white opacity-75">
                                                Панель {{ Auth::user()->isAdmin() ? 'админа' : 'менеджера' }}
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active text-white" aria-current="page">
                                            История операций
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('limits.history.export', request()->query()) }}" class="btn btn-light">
                                <i class="bi bi-download me-2"></i>Экспорт CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика за период -->
    @if(isset($stats) && $stats['total_operations'] > 0)
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">Всего операций</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_operations'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">Начислено</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['by_action']['return_quantity']['total_quantity'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">Списано</h6>
                            <h3 class="mb-0 fw-bold">{{ abs($stats['by_action']['use_quantity']['total_quantity'] ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-info bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">Создано/Делегировано</h6>
                            <h3 class="mb-0 fw-bold">{{ ($stats['by_action']['create']['total_quantity'] ?? 0) + ($stats['by_action']['delegate']['total_quantity'] ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- График по дням (если выбран период) -->
    @if(count($stats['daily']) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4">
            <h5 class="mb-0">
                <i class="bi bi-graph-up text-primary me-2"></i>
                Активность по дням
            </h5>
        </div>
        <div class="card-body">
            <canvas id="dailyChart" style="height: 300px; width: 100%;"></canvas>
        </div>
    </div>
    @endif
    @endif

    <!-- Карточка с фильтрами -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4">
            <h5 class="mb-0">
                <i class="bi bi-funnel text-primary me-2"></i>
                Фильтры
                <button class="btn btn-sm btn-link float-end text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#filters">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </h5>
        </div>
        <div class="collapse show" id="filters">
            <div class="card-body pt-3">
                <form method="GET" action="{{ route('limits.history') }}" id="filterForm" class="row g-3">
                    <!-- Фильтр по организации -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Организация</label>
                        <select name="organization_id" id="organization_id" class="form-select select2-organization">
                            <option value="">Все организации</option>
                            @foreach($organizations ?? [] as $org)
                                <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }} @if($org->inn) (ИНН: {{ $org->inn }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Фильтр по пользователю с поиском -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Пользователь</label>
                        <select name="user_id" id="user_id" class="form-select select2-user" data-placeholder="Поиск пользователя...">
                            <option value="">Все пользователи</option>
                            @foreach($users as $userOption)
                                <option value="{{ $userOption->id }}" {{ request('user_id') == $userOption->id ? 'selected' : '' }}>
                                    {{ $userOption->name }} ({{ $userOption->email }}) - {{ $userOption->getRoleDisplayName() }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Можно ввести имя или email для поиска</small>
                    </div>

                    <!-- Фильтр по подписке -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Подписка</label>
                        <select name="subscription_id" class="form-select select2-subscription">
                            <option value="">Все подписки</option>
                            @foreach($subscriptions as $sub)
                                <option value="{{ $sub->id }}" {{ request('subscription_id') == $sub->id ? 'selected' : '' }}>
                                    {{ $sub->name ?? 'Подписка #' . $sub->id }} - {{ $sub->user->name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Фильтр по типу операции -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Тип операции</label>
                        <select name="action" class="form-select">
                            <option value="">Все операции</option>
                            @foreach($actions as $value => $label)
                                <option value="{{ $value }}" {{ request('action') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Фильтр по типу отчета -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Тип отчета</label>
                        <select name="report_type_id" class="form-select">
                            <option value="">Все типы</option>
                            @foreach($reportTypes as $type)
                                <option value="{{ $type->id }}" {{ request('report_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Фильтр по дате -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Дата с</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Дата по</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>

                    <!-- Кнопки действий -->
                    <div class="col-12 mt-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-2"></i>Применить фильтры
                            </button>
                            <a href="{{ route('limits.history') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Сбросить
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Активные фильтры -->
    @if(request()->anyFilled(['organization_id', 'user_id', 'subscription_id', 'action', 'report_type_id', 'date_from', 'date_to']))
        <div class="alert alert-info py-2 mb-4">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <i class="bi bi-funnel-fill me-1"></i>
                <span class="fw-semibold">Активные фильтры:</span>
                
                @if(request('organization_id'))
                    @php 
                        $org = isset($organizations) ? $organizations->firstWhere('id', request('organization_id')) : null; 
                    @endphp
                    <span class="badge bg-info text-white">Организация: {{ $org->name ?? 'ID: ' . request('organization_id') }}</span>
                @endif
                
                @if(request('user_id'))
                    @php 
                        $usr = $users->firstWhere('id', request('user_id')); 
                    @endphp
                    <span class="badge bg-info text-white">Пользователь: {{ $usr->name ?? 'ID: ' . request('user_id') }}</span>
                @endif
                
                @if(request('subscription_id'))
                    @php 
                        $sub = $subscriptions->firstWhere('id', request('subscription_id')); 
                    @endphp
                    <span class="badge bg-info text-white">Подписка: {{ $sub->name ?? 'ID: ' . request('subscription_id') }}</span>
                @endif
                
                @if(request('action'))
                    <span class="badge bg-info text-white">Операция: {{ $actions[request('action')] ?? request('action') }}</span>
                @endif
                
                @if(request('report_type_id'))
                    @php 
                        $type = $reportTypes->firstWhere('id', request('report_type_id')); 
                    @endphp
                    <span class="badge bg-info text-white">Тип отчета: {{ $type->name ?? 'ID: ' . request('report_type_id') }}</span>
                @endif
                
                @if(request('date_from') && request('date_to'))
                    <span class="badge bg-info text-white">Период: {{ request('date_from') }} - {{ request('date_to') }}</span>
                @elseif(request('date_from'))
                    <span class="badge bg-info text-white">С: {{ request('date_from') }}</span>
                @elseif(request('date_to'))
                    <span class="badge bg-info text-white">По: {{ request('date_to') }}</span>
                @endif
            </div>
        </div>
    @endif

    <!-- Таблица истории -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 150px;">Дата</th>
                            <th style="min-width: 120px;">Операция</th>
                            <th style="min-width: 180px;">Кто</th>
                            <th style="min-width: 180px;">Кому</th>
                            <th style="min-width: 200px;">Подписка</th>
                            <th style="min-width: 150px;">Тип отчета</th>
                            <th style="width: 80px;">Кол-во</th>
                            <th style="min-width: 120px;">Баланс</th>
                            <th style="width: 60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $targetUser = $log->targetUser();
                                $subscription = $log->subscription();
                                $reportType = $log->reportType();
                                
                                // Определяем URL для пользователя (кто совершил действие)
                                $actorUrl = null;
                                if ($log->user) {
                                    if ($log->user->isAdmin()) {
                                        $actorUrl = route('admin.dashboard');
                                    } elseif ($log->user->isManager()) {
                                        $actorUrl = route('admin.managers.show', $log->user->id);
                                    } elseif ($log->user->isOrgOwner() && $log->user->orgOwnerProfile) {
                                        $actorUrl = route('admin.organization.show', $log->user->orgOwnerProfile->organization_id);
                                    } elseif ($log->user->isOrgMember() && $log->user->orgMemberProfile) {
                                        $actorUrl = route('admin.org-members.show', [
                                            $log->user->orgMemberProfile->organization_id,
                                            $log->user->orgMemberProfile->id
                                        ]);
                                    }
                                }
                                
                                // Определяем URL для целевого пользователя (кому)
                                $targetUrl = null;
                                if ($targetUser) {
                                    if ($targetUser->isAdmin()) {
                                        $targetUrl = route('admin.dashboard');
                                    } elseif ($targetUser->isManager()) {
                                        $targetUrl = route('admin.managers.show', $targetUser->id);
                                    } elseif ($targetUser->isOrgOwner() && $targetUser->orgOwnerProfile) {
                                        $targetUrl = route('admin.organization.show', $targetUser->orgOwnerProfile->organization_id);
                                    } elseif ($targetUser->isOrgMember() && $targetUser->orgMemberProfile) {
                                        $targetUrl = route('admin.org-members.show', [
                                            $targetUser->orgMemberProfile->organization_id,
                                            $targetUser->orgMemberProfile->id
                                        ]);
                                    }
                                }
                                
                                // Определяем URL для подписки
                                $subscriptionUrl = $subscription ? route('subscriptions.show', $subscription->id) : null;
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" 
                                             style="width: 28px; height: 28px;">
                                            <i class="bi bi-clock text-primary small"></i>
                                        </div>
                                        <div>
                                            <span class="small">{{ $log->created_at->format('d.m.Y') }}</span>
                                            <small class="text-muted d-block">{{ $log->created_at->format('H:i:s') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->operation_color }} px-3 py-2">
                                        <i class="bi {{ $log->operation_icon }} me-1"></i>
                                        {{ $log->action_name }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->user)
                                        @if($actorUrl)
                                            <a href="{{ $actorUrl }}" class="text-decoration-none">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 28px; height: 28px; font-size: 0.8rem; color: #0d6efd;">
                                                        {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <span class="fw-semibold small">{{ $log->user->name }}</span>
                                                        <small class="text-muted d-block">{{ $log->user->getRoleDisplayName() }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                        @else
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 28px; height: 28px; font-size: 0.8rem; color: #0d6efd;">
                                                    {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="fw-semibold small">{{ $log->user->name }}</span>
                                                    <small class="text-muted d-block">{{ $log->user->getRoleDisplayName() }}</small>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 28px; height: 28px; color: #6c757d;">
                                                <i class="bi bi-robot"></i>
                                            </div>
                                            <span class="text-muted small">Система</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($targetUser)
                                        @if($targetUrl)
                                            <a href="{{ $targetUrl }}" class="text-decoration-none">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 28px; height: 28px; font-size: 0.8rem; color: #198754;">
                                                        {{ strtoupper(substr($targetUser->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <span class="fw-semibold small">{{ $targetUser->name }}</span>
                                                        <small class="text-muted d-block">{{ $targetUser->getRoleDisplayName() }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                        @else
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 28px; height: 28px; font-size: 0.8rem; color: #198754;">
                                                    {{ strtoupper(substr($targetUser->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="fw-semibold small">{{ $targetUser->name }}</span>
                                                    <small class="text-muted d-block">{{ $targetUser->getRoleDisplayName() }}</small>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($subscription)
                                        @if($subscriptionUrl)
                                            <a href="{{ $subscriptionUrl }}" class="text-decoration-none">
                                                <div>
                                                    <span class="fw-semibold small">{{ $subscription->name ?? 'Подписка #' . $subscription->id }}</span>
                                                    @if($subscription->user)
                                                        <small class="text-muted d-block">{{ $subscription->user->name }}</small>
                                                    @endif
                                                </div>
                                            </a>
                                        @else
                                            <div>
                                                <span class="fw-semibold small">{{ $subscription->name ?? 'Подписка #' . $subscription->id }}</span>
                                                @if($subscription->user)
                                                    <small class="text-muted d-block">{{ $subscription->user->name }}</small>
                                                @endif
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($reportType)
                                        <span class="small">{{ $reportType->name }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->display_quantity)
                                        <span class="badge bg-{{ $log->display_quantity > 0 ? 'success' : ($log->display_quantity < 0 ? 'danger' : 'secondary') }} bg-opacity-10 text-{{ $log->display_quantity > 0 ? 'success' : ($log->display_quantity < 0 ? 'danger' : 'secondary') }} px-3 py-2">
                                            {{ $log->display_quantity > 0 ? '+' : '' }}{{ $log->display_quantity }}
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->display_balance_before !== '—' || $log->display_balance_after !== '—')
                                        <div class="d-flex align-items-center">
                                            <span class="small fw-semibold">{{ $log->display_balance_before }}</span>
                                            <i class="bi bi-arrow-right mx-1 text-muted small"></i>
                                            <span class="small fw-semibold">{{ $log->display_balance_after }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->entity_type === 'delegated_limit')
                                        <span class="badge bg-info" title="Делегированный лимит">
                                            <i class="bi bi-people"></i>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                                         style="width: 80px; height: 80px;">
                                        <i class="bi bi-clock-history fs-1 text-secondary"></i>
                                    </div>
                                    <h5 class="text-muted mb-3">История операций пуста</h5>
                                    <p class="text-muted mb-0">Попробуйте изменить параметры фильтрации</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            @if($logs->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4 px-4 pb-4">
                    <div class="text-muted small">
                        Показано {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} из {{ $logs->total() }}
                    </div>
                    <div>
                        {{ $logs->withQueryString()->links() }}
                    </div>
                </div>
            @elseif($logs->total() > 0 && !$logs->hasPages())
                <div class="text-center py-3 border-top">
                    <div class="text-muted small">
                        Всего {{ $logs->total() }} записей
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* ТОЧНО КАК ВО ВСЕХ СТРАНИЦАХ */

    /* Круги всегда идеальные */
    .rounded-circle {
        border-radius: 50% !important;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    /* Карточки - скругление 1rem (16px) */
    .card {
        border-radius: 1rem;
        transition: all 0.2s;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Бейджи - сильно скругленные 30px */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
        border-radius: 30px;
        padding: 0.35em 0.65em;
    }

    /* Заголовки карточек */
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0,0,0,.125);
        padding: 1rem 1.25rem;
    }

    .card-header h5, .card-header h6 {
        margin-bottom: 0;
        font-weight: 600;
    }

    .card-body {
        padding: 1.25rem;
    }

    /* Градиент для заголовка */
    .bg-gradient-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
    }

    /* Таблицы */
    .table th {
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
    }

    .table td {
        vertical-align: middle;
    }

    /* Для иконок в кружках */
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }

    /* Для текста в кружках с буквами */
    .rounded-circle.bg-primary, 
    .rounded-circle.bg-success, 
    .rounded-circle.bg-info,
    .rounded-circle.bg-warning,
    .rounded-circle.bg-danger {
        font-weight: 500;
    }

    /* Для форм */
    .form-label {
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Для алертов */
    .alert-info {
        background-color: #cff4fc;
        border-color: #b6effb;
        color: #055160;
        border-radius: 0.75rem;
    }

    /* Select2 стили */
    .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }
    
    /* Стили для кнопок действий */
    .btn-sm.rounded-circle {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Стили для ссылок */
    a.text-decoration-none:hover {
        text-decoration: underline !important;
    }
    
    .table a {
        color: #0d6efd;
    }
    
    .table a:hover {
        color: #0a58ca;
    }
    
    /* Для баланса */
    .bi-arrow-right {
        font-size: 0.8rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ru.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Инициализация Select2 для организаций
    $('.select2-organization').select2({
        theme: 'default',
        language: 'ru',
        placeholder: 'Выберите организацию',
        allowClear: true,
        width: '100%'
    });

    // Инициализация Select2 для пользователей с поиском
    $('.select2-user').select2({
        theme: 'default',
        language: 'ru',
        placeholder: 'Поиск пользователя...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 0,
        ajax: {
            url: '{{ route("users.search") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    search: params.term || '',
                    organization_id: $('#organization_id').val()
                };
            },
            processResults: function(data) {
                let results = [{
                    id: '',
                    text: 'Все пользователи'
                }];
                
                if (Array.isArray(data)) {
                    results = results.concat(data);
                }
                
                return {
                    results: results
                };
            },
            cache: true
        }
    });

    // Инициализация Select2 для подписок
    $('.select2-subscription').select2({
        theme: 'default',
        language: 'ru',
        placeholder: 'Выберите подписку',
        allowClear: true,
        width: '100%'
    });

    // При изменении организации - обновляем список пользователей
    $('#organization_id').on('change', function() {
        $('.select2-user').val(null).trigger('change');
    });

    // Автоматическая отправка формы при изменении select-фильтров
    $('#organization_id, #user_id, #subscription_id, select[name="action"], select[name="report_type_id"]').on('change', function() {
        $('#filterForm').submit();
    });

    // Отправка формы при изменении дат (с задержкой)
    let dateTimer;
    $('input[type="date"]').on('change', function() {
        clearTimeout(dateTimer);
        dateTimer = setTimeout(() => {
            $('#filterForm').submit();
        }, 500);
    });

    // Инициализация тултипов
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// График
document.addEventListener('DOMContentLoaded', function() {
    @if(isset($stats['daily']) && count($stats['daily']) > 0)
    const ctx = document.getElementById('dailyChart').getContext('2d');

    const dates = @json($stats['daily']->pluck('date'));
    const created = @json($stats['daily']->pluck('created'));
    const returned = @json($stats['daily']->pluck('returned'));
    const used = @json($stats['daily']->pluck('used'));

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dates.map(date => {
                const d = new Date(date);
                return d.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit' });
            }),
            datasets: [
                {
                    label: 'Создано',
                    data: created,
                    backgroundColor: 'rgba(23, 162, 184, 0.5)',
                    borderColor: '#17a2b8',
                    borderWidth: 1
                },
                {
                    label: 'Возвращено',
                    data: returned,
                    backgroundColor: 'rgba(40, 167, 69, 0.5)',
                    borderColor: '#28a745',
                    borderWidth: 1
                },
                {
                    label: 'Использовано',
                    data: used.map(v => Math.abs(v)),
                    backgroundColor: 'rgba(220, 53, 69, 0.5)',
                    borderColor: ' #fd7e14',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {  
                            return context.dataset.label + ': ' + context.raw;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value;
                        }
                    }
                }
            }
        }
    });
    @endif
});
</script>
@endpush
