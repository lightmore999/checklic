@extends('layouts.app')

@section('title', 'История операций с лимитами')
@section('page-icon', 'bi-clock-history')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-clock-history text-info me-2"></i>
            История операций с лимитами
        </h2>
        <div class="btn-group">
            <a href="{{ route('limits.history.export', request()->query()) }}" class="btn btn-success">
                <i class="bi bi-download me-2"></i>
                Экспорт CSV
            </a>
        </div>
    </div>

    <!-- Статистика за период -->
    @if(isset($stats) && $stats['total_operations'] > 0)
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Всего операций</div>
                            <div class="h3 mb-0">{{ $stats['total_operations'] }}</div>
                        </div>
                        <i class="bi bi-list-check fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Начислено</div>
                            <div class="h3 mb-0">
                                {{ $stats['by_action']['return_quantity']['total_quantity'] ?? 0 }}
                            </div>
                        </div>
                        <i class="bi bi-arrow-up-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Списано</div>
                            <div class="h3 mb-0">
                                {{ abs($stats['by_action']['use_quantity']['total_quantity'] ?? 0) }}
                            </div>
                        </div>
                        <i class="bi bi-arrow-down-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Создано/Делегировано</div>
                            <div class="h3 mb-0">
                                {{ ($stats['by_action']['create']['total_quantity'] ?? 0) + ($stats['by_action']['delegate']['total_quantity'] ?? 0) }}
                            </div>
                        </div>
                        <i class="bi bi-plus-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- График по дням (если выбран период) -->
    @if(count($stats['daily']) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="bi bi-graph-up me-2"></i>
                Активность по дням
            </h5>
        </div>
        <div class="card-body">
            <canvas id="dailyChart" style="height: 300px;"></canvas>
        </div>
    </div>
    @endif
    @endif

    <!-- Фильтры -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="bi bi-funnel me-2"></i>
                Фильтры
                <button class="btn btn-sm btn-link float-end" type="button" data-bs-toggle="collapse" data-bs-target="#filters">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </h5>
        </div>
        <div class="collapse show" id="filters">
            <div class="card-body">
                <form method="GET" action="{{ route('limits.history') }}" id="filterForm" class="row g-3">
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

                    <!-- Фильтр по подписке -->
                    <div class="col-md-3">
                        <label class="form-label">Подписка</label>
                        <select name="subscription_id" class="form-select select2-subscription">
                            <option value="">Все подписки</option>
                            @foreach($subscriptions as $sub)
                                <option value="{{ $sub->id }}" {{ request('subscription_id') == $sub->id ? 'selected' : '' }}>
                                    {{ $sub->name ?? 'Подписка #' . $sub->id }} - {{ $sub->user->name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Фильтр по типу операции -->
                    <div class="col-md-3">
                        <label class="form-label">Тип операции</label>
                        <select name="action" class="form-select">
                            <option value="">Все операции</option>
                            @foreach($actions as $value => $label)
                                <option value="{{ $value }}" {{ request('action') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Фильтр по типу отчета -->
                    <div class="col-md-3">
                        <label class="form-label">Тип отчета</label>
                        <select name="report_type_id" class="form-select">
                            <option value="">Все типы</option>
                            @foreach($reportTypes as $type)
                                <option value="{{ $type->id }}" {{ request('report_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Фильтр по дате -->
                    <div class="col-md-3">
                        <label class="form-label">Дата с</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Дата по</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>

                    <!-- Кнопки действий -->
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-funnel me-1"></i> Применить фильтры
                            </button>
                            <a href="{{ route('limits.history') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Сбросить
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Активные фильтры -->
    @if(request()->anyFilled(['organization_id', 'user_id', 'subscription_id', 'action', 'report_type_id', 'date_from', 'date_to']))
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
                
                @if(request('subscription_id'))
                    @php 
                        $sub = $subscriptions->firstWhere('id', request('subscription_id')); 
                    @endphp
                    <span class="badge bg-info text-white">Подписка: {{ $sub->name ?? 'ID: ' . request('subscription_id') }}</span>
                @endif
                
                @if(request('action'))
                    <span class="badge bg-info text-white">Операция: {{ $actions[request('action')] ?? request('action') }}</span>
                @endif
                
                @if(request('report_type_id'))
                    @php 
                        $type = $reportTypes->firstWhere('id', request('report_type_id')); 
                    @endphp
                    <span class="badge bg-info text-white">Тип отчета: {{ $type->name ?? 'ID: ' . request('report_type_id') }}</span>
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

    <!-- Таблица истории -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Дата</th>
                            <th>Операция</th>
                            <th>Кто</th>
                            <th>Кому</th>
                            <th>Подписка</th>
                            <th>Тип отчета</th>
                            <th>Кол-во</th>
                            <th>Баланс</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $targetUser = $log->targetUser();
                                $subscription = $log->subscription();
                                $reportType = $log->reportType();
                                
                                // Определяем URL для пользователя (кто совершил действие)
                                $actorUrl = null;
                                if ($log->user) {
                                    if ($log->user->isAdmin()) {
                                        $actorUrl = route('admin.dashboard');
                                    } elseif ($log->user->isManager()) {
                                        $actorUrl = route('admin.managers.show', $log->user->id);
                                    } elseif ($log->user->isOrgOwner() && $log->user->orgOwnerProfile) {
                                        $actorUrl = route('admin.organization.show', $log->user->orgOwnerProfile->organization_id);
                                    } elseif ($log->user->isOrgMember() && $log->user->orgMemberProfile) {
                                        $actorUrl = route('admin.org-members.show', [
                                            $log->user->orgMemberProfile->organization_id,
                                            $log->user->orgMemberProfile->id
                                        ]);
                                    }
                                }
                                
                                // Определяем URL для целевого пользователя (кому)
                                $targetUrl = null;
                                if ($targetUser) {
                                    if ($targetUser->isAdmin()) {
                                        $targetUrl = route('admin.dashboard');
                                    } elseif ($targetUser->isManager()) {
                                        $targetUrl = route('admin.managers.show', $targetUser->id);
                                    } elseif ($targetUser->isOrgOwner() && $targetUser->orgOwnerProfile) {
                                        $targetUrl = route('admin.organization.show', $targetUser->orgOwnerProfile->organization_id);
                                    } elseif ($targetUser->isOrgMember() && $targetUser->orgMemberProfile) {
                                        $targetUrl = route('admin.org-members.show', [
                                            $targetUser->orgMemberProfile->organization_id,
                                            $targetUser->orgMemberProfile->id
                                        ]);
                                    }
                                }
                                
                                // Определяем URL для подписки
                                $subscriptionUrl = $subscription ? route('subscriptions.show', $subscription->id) : null;
                            @endphp
                            <tr>
                                <td class="text-nowrap">
                                    <small>{{ $log->created_at->format('d.m.Y H:i:s') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->operation_color }} fs-6">
                                        <i class="bi {{ $log->operation_icon }} me-1"></i>
                                        {{ $log->action_name }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->user)
                                        @if($actorUrl)
                                            <a href="{{ $actorUrl }}" class="text-decoration-none" target="_blank">
                                                <div class="d-flex align-items-center">
                                                    <span class="fw-semibold">{{ $log->user->name }}</span>
                                                    <small class="text-muted ms-1">({{ $log->user->getRoleDisplayName() }})</small>
                                                </div>
                                            </a>
                                        @else
                                            <div class="d-flex align-items-center">
                                                <span class="fw-semibold">{{ $log->user->name }}</span>
                                                <small class="text-muted ms-1">({{ $log->user->getRoleDisplayName() }})</small>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">Система</span>
                                    @endif
                                </td>
                                <td>
                                    @if($targetUser)
                                        @if($targetUrl)
                                            <a href="{{ $targetUrl }}" class="text-decoration-none" target="_blank">
                                                <div>
                                                    <span class="fw-semibold">{{ $targetUser->name }}</span>
                                                    <small class="text-muted d-block">{{ $targetUser->getRoleDisplayName() }}</small>
                                                </div>
                                            </a>
                                        @else
                                            <div>
                                                <span class="fw-semibold">{{ $targetUser->name }}</span>
                                                <small class="text-muted d-block">{{ $targetUser->getRoleDisplayName() }}</small>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($subscription)
                                        @if($subscriptionUrl)
                                            <a href="{{ $subscriptionUrl }}" class="text-decoration-none" target="_blank">
                                                <div>
                                                    <small>{{ $subscription->name ?? 'Подписка #' . $subscription->id }}</small>
                                                    @if($subscription->user)
                                                        <small class="text-muted d-block">{{ $subscription->user->name }}</small>
                                                    @endif
                                                </div>
                                            </a>
                                        @else
                                            <div>
                                                <small>{{ $subscription->name ?? 'Подписка #' . $subscription->id }}</small>
                                                @if($subscription->user)
                                                    <small class="text-muted d-block">{{ $subscription->user->name }}</small>
                                                @endif
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($reportType)
                                        <small>{{ $reportType->name }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->display_quantity)
                                        <span class="badge bg-{{ $log->display_quantity > 0 ? 'success' : ($log->display_quantity < 0 ? 'danger' : 'secondary') }} fs-6">
                                            {{ $log->display_quantity > 0 ? '+' : '' }}{{ $log->display_quantity }}
                                        </span>
                                        @if($log->action === 'create')
                                            <small class="d-block text-muted">создание</small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->display_balance_before !== '—' || $log->display_balance_after !== '—')
                                        <small>
                                            {{ $log->display_balance_before }} → {{ $log->display_balance_after }}
                                        </small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->entity_type === 'delegated_limit')
                                        <span class="badge bg-info" title="Делегированный лимит">
                                            <i class="bi bi-people"></i>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="bi bi-clock-history display-4 text-muted d-block mb-3"></i>
                                    <h5 class="text-muted">История операций пуста</h5>
                                    <p class="text-muted">Попробуйте изменить параметры фильтрации</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                <div class="text-muted small">
                    Показано {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} из {{ $logs->total() }}
                </div>
                <div>
                    {{ $logs->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .text-white-50 {
        color: rgba(255,255,255,0.7);
    }
    .badge.fs-6 {
        font-size: 0.9rem;
        padding: 0.5rem 0.8rem;
    }
    .table td {
        vertical-align: middle;
    }
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
    
    /* Стили для ссылок */
    .table a {
        color: #0d6efd;
        transition: color 0.2s;
        text-decoration: none;
    }
    
    .table a:hover {
        color: #0a58ca;
        text-decoration: underline !important;
    }
    
    .table a .fw-semibold:hover {
        text-decoration: underline;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ru.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Инициализация Select2 для организаций
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

    // Инициализация Select2 для подписок
    $('.select2-subscription').select2({
        theme: 'default',
        language: 'ru',
        placeholder: 'Выберите подписку',
        allowClear: true,
        width: '100%'
    });

    // При изменении организации - обновляем список пользователей
    $('#organization_id').on('change', function() {
        $('.select2-user').val(null).trigger('change');
    });

    // Автоматическая отправка формы при изменении select-фильтров
    $('#organization_id, #user_id, #subscription_id, select[name="action"], select[name="report_type_id"]').on('change', function() {
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

    // Инициализация тултипов
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// График
document.addEventListener('DOMContentLoaded', function() {
    @if(isset($stats['daily']) && count($stats['daily']) > 0)
    const ctx = document.getElementById('dailyChart').getContext('2d');

    const dates = @json($stats['daily']->pluck('date'));
    const created = @json($stats['daily']->pluck('created'));
    const returned = @json($stats['daily']->pluck('returned'));
    const used = @json($stats['daily']->pluck('used'));

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dates.map(date => {
                const d = new Date(date);
                return d.toLocaleDateString('ru-RU');
            }),
            datasets: [
                {
                    label: 'Создано',
                    data: created,
                    backgroundColor: 'rgba(23, 162, 184, 0.5)',
                    borderColor: '#17a2b8',
                    borderWidth: 1
                },
                {
                    label: 'Возвращено',
                    data: returned,
                    backgroundColor: 'rgba(40, 167, 69, 0.5)',
                    borderColor: '#28a745',
                    borderWidth: 1
                },
                {
                    label: 'Использовано',
                    data: used.map(v => Math.abs(v)),
                    backgroundColor: 'rgba(220, 53, 69, 0.5)',
                    borderColor: '#dc3545',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value;
                        }
                    }
                }
            }
        }
    });
    @endif
});
</script>
@endpush