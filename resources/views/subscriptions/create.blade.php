@extends('layouts.app')

@section('title', 'Создание подписки')
@section('page-icon', 'bi-stars')

@section('content')
<div class="container-fluid py-4">
    <!-- Заголовок с градиентом -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-info text-white shadow-lg" 
                 style="background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-4" 
                                 style="width: 70px; height: 70px; font-size: 2rem; font-weight: 500; color: white; border: 3px solid rgba(255,255,255,0.3);">
                                <i class="bi bi-stars"></i>
                            </div>
                            <div>
                                <h1 class="h2 mb-2">Создание новой подписки</h1>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-white text-info px-3 py-2">
                                        <i class="bi bi-stars me-1"></i>Новая подписка
                                    </span>
                                    <span class="badge bg-white bg-opacity-25 px-3 py-2">
                                        <i class="bi bi-calendar me-1"></i>{{ now()->format('d.m.Y') }}
                                    </span>
                                </div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'manager.dashboard') }}" 
                                               class="text-white opacity-75">
                                                Панель {{ Auth::user()->isAdmin() ? 'админа' : 'менеджера' }}
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('subscriptions.index') }}" class="text-white opacity-75">
                                                Подписки
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active text-white" aria-current="page">
                                            Создание
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('subscriptions.index') }}" class="btn btn-light">
                                <i class="bi bi-arrow-left me-2"></i>Назад
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Флеш-сообщения -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>
                    <strong>Ошибка!</strong> {{ session('error') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>
                    <strong>Ошибка!</strong> Пожалуйста, исправьте следующие ошибки:
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Карточка с фильтром по организации -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4">
            <h5 class="mb-0">
                <i class="bi bi-funnel text-primary me-2"></i>
                Фильтр по организации
            </h5>
        </div>
        <div class="card-body pt-3">
            <div class="row">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Организация</label>
                    <select name="filter_organization_id" id="filter_organization_id" class="form-select select2-organization">
                        <option value="">Все организации</option>
                        @if(isset($organizations) && $organizations->count() > 0)
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}">{{ $org->name }} @if($org->inn) (ИНН: {{ $org->inn }}) @endif</option>
                            @endforeach
                        @endif
                    </select>
                    <small class="text-muted mt-1 d-block">
                        <i class="bi bi-info-circle me-1"></i>
                        Выберите организацию для фильтрации списка пользователей
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Основная форма -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4">
            <h5 class="mb-0">
                <i class="bi bi-pencil-square text-primary me-2"></i>
                Данные подписки
            </h5>
        </div>
        <div class="card-body pt-3">
            <form action="{{ route('subscriptions.store') }}" method="POST" id="createSubscriptionForm">
                @csrf
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <!-- Пользователь с поиском -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person text-primary me-1"></i>
                                Пользователь <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('user_id') is-invalid @enderror" 
                                    id="user_id" name="user_id" required
                                    style="width: 100%;">
                                <option value=""></option>
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Начните вводить имя, email или выберите из списка
                            </small>
                        </div>
                        
                        <!-- Название подписки -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-tag text-primary me-1"></i>
                                Название подписки
                            </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="Например: Премиум подписка 2025">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Удобное название для идентификации подписки (необязательно)</small>
                        </div>
                        
                        <!-- Статус -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-flag text-primary me-1"></i>
                                Статус подписки <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="">Выберите статус</option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Активна</option>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Ожидает</option>
                                <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Приостановлена</option>
                                <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>Истекла</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Отменена</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Дата начала -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-plus text-primary me-1"></i>
                                Дата начала
                            </label>
                            <input type="date" class="form-control @error('starts_at') is-invalid @enderror" 
                                   id="starts_at" name="starts_at" 
                                   value="{{ old('starts_at', now()->format('Y-m-d')) }}">
                            @error('starts_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Оставьте пустым для автоматической установки на сегодня</small>
                        </div>
                        
                        <!-- Дата окончания -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-x text-primary me-1"></i>
                                Дата окончания
                            </label>
                            <input type="date" class="form-control @error('ends_at') is-invalid @enderror" 
                                   id="ends_at" name="ends_at" 
                                   value="{{ old('ends_at') }}"
                                   min="{{ now()->addDay()->format('Y-m-d') }}">
                            @error('ends_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Оставьте пустым для бессрочной подписки</small>
                        </div>
                    </div>
                </div>
                
                <!-- Информация о выбранном пользователе -->
                <div class="row mt-3" id="userInfo" style="display: none;">
                    <div class="col-12">
                        <div class="bg-light rounded p-3">
                            <h6 class="mb-3">
                                <i class="bi bi-info-circle text-info me-2"></i>
                                Информация о пользователе
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                             style="width: 28px; height: 28px;">
                                            <i class="bi bi-person-badge text-primary small"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Роль</small>
                                            <span class="fw-semibold" id="userRole"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                             style="width: 28px; height: 28px;">
                                            <i class="bi bi-envelope text-info small"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Email</small>
                                            <span class="fw-semibold" id="userEmail"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                             style="width: 28px; height: 28px;">
                                            <i class="bi bi-building text-success small"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Организация</small>
                                            <span class="fw-semibold" id="userOrganization"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                             style="width: 28px; height: 28px;">
                                            <i class="bi bi-stars text-warning small"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Активных подписок</small>
                                            <span class="fw-semibold" id="userActiveSubscriptions">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="activeSubscriptionWarning" class="alert alert-warning py-2 mt-3" style="display: none;">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <span id="activeSubscriptionMessage"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- БЛОК С ТИПАМИ ОТЧЕТОВ ДЛЯ ЛИМИТОВ -->
                <div class="card mt-4 mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-file-text me-2"></i>
                            Лимиты по типам отчетов
                            <small class="text-white-50 ms-2">(необязательно, можно добавить позже)</small>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Укажите количество отчетов для каждого типа. Если оставить 0, лимит создан не будет.
                        </p>
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Тип отчета</th>
                                        <th width="200" class="text-center">Количество</th>
                                        <th width="200" class="text-center">Действия</th>
                                    </tr>
                                </thead>
                                <tbody id="reportTypesBody">
                                    @foreach($reportTypes as $type)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 32px; height: 32px; font-size: 0.9rem; color: #0dcaf0;">
                                                    {{ strtoupper(substr($type->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $type->name }}</strong>
                                                    @if($type->only_api)
                                                        <span class="badge bg-warning ms-2">только API</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($type->description)
                                                <small class="text-muted d-block mt-1">{{ $type->description }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="input-group">
                                                <input type="number" 
                                                       name="quantities[{{ $type->id }}]" 
                                                       id="quantity_{{ $type->id }}"
                                                       class="form-control form-control-sm quantity-input text-center" 
                                                       value="{{ old('quantities.' . $type->id, 0) }}"
                                                       min="0"
                                                       max="999999"
                                                       step="1"
                                                       data-type-id="{{ $type->id }}">
                                                <span class="input-group-text">шт.</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="setQuantity({{ $type->id }}, 1)">
                                                    +1
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="setQuantity({{ $type->id }}, 5)">
                                                    +5
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="setQuantity({{ $type->id }}, 10)">
                                                    +10
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                                <div>
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    <span class="text-muted">Всего выбрано: <strong class="text-success" id="totalSelected">0</strong> отчетов</span>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="setAllQuantities(1)">
                                                        +1 для всех
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="setAllQuantities(5)">
                                                        +5 для всех
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="setAllQuantities(10)">
                                                        +10 для всех
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="resetAllQuantities()">
                                                        Сбросить все
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Скрытое поле для передачи выбранных типов отчетов -->
                <div id="reportTypesContainer"></div>
                
                <!-- Если есть redirect_to_organization -->
                @if(request()->has('organization_id'))
                    <input type="hidden" name="redirect_to_organization" value="{{ request('organization_id') }}">
                @endif
                
                <!-- Кнопки -->
                <div class="d-flex gap-2">
                    <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg me-2"></i> Отмена
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-check-lg me-2"></i> Создать подписку
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* ТОЧНО КАК ВО ВСЕХ СТРАНИЦАХ */

    /* Круги всегда идеальные */
    .rounded-circle {
        border-radius: 50% !important;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    /* Карточки - скругление 1rem (16px) */
    .card {
        border-radius: 1rem;
        transition: all 0.2s;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Бейджи - сильно скругленные 30px */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
        border-radius: 30px;
        padding: 0.35em 0.65em;
    }

    /* Заголовки карточек */
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0,0,0,.125);
        padding: 1rem 1.25rem;
    }

    .card-header h5, .card-header h6 {
        margin-bottom: 0;
        font-weight: 600;
    }

    .card-body {
        padding: 1.25rem;
    }

    /* Градиент для заголовка */
    .bg-gradient-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
    }

    /* Таблицы */
    .table th {
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
    }

    .table td {
        vertical-align: middle;
    }

    /* Для иконок в кружках */
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }

    /* Для текста в кружках с буквами */
    .rounded-circle.bg-primary, 
    .rounded-circle.bg-success, 
    .rounded-circle.bg-info,
    .rounded-circle.bg-warning,
    .rounded-circle.bg-danger {
        font-weight: 500;
    }

    /* Для форм */
    .form-label {
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
        font-weight: 600;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Для алертов */
    .alert-info {
        background-color: #cff4fc;
        border-color: #b6effb;
        color: #055160;
        border-radius: 0.75rem;
    }

    .alert-success {
        border-radius: 0.75rem;
    }

    .alert-danger {
        border-radius: 0.75rem;
    }

    .alert-warning {
        border-radius: 0.75rem;
    }

    /* Select2 стили */
    .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }
    
    /* Компактный вид для результатов поиска */
    .select2-results__option {
        padding: 6px 12px !important;
        font-size: 0.95rem;
    }
    .select2-results__option small {
        color: #6c757d;
        font-size: 0.85rem;
    }
    
    /* Стили для таблицы типов отчетов */
    .quantity-input {
        text-align: center;
        font-weight: bold;
    }
    
    .btn-outline-primary, .btn-outline-success, .btn-outline-danger {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    #totalSelected {
        font-size: 1.1rem;
        color: #28a745;
    }

    /* Для светлого фона */
    .bg-light.rounded {
        border-radius: 0.75rem !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ru.js"></script>

<script>
$(document).ready(function() {
    console.log('Инициализация создания подписки...');
    
    // Инициализация Select2 для организаций с поиском
    $('.select2-organization').select2({
        theme: 'default',
        language: 'ru',
        placeholder: 'Выберите организацию',
        allowClear: true,
        width: '100%'
    });
    
    // Инициализация Select2 для пользователей
    $('#user_id').select2({
        placeholder: 'Введите имя или email для поиска...',
        allowClear: true,
        minimumInputLength: 0,
        language: 'ru',
        width: '100%',
        ajax: {
            url: '{{ route("users.search") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    search: params.term || '',
                    organization_id: $('#filter_organization_id').val(),
                    roles: ['org_owner', 'org_member', 'manager', 'admin']
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(user) {
                        // Определяем организацию пользователя
                        let organizationName = 'Нет организации';
                        
                        if (user.role === 'org_owner' && user.org_owner_profile) {
                            organizationName = user.org_owner_profile.organization_name || 'Организация владельца';
                        } else if (user.role === 'org_member' && user.org_member_profile) {
                            organizationName = user.org_member_profile.organization_name || 'Организация сотрудника';
                        } else if (user.role === 'manager') {
                            organizationName = 'Менеджер';
                        } else if (user.role === 'admin') {
                            organizationName = 'Администратор';
                        }
                        
                        return {
                            id: user.id,
                            text: user.name + ' (' + user.email + ')',
                            name: user.name,
                            email: user.email,
                            role: user.role_display,
                            role_code: user.role,
                            organization: organizationName,
                            organization_id: user.organization_id,
                            is_org_owner: user.role === 'org_owner',
                            has_active: user.has_active_subscriptions || false
                        };
                    })
                };
            },
            cache: true
        },
        templateResult: formatUser,
        templateSelection: formatUserSelection
    });

    // Компактный формат отображения пользователя в списке
    function formatUser(user) {
        if (user.loading) return user.text;
        
        let roleBadge = '';
        if (user.role_code === 'org_owner') {
            roleBadge = '<span class="badge bg-success ms-1">Владелец</span>';
        } else if (user.role_code === 'org_member') {
            roleBadge = '<span class="badge bg-info ms-1">Сотрудник</span>';
        } else if (user.role_code === 'manager') {
            roleBadge = '<span class="badge bg-primary ms-1">Менеджер</span>';
        } else if (user.role_code === 'admin') {
            roleBadge = '<span class="badge bg-danger ms-1">Админ</span>';
        }
        
        let status = user.has_active ? '<span class="badge bg-warning ms-2"><i class="bi bi-check-circle-fill"></i> есть подписка</span>' : '';
        let organizationDisplay = user.organization !== 'Нет организации' ? user.organization : '';
        
        return $(`
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>${user.name}</strong> ${roleBadge}<br>
                    <small class="text-muted"><i class="bi bi-envelope"></i> ${user.email}</small>
                    ${organizationDisplay ? `<small class="text-muted ms-2"><i class="bi bi-building"></i> ${organizationDisplay}</small>` : ''}
                </div>
                <div>${status}</div>
            </div>
        `);
    }

    function formatUserSelection(user) {
        return user.name || user.text;
    }

    // Обработка изменения фильтра по организации - автоматическая установка владельца
    $('#filter_organization_id').on('change', function() {
        const organizationId = $(this).val();
        
        if (organizationId) {
            // Показываем индикатор загрузки
            $('#user_id').prop('disabled', true);
            
            // Загружаем информацию об организации и её владельце
            $.ajax({
                url: '{{ route("organizations.get", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', organizationId),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.owner) {
                        // Создаем объект пользователя для Select2
                        const ownerData = {
                            id: response.owner.id,
                            text: response.owner.name + ' (' + response.owner.email + ')',
                            name: response.owner.name,
                            email: response.owner.email,
                            role: 'Владелец организации',
                            role_code: 'org_owner',
                            organization: response.organization.name,
                            organization_id: organizationId,
                            is_org_owner: true,
                            has_active: response.owner.has_active_subscriptions || false
                        };
                        
                        // Очищаем текущий выбор и добавляем новую опцию
                        $('#user_id').empty().append(new Option(ownerData.text, ownerData.id, true, true)).trigger('change');
                        
                        // Показываем информацию о пользователе
                        $('#userInfo').show();
                        $('#userRole').text(ownerData.role);
                        $('#userEmail').text(ownerData.email);
                        $('#userOrganization').text(ownerData.organization);
                        
                        // Загружаем подписки пользователя
                        loadUserSubscriptions(ownerData.id);
                        
                        // Показываем сообщение, что автоматически выбран владелец
                        showNotification('Автоматически выбран владелец организации', 'info');
                    } else if (response.success && !response.owner) {
                        $('#user_id').val(null).trigger('change');
                        $('#userInfo').hide();
                        showNotification('У организации нет владельца. Выберите пользователя вручную.', 'warning');
                    }
                },
                error: function(xhr) {
                    console.error('Ошибка загрузки организации:', xhr);
                    $('#user_id').val(null).trigger('change');
                    $('#userInfo').hide();
                    showNotification('Ошибка при загрузке информации об организации', 'danger');
                },
                complete: function() {
                    $('#user_id').prop('disabled', false);
                }
            });
        } else {
            // Если организация не выбрана, очищаем пользователя
            $('#user_id').val(null).trigger('change');
            $('#userInfo').hide();
        }
    });

    // Функция для показа уведомлений
    function showNotification(message, type = 'info') {
        // Проверяем, есть ли уже контейнер для уведомлений
        let notificationContainer = $('#notificationContainer');
        if (notificationContainer.length === 0) {
            notificationContainer = $('<div id="notificationContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>');
            $('body').append(notificationContainer);
        }
        
        const bgColor = type === 'info' ? 'bg-info' : (type === 'warning' ? 'bg-warning' : (type === 'danger' ? 'bg-danger' : 'bg-success'));
        const icon = type === 'info' ? 'bi-info-circle' : (type === 'warning' ? 'bi-exclamation-triangle' : (type === 'danger' ? 'bi-x-circle' : 'bi-check-circle'));
        
        const notification = $(`
            <div class="alert ${bgColor} text-white alert-dismissible fade show shadow-lg" role="alert" style="min-width: 300px;">
                <i class="bi ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
        
        notificationContainer.append(notification);
        
        // Автоматически скрываем через 5 секунд
        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Обработка выбора пользователя вручную
    $('#user_id').on('select2:select', function(e) {
        var data = e.params.data;
        
        $('#userInfo').show();
        $('#userRole').text(data.role || 'Не указана');
        $('#userEmail').text(data.email || 'Не указан');
        $('#userOrganization').text(data.organization || 'Не указана');
        
        loadUserSubscriptions(data.id);
    });

    $('#user_id').on('select2:clear', function() {
        $('#userInfo').hide();
        $('#activeSubscriptionWarning').hide();
        $('#userActiveSubscriptions').text('0');
    });

    // Загрузка подписок пользователя
    function loadUserSubscriptions(userId) {
        $.ajax({
            url: '{{ url("api/users") }}/' + userId + '/subscriptions',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const activeCount = response.subscriptions ? 
                        response.subscriptions.filter(s => s.status === 'active').length : 0;
                    $('#userActiveSubscriptions').text(activeCount);
                    
                    if (activeCount > 0) {
                        let message = 'У пользователя есть активные подписки: ';
                        response.subscriptions.forEach(function(sub, index) {
                            if (sub.status === 'active') {
                                let endDate = sub.ends_at ? `до ${sub.ends_at}` : 'бессрочно';
                                if (index > 0) message += ', ';
                                message += `#${sub.id} (${endDate})`;
                            }
                        });
                        
                        $('#activeSubscriptionMessage').text(message);
                        $('#activeSubscriptionWarning').show();
                    } else {
                        $('#activeSubscriptionWarning').hide();
                    }
                }
            }
        });
    }

    // Валидация дат
    const startsAt = document.getElementById('starts_at');
    const endsAt = document.getElementById('ends_at');
    
    function validateDates() {
        if (startsAt.value && endsAt.value) {
            if (new Date(endsAt.value) <= new Date(startsAt.value)) {
                endsAt.setCustomValidity('Дата окончания должна быть позже даты начала');
                return false;
            }
        }
        
        if (endsAt.value) {
            const today = new Date();
            today.setHours(0,0,0,0);
            if (new Date(endsAt.value) <= today) {
                endsAt.setCustomValidity('Дата окончания должна быть в будущем');
                return false;
            }
        }
        
        endsAt.setCustomValidity('');
        return true;
    }
    
    startsAt?.addEventListener('change', validateDates);
    endsAt?.addEventListener('change', validateDates);
    
    // Предотвращение двойной отправки
    const form = document.getElementById('createSubscriptionForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        if (!validateDates()) {
            e.preventDefault();
            return false;
        }
        
        const userId = $('#user_id').val();
        const status = document.getElementById('status').value;
        
        if (!userId || !status) {
            e.preventDefault();
            alert('Пожалуйста, заполните все обязательные поля');
            return false;
        }
        
        const name = document.getElementById('name').value;
        if (name && name.length > 255) {
            e.preventDefault();
            alert('Название подписки слишком длинное (максимум 255 символов)');
            return false;
        }
        
        if (status === 'active' && $('#activeSubscriptionWarning').is(':visible')) {
            if (!confirm('У пользователя уже есть активная подписка. Продолжить?')) {
                e.preventDefault();
                return false;
            }
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Создание...';
    });
});

// ========== ФУНКЦИИ ДЛЯ РАБОТЫ С ТИПАМИ ОТЧЕТОВ ==========

// Обновление выбранных типов и подсчет общего количества
function updateSelectedTypes() {
    const quantities = document.querySelectorAll('.quantity-input');
    const container = document.getElementById('reportTypesContainer');
    let total = 0;
    
    // Очищаем контейнер
    container.innerHTML = '';
    
    quantities.forEach(input => {
        const typeId = input.dataset.typeId;
        const value = parseInt(input.value) || 0;
        
        if (value > 0) {
            total += value;
            
            // Добавляем скрытое поле для каждого выбранного типа
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `report_types[]`;
            hiddenInput.value = typeId;
            container.appendChild(hiddenInput);
        }
    });
    
    // Обновляем общее количество
    document.getElementById('totalSelected').textContent = total;
    
    // Подсвечиваем строки с выбранными значениями
    quantities.forEach(input => {
        const row = input.closest('tr');
        if (parseInt(input.value) > 0) {
            row.classList.add('table-success');
        } else {
            row.classList.remove('table-success');
        }
    });
}

// Установка количества для конкретного типа
function setQuantity(typeId, amount) {
    const input = document.getElementById(`quantity_${typeId}`);
    if (input) {
        const current = parseInt(input.value) || 0;
        const max = parseInt(input.getAttribute('max')) || 999999;
        let newValue = current + amount;
        
        if (newValue < 0) newValue = 0;
        if (newValue > max) newValue = max;
        
        input.value = newValue;
        updateSelectedTypes();
    }
}

// Установка количества для всех типов
function setAllQuantities(amount) {
    const inputs = document.querySelectorAll('.quantity-input');
    inputs.forEach(input => {
        const current = parseInt(input.value) || 0;
        const max = parseInt(input.getAttribute('max')) || 999999;
        let newValue = current + amount;
        
        if (newValue < 0) newValue = 0;
        if (newValue > max) newValue = max;
        
        input.value = newValue;
    });
    updateSelectedTypes();
}

// Сброс всех количеств
function resetAllQuantities() {
    const inputs = document.querySelectorAll('.quantity-input');
    inputs.forEach(input => {
        input.value = 0;
    });
    updateSelectedTypes();
}

// Добавляем обработчики событий после загрузки страницы
document.addEventListener('DOMContentLoaded', function() {
    // Добавляем обработчики для всех инпутов
    const inputs = document.querySelectorAll('.quantity-input');
    inputs.forEach(input => {
        input.addEventListener('input', updateSelectedTypes);
        input.addEventListener('change', updateSelectedTypes);
    });
    
    // Инициализируем скрытое поле
    updateSelectedTypes();
});
</script>
@endpush