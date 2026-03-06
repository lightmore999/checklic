@extends('layouts.app')

@section('title', 'Логи действий пользователей и организаций')

@section('content')
@php
    use App\Models\User;
    use App\Models\Organization;
    use App\Models\Manager;
    use App\Models\OrgOwnerProfile;
    use App\Models\OrgMemberProfile;
@endphp
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4">
            <i class="bi bi-journal-text me-2"></i>
            Логи действий
        </h1>
        
        <div class="btn-group">
            <a href="{{ route('admin.logs.export', request()->query()) }}" class="btn btn-success">
                <i class="bi bi-download"></i> Экспорт в CSV
            </a>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-4" id="statistics-container">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Всего записей</div>
                            <div class="h3" id="stat-total">0</div>
                        </div>
                        <i class="bi bi-database fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">За сегодня</div>
                            <div class="h3" id="stat-today">0</div>
                        </div>
                        <i class="bi bi-calendar-day fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">За неделю</div>
                            <div class="h3" id="stat-week">0</div>
                        </div>
                        <i class="bi bi-calendar-week fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">За месяц</div>
                            <div class="h3" id="stat-month">0</div>
                        </div>
                        <i class="bi bi-calendar-month fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-funnel me-1"></i>
            Фильтры
            <button class="btn btn-sm btn-link float-end" type="button" data-bs-toggle="collapse" data-bs-target="#filters">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>
        <div class="collapse show" id="filters">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.logs.index') }}" id="filterForm" class="row g-3">
                    <!-- Фильтр по организации -->
                    <div class="col-md-3">
                        <label class="form-label">Организация</label>
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
                        <label class="form-label">Пользователь</label>
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

                    <div class="col-md-2">
                        <label class="form-label">Тип сущности</label>
                        <select name="entity_type" class="form-select">
                            <option value="">Все типы</option>
                            @foreach($entityTypes as $value => $label)
                                <option value="{{ $value }}" {{ request('entity_type') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Действие</label>
                        <select name="action" class="form-select">
                            <option value="">Все действия</option>
                            @foreach($actions as $value => $label)
                                <option value="{{ $value }}" {{ request('action') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label">ID сущности</label>
                        <input type="number" name="entity_id" class="form-control" value="{{ request('entity_id') }}" placeholder="ID">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Поиск по тексту</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Поиск...">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Дата с</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Дата по</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Применить фильтры
                        </button>
                        <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary">
                            <i class="bi bi-eraser"></i> Сбросить
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Активные фильтры -->
    @if(request()->anyFilled(['organization_id', 'user_id', 'entity_type', 'action', 'entity_id', 'search', 'date_from', 'date_to']))
        <div class="alert alert-info py-2 mb-3">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <i class="bi bi-funnel me-1"></i>
                <span>Активные фильтры:</span>
                
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
                
                @if(request('entity_type'))
                    <span class="badge bg-info text-white">Тип: {{ $entityTypes[request('entity_type')] ?? request('entity_type') }}</span>
                @endif
                
                @if(request('action'))
                    <span class="badge bg-info text-white">Действие: {{ $actions[request('action')] ?? request('action') }}</span>
                @endif
                
                @if(request('entity_id'))
                    <span class="badge bg-info text-white">ID сущности: {{ request('entity_id') }}</span>
                @endif
                
                @if(request('search'))
                    <span class="badge bg-info text-white">Поиск: {{ request('search') }}</span>
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

    <!-- Таблица логов -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-table me-1"></i>
            Список действий
            <span class="badge bg-secondary ms-2">{{ $logs->total() }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Дата</th>
                            <th>Пользователь</th>
                            <th>Тип</th>
                            <th>ID сущности</th>
                            <th>Действие</th>
                            <th>IP</th>
                            <th class="text-center">Детали</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                // Получаем информацию о сущности в зависимости от типа
                                $entityInfo = null;
                                $entityRoute = null;
                                $entityId = $log->entity_id;
                                
                                if ($log->entity_type === 'user') {
                                    $entityUser = \App\Models\User::find($entityId);
                                    if ($entityUser) {
                                        $entityInfo = $entityUser->name . ' (' . $entityUser->email . ') - ' . $entityUser->getRoleDisplayName();
                                        // Для пользователей нет отдельного маршрута, используем фильтр
                                        $entityRoute = '#';
                                    }
                                } 
                                elseif ($log->entity_type === 'organization') {
                                    $entityOrg = \App\Models\Organization::find($entityId);
                                    if ($entityOrg) {
                                        $entityInfo = $entityOrg->name;
                                        if ($entityOrg->inn) {
                                            $entityInfo .= ' (ИНН: ' . $entityOrg->inn . ')';
                                        }
                                        // Для организации используем маршрут показа организации
                                        $entityRoute = route('admin.organization.show', $entityId);
                                    }
                                } 
                                elseif ($log->entity_type === 'manager') {
                                    $entityManager = \App\Models\Manager::with('user')->find($entityId);
                                    if ($entityManager && $entityManager->user) {
                                        $entityInfo = $entityManager->user->name . ' (Менеджер)';
                                        // Для менеджера используем маршрут показа менеджера
                                        $entityRoute = route('admin.managers.show', $entityId);
                                    }
                                } 
                                elseif ($log->entity_type === 'org_owner') {
                                    $entityOwner = \App\Models\OrgOwnerProfile::with('user', 'organization')->find($entityId);
                                    if ($entityOwner && $entityOwner->user) {
                                        $entityInfo = $entityOwner->user->name . ' (Владелец)';
                                        if ($entityOwner->organization) {
                                            $entityInfo .= ' - ' . $entityOwner->organization->name;
                                            // Для владельца ведем на страницу организации
                                            $entityRoute = route('admin.organization.show', $entityOwner->organization->id);
                                        }
                                    }
                                } 
                                elseif ($log->entity_type === 'org_member') {
                                    $entityMember = \App\Models\OrgMemberProfile::with('user', 'organization')->find($entityId);
                                    if ($entityMember && $entityMember->user) {
                                        $entityInfo = $entityMember->user->name . ' (Сотрудник)';
                                        if ($entityMember->organization) {
                                            $entityInfo .= ' - ' . $entityMember->organization->name;
                                            // Для сотрудника ведем на страницу просмотра сотрудника
                                            $entityRoute = route('admin.org-members.show', [
                                                'organizationId' => $entityMember->organization_id, 
                                                'memberId' => $entityId
                                            ]);
                                        }
                                    }
                                }
                            @endphp
                            <tr>
                                <td><code>#{{ $log->id }}</code></td>
                                <td>{{ $log->created_at->format('d.m.Y H:i:s') }}</td>
                                <td>
                                    @if($log->user)
                                        <a href="#" onclick="event.preventDefault(); document.getElementById('filter-user-{{ $log->user_id }}').submit();" class="text-decoration-none">
                                            <strong>{{ $log->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $log->user->email }}</small>
                                        </a>
                                        <form id="filter-user-{{ $log->user_id }}" method="GET" action="{{ route('admin.logs.index') }}" class="d-none">
                                            <input type="hidden" name="user_id" value="{{ $log->user_id }}">
                                        </form>
                                    @else
                                        <span class="text-muted">Система</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $log->entity_type_name }}</span>
                                </td>
                                <td>
                                    @if($entityInfo)
                                        @if($entityRoute && $entityRoute !== '#')
                                            <a href="{{ $entityRoute }}" class="text-decoration-none" target="_blank">
                                                <div class="d-flex flex-column">
                                                    <strong>{{ Str::limit($entityInfo, 50) }}</strong>
                                                    <small class="text-muted">ID: {{ $log->entity_id }}</small>
                                                </div>
                                            </a>
                                        @else
                                            <a href="#" onclick="event.preventDefault(); document.getElementById('filter-entity-{{ $log->id }}').submit();" class="text-decoration-none">
                                                <div class="d-flex flex-column">
                                                    <strong>{{ Str::limit($entityInfo, 50) }}</strong>
                                                    <small class="text-muted">ID: {{ $log->entity_id }}</small>
                                                </div>
                                            </a>
                                        @endif
                                        <form id="filter-entity-{{ $log->id }}" method="GET" action="{{ route('admin.logs.index') }}" class="d-none">
                                            <input type="hidden" name="entity_type" value="{{ $log->entity_type }}">
                                            <input type="hidden" name="entity_id" value="{{ $log->entity_id }}">
                                        </form>
                                    @else
                                        <a href="#" onclick="event.preventDefault(); document.getElementById('filter-entity-{{ $log->id }}').submit();" class="text-decoration-none">
                                            #{{ $log->entity_id }}
                                        </a>
                                        <form id="filter-entity-{{ $log->id }}" method="GET" action="{{ route('admin.logs.index') }}" class="d-none">
                                            <input type="hidden" name="entity_type" value="{{ $log->entity_type }}">
                                            <input type="hidden" name="entity_id" value="{{ $log->entity_id }}">
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($log->action) {
                                            'create' => 'success',
                                            'update' => 'primary',
                                            'delete', 'force_delete' => 'danger',
                                            'restore' => 'warning',
                                            'login', 'logout' => 'info',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}">{{ $log->action_name }}</span>
                                </td>
                                <td>
                                    @if($log->ip_address)
                                        <code>{{ $log->ip_address }}</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.logs.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    <p class="mb-0">Логи не найдены</p>
                                    <small class="text-muted">Попробуйте изменить параметры фильтрации</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <p class="text-muted small">
                        Показано с {{ $logs->firstItem() ?? 0 }} по {{ $logs->lastItem() ?? 0 }} из {{ $logs->total() }} записей
                    </p>
                </div>
                <div>
                    {{ $logs->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Форма очистки старых логов -->
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-white">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Очистка логов
        </div>
        <div class="card-body">
            <form action="{{ route('admin.logs.clean') }}" method="POST" class="row g-3 align-items-center">
                @csrf
                <div class="col-auto">
                    <label class="col-form-label">Удалить записи старше</label>
                </div>
                <div class="col-auto">
                    <input type="number" name="days" class="form-control" value="30" min="1" max="365" required>
                </div>
                <div class="col-auto">
                    <span class="col-form-label">дней</span>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Вы уверены? Это действие нельзя отменить.')">
                        <i class="bi bi-trash"></i> Очистить
                    </button>
                </div>
            </form>
            <small class="text-muted d-block mt-2">
                <i class="bi bi-info-circle"></i> Рекомендуется хранить логи не более 90 дней для экономии места в БД.
            </small>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ru.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация Select2 для организаций
    if (typeof $.fn.select2 !== 'undefined') {
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
    }

    // Загрузка статистики
    fetch('{{ route("admin.logs.statistics") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('stat-total').textContent = data.total;
            document.getElementById('stat-today').textContent = data.today;
            document.getElementById('stat-week').textContent = data.week;
            document.getElementById('stat-month').textContent = data.month;
        })
        .catch(error => console.error('Error loading statistics:', error));
        
    // При изменении организации - обновляем список пользователей
    $('#organization_id').on('change', function() {
        $('.select2-user').val(null).trigger('change');
        $('#filterForm').submit();
    });

    // Отправка формы при изменении пользователя
    $('.select2-user').on('change', function() {
        $('#filterForm').submit();
    });

    // Автоматическая отправка формы при изменении select-фильтров
    $('select[name="entity_type"], select[name="action"]').on('change', function() {
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
    
    // Отправка формы при изменении ID сущности (с задержкой)
    let entityIdTimer;
    $('input[name="entity_id"]').on('input', function() {
        clearTimeout(entityIdTimer);
        entityIdTimer = setTimeout(() => {
            $('#filterForm').submit();
        }, 500);
    });
    
    // Отправка формы при поиске по тексту (с задержкой)
    let searchTimer;
    $('input[name="search"]').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            $('#filterForm').submit();
        }, 500);
    });
});
</script>
@endpush