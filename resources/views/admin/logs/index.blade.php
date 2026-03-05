@extends('layouts.app')

@section('title', 'Логи действий пользователей и организаций')

@section('content')
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
                <form method="GET" action="{{ route('admin.logs.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Пользователь</label>
                        <select name="user_id" class="form-select">
                            <option value="">Все пользователи</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
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
                                    <a href="#" onclick="event.preventDefault(); document.getElementById('filter-entity-{{ $log->id }}').submit();" class="text-decoration-none">
                                        #{{ $log->entity_id }}
                                    </a>
                                    <form id="filter-entity-{{ $log->id }}" method="GET" action="{{ route('admin.logs.index') }}" class="d-none">
                                        <input type="hidden" name="entity_type" value="{{ $log->entity_type }}">
                                        <input type="hidden" name="entity_id" value="{{ $log->entity_id }}">
                                    </form>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
@endpush