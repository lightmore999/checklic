@extends('layouts.app')

@section('title', 'Управление подписками')
@section('page-icon', 'bi-stars')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-stars text-info me-2"></i>
            Управление подписками
            @if(isset($subscriptions) && $subscriptions->total() > 0)
                <span class="badge bg-info ms-2">{{ $subscriptions->total() }}</span>
            @endif
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('subscriptions.create') }}" class="btn btn-success">
                <i class="bi bi-plus-lg me-2"></i>
                Создать подписку
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

    <!-- Статистика -->
    @if(isset($stats))
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="display-5 text-primary mb-2">{{ $stats['total'] }}</div>
                    <div class="text-muted">Всего подписок</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="display-5 text-success mb-2">{{ $stats['active'] }}</div>
                    <div class="text-muted">Активных</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="display-5 text-warning mb-2">{{ $stats['expiring_soon'] }}</div>
                    <div class="text-muted">Скоро истекают</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="display-5 text-danger mb-2">{{ $stats['expired'] }}</div>
                    <div class="text-muted">Истекшие</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Форма фильтров -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0">
                <i class="bi bi-funnel me-2"></i>
                Фильтры
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('subscriptions.index') }}" id="filterForm">
                <div class="row g-3">
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
                    
                    <!-- Фильтр по статусу -->
                    <div class="col-md-2">
                        <label for="status" class="form-label">Статус</label>
                        <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                            <option value="">Все статусы</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активна</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Ожидает</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Приостановлена</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Истекла</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Отменена</option>
                        </select>
                    </div>
                    
                    <!-- Фильтр по истекающим -->
                    <div class="col-md-2">
                        <label for="expiring_soon" class="form-label">Скоро истекают</label>
                        <select name="expiring_soon" id="expiring_soon" class="form-select" onchange="this.form.submit()">
                            <option value="">Не выбрано</option>
                            <option value="7" {{ request('expiring_soon') == '7' ? 'selected' : '' }}>Менее 7 дней</option>
                            <option value="14" {{ request('expiring_soon') == '14' ? 'selected' : '' }}>Менее 14 дней</option>
                            <option value="30" {{ request('expiring_soon') == '30' ? 'selected' : '' }}>Менее 30 дней</option>
                        </select>
                    </div>
                    
                    <!-- Фильтр по истекшим -->
                    <div class="col-md-2">
                        <label for="expired" class="form-label">Истекшие</label>
                        <select name="expired" id="expired" class="form-select" onchange="this.form.submit()">
                            <option value="">Не выбрано</option>
                            <option value="1" {{ request('expired') == '1' ? 'selected' : '' }}>Показать истекшие</option>
                        </select>
                    </div>
                    
                    <!-- Кнопки действий -->
                    <div class="col-12 mt-3">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-funnel me-1"></i> Применить фильтры
                            </button>
                            <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Сбросить
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Активные фильтры -->
    @if(request()->anyFilled(['organization_id', 'user_id', 'status', 'expiring_soon', 'expired']))
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
                
                @if(request('status'))
                    <span class="badge bg-info text-white">Статус: 
                        @switch(request('status'))
                            @case('active') Активна @break
                            @case('pending') Ожидает @break
                            @case('suspended') Приостановлена @break
                            @case('expired') Истекла @break
                            @case('cancelled') Отменена @break
                            @default {{ request('status') }}
                        @endswitch
                    </span>
                @endif
                
                @if(request('expiring_soon'))
                    <span class="badge bg-info text-white">Истекают менее чем через {{ request('expiring_soon') }} дней</span>
                @endif
                
                @if(request('expired'))
                    <span class="badge bg-info text-white">Только истекшие</span>
                @endif
            </div>
        </div>
    @endif

    <!-- Таблица подписок -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if(isset($subscriptions) && $subscriptions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Пользователь</th>
                                <th>Организация</th>
                                <th>Дата начала</th>
                                <th>Дата окончания</th>
                                <th>Статус</th>
                                <th>Осталось дней</th>
                                <th>Создана</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subscriptions as $subscription)
                                @php
                                    $remainingDays = $subscription->getRemainingDays();
                                    $statusClass = $subscription->status === 'active' ? 'success' : 
                                                ($subscription->status === 'expired' ? 'danger' : 
                                                ($subscription->status === 'pending' ? 'warning' : 
                                                ($subscription->status === 'cancelled' ? 'secondary' : 'info')));
                                    
                                    $user = $subscription->user;
                                    $userRoleDisplay = $user ? $user->getRoleDisplayName() : 'Неизвестно';
                                    $userInitial = $user ? strtoupper(substr($user->name, 0, 1)) : '?';
                                    
                                    // Определяем организацию пользователя
                                    $userOrg = null;
                                    if ($user) {
                                        if ($user->isOrgOwner() && $user->orgOwnerProfile) {
                                            $userOrg = $user->orgOwnerProfile->organization;
                                        } elseif ($user->isOrgMember() && $user->orgMemberProfile) {
                                            $userOrg = $user->orgMemberProfile->organization;
                                        }
                                    }
                                    
                                    // Определяем маршрут к профилю пользователя в зависимости от роли
                                    $userProfileRoute = null;
                                    if ($user) {
                                        if ($user->isAdmin()) {
                                            $userProfileRoute = route('admin.dashboard'); // или другой маршрут для админа
                                        } elseif ($user->isManager()) {
                                            $userProfileRoute = route('admin.managers.show', $user->id);
                                        } elseif ($user->isOrgOwner()) {
                                            $userProfileRoute = route('admin.organization.show', $userOrg->id ?? 0);
                                        } elseif ($user->isOrgMember()) {
                                            $userProfileRoute = $userOrg ? route('admin.org-members.show', [$userOrg->id, $user->orgMemberProfile->id]) : null;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td class="fw-bold">#{{ $subscription->id }}</td>
                                    <td>
                                        @if($user)
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-{{ $user->getRoleColor() }} d-flex align-items-center justify-content-center me-2" 
                                                    style="width: 36px; height: 36px; color: white; font-size: 0.9rem;">
                                                    {{ $userInitial }}
                                                </div>
                                                <div>
                                                    @if($userProfileRoute)
                                                        <a href="{{ $userProfileRoute }}" class="fw-bold text-decoration-none">{{ $user->name }}</a>
                                                    @else
                                                        <div class="fw-bold">{{ $user->name }}</div>
                                                    @endif
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                    <div>
                                                        <span class="badge bg-{{ $user->getRoleColor() }} mt-1">
                                                            {{ $userRoleDisplay }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Пользователь удален</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($userOrg)
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-2" 
                                                    style="width: 28px; height: 28px; color: white; font-size: 0.7rem;">
                                                    <i class="bi bi-building"></i>
                                                </div>
                                                <div>
                                                    <a href="{{ route('admin.organization.show', $userOrg->id) }}" class="text-decoration-none">
                                                        {{ $userOrg->name }}
                                                    </a>
                                                    @if($user && $user->isOrgOwner())
                                                        <small class="badge bg-primary">Руководитель</small>
                                                    @elseif($user && $user->isOrgMember())
                                                        <small class="badge bg-info">Сотрудник</small>
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif($user && $user->isManager())
                                            <span class="badge bg-secondary">Менеджер</span>
                                        @else
                                            <span class="text-muted fst-italic">Не указана</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($subscription->starts_at)
                                            {{ $subscription->starts_at->format('d.m.Y') }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
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
                                            @elseif($subscription->status === 'expired' || ($subscription->ends_at && $subscription->ends_at->isPast()))
                                                <span class="badge bg-danger">Истекла</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        @else
                                            <span class="text-muted">∞</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $subscription->created_at->format('d.m.Y') }}<br>
                                        <small class="text-muted">{{ $subscription->created_at->format('H:i') }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Пагинация -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <p class="text-muted mb-0">
                            Показано {{ $subscriptions->firstItem() ?? 0 }} - {{ $subscriptions->lastItem() ?? 0 }} 
                            из {{ $subscriptions->total() }} подписок
                        </p>
                    </div>
                    <div>
                        {{ $subscriptions->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-stars display-1 text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">Подписки не найдены</h4>
                    <p class="text-muted mb-4">
                        @if(request()->anyFilled(['organization_id', 'user_id', 'status', 'expiring_soon', 'expired']))
                            Попробуйте изменить параметры фильтрации
                        @else
                            Создайте первую подписку
                        @endif
                    </p>
                    @if(!request()->anyFilled(['organization_id', 'user_id', 'status', 'expiring_soon', 'expired']))
                        <a href="{{ route('subscriptions.create') }}" class="btn btn-success">
                            <i class="bi bi-plus-lg me-2"></i>
                            Создать подписку
                        </a>
                    @else
                        <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Сбросить фильтры
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Модальное окно удаления подписки -->
<div class="modal fade" id="deleteSubscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-trash me-2"></i>
                    Удаление подписки
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить эту подписку?</p>
                <p class="text-danger"><strong>Внимание!</strong> Это действие также удалит все лимиты, связанные с этой подпиской.</p>
            </div>
            <div class="modal-footer">
                <form action="" method="POST" id="deleteSubscriptionForm">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Удалить подписку</button>
                </form>
            </div>
        </div>
    </div>
</div>

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
    .table td, .table th {
        vertical-align: middle;
    }
    .badge {
        font-size: 85%;
    }
    .btn-sm {
        line-height: 1;
    }
    .btn-sm i {
        font-size: 1rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ru.js"></script>
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
                    
                    results = results.concat(data);
                    
                    return {
                        results: results
                    };
                },
                cache: true
            }
        });

        // При изменении организации - обновляем список пользователей
        $('#organization_id').on('change', function() {
            $('.select2-user').val(null).trigger('change');
            $('#filterForm').submit();
        });

        // Автоматическая отправка формы при изменении полей
        $('select[name="status"], select[name="expiring_soon"], select[name="expired"]').on('change', function() {
            $('#filterForm').submit();
        });

        // Отправка формы при изменении пользователя
        $('.select2-user').on('change', function() {
            $('#filterForm').submit();
        });

        // Функция для открытия модалки удаления
        window.deleteSubscription = function(id) {
            document.getElementById('deleteSubscriptionForm').action = '/subscriptions/' + id;
            new bootstrap.Modal(document.getElementById('deleteSubscriptionModal')).show();
        };

        // Инициализация тултипов
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
@endsection