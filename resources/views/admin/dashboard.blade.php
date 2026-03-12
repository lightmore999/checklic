@extends('layouts.app')

@section('title', 'Панель администратора')
@section('page-icon', 'bi-speedometer2')

@section('content')
<div class="container-fluid py-4">
    <!-- Заголовок с градиентом -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-danger text-white shadow-lg" 
                 style="background: linear-gradient(135deg, #fd7e14 0%, #e96b02 100%);">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <!-- Аватар администратора -->
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-4" 
                                 style="width: 70px; height: 70px; font-size: 2rem; font-weight: 500; color: white; border: 3px solid rgba(255,255,255,0.3); flex-shrink: 0;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h1 class="h2 mb-2">{{ $user->name }}</h1>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-white text-danger px-3 py-2">
                                        <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                                    </span>
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class="bi bi-person-gear me-1"></i>Администратор
                                    </span>
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="bi bi-check-circle-fill me-1"></i>Активен
                                    </span>
                                </div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item active text-white" aria-current="page">
                                            Панель администратора
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.managers.create') }}" class="btn btn-light">
                                <i class="bi bi-person-plus me-2"></i>Создать менеджера
                            </a>
                            <a href="{{ route('admin.organization.create') }}" class="btn btn-light">
                                <i class="bi bi-building-add me-2"></i>Создать организацию
                            </a>
                            <a href="{{ route('subscriptions.create') }}" class="btn btn-light">
                                <i class="bi bi-stars me-2"></i>Создать подписку
                            </a>
                            <a href="{{ route('admin.profile.edit') }}"  class="btn btn-light">
                                <i class="bi bi-pencil me-1"></i>Редактировать профиль
                            </a>
                            <a href="{{ route('admin.profile.change-password') }}"  class="btn btn-light">
                                <i class="bi bi-key me-1"></i>Сменить пароль
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row g-4 mb-4">
        <!-- Информация о текущем админе -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle text-danger me-2"></i>
                        Ваш профиль
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center text-center">
                        <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px; font-size: 2rem; color: white;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <h5 class="mb-1">{{ $user->name }}</h5>
                        <p class="text-muted mb-3">{{ $user->email }}</p>
                        
                        @if($user->phone)
                            <p class="mb-2">
                                <i class="bi bi-telephone text-primary me-2"></i>
                                <a href="tel:{{ $user->phone }}" class="text-decoration-none">{{ $user->phone }}</a>
                            </p>
                        @endif
                        
                        <div class="mb-3">
                            <span class="badge bg-danger">Администратор</span>
                            <span class="badge bg-success">Активен</span>
                        </div>
                        
                        <div class="small text-muted">
                            <i class="bi bi-calendar me-1"></i> В системе с: {{ $user->created_at->format('d.m.Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Статистика организаций -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-buildings text-success me-2"></i>
                        Организации
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-4 text-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-2" 
                                 style="width: 48px; height: 48px;">
                                <i class="bi bi-building text-primary"></i>
                            </div>
                            <h3 class="fw-bold mb-0">{{ $stats['total_organizations'] ?? 0 }}</h3>
                            <small class="text-muted">Всего</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-2" 
                                 style="width: 48px; height: 48px;">
                                <i class="bi bi-check-circle text-success"></i>
                            </div>
                            <h3 class="fw-bold mb-0">{{ $stats['active_organizations'] ?? 0 }}</h3>
                            <small class="text-muted">Активные</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-2" 
                                 style="width: 48px; height: 48px;">
                                <i class="bi bi-clock text-warning"></i>
                            </div>
                            <h3 class="fw-bold mb-0">{{ $stats['pending_organizations'] ?? 0 }}</h3>
                            <small class="text-muted">Ожидают</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Статистика менеджеров -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge text-primary me-2"></i>
                        Менеджеры
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-6 text-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-2" 
                                 style="width: 48px; height: 48px;">
                                <i class="bi bi-people text-primary"></i>
                            </div>
                            <h3 class="fw-bold mb-0">{{ $stats['managers_count'] ?? 0 }}</h3>
                            <small class="text-muted">Всего</small>
                        </div>
                        <div class="col-6 text-center">
                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-2" 
                                 style="width: 48px; height: 48px;">
                                <i class="bi bi-person-check text-success"></i>
                            </div>
                            <h3 class="fw-bold mb-0">{{ $stats['managers_active'] ?? 0 }}</h3>
                            <small class="text-muted">Активные</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Подписки администратора -->
    @if(isset($subscriptions) && $subscriptions->count() > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">
                <i class="bi bi-stars text-info me-2"></i>
                Ваши подписки
            </h5>
            <span class="badge bg-info">{{ $subscriptions->count() }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Дата начала</th>
                            <th>Дата окончания</th>
                            <th>Статус</th>
                            <th>Осталось дней</th>
                            <th>Отчетов</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscriptions as $subscription)
                        @php
                            $remainingDays = $subscription->getRemainingDays();
                            $statusClass = $subscription->status === 'active' ? 'success' : 
                                          ($subscription->status === 'expired' ? 'danger' : 
                                          ($subscription->status === 'pending' ? 'warning' : 'secondary'));
                            
                            $limitsCount = 0;
                            if (isset($groupedLimits)) {
                                foreach($groupedLimits as $group) {
                                    if ($group['subscription']->id == $subscription->id) {
                                        $limitsCount = count($group['limits']);
                                        break;
                                    }
                                }
                            }
                            $subscriptionName = $subscription->name ?? 'Подписка #' . $subscription->id;
                        @endphp
                        <tr>
                            <td class="text-muted">#{{ $subscription->id }}</td>
                            <td>
                                <span class="fw-semibold small">
                                    <i class="bi bi-tag text-info me-1"></i>
                                    {{ Str::limit($subscriptionName, 25) }}
                                </span>
                            </td>
                            <td>{{ $subscription->starts_at ? $subscription->starts_at->format('d.m.Y') : '—' }}</td>
                            <td>
                                @if($subscription->ends_at)
                                    {{ $subscription->ends_at->format('d.m.Y') }}
                                    @if($subscription->isExpiringSoon() && $subscription->isActive())
                                        <span class="badge bg-warning ms-1">скоро</span>
                                    @endif
                                @else
                                    <span class="text-muted">Бессрочно</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $statusClass }}">
                                    {{ $subscription->getStatusTextAttribute() }}
                                </span>
                            </td>
                            <td>
                                @if($remainingDays !== null)
                                    @if($subscription->isActive())
                                        <span class="badge bg-{{ $remainingDays <= 7 ? 'warning' : 'success' }}">
                                            {{ $remainingDays }} дн.
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                @else
                                    <span class="text-muted">∞</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $limitsCount }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Отчеты по подпискам -->
    @if(isset($groupedLimits) && count($groupedLimits) > 0)
        @foreach($groupedLimits as $group)
            @php
                $subscriptionName = $group['subscription']->name ?? 'Подписка #' . $group['subscription']->id;
            @endphp
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-stars text-info me-2"></i>
                            <span class="me-2">{{ Str::limit($subscriptionName, 30) }}</span>
                            @if($group['subscription']->status === 'active')
                                <span class="badge bg-success ms-2">Активна</span>
                            @elseif($group['subscription']->status === 'expired')
                                <span class="badge bg-danger ms-2">Истекла</span>
                            @else
                                <span class="badge bg-secondary ms-2">{{ $group['subscription']->getStatusTextAttribute() }}</span>
                            @endif
                        </h5>
                        <div class="text-muted small">
                            @if($group['subscription']->starts_at)
                                <span class="me-3"><i class="bi bi-calendar-plus me-1"></i>С {{ $group['subscription']->starts_at->format('d.m.Y') }}</span>
                            @endif
                            @if($group['subscription']->ends_at)
                                <span><i class="bi bi-calendar-x me-1"></i>до {{ $group['subscription']->ends_at->format('d.m.Y') }}</span>
                                @if($group['subscription']->getRemainingDays())
                                    <span class="badge bg-{{ $group['subscription']->getRemainingDays() <= 7 ? 'warning' : 'info' }} ms-2">
                                        осталось {{ $group['subscription']->getRemainingDays() }} дн.
                                    </span>
                                @endif
                            @else
                                <span class="text-muted">(бессрочная)</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Статистика по подписке -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block">Всего отчетов</small>
                                <span class="fw-bold fs-4">{{ $group['total_quantity'] }}</span>
                                <small class="text-muted d-block">шт.</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block">Использовано</small>
                                <span class="fw-bold fs-4 text-warning">{{ $group['total_used'] }}</span>
                                <small class="text-muted d-block">шт.</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block">Доступно</small>
                                <span class="fw-bold fs-4 text-{{ $group['total_available'] > 0 ? 'success' : 'danger' }}">
                                    {{ $group['total_available'] }}
                                </span>
                                <small class="text-muted d-block">шт.</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block">Типов отчетов</small>
                                <span class="fw-bold fs-4 text-info">{{ count($group['limits']) }}</span>
                                <small class="text-muted d-block">шт.</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Тип отчета</th>
                                    <th>Описание</th>
                                    <th>Доступ через</th>
                                    <th class="text-center">Всего</th>
                                    <th class="text-center">Использовано</th>
                                    <th class="text-center">Доступно</th>
                                    <th>Статус</th>
                                    <th>Прогресс</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($group['limits'] as $limit)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 32px; height: 32px; font-size: 0.9rem; color: #0d6efd;">
                                                {{ strtoupper(substr($limit['report_type_name'], 0, 1)) }}
                                            </div>
                                            <strong>{{ $limit['report_type_name'] }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        @if($limit['description'])
                                            <small class="text-muted">{{ Str::limit($limit['description'], 50) }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($limit['only_api'])
                                            <span class="badge bg-warning">API</span>
                                        @else
                                            <span class="badge bg-primary">UI</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">{{ $limit['quantity'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">
                                            {{ $limit['used_quantity'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $limit['available_quantity'] > 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $limit['available_quantity'] > 0 ? 'success' : 'danger' }} px-3 py-2">
                                            {{ $limit['available_quantity'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($limit['is_exhausted'])
                                            <span class="badge bg-danger">Исчерпан</span>
                                        @elseif(!$limit['has_limit'])
                                            <span class="badge bg-secondary">Не настроен</span>
                                        @else
                                            <span class="badge bg-success">Активен</span>
                                        @endif
                                    </td>
                                    <td style="min-width: 120px;">
                                        @if($limit['quantity'] > 0)
                                            @php
                                                $percentage = round(($limit['used_quantity'] / $limit['quantity']) * 100);
                                                $progressClass = $limit['is_exhausted'] ? 'bg-danger' : 
                                                                ($percentage > 80 ? 'bg-danger' : 
                                                                ($percentage > 50 ? 'bg-warning' : 'bg-success'));
                                            @endphp
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar {{ $progressClass }}" 
                                                         style="width: {{ $percentage }}%">
                                                    </div>
                                                </div>
                                                <small class="text-muted">{{ $percentage }}%</small>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
        
        <!-- Общая сводка -->
        @php
            $totalAllQuantity = collect($groupedLimits)->sum('total_quantity');
            $totalAllUsed = collect($groupedLimits)->sum('total_used');
            $totalAllAvailable = collect($groupedLimits)->sum('total_available');
            $totalAllPercentage = $totalAllQuantity > 0 ? round(($totalAllUsed / $totalAllQuantity) * 100) : 0;
            $totalAllSubscriptions = count($groupedLimits);
        @endphp
        
        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex flex-wrap align-items-center gap-4">
                            <div>
                                <span class="text-muted">Подписок:</span>
                                <span class="fw-bold ms-2">{{ $totalAllSubscriptions }}</span>
                            </div>
                            <div>
                                <span class="text-muted">Всего отчетов:</span>
                                <span class="fw-bold ms-2">{{ $totalAllQuantity }} шт.</span>
                            </div>
                            <div>
                                <span class="text-muted">Использовано:</span>
                                <span class="fw-bold text-warning ms-2">{{ $totalAllUsed }} шт.</span>
                            </div>
                            <div>
                                <span class="text-muted">Доступно:</span>
                                <span class="fw-bold text-success ms-2">{{ $totalAllAvailable }} шт.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted">Общий прогресс:</span>
                            <div class="progress flex-grow-1" style="height: 6px;">
                                <div class="progress-bar bg-{{ $totalAllPercentage > 80 ? 'danger' : ($totalAllPercentage > 50 ? 'warning' : 'success') }}" 
                                     style="width: {{ $totalAllPercentage }}%">
                                </div>
                            </div>
                            <small class="text-muted">{{ $totalAllPercentage }}%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0">
                    <i class="bi bi-speedometer text-info me-2"></i>
                    Ваши отчеты
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-speedometer fs-1 text-secondary"></i>
                    </div>
                    <h5 class="text-muted mb-3">У вас нет отчетов</h5>
                    <p class="text-muted mb-4">Создайте подписку и добавьте отчеты</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('subscriptions.create') }}" class="btn btn-info">
                            <i class="bi bi-stars me-1"></i> Создать подписку
                        </a>
                        <a href="{{ route('limits.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Создать отчет
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Раздел организаций -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0">
                    <i class="bi bi-buildings text-success me-2"></i>
                    Организации
                </h5>
                <small class="text-muted">Последние созданные организации</small>
            </div>
            <a href="{{ route('admin.organizations.list') }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-list-ul me-1"></i> Все организации
            </a>
        </div>
        <div class="card-body">
            @if($organizations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Организация / ИНН</th>
                                <th>Владелец</th>
                                <th>Менеджер</th>
                                <th>Статус</th>
                                <th class="text-center">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($organizations->take(5) as $organization)
                            <tr>
                                <td class="text-muted">#{{ $organization->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                             style="width: 32px; height: 32px; font-size: 0.9rem; color: #198754;">
                                            {{ strtoupper(substr($organization->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="fw-semibold">{{ Str::limit($organization->name, 25) }}</span>
                                            @if($organization->inn)
                                                <small class="text-muted d-block">ИНН: {{ $organization->inn }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($organization->owner && $organization->owner->user)
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 28px; height: 28px; font-size: 0.8rem; color: #0dcaf0;">
                                                {{ strtoupper(substr($organization->owner->user->name, 0, 1)) }}
                                            </div>
                                            <span class="small">{{ Str::limit($organization->owner->user->name, 15) }}</span>
                                        </div>
                                    @else
                                        <span class="badge bg-danger">Не назначен</span>
                                    @endif
                                </td>
                                <td>
                                    @if($organization->manager)
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 28px; height: 28px; font-size: 0.8rem; color: #0d6efd;">
                                                {{ strtoupper(substr($organization->manager->name, 0, 1)) }}
                                            </div>
                                            <span class="small">{{ Str::limit($organization->manager->name, 15) }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusConfig = [
                                            'active' => ['class' => 'success', 'text' => 'Активна'],
                                            'inactive' => ['class' => 'danger', 'text' => 'Неактивна'],
                                            'pending' => ['class' => 'warning', 'text' => 'Ожидает'],
                                            'suspended' => ['class' => 'warning', 'text' => 'Приостановлена'],
                                            'expired' => ['class' => 'danger', 'text' => 'Истекла'],
                                        ];
                                        $config = $statusConfig[$organization->status] ?? ['class' => 'secondary', 'text' => $organization->status];
                                    @endphp
                                    <span class="badge bg-{{ $config['class'] }}">
                                        {{ $config['text'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('admin.organization.show', $organization->id) }}" 
                                           class="btn btn-sm btn-outline-info rounded-circle"
                                           style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                           title="Просмотреть">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.organization.edit', $organization->id) }}" 
                                           class="btn btn-sm btn-outline-warning rounded-circle"
                                           style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                           title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($organizations->count() > 5)
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.organizations.list') }}" class="btn btn-outline-success">
                            <i class="bi bi-list-ul me-1"></i> Показать все организации ({{ $organizations->count() }})
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-buildings fs-1 text-secondary"></i>
                    </div>
                    <h5 class="text-muted mb-3">Организаций пока нет</h5>
                    <p class="text-muted mb-4">Создайте свою первую организацию</p>
                    <a href="{{ route('admin.organization.create') }}" class="btn btn-success">
                        <i class="bi bi-building-add me-1"></i> Создать организацию
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Менеджеры -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0">
                    <i class="bi bi-person-badge text-primary me-2"></i>
                    Менеджеры
                </h5>
                <small class="text-muted">Все менеджеры системы</small>
            </div>
        </div>
        <div class="card-body">
            @if($managers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Менеджер</th>
                                <th>Контакты</th>
                                <th>Создан</th>
                                <th>Кем создан</th>
                                <th>Статус</th>
                                <th class="text-center">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($managers->take(5) as $manager)
                            <tr>
                                <td class="text-muted">#{{ $manager->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                             style="width: 32px; height: 32px; font-size: 0.9rem; color: #0d6efd;">
                                            {{ strtoupper(substr($manager->name, 0, 1)) }}
                                        </div>
                                        <span class="fw-semibold">{{ Str::limit($manager->name, 20) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        <div><i class="bi bi-envelope text-muted me-1"></i> {{ Str::limit($manager->email, 20) }}</div>
                                        @if($manager->phone)
                                            <div><i class="bi bi-telephone text-muted me-1"></i> {{ $manager->phone }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $manager->created_at->format('d.m.Y') }}</td>
                                <td>
                                    @if($manager->managerProfile && $manager->managerProfile->admin)
                                        <span class="badge bg-dark">
                                            {{ Str::limit($manager->managerProfile->admin->name, 15) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($manager->is_active)
                                        <span class="badge bg-success">Активен</span>
                                    @else
                                        <span class="badge bg-danger">Неактивен</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('admin.managers.show', $manager->id) }}" 
                                           class="btn btn-sm btn-outline-info rounded-circle"
                                           style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                           title="Просмотреть">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.managers.edit', $manager->id) }}" 
                                           class="btn btn-sm btn-outline-warning rounded-circle"
                                           style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                           title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($managers->count() > 5)
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.managers.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-list-ul me-1"></i> Показать всех менеджеров ({{ $managers->count() }})
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-person-badge fs-1 text-secondary"></i>
                    </div>
                    <h5 class="text-muted mb-3">Менеджеров пока нет</h5>
                    <p class="text-muted mb-4">Создайте первого менеджера</p>
                    <a href="{{ route('admin.managers.create') }}" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i> Создать менеджера
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

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

/* Прогресс-бары - скругление 10px */
.progress {
    border-radius: 10px;
    background-color: #e9ecef;
}

.progress-bar {
    border-radius: 10px;
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

/* Градиент для админа */
.bg-gradient-danger {
    background: linear-gradient(135deg,  #fd7e14 0%, #e96b02 100%);
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

/* Для кнопок действий */
.btn-sm.rounded-circle {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Для статистики */
.display-4 {
    font-size: 2.5rem;
    font-weight: 300;
    line-height: 1.2;
}

/* Для светлых фонов */
.bg-light.rounded {
    border-radius: 0.75rem !important;
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
</style>


@endsection


@push('scripts')
<script>
    // Инициализация тултипов
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush