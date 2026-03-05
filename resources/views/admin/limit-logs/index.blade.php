@extends('layouts.app')

@section('title', 'Логи лимитов и подписок')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-pie-chart me-2"></i>
            Логи лимитов и подписок
        </h1>
        
        <div>
            <a href="{{ route('admin.limit-logs.export', request()->query()) }}" class="btn btn-success">
                <i class="bi bi-download"></i> Экспорт в CSV
            </a>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-4" id="statistics-container">
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <h6 class="card-title">Всего записей</h6>
                    <h2 class="mb-0" id="stat-total">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <h6 class="card-title">За сегодня</h6>
                    <h2 class="mb-0" id="stat-today">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <h6 class="card-title">Изменения</h6>
                    <h2 class="mb-0" id="stat-quantity">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <h6 class="card-title">Лимиты</h6>
                    <h2 class="mb-0" id="stat-limits">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body">
                    <h6 class="card-title">Делегированные</h6>
                    <h2 class="mb-0" id="stat-delegated">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <h6 class="card-title">Подписки</h6>
                    <h2 class="mb-0" id="stat-subscriptions">0</h2>
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
                <form method="GET" action="{{ route('admin.limit-logs.index') }}" class="row g-3">
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
                        <label class="form-label">ID</label>
                        <input type="number" name="entity_id" class="form-control" value="{{ request('entity_id') }}" placeholder="ID">
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
                            <i class="bi bi-search"></i> Применить
                        </button>
                        <a href="{{ route('admin.limit-logs.index') }}" class="btn btn-secondary">
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
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Дата</th>
                            <th>Пользователь</th>
                            <th>Тип</th>
                            <th>ID</th>
                            <th>Действие</th>
                            <th>Изменение</th>
                            <th>IP</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td><code>#{{ $log->id }}</code></td>
                                <td>{{ $log->created_at->format('d.m.Y H:i:s') }}</td>
                                <td>
                                    @if($log->user)
                                        {{ $log->user->name }}
                                        <br>
                                        <small class="text-muted">{{ $log->user->email }}</small>
                                    @else
                                        <span class="text-muted">Система</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $log->entity_type_name }}</span>
                                </td>
                                <td>#{{ $log->entity_id }}</td>
                                <td>
                                    @php
                                        $badgeClass = 'secondary';
                                        if ($log->action === 'create') $badgeClass = 'success';
                                        elseif ($log->action === 'update') $badgeClass = 'primary';
                                        elseif ($log->action === 'delete') $badgeClass = 'danger';
                                        elseif ($log->action === 'activate') $badgeClass = 'success';
                                        elseif ($log->action === 'suspend') $badgeClass = 'warning';
                                        elseif ($log->action === 'cancel') $badgeClass = 'danger';
                                        elseif ($log->action === 'use_quantity') $badgeClass = 'warning';
                                        elseif ($log->action === 'return_quantity') $badgeClass = 'success';
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}">{{ $log->action_name }}</span>
                                </td>
                                <td>
                                    @if($log->quantity_change !== null)
                                        <span class="badge bg-{{ $log->quantity_change > 0 ? 'success' : 'danger' }}">
                                            {{ $log->quantity_change > 0 ? '+' : '' }}{{ $log->quantity_change }}
                                        </span>
                                        <br>
                                        <small>
                                            {{ $log->old_quantity }} → {{ $log->new_quantity }}
                                        </small>
                                    @elseif($log->old_ends_at || $log->new_ends_at)
                                        <small>
                                            {{ $log->old_ends_at ? date('d.m.Y', strtotime($log->old_ends_at)) : '—' }} 
                                            → 
                                            {{ $log->new_ends_at ? date('d.m.Y', strtotime($log->new_ends_at)) : '—' }}
                                        </small>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($log->ip_address)
                                        <code>{{ $log->ip_address }}</code>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.limit-logs.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <p class="text-muted mb-0">Логи не найдены</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Показано с {{ $logs->firstItem() ?? 0 }} по {{ $logs->lastItem() ?? 0 }} из {{ $logs->total() }}
                </div>
                <div>
                    {{ $logs->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Форма очистки -->
    <div class="card border-warning mb-4">
        <div class="card-header bg-warning text-white">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Очистка логов
        </div>
        <div class="card-body">
            <form action="{{ route('admin.limit-logs.clean') }}" method="POST" class="row align-items-center">
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
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Вы уверены?')">
                        <i class="bi bi-trash"></i> Очистить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('{{ route("admin.limit-logs.statistics") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('stat-total').textContent = data.total;
            document.getElementById('stat-today').textContent = data.today;
            document.getElementById('stat-quantity').textContent = data.quantity_changes;
            document.getElementById('stat-limits').textContent = data.by_entity?.limit || 0;
            document.getElementById('stat-delegated').textContent = data.by_entity?.delegated_limit || 0;
            document.getElementById('stat-subscriptions').textContent = data.by_entity?.subscription || 0;
        });
});
</script>     
@endpush