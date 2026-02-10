@extends('layouts.app')

@section('title', 'Панель администратора')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">
        <i class="bi bi-speedometer2 text-primary"></i> Панель администратора
    </h2>
    <div class="d-flex gap-2">
        <!-- ДОБАВЛЕНА КНОПКА УПРАВЛЕНИЯ ЛИМИТАМИ -->
        <a href="{{ route('limits.index') }}" class="btn btn-info">
            <i class="bi bi-graph-up"></i> Управление лимитами
        </a>
        <a href="{{ route('admin.organization.create') }}" class="btn btn-success">
            <i class="bi bi-building-add"></i> Создать организацию
        </a>
        <a href="{{ route('admin.managers.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Создать менеджера
        </a>
    </div>
</div>

<!-- Статистика -->
<div class="row mb-4">
    <!-- Информация о текущем админе -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-person-circle text-danger"></i> Ваш профиль
                </h5>
            </div>
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-danger d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px; font-size: 2rem; color: white;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                
                <div class="mb-3">
                    <span class="badge bg-danger">Администратор</span>
                    <span class="badge bg-success">Активен</span>
                </div>
                
                <div class="small text-muted">
                    <i class="bi bi-calendar"></i> В системе с: {{ $user->created_at->format('d.m.Y') }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Статистика организаций -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-buildings text-success"></i> Организации
                </h5>
            </div>
            <div class="card-body py-4">
                <div class="d-flex justify-content-around text-center">
                    <div>
                        <h2 class="text-primary mb-0">{{ $stats['total_organizations'] ?? 0 }}</h2>
                        <small class="text-muted">Всего</small>
                    </div>
                    <div>
                        <h2 class="text-success mb-0">{{ $stats['active_organizations'] ?? 0 }}</h2>
                        <small class="text-muted">Активные</small>
                    </div>
                    <div>
                        <h2 class="text-warning mb-0">{{ $stats['pending_organizations'] ?? 0 }}</h2>
                        <small class="text-muted">Ожидают</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Статистика менеджеров -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-person-badge text-primary"></i> Менеджеры
                </h5>
            </div>
            <div class="card-body py-4">
                <div class="d-flex justify-content-around text-center">
                    <div>
                        <h2 class="text-primary mb-0">{{ $stats['managers_count'] ?? 0 }}</h2>
                        <small class="text-muted">Всего</small>
                    </div>
                    <div>
                        <h2 class="text-success mb-0">{{ $stats['managers_active'] ?? 0 }}</h2>
                        <small class="text-muted">Активные</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Лимиты администратора -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-speedometer text-info"></i> Ваши лимиты
            <span class="badge bg-secondary ms-2">{{ now()->format('d.m.Y') }}</span>
        </h5>
        @if(count($limits) > 0)
            <div class="small text-muted">
                <i class="bi bi-info-circle"></i> Обновлено: {{ now()->format('H:i') }}
            </div>
        @endif
    </div>
    <div class="card-body">
        @if(count($limits) > 0)
            <div class="row">
                @foreach($limits as $limit)
                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                    <div class="card h-100 border-{{ $limit['is_exhausted'] ? 'danger' : ($limit['only_api'] ? 'warning' : 'primary') }} shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0 text-truncate" title="{{ $limit['report_type_name'] }}">
                                    <i class="bi bi-{{ $limit['only_api'] ? 'plug' : 'window' }} me-1"></i>
                                    {{ $limit['report_type_name'] }}
                                </h6>
                                @if($limit['only_api'])
                                    <span class="badge bg-warning" title="Только через API">API</span>
                                @else
                                    <span class="badge bg-primary" title="Доступен в интерфейсе">UI</span>
                                @endif
                            </div>
                            
                            @if($limit['description'])
                                <p class="small text-muted mb-2">{{ Str::limit($limit['description'], 60) }}</p>
                            @endif
                            
                            <div class="text-center my-3">
                                <div class="display-6 {{ $limit['is_exhausted'] ? 'text-danger' : ($limit['quantity'] > 0 ? 'text-success' : 'text-secondary') }}">
                                    {{ $limit['quantity'] }}
                                </div>
                                <small class="text-muted">осталось запросов</small>
                            </div>
                            
                            <div class="progress mb-2" style="height: 6px;">
                                @php
                                    $percentage = $limit['quantity'] > 0 ? min(100, ($limit['quantity'] / 100) * 100) : 0;
                                    $progressClass = $limit['is_exhausted'] ? 'bg-danger' : 
                                                    ($limit['quantity'] > 50 ? 'bg-success' : 
                                                    ($limit['quantity'] > 10 ? 'bg-warning' : 'bg-danger'));
                                @endphp
                                <div class="progress-bar {{ $progressClass }}" 
                                     style="width: {{ $percentage }}%"
                                     role="progressbar"
                                     aria-valuenow="{{ $limit['quantity'] }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small">
                                    @if($limit['has_limit'])
                                        <span class="text-success" title="Индивидуальный лимит установлен">
                                            <i class="bi bi-check-circle-fill"></i> Установлен
                                        </span>
                                    @else
                                        <span class="text-muted" title="Лимит не настроен">
                                            <i class="bi bi-dash-circle"></i> Не настроен
                                        </span>
                                    @endif
                                </div>
                                
                                @if($limit['is_exhausted'])
                                    <span class="badge bg-danger">
                                        <i class="bi bi-exclamation-triangle"></i> Исчерпан
                                    </span>
                                @elseif($limit['quantity'] == 0)
                                    <span class="badge bg-secondary">Нет лимита</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Сводка по лимитам -->
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        @php
                            $interfaceCount = count(array_filter($limits, fn($l) => !$l['only_api']));
                            $apiCount = count(array_filter($limits, fn($l) => $l['only_api']));
                            $exhaustedCount = count(array_filter($limits, fn($l) => $l['is_exhausted']));
                            $hasLimitCount = count(array_filter($limits, fn($l) => $l['has_limit']));
                        @endphp
                        <i class="bi bi-info-circle"></i>
                        Всего: {{ count($limits) }} | 
                        UI: {{ $interfaceCount }} | 
                        API: {{ $apiCount }} | 
                        Исчерпано: <span class="{{ $exhaustedCount > 0 ? 'text-danger fw-bold' : 'text-success' }}">{{ $exhaustedCount }}</span>
                    </div>
                    
                    @if($exhaustedCount > 0)
                        <div class="alert alert-danger py-1 px-3 mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <small>{{ $exhaustedCount }} лимит(ов) исчерпано</small>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-speedometer display-1 text-muted"></i>
                </div>
                <h4 class="text-muted mb-3">Лимиты не настроены</h4>
                <p class="text-muted mb-4">Нет доступных типов отчетов или лимитов для отображения</p>
                <a href="{{ route('limits.index') }}" class="btn btn-info">
                    <i class="bi bi-sliders"></i> Настроить лимиты
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Раздел организаций -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">
                <i class="bi bi-buildings text-success me-2"></i> Организации
                <span class="badge bg-success ms-2">{{ $organizations->count() }}</span>
            </h5>
            <small class="text-muted">Последние созданные организации</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.organizations.list') }}" class="btn btn-sm btn-outline-success">
                Все организации <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($organizations->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>Организация</th>
                            <th>Владелец</th>
                            <th>Менеджер</th>
                            <th width="120">Статус</th>
                            <th width="140">Подписка</th>
                            <th width="100" class="text-center">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($organizations as $organization)
                        <tr>
                            <td class="text-muted">#{{ $organization->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px;">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ Str::limit($organization->name, 30) }}</div>
                                        <small class="text-muted">{{ $organization->created_at->format('d.m.Y') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($organization->owner && $organization->owner->user)
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center me-2" 
                                            style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            {{ strtoupper(substr($organization->owner->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="small fw-bold">{{ $organization->owner->user->name }}</div>
                                            <small class="text-muted">Владелец</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-danger">Не назначен</span>
                                @endif
                            </td>
                            <td>
                                @if($organization->manager && $organization->manager->user)
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2" 
                                            style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            {{ strtoupper(substr($organization->manager->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="small">{{ Str::limit($organization->manager->user->name, 15) }}</div>
                                            @if($organization->manager->user->id === $user->id)
                                                <small class="badge bg-danger">Вы</small>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusConfig = [
                                        'active' => ['class' => 'success', 'icon' => 'check-circle'],
                                        'inactive' => ['class' => 'danger', 'icon' => 'x-circle'],
                                        'pending' => ['class' => 'warning', 'icon' => 'clock'],
                                        'suspended' => ['class' => 'secondary', 'icon' => 'pause-circle'],
                                    ];
                                    $config = $statusConfig[$organization->status] ?? ['class' => 'secondary', 'icon' => 'question-circle'];
                                @endphp
                                <span class="badge bg-{{ $config['class'] }}">
                                    <i class="bi bi-{{ $config['icon'] }} me-1"></i>
                                    {{ $organization->status === 'active' ? 'Активна' : 
                                       ($organization->status === 'inactive' ? 'Неактивна' : 
                                       ($organization->status === 'pending' ? 'Ожидает' : 
                                       ($organization->status === 'suspended' ? 'Приостановлена' : $organization->status))) }}
                                </span>
                            </td>
                            <td>
                                @if($organization->subscription_ends_at)
                                    @if($organization->subscription_ends_at->isPast())
                                        <span class="badge bg-danger">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            {{ $organization->subscription_ends_at->format('d.m.Y') }}
                                        </span>
                                    @elseif($organization->subscription_ends_at->diffInDays(now()) < 7)
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $organization->subscription_ends_at->format('d.m.Y') }}
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            {{ $organization->subscription_ends_at->format('d.m.Y') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Бессрочно</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.organization.show', $organization->id) }}" 
                                       class="btn btn-sm btn-outline-info rounded-circle d-flex align-items-center justify-content-center"
                                       style="width: 32px; height: 32px;"
                                       title="Просмотреть"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
                                    <a href="{{ route('admin.organization.edit', $organization->id) }}" 
                                       class="btn btn-sm btn-outline-warning rounded-circle d-flex align-items-center justify-content-center"
                                       style="width: 32px; height: 32px;"
                                       title="Редактировать"
                                       data-bs-toggle="tooltip">
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
                <div class="mb-4">
                    <i class="bi bi-buildings display-1 text-muted"></i>
                </div>
                <h4 class="text-muted mb-3">Организаций пока нет</h4>
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
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">
                <i class="bi bi-person-badge text-primary me-2"></i> Менеджеры
                <span class="badge bg-primary ms-2">{{ $managers->count() }}</span>
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
                            <th width="60">ID</th>
                            <th>Менеджер</th>
                            <th>Контакты</th>
                            <th>Создан</th>
                            <th>Кем создан</th>
                            <th width="100">Статус</th>
                            <th width="150" class="text-center">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($managers as $manager)
                        <tr>
                            <td class="text-muted">#{{ $manager->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr($manager->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $manager->name }}</div>
                                        @if($manager->managerProfile)
                                            <small class="text-muted">Профиль #{{ $manager->managerProfile->id }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <div><i class="bi bi-envelope text-muted me-1"></i> {{ $manager->email }}</div>
                                    @if($manager->phone)
                                        <div><i class="bi bi-telephone text-muted me-1"></i> {{ $manager->phone }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <div>{{ $manager->created_at->format('d.m.Y') }}</div>
                                    <div class="text-muted">{{ $manager->created_at->format('H:i') }}</div>
                                </div>
                            </td>
                            <td>
                                @if($manager->managerProfile && $manager->managerProfile->admin)
                                    <span class="badge bg-dark">
                                        <i class="bi bi-person-gear me-1"></i>
                                        {{ $manager->managerProfile->admin->name }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($manager->is_active)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i> Активен
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle me-1"></i> Неактивен
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <!-- Просмотр -->
                                    <a href="{{ route('admin.managers.show', $manager->id) }}" 
                                    class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 32px; height: 32px;"
                                    title="Просмотреть профиль"
                                    data-bs-toggle="tooltip">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
                                    <!-- Редактирование -->
                                    <a href="{{ route('admin.managers.edit', $manager->id) }}" 
                                    class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 32px; height: 32px;"
                                    title="Редактировать"
                                    data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <!-- Статус -->
                                    <form method="POST" action="{{ route('admin.managers.toggle-status', $manager->id) }}" 
                                          class="m-0" 
                                          onsubmit="return confirm('{{ $manager->is_active ? 'Деактивировать' : 'Активировать' }} менеджера {{ $manager->name }}?')">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-{{ $manager->is_active ? 'warning' : 'secondary' }} rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px;"
                                                title="{{ $manager->is_active ? 'Деактивировать' : 'Активировать' }}"
                                                data-bs-toggle="tooltip">
                                            <i class="bi bi-toggle-{{ $manager->is_active ? 'on' : 'off' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-person-badge display-1 text-muted"></i>
                </div>
                <h4 class="text-muted mb-3">Менеджеров пока нет</h4>
                <p class="text-muted mb-4">Создайте первого менеджера</p>
                <a href="{{ route('admin.managers.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i> Создать менеджера
                </a>
            </div>
        @endif
    </div>
</div>

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