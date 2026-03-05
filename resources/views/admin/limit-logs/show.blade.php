@extends('layouts.app')

@section('title', 'Детали лога #' . $log->id)

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-pie-chart me-2"></i>
            Детали лога #{{ $log->id }}
        </h1>
        <a href="{{ route('admin.limit-logs.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-info-circle me-1"></i>
                    Основная информация
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th style="width: 150px;">ID:</th>
                            <td><code>#{{ $log->id }}</code></td>
                        </tr>
                        <tr>
                            <th>Дата:</th>
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
                            <td><code>{{ $log->ip_address ?? '—' }}</code></td>
                        </tr>
                        <tr>
                            <th>Batch ID:</th>
                            <td><code>{{ $log->batch_id ?? '—' }}</code></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-briefcase me-1"></i>
                    Информация о сущности
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th style="width: 150px;">Тип:</th>
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
                        </tr>
                        <tr>
                            <th>Состояние:</th>
                            <td>
                                @if($entity)
                                    <span class="text-success">Сущность существует</span>
                                @else
                                    <span class="text-danger">Сущность удалена</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($log->quantity_change !== null)
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <i class="bi bi-arrow-left-right me-1"></i>
            Изменение количества
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center p-3 border rounded bg-light">
                        <h5>Было</h5>
                        <h2>{{ $log->old_quantity }}</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 border rounded bg-light">
                        <h5>Изменение</h5>
                        <h2 class="{{ $log->quantity_change > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $log->quantity_change > 0 ? '+' : '' }}{{ $log->quantity_change }}
                        </h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 border rounded bg-light">
                        <h5>Стало</h5>
                        <h2>{{ $log->new_quantity }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($log->old_ends_at || $log->new_ends_at)
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <i class="bi bi-calendar me-1"></i>
            Изменение даты
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="text-center p-3 border rounded bg-light">
                        <h5>Было</h5>
                        <h3>{{ $log->old_ends_at ? date('d.m.Y H:i:s', strtotime($log->old_ends_at)) : '—' }}</h3>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-center p-3 border rounded bg-light">
                        <h5>Стало</h5>
                        <h3>{{ $log->new_ends_at ? date('d.m.Y H:i:s', strtotime($log->new_ends_at)) : '—' }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-arrow-left-circle me-1"></i>
                    Данные ДО изменения
                </div>
                <div class="card-body">
                    @if($log->old_data)
                        <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow: auto;"><code>{{ json_encode($log->old_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    @else
                        <p class="text-muted text-center py-4">Нет данных</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-arrow-right-circle me-1"></i>
                    Данные ПОСЛЕ изменения
                </div>
                <div class="card-body">
                    @if($log->new_data)
                        <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow: auto;"><code>{{ json_encode($log->new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    @else
                        <p class="text-muted text-center py-4">Нет данных</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection