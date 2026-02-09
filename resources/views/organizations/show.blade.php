@extends('layouts.app')

@section('title', $organization->name)

@section('content')
<div class="container-fluid">
    @php
        $isAdmin = Auth::user()->isAdmin();
        $isManager = Auth::user()->isManager();
        $routePrefix = $isAdmin ? 'admin.' : 'manager.';
    @endphp

    <!-- Заголовок -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" 
                 style="width: 50px; height: 50px; color: white;">
                <i class="bi bi-building fs-4"></i>
            </div>
            <div>
                <h1 class="h3 mb-0">{{ $organization->name }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        @if($isAdmin)
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Панель админа</a></li>
                        @elseif($isManager)
                            <li class="breadcrumb-item"><a href="{{ route('manager.dashboard') }}">Панель менеджера</a></li>
                        @endif
                        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($organization->name, 20) }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if($isAdmin || $isManager)
                <a href="{{ route($routePrefix . 'organization.edit', $organization->id) }}" 
                   class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Редактировать
                </a>
            @endif
            <a href="{{ $isAdmin ? route('admin.dashboard') : route('manager.dashboard') }}" 
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Назад
            </a>
        </div>
    </div>

    <!-- Флеш-сообщения -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Статистика организации -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="display-4 text-primary mb-2">{{ $organization->members->count() }}</div>
                    <div class="text-muted">Сотрудников</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="display-4 text-success mb-2">{{ $organization->members->where('is_active', true)->count() }}</div>
                    <div class="text-muted">Активных</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    @if($organization->subscription_ends_at)
                        <div class="{{ $organization->subscription_ends_at->isPast() ? 'text-danger' : 'text-success' }} display-4 mb-2">
                            {{ $organization->subscription_ends_at->format('d.m.Y') }}
                        </div>
                        <div class="text-muted">Подписка до</div>
                    @else
                        <div class="display-4 text-success mb-2">∞</div>
                        <div class="text-muted">Подписка</div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    @if($organization->status === 'active')
                        <span class="badge bg-success fs-5 px-3 py-2">Активна</span>
                    @elseif($organization->status === 'suspended')
                        <span class="badge bg-warning fs-5 px-3 py-2">Приостановлена</span>
                    @elseif($organization->status === 'expired')
                        <span class="badge bg-danger fs-5 px-3 py-2">Истекла</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Основная информация -->
        <div class="col-lg-8">
            <!-- Карточка информации об организации -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle text-primary me-2"></i>
                        Информация об организации
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="text-primary mb-3">{{ $organization->name }}</h5>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-hash text-muted me-2" style="width: 20px;"></i>
                                    <span class="text-muted">ID:</span>
                                    <span class="ms-2 fw-bold">#{{ $organization->id }}</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-calendar text-muted me-2" style="width: 20px;"></i>
                                    <span class="text-muted">Создана:</span>
                                    <span class="ms-2">{{ $organization->created_at->format('d.m.Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-calendar-check text-muted me-2" style="width: 20px;"></i>
                                    <span class="text-muted">Подписка до:</span>
                                    <div class="ms-2">
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
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-badge text-muted me-2" style="width: 20px;"></i>
                                    <span class="text-muted">Менеджер:</span>
                                    <div class="ms-2">
                                        @if($organization->manager && $organization->manager->user)
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 28px; height: 28px; color: white; font-size: 0.7rem;">
                                                    {{ strtoupper(substr($organization->manager->user->name, 0, 1)) }}
                                                </div>
                                                <div>{{ $organization->manager->user->name }}</div>
                                            </div>
                                        @else
                                            <span class="text-muted">Не назначен</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Владелец организации -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge text-success me-2"></i>
                        Владелец организации
                    </h5>
                    @if($isAdmin && $organization->owner && $organization->owner->user)
                        <a href="{{ route($routePrefix . 'organization.edit', $organization->id) }}" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i> Редактировать
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($organization->owner && $organization->owner->user)
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 80px; height: 80px; color: white; font-size: 2rem;">
                                    {{ strtoupper(substr($organization->owner->user->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="col-md-9">
                                <h4 class="mb-2">{{ $organization->owner->user->name }}</h4>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-envelope text-muted me-2"></i>
                                    <span>{{ $organization->owner->user->email }}</span>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-calendar text-muted me-2"></i>
                                    <span>Зарегистрирован: {{ $organization->owner->user->created_at->format('d.m.Y') }}</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-success">Владелец</span>
                                    @if($organization->owner->user->is_active)
                                        <span class="badge bg-info">Активен</span>
                                    @else
                                        <span class="badge bg-danger">Неактивен</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="bi bi-person-x display-1 text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-3">Владелец не назначен</h5>
                            @if($isAdmin)
                                <a href="{{ route('admin.organization.edit', $organization->id) }}" 
                                   class="btn btn-primary">
                                    <i class="bi bi-person-plus me-1"></i> Назначить владельца
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Лимиты владельца -->
            @if(isset($ownerLimits) && count($ownerLimits) > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-speedometer text-info me-2"></i>
                            Лимиты владельца
                            <small class="text-muted ms-2">({{ now()->format('d.m.Y') }})</small>
                        </h5>
                        <span class="badge bg-secondary">
                            {{ count($ownerLimits) }} тип(ов)
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($ownerLimits as $limit)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-{{ $limit['is_exhausted'] ? 'danger' : ($limit['only_api'] ? 'warning' : 'primary') }}">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0">{{ $limit['report_type_name'] }}</h6>
                                        @if($limit['only_api'])
                                            <span class="badge bg-warning">API</span>
                                        @else
                                            <span class="badge bg-primary">UI</span>
                                        @endif
                                    </div>
                                    
                                    @if($limit['description'])
                                        <p class="small text-muted mb-2">{{ $limit['description'] }}</p>
                                    @endif
                                    
                                    <div class="text-center my-3">
                                        <div class="display-5 {{ $limit['is_exhausted'] ? 'text-danger' : ($limit['quantity'] > 0 ? 'text-success' : 'text-secondary') }}">
                                            {{ $limit['quantity'] }}
                                        </div>
                                        <small class="text-muted">осталось запросов</small>
                                    </div>
                                    
                                    <div class="progress mb-2" style="height: 8px;">
                                        @php
                                            $percentage = $limit['quantity'] > 0 ? min(100, ($limit['quantity'] / 100) * 100) : 0;
                                            $progressClass = $limit['is_exhausted'] ? 'bg-danger' : 
                                                            ($limit['quantity'] > 50 ? 'bg-success' : 
                                                            ($limit['quantity'] > 10 ? 'bg-warning' : 'bg-danger'));
                                        @endphp
                                        <div class="progress-bar {{ $progressClass }}" 
                                             style="width: {{ $percentage }}%">
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center small">
                                        <div>
                                            @if($limit['has_limit'])
                                                <span class="text-success">
                                                    <i class="bi bi-check-circle"></i> Установлен
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="bi bi-dash-circle"></i> Не настроен
                                                </span>
                                            @endif
                                        </div>
                                        
                                        @if($limit['is_exhausted'])
                                            <span class="badge bg-danger">Исчерпан</span>
                                        @elseif($limit['quantity'] == 0)
                                            <span class="badge bg-secondary">Нет лимита</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Сводка -->
                    @php
                        $exhaustedCount = count(array_filter($ownerLimits, fn($l) => $l['is_exhausted']));
                    @endphp
                    
                    @if($exhaustedCount > 0)
                        <div class="alert alert-danger mt-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ $exhaustedCount }} лимит(ов) исчерпано
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Боковая панель -->
        <div class="col-lg-4">
            <!-- Действия -->
            @if($isAdmin)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-gear text-warning me-2"></i>
                        Действия
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($organization->owner && $organization->owner->user)
                            <a href="{{ route($routePrefix . 'organization.edit', $organization->id) }}" 
                               class="btn btn-outline-primary">
                                <i class="bi bi-pencil me-1"></i> Редактировать организацию
                            </a>
                        @endif

                        <form action="{{ route('admin.organization.toggle-status', $organization->id) }}" 
                              method="POST" class="d-grid">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-outline-{{ $organization->status == 'active' ? 'warning' : 'success' }}">
                                <i class="bi bi-toggle-{{ $organization->status == 'active' ? 'off' : 'on' }} me-1"></i>
                                {{ $organization->status == 'active' ? 'Деактивировать' : 'Активировать' }}
                            </button>
                        </form>

                        <button type="button" class="btn btn-outline-danger" 
                                onclick="confirmDelete({{ $organization->id }}, '{{ $organization->name }}')">
                            <i class="bi bi-trash me-1"></i> Удалить организацию
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Ответственный менеджер -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge text-primary me-2"></i>
                        Ответственный менеджер
                    </h5>
                </div>
                <div class="card-body">
                    @if($organization->manager && $organization->manager->user)
                        <div class="text-center">
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 60px; height: 60px; color: white; font-size: 1.5rem;">
                                {{ strtoupper(substr($organization->manager->user->name, 0, 1)) }}
                            </div>
                            <h6 class="mb-1">{{ $organization->manager->user->name }}</h6>
                            <p class="text-muted small mb-3">{{ $organization->manager->user->email }}</p>
                            
                            @if($organization->manager->user->id === Auth::id())
                                <span class="badge bg-success">Это вы</span>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-person-slash text-muted fs-1 mb-2"></i>
                            <p class="text-muted mb-0">Менеджер не назначен</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Быстрые действия -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning text-success me-2"></i>
                        Быстрые действия
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route($routePrefix . 'org-members.create', $organization->id) }}" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-person-plus me-1"></i> Добавить сотрудника
                        </a>
                        
                        @if($organization->members->count() > 0)
                            <a href="{{ route('reports.create') }}" class="btn btn-outline-success">
                                <i class="bi bi-file-earmark-plus me-1"></i> Создать отчет
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Сотрудники организации -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    <i class="bi bi-people text-primary me-2"></i>
                    Сотрудники организации
                    <span class="badge bg-primary ms-2">{{ $organization->members->count() }}</span>
                </h5>
                <small class="text-muted">Все сотрудники вашей организации</small>
            </div>
            @if($isAdmin || $isManager)
                <a href="{{ route($routePrefix . 'org-members.create', $organization->id) }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i> Добавить сотрудника
                </a>
            @endif
        </div>
        <div class="card-body">
            @if($organization->members->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Сотрудник</th>
                                <th>Контакты</th>
                                <th>Статус</th>
                                <th>Добавлен</th>
                                <th class="text-center">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($organization->members as $member)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 40px; height: 40px; color: white;">
                                                {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $member->user->name }}</div>
                                                <small class="text-muted">ID: {{ $member->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $member->user->email }}</div>
                                    </td>
                                    <td>
                                        @if($member->is_active)
                                            <span class="badge bg-success">Активен</span>
                                        @else
                                            <span class="badge bg-danger">Неактивен</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-muted">{{ $member->created_at->format('d.m.Y') }}</div>
                                        <div class="small text-muted">{{ $member->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route($routePrefix . 'org-members.show', [$organization->id, $member->id]) }}" 
                                               class="btn btn-sm btn-outline-info rounded-circle d-flex align-items-center justify-content-center"
                                               style="width: 32px; height: 32px;"
                                               title="Просмотр профиля"
                                               data-bs-toggle="tooltip">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($isAdmin || $isManager)
                                                <a href="{{ route($routePrefix . 'org-members.edit', [$organization->id, $member->id]) }}" 
                                                   class="btn btn-sm btn-outline-warning rounded-circle d-flex align-items-center justify-content-center"
                                                   style="width: 32px; height: 32px;"
                                                   title="Редактировать"
                                                   data-bs-toggle="tooltip">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif
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
                        <i class="bi bi-people display-1 text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">Сотрудников пока нет</h4>
                    <p class="text-muted mb-4">Добавьте первого сотрудника в вашу организацию</p>
                    @if($isAdmin || $isManager)
                        <a href="{{ route($routePrefix . 'org-members.create', $organization->id) }}" class="btn btn-primary">
                            <i class="bi bi-person-plus me-1"></i> Добавить сотрудника
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@if($isAdmin)
<!-- Форма для удаления -->
<form id="delete-form" method="POST" action="{{ route('admin.organization.delete', $organization->id) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(id, name) {
    if (confirm(`Вы уверены, что хотите удалить организацию "${name}"? Это действие удалит также владельца организации и всех сотрудников.`)) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endif

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
@endsection