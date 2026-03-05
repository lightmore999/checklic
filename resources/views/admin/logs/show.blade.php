@extends('layouts.app')

@section('title', 'Детали лога #' . $log->id)

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4">
            <i class="bi bi-journal-text me-2"></i>
            Детали лога #{{ $log->id }}
        </h1>
        <div>
            <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад к списку
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-info-circle me-1"></i>
                    Основная информация
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th style="width: 200px;">ID записи:</th>
                            <td><code>#{{ $log->id }}</code></td>
                        </tr>
                        <tr>
                            <th>Дата и время:</th>
                            <td>{{ $log->created_at->format('d.m.Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Пользователь:</th>
                            <td>
                                @if($log->user)
                                    <strong>{{ $log->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $log->user->email }}</small>
                                @else
                                    <span class="text-muted">Система</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>IP адрес:</th>
                            <td>
                                @if($log->ip_address)
                                    <code>{{ $log->ip_address }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>User Agent:</th>
                            <td>
                                @if($log->user_agent)
                                    <small class="text-muted">{{ $log->user_agent }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Batch ID:</th>
                            <td>
                                @if($log->batch_id)
                                    <code>{{ $log->batch_id }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-briefcase me-1"></i>
                    Информация о сущности
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th style="width: 200px;">Тип сущности:</th>
                            <td>
                                <span class="badge bg-secondary">{{ $log->entity_type_name }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>ID сущности:</th>
                            <td><code>#{{ $log->entity_id }}</code></td>
                        </tr>
                        <tr>
                            <th>Действие:</th>
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
                        </tr>
                        <tr>
                            <th>Текущее состояние:</th>
                            <td>
                                @if($entity)
                                    <span class="text-success">
                                        <i class="bi bi-check-circle"></i> Сущность существует
                                    </span>
                                    <br>
                                    <small class="text-muted">ID: {{ $entity->id }}</small>

                                @else
                                    <span class="text-danger">
                                        <i class="bi bi-exclamation-triangle"></i> Сущность удалена
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-arrow-left-circle me-1"></i>
                    Данные ДО изменения
                </div>
                <div class="card-body">
                    @if($log->old_data)
                        <pre class="bg-light p-3 rounded" style="max-height: 500px; overflow: auto;"><code class="language-json">{{ json_encode($log->old_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-database-slash fs-1 d-block mb-3"></i>
                            <p class="mb-0">Нет данных до изменения</p>
                            <small>(создание или действие не связано с изменением)</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-arrow-right-circle me-1"></i>
                    Данные ПОСЛЕ изменения
                </div>
                <div class="card-body">
                    @if($log->new_data)
                        <pre class="bg-light p-3 rounded" style="max-height: 500px; overflow: auto;"><code class="language-json">{{ json_encode($log->new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-database-slash fs-1 d-block mb-3"></i>
                            <p class="mb-0">Нет данных после изменения</p>
                            <small>(удаление)</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($log->changes)
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <i class="bi bi-arrow-left-right me-1"></i>
            Измененные поля
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Поле</th>
                            <th>Было</th>
                            <th>Стало</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($log->changes as $field => $change)
                            <tr>
                                <td><code>{{ $field }}</code></td>
                                <td class="text-danger">
                                    @if(is_array($change['old']) || is_object($change['old']))
                                        <pre class="mb-0" style="max-height: 100px; overflow: auto;"><code>{{ json_encode($change['old'], JSON_UNESCAPED_UNICODE) }}</code></pre>
                                    @else
                                        {{ $change['old'] ?? 'null' }}
                                    @endif
                                </td>
                                <td class="text-success">
                                    @if(is_array($change['new']) || is_object($change['new']))
                                        <pre class="mb-0" style="max-height: 100px; overflow: auto;"><code>{{ json_encode($change['new'], JSON_UNESCAPED_UNICODE) }}</code></pre>
                                    @else
                                        {{ $change['new'] ?? 'null' }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Связанные логи (с таким же batch_id) -->
    @if($log->batch_id)
        @php
            $relatedLogs = \App\Models\UserOrganizationLog::where('batch_id', $log->batch_id)
                ->where('id', '!=', $log->id)
                ->with('user')
                ->latest()
                ->get();
        @endphp

        @if($relatedLogs->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <i class="bi bi-link me-1"></i>
                Связанные действия (Batch ID: {{ $log->batch_id }})
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Дата</th>
                                <th>Тип</th>
                                <th>Действие</th>
                                <th>Пользователь</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($relatedLogs as $related)
                                <tr>
                                    <td><code>#{{ $related->id }}</code></td>
                                    <td>{{ $related->created_at->format('d.m.Y H:i:s') }}</td>
                                    <td><span class="badge bg-secondary">{{ $related->entity_type_name }}</span></td>
                                    <td>
                                        @php
                                            $badgeClass = match($related->action) {
                                                'create' => 'success',
                                                'update' => 'primary',
                                                'delete', 'force_delete' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">{{ $related->action_name }}</span>
                                    </td>
                                    <td>{{ $related->user?->name ?? 'Система' }}</td>
                                    <td>
                                        <a href="{{ route('admin.logs.show', $related) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    @endif
</div>
@endsection

@push('styles')
<style>
    pre {
        font-size: 0.85rem;
        tab-size: 2;
        margin-bottom: 0;
    }
    .table-borderless th,
    .table-borderless td {
        border: none;
        padding: 0.5rem 0;
    }
</style>
@endpush