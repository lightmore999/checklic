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
                            <!-- Аватар с первой буквой -->
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-4" 
                                 style="width: 70px; height: 70px; font-size: 2rem; font-weight: 500; color: white; border: 3px solid rgba(255,255,255,0.3); flex-shrink: 0;">
                                {{ strtoupper(substr($manager->name, 0, 1)) }}
                            </div>
                            <div>
                                <h1 class="h2 mb-2">{{ $manager->name }}</h1>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-white text-primary px-3 py-2">{{ $manager->email }}</span>
                                    <span class="badge bg-primary px-3 py-2">Менеджер</span>
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
                                            <a href="{{ route('admin.managers.index') }}" class="text-white opacity-75">Менеджеры</a>
                                        </li>
                                        <li class="breadcrumb-item active text-white" aria-current="page">{{ $manager->name }}</li>
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

    <div class="row g-4">
        <!-- Левая колонка -->
        <div class="col-lg-8">
            <!-- Основная информация -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle text-primary me-2"></i>
                        Основная информация
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-4">
                        <div class="col-md-3 text-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto" 
                                 style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: 500; color: #0d6efd;">
                                {{ strtoupper(substr($manager->name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                             style="width: 36px; height: 36px;">
                                            <i class="bi bi-person text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Имя</small>
                                            <span class="fw-semibold">{{ $manager->name }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                             style="width: 36px; height: 36px;">
                                            <i class="bi bi-envelope text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Email</small>
                                            <span class="fw-semibold">{{ $manager->email }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($manager->phone)
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                             style="width: 36px; height: 36px;">
                                            <i class="bi bi-telephone text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Телефон</small>
                                            <span class="fw-semibold">{{ $manager->phone }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                             style="width: 36px; height: 36px;">
                                            <i class="bi bi-calendar text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Зарегистрирован</small>
                                            <span class="fw-semibold">{{ $manager->created_at->format('d.m.Y H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($manager->managerProfile && $manager->managerProfile->admin)
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                             style="width: 36px; height: 36px;">
                                            <i class="bi bi-person-gear text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Создал администратор</small>
                                            <span class="fw-semibold">{{ $manager->managerProfile->admin->name }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Подписки менеджера -->
            @if(isset($subscriptions) && $subscriptions->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-stars text-warning me-2"></i>
                        Подписки менеджера
                    </h5>
                    <span class="badge bg-warning">{{ $subscriptions->count() }}</span>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-4">
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
                            
                            <div class="col-md-6">
                                <div class="card border h-100">
                                    <div class="card-header bg-transparent border-0 pt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-{{ $statusClass }} px-3 py-2">
                                                {{ $subscription->getStatusTextAttribute() }}
                                            </span>
                                            <span class="text-muted small">#{{ $subscription->id }}</span>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <h6 class="fw-bold mb-2">{{ Str::limit($subscriptionName, 30) }}</h6>
                                        
                                        <div class="d-flex justify-content-between small mb-2">
                                            <span class="text-muted">
                                                <i class="bi bi-calendar-plus me-1"></i>{{ $subscription->starts_at ? $subscription->starts_at->format('d.m.Y') : '—' }}
                                            </span>
                                            <span class="text-muted">
                                                <i class="bi bi-calendar-x me-1"></i>{{ $subscription->ends_at ? $subscription->ends_at->format('d.m.Y') : '∞' }}
                                            </span>
                                        </div>
                                        
                                        @if($limitsCount > 0)
                                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                                <span class="small text-muted">Отчетов в подписке:</span>
                                                <span class="badge bg-info">{{ $limitsCount }} шт.</span>
                                            </div>
                                        @else
                                            <div class="text-center py-2">
                                                <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-2" 
                                                     style="width: 32px; height: 32px;">
                                                    <i class="bi bi-file-text text-secondary"></i>
                                                </div>
                                                <small class="text-muted">Нет отчетов</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Отчеты по подпискам -->
            @if(isset($groupedLimits) && count($groupedLimits) > 0)
                @foreach($groupedLimits as $group)
                    @php
                        $subscriptionName = $group['subscription']->name ?? 'Подписка #' . $group['subscription']->id;
                        $statusClass = $group['subscription']->status === 'active' ? 'success' : 
                                      ($group['subscription']->status === 'expired' ? 'danger' : 
                                      ($group['subscription']->status === 'pending' ? 'warning' : 'secondary'));
                    @endphp
                    
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pt-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <h5 class="mb-1">
                                        <i class="bi bi-stars text-info me-2"></i>
                                        {{ $subscriptionName }}
                                        <span class="badge bg-{{ $statusClass }} ms-2">
                                            {{ $group['subscription']->getStatusTextAttribute() }}
                                        </span>
                                    </h5>
                                    <div class="text-muted small">
                                        @if($group['subscription']->starts_at)
                                            <span class="me-3"><i class="bi bi-calendar-plus me-1"></i>С {{ $group['subscription']->starts_at->format('d.m.Y') }}</span>
                                        @endif
                                        @if($group['subscription']->ends_at)
                                            <span><i class="bi bi-calendar-x me-1"></i>до {{ $group['subscription']->ends_at->format('d.m.Y') }}</span>
                                            @if($group['subscription']->getRemainingDays() && $group['subscription']->isActive())
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
                        </div>
                        
                        <div class="card-body pt-3">
                            <!-- Статистика по подписке -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-3 col-sm-6">
                                    <div class="bg-light rounded p-3 text-center">
                                        <small class="text-muted d-block">Всего отчетов</small>
                                        <span class="fw-bold fs-5">{{ $group['total_quantity'] }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="bg-light rounded p-3 text-center">
                                        <small class="text-muted d-block">Использовано</small>
                                        <span class="fw-bold fs-5 text-warning">{{ $group['total_used'] }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="bg-light rounded p-3 text-center">
                                        <small class="text-muted d-block">Доступно</small>
                                        <span class="fw-bold fs-5 text-{{ $group['total_available'] > 0 ? 'success' : 'danger' }}">
                                            {{ $group['total_available'] }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="bg-light rounded p-3 text-center">
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
                                            <th class="text-center">Всего</th>
                                            <th class="text-center">Использовано</th>
                                            <th class="text-center">Доступно</th>
                                            <th>Статус</th>
                                            <th>Прогресс</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($group['limits'] as $limit)
                                        @php
                                            $percentage = $limit['quantity'] > 0 ? round(($limit['used_quantity'] / $limit['quantity']) * 100) : 0;
                                            $progressClass = $limit['is_exhausted'] ? 'bg-danger' : 
                                                            ($percentage > 80 ? 'bg-danger' : 
                                                            ($percentage > 50 ? 'bg-warning' : 'bg-success'));
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 32px; height: 32px; font-size: 0.9rem; color: #0d6efd;">
                                                        {{ strtoupper(substr($limit['report_type_name'], 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <span class="fw-semibold">{{ $limit['report_type_name'] }}</span>
                                                        @if($limit['only_api'])
                                                            <span class="badge bg-warning ms-2">API</span>
                                                        @else
                                                            <span class="badge bg-primary ms-2">UI</span>
                                                        @endif
                                                    </div>
                                                </div>
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
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-grow-1" style="height: 6px;">
                                                            <div class="progress-bar {{ $progressClass }}" 
                                                                 style="width: {{ $percentage }}%"></div>
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
                    <div class="card-header bg-white border-0 pt-4">
                        <h5 class="mb-0">
                            <i class="bi bi-stars text-info me-2"></i>
                            Отчеты по подпискам
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-5">
                            <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                                 style="width: 80px; height: 80px;">
                                <i class="bi bi-file-text fs-1 text-secondary"></i>
                            </div>
                            <h5 class="text-muted mb-3">В подписках пока нет отчетов</h5>
                            <p class="text-muted mb-4">Добавьте отчеты в подписки</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Организации менеджера -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-buildings text-success me-2"></i>
                        Организации менеджера
                    </h5>
                    <span class="badge bg-success">{{ $orgCount }}</span>
                </div>
                <div class="card-body pt-3">
                    @if($orgCount > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Организация</th>
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
                                                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 36px; height: 36px; font-size: 1rem; color: #198754;">
                                                    {{ strtoupper(substr($organization->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="fw-semibold">{{ $organization->name }}</span>
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
                                                    <div>
                                                        <span class="small fw-semibold">{{ $organization->owner->user->name }}</span>
                                                        <small class="text-muted d-block">{{ $organization->owner->user->email }}</small>
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
                                            <span class="badge bg-{{ $config['class'] }} px-3 py-2">
                                                {{ $config['text'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.organization.show', $organization->id) }}" 
                                               class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center"
                                               style="width: 32px; height: 32px;"
                                               title="Перейти в организацию">
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
                            <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                                 style="width: 80px; height: 80px;">
                                <i class="bi bi-buildings fs-1 text-secondary"></i>
                            </div>
                            <h5 class="text-muted mb-0">Организаций пока нет</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Правая колонка - Статистика -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart text-info me-2"></i>
                        Статистика
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="bg-light rounded p-3 text-center">
                                <div class="display-4 text-primary mb-2">{{ $orgCount }}</div>
                                <div class="text-muted">Всего организаций</div>
                            </div>
                        </div>
                        
                        <div class="col-6">
                            <div class="bg-light rounded p-3 text-center">
                                <div class="display-6 text-success mb-1">{{ $activeOrgs }}</div>
                                <small class="text-muted">Активных</small>
                            </div>
                        </div>
                        
                        <div class="col-6">
                            <div class="bg-light rounded p-3 text-center">
                                <div class="display-6 text-warning mb-1">{{ $pendingOrgs }}</div>
                                <small class="text-muted">Ожидают</small>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="bg-light rounded p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Неактивных:</span>
                                    <span class="badge bg-secondary">{{ $inactiveOrgs }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Истекших:</span>
                                    <span class="badge bg-danger">{{ $expiredOrgs }}</span>
                                </div>
                            </div>
                        </div>

                        @if($orgCount > 0)
                            @php
                                $activePercentage = round(($activeOrgs / $orgCount) * 100);
                            @endphp
                            <div class="col-12">
                                <div class="bg-light rounded p-3">
                                    <div class="d-flex justify-content-between small mb-2">
                                        <span class="text-muted">Активность организаций</span>
                                        <span class="fw-semibold">{{ $activePercentage }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: {{ $activePercentage }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
/* Гарантируем идеальные круги - ТОЧНО КАК В ПРЕДЫДУЩИХ ШАБЛОНАХ */
.rounded-circle {
    border-radius: 50% !important;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.card {
    border-radius: 1rem;
    transition: all 0.2s;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.badge {
    font-weight: 500;
    letter-spacing: 0.3px;
    border-radius: 30px;
}

/* Градиент как в профиле сотрудника */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.table th {
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.table td {
    vertical-align: middle;
}

.progress {
    border-radius: 10px;
    background-color: #e9ecef;
}

.progress-bar {
    border-radius: 10px;
}

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

/* Для списка информации */
.bg-light.rounded.p-3 {
    border-radius: 0.75rem !important;
}
</style>