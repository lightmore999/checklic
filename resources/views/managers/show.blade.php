@extends('layouts.app')

@section('title', 'Просмотр менеджера')
@section('page-icon', 'bi-person-badge')

@section('content')
<div class="container-fluid py-4">
    @php
        $orgCount = $organizations->count();
        $activeOrgs = $organizations->where('status', 'active')->count();
        $pendingOrgs = $organizations->where('status', 'pending')->count();
        $inactiveOrgs = $organizations->where('status', 'inactive')->count();
        $expiredOrgs = $organizations->where('status', 'expired')->count();
    @endphp

    <!-- Заголовок с градиентом -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-primary text-white shadow-lg" 
                 style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <!-- Аватар организации -->
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-4" 
                                 style="width: 70px; height: 70px; font-size: 2rem; font-weight: 500; color: white; border: 3px solid rgba(255,255,255,0.3); flex-shrink: 0;">
                                {{ strtoupper(substr($manager->name, 0, 1)) }}
                            </div>
                            <div>
                                <h1 class="h2 mb-2">{{ $manager->name }}</h1>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-white text-primary px-3 py-2">
                                        <i class="bi bi-envelope me-1"></i>{{ $manager->email }}
                                    </span>
                                    <span class="badge bg-primary px-3 py-2">
                                        <i class="bi bi-person-badge me-1"></i>Менеджер
                                    </span>
                                    @if($manager->is_active)
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-check-circle-fill me-1"></i>Активен
                                        </span>
                                    @else
                                        <span class="badge bg-danger px-3 py-2">
                                            <i class="bi bi-x-circle-fill me-1"></i>Неактивен
                                        </span>
                                    @endif
                                </div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('admin.managers.index') }}" class="text-white opacity-75">
                                                Менеджеры
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active text-white" aria-current="page">
                                            {{ $manager->name }}
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.managers.index') }}" class="btn btn-light">
                                <i class="bi bi-arrow-left me-2"></i>Назад
                            </a>
                            <a href="{{ route('admin.managers.edit', $manager->id) }}" class="btn btn-warning">
                                <i class="bi bi-pencil-square me-2"></i>Редактировать
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика менеджера -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">Всего организаций</h6>
                            <h3 class="mb-0 fw-bold">{{ $orgCount }}</h3>
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
                            <h6 class="text-muted mb-1">Активных</h6>
                            <h3 class="mb-0 fw-bold">{{ $activeOrgs }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">Ожидают</h6>
                            <h3 class="mb-0 fw-bold">{{ $pendingOrgs }}</h3>
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
                            <h6 class="text-muted mb-1">Зарегистрирован</h6>
                            <h5 class="mb-0 fw-bold">{{ $manager->created_at->format('d.m.Y') }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Левая колонка -->
        <div class="col-lg-8">
            <!-- Основная информация -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle text-primary me-2"></i>
                        Основная информация
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 100px; height: 100px; font-size: 2.5rem; color: white;">
                                {{ strtoupper(substr($manager->name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold text-muted">Имя:</div>
                                <div class="col-md-9">{{ $manager->name }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold text-muted">Email:</div>
                                <div class="col-md-9">{{ $manager->email }}</div>
                            </div>
                            @if($manager->phone)
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold text-muted">Телефон:</div>
                                <div class="col-md-9">{{ $manager->phone }}</div>
                            </div>
                            @endif
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold text-muted">Статус:</div>
                                <div class="col-md-9">
                                    @if($manager->is_active)
                                        <span class="badge bg-success">Активен</span>
                                    @else
                                        <span class="badge bg-danger">Неактивен</span>
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold text-muted">Зарегистрирован:</div>
                                <div class="col-md-9">{{ $manager->created_at->format('d.m.Y H:i') }}</div>
                            </div>
                            @if($manager->managerProfile && $manager->managerProfile->admin)
                            <div class="row">
                                <div class="col-md-3 fw-bold text-muted">Создал:</div>
                                <div class="col-md-9">
                                    <span class="badge bg-dark">
                                        <i class="bi bi-person-gear me-1"></i>
                                        {{ $manager->managerProfile->admin->name }}
                                    </span>
                                    <small class="text-muted ms-2">(администратор)</small>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Подписки менеджера -->
            @if(isset($subscriptions) && $subscriptions->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-stars text-info me-2"></i>
                        Подписки менеджера
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
                                    <td>#{{ $subscription->id }}</td>
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
                                <h6 class="mb-0">
                                    <i class="bi bi-stars text-info me-2"></i>
                                    <span class="me-2">{{ $subscriptionName }}</span>
                                    @if($group['subscription']->status === 'active')
                                        <span class="badge bg-success ms-2">Активна</span>
                                    @elseif($group['subscription']->status === 'expired')
                                        <span class="badge bg-danger ms-2">Истекла</span>
                                    @else
                                        <span class="badge bg-secondary ms-2">{{ $group['subscription']->getStatusTextAttribute() }}</span>
                                    @endif
                                </h6>
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
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="border rounded p-2 text-center bg-light">
                                        <small class="text-muted d-block">Всего отчетов</small>
                                        <span class="fw-bold fs-5">{{ $group['total_quantity'] }} шт.</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 text-center bg-light">
                                        <small class="text-muted d-block">Использовано</small>
                                        <span class="fw-bold fs-5 text-warning">{{ $group['total_used'] }} шт.</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 text-center bg-light">
                                        <small class="text-muted d-block">Доступно</small>
                                        <span class="fw-bold fs-5 text-{{ $group['total_available'] > 0 ? 'success' : 'danger' }}">
                                            {{ $group['total_available'] }} шт.
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 text-center bg-light">
                                        <small class="text-muted d-block">Типов отчетов</small>
                                        <span class="fw-bold fs-5 text-info">{{ count($group['limits']) }}</span>
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
                                            <th>Всего</th>
                                            <th>Использовано</th>
                                            <th>Доступно</th>
                                            <th>Статус</th>
                                            <th>Прогресс</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($group['limits'] as $limit)
                                        <tr>
                                            <td>
                                                <strong>{{ $limit['report_type_name'] }}</strong>
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
                                            <td>
                                                <span class="badge bg-secondary">{{ $limit['quantity'] }} шт.</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $limit['used_quantity'] }} шт.</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $limit['available_quantity'] > 0 ? 'success' : 'danger' }}">
                                                    {{ $limit['available_quantity'] }} шт.
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
                                            <td style="min-width: 150px;">
                                                @if($limit['quantity'] > 0)
                                                    @php
                                                        $percentage = round(($limit['used_quantity'] / $limit['quantity']) * 100);
                                                        $progressClass = $limit['is_exhausted'] ? 'bg-danger' : 
                                                                        ($percentage > 80 ? 'bg-danger' : 
                                                                        ($percentage > 50 ? 'bg-warning' : 'bg-success'));
                                                    @endphp
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-grow-1" style="height: 8px;">
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
            @elseif(isset($subscriptions) && $subscriptions->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-stars text-info me-2"></i>
                            Отчеты по подпискам
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-file-text display-1 text-muted"></i>
                            </div>
                            <h4 class="text-muted mb-3">В подписках пока нет отчетов</h4>
                            <p class="text-muted mb-4">Добавьте отчеты в подписки</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Организации менеджера -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-buildings text-success me-2"></i>
                        Организации менеджера
                    </h5>
                    <span class="badge bg-success">{{ $orgCount }}</span>
                </div>
                <div class="card-body">
                    @if($orgCount > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Организация / ИНН</th>
                                        <th>Владелец</th>
                                        <th>Статус</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($organizations as $organization)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center me-2" 
                                                    style="width: 32px; height: 32px;">
                                                    <i class="bi bi-building"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $organization->name }}</div>
                                                    @if($organization->inn)
                                                        <small class="text-muted">ИНН: {{ $organization->inn }}</small>
                                                    @endif
                                                    <small class="text-muted d-block">ID: {{ $organization->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($organization->owner && $organization->owner->user)
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-2" 
                                                        style="width: 28px; height: 28px; font-size: 0.8rem;">
                                                        {{ strtoupper(substr($organization->owner->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="small fw-bold">{{ $organization->owner->user->name }}</div>
                                                        <small class="text-muted">{{ $organization->owner->user->email }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="badge bg-danger">Не назначен</span>
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
                                        <td>
                                            <a href="{{ route('admin.organization.show', $organization->id) }}" 
                                               class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center"
                                               style="width: 32px; height: 32px;"
                                               title="Перейти в организацию"
                                               data-bs-toggle="tooltip">
                                                <i class="bi bi-arrow-right-circle"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-buildings fs-1 text-muted mb-3"></i>
                            <h6 class="text-muted">Организаций пока нет</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Правая колонка - Статистика -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart text-info me-2"></i>
                        Статистика
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-3 border-bottom">
                        <div class="display-4 text-primary mb-2">{{ $orgCount }}</div>
                        <div class="text-muted">Всего организаций</div>
                    </div>
                    
                    <div class="row g-3 py-3 border-bottom">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="display-6 text-success mb-1">{{ $activeOrgs }}</div>
                                <small class="text-muted">Активных</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="display-6 text-warning mb-1">{{ $pendingOrgs }}</div>
                                <small class="text-muted">Ожидают</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Неактивных:</span>
                            <span class="badge bg-secondary">{{ $inactiveOrgs }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Истекших:</span>
                            <span class="badge bg-danger">{{ $expiredOrgs }}</span>
                        </div>
                    </div>

                    @if($orgCount > 0)
                        @php
                            $activePercentage = round(($activeOrgs / $orgCount) * 100);
                        @endphp
                        <div class="mt-4">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Активность организаций</span>
                                <span class="fw-semibold">{{ $activePercentage }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $activePercentage }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ТОЧНО КАК В СТРАНИЦЕ ОРГАНИЗАЦИИ */

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

/* Градиент */
.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
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

.display-6 {
    font-size: 1.5rem;
    font-weight: 300;
    line-height: 1.2;
}

.border-bottom {
    border-bottom: 1px solid #dee2e6 !important;
}

/* Для иконок в кружках */
.bg-opacity-10 {
    --bs-bg-opacity: 0.1;
}
</style>

@endsection

