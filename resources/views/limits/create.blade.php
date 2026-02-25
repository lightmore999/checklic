@extends('layouts.app')

@section('title', 'Создание отчета')
@section('page-icon', 'bi-plus-circle')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Хлебные крошки -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('limits.index') }}">Отчеты</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Создание нового отчета</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3 d-flex align-items-center">
                    <i class="bi bi-plus-circle fs-4 me-2"></i>
                    <h4 class="card-title mb-0">Создание нового отчета</h4>
                </div>
                
                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('limits.store') }}" id="limitForm">
                        @csrf

                        <!-- Фильтр по организации -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card border-primary bg-light">
                                    <div class="card-header bg-primary text-white py-2">
                                        <h6 class="mb-0">
                                            <i class="bi bi-funnel me-2"></i>
                                            Фильтр по организации
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label class="form-label">Организация</label>
                                                <select name="filter_organization_id" id="filter_organization_id" class="form-select">
                                                    <option value="">Все организации</option>
                                                    @if(isset($organizations) && $organizations->count() > 0)
                                                        @foreach($organizations as $org)
                                                            <option value="{{ $org->id }}">{{ $org->name }} @if($org->inn) (ИНН: {{ $org->inn }}) @endif</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <div class="form-text">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    Выберите организацию для фильтрации списка пользователей
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Шаг 1: Выбор пользователя -->
                        <div class="step-container mb-4">
                            <div class="step-header bg-light p-3 rounded-top border-bottom">
                                <h5 class="mb-0 text-primary">
                                    <span class="step-number bg-primary text-white rounded-circle me-2">1</span>
                                    Выберите пользователя
                                </h5>
                            </div>
                            <div class="step-body p-4 border rounded-bottom">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="user_id" class="form-label fw-bold">
                                                <i class="bi bi-person me-1"></i>
                                                Пользователь *
                                            </label>
                                            <select name="user_id" id="user_id" 
                                                    class="form-select @error('user_id') is-invalid @enderror" 
                                                    required
                                                    style="width: 100%;">
                                                <option value=""></option>
                                            </select>
                                            @error('user_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Начните вводить имя, email или выберите из списка
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Информация о выбранном пользователе -->
                                <div class="row mt-3" id="userInfo" style="display: none;">
                                    <div class="col-12">
                                        <div class="card border-info bg-light">
                                            <div class="card-header bg-info text-white py-2">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    Информация о пользователе
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3 mb-2">
                                                        <small class="text-muted d-block">Роль</small>
                                                        <span class="fw-bold" id="userRole"></span>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <small class="text-muted d-block">Организация</small>
                                                        <span class="fw-bold" id="userOrganization"></span>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <small class="text-muted d-block">Email</small>
                                                        <span class="fw-bold" id="userEmail"></span>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <small class="text-muted d-block">Всего подписок</small>
                                                        <span class="fw-bold" id="userSubscriptionsCount">0</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Предупреждение об отсутствии подписок -->
                                <div class="row mt-3" id="noSubscriptionWarning" style="display: none;">
                                    <div class="col-12">
                                        <div class="alert alert-danger mb-0">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong>Внимание!</strong> У выбранного пользователя нет активных подписок.
                                            Создание отчетов невозможно.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Шаг 2: Выбор подписки (появляется после выбора пользователя) -->
                        <div class="step-container mb-4" id="subscriptionStep" style="display: none;">
                            <div class="step-header bg-light p-3 rounded-top border-bottom">
                                <h5 class="mb-0 text-primary">
                                    <span class="step-number bg-primary text-white rounded-circle me-2">2</span>
                                    Выберите подписку
                                </h5>
                            </div>
                            <div class="step-body p-4 border rounded-bottom">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="subscription_id" class="form-label fw-bold">
                                                <i class="bi bi-stars me-1"></i>
                                                Подписка *
                                            </label>
                                            <select name="subscription_id" id="subscription_id" 
                                                    class="form-select @error('subscription_id') is-invalid @enderror" 
                                                    required
                                                    style="width: 100%;">
                                                <option value="">Сначала выберите пользователя</option>
                                            </select>
                                            @error('subscription_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Выберите подписку, в которую будет добавлен отчет
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Информация о выбранной подписке -->
                                <div class="row mt-3" id="subscriptionInfo" style="display: none;">
                                    <div class="col-12">
                                        <div class="card border-success bg-light">
                                            <div class="card-header bg-success text-white py-2">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-stars me-2"></i>
                                                    Информация о подписке
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3 mb-2">
                                                        <small class="text-muted d-block">Статус</small>
                                                        <span id="subscriptionStatus"></span>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <small class="text-muted d-block">Действует до</small>
                                                        <span class="fw-bold" id="subscriptionEnds"></span>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <small class="text-muted d-block">Осталось дней</small>
                                                        <span class="fw-bold" id="subscriptionRemaining"></span>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <small class="text-muted d-block">Отчетов в подписке</small>
                                                        <span class="fw-bold" id="subscriptionLimitsCount">0</span>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-12">
                                                        <div class="progress" style="height: 6px;" id="subscriptionProgress" style="display: none;">
                                                            <div class="progress-bar" role="progressbar" style="width: 0%;" id="subscriptionProgressBar"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Шаг 3: Параметры отчета -->
                        <div class="step-container mb-4" id="reportStep" style="display: none;">
                            <div class="step-header bg-light p-3 rounded-top border-bottom">
                                <h5 class="mb-0 text-primary">
                                    <span class="step-number bg-primary text-white rounded-circle me-2">3</span>
                                    Параметры отчета
                                </h5>
                            </div>
                            <div class="step-body p-4 border rounded-bottom">
                                <div class="row">
                                    <div class="col-md-6">
                                        <!-- Тип отчета -->
                                        <div class="mb-4">
                                            <label for="report_type_id" class="form-label fw-bold">
                                                <i class="bi bi-file-text me-1"></i>
                                                Тип отчета *
                                            </label>
                                            <select name="report_type_id" id="report_type_id" 
                                                    class="form-select @error('report_type_id') is-invalid @enderror" 
                                                    required
                                                    style="width: 100%;">
                                                <option value=""></option>
                                                @foreach($reportTypes as $type)
                                                    <option value="{{ $type->id }}" 
                                                        {{ old('report_type_id') == $type->id ? 'selected' : '' }}
                                                        data-api="{{ $type->only_api ? 'true' : 'false' }}">
                                                        {{ $type->name }}
                                                        @if($type->only_api)
                                                            (только API)
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('report_type_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <!-- Количество -->
                                        <div class="mb-3">
                                            <label for="quantity" class="form-label fw-bold">
                                                <i class="bi bi-sort-numeric-up me-1"></i>
                                                Количество отчетов *
                                            </label>
                                            <div class="input-group">
                                                <input type="number" name="quantity" id="quantity" 
                                                       class="form-control @error('quantity') is-invalid @enderror"
                                                       value="{{ old('quantity', 1) }}" 
                                                       min="1" 
                                                       max="9999"
                                                       required>
                                                <span class="input-group-text">шт.</span>
                                                @error('quantity')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-text">Минимум 1, максимум 9999</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-primary mb-3" onclick="setDefaultValues()">
                                            <i class="bi bi-lightning me-2"></i>
                                            Быстрые значения
                                        </button>
                                    </div>
                                </div>

                                <!-- Информация о типе отчета -->
                                <div class="row mt-2" id="reportTypeInfo" style="display: none;">
                                    <div class="col-12">
                                        <div class="alert alert-warning mb-0" id="apiOnlyWarning">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <span id="reportTypeMessage"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопки действий -->
                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4" id="submitBtn" disabled>
                                    <i class="bi bi-check-circle me-2"></i>
                                    Создать отчет
                                </button>
                                <a href="{{ route('limits.index') }}" class="btn btn-outline-secondary px-4">
                                    <i class="bi bi-x-circle me-2"></i>
                                    Отмена
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('limits.bulk-create') }}" class="btn btn-info">
                                    <i class="bi bi-files me-2"></i>
                                    Массовое создание
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
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
    
    .card {
        border-radius: 10px;
    }
    .card-header {
        border-radius: 10px 10px 0 0 !important;
    }
    
    .step-number {
        display: inline-flex;
        width: 28px;
        height: 28px;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    
    .step-header {
        background-color: #f8f9fa;
    }
    
    .step-body {
        background-color: white;
    }
    
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .progress {
        border-radius: 10px;
    }
    
    .badge {
        font-size: 85%;
    }
    
    .btn {
        border-radius: 6px;
    }
    
    /* Анимации */
    .step-container {
        transition: all 0.3s ease;
    }
    
    .step-body {
        transition: all 0.3s ease;
    }
    
    /* Адаптивность */
    @media (max-width: 768px) {
        .step-number {
            width: 24px;
            height: 24px;
            font-size: 0.9rem;
        }
        
        .card-body {
            padding: 1rem !important;
        }
    }
</style>
@endpush

@push('scripts')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/ru.js"></script>

<script>
$(document).ready(function() {
    console.log('Инициализация создания лимита...');
    console.log('Organizations available:', $('#filter_organization_id option').length);
    
    // Инициализация Select2 для пользователей с поддержкой поиска
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
                    roles: ['org_owner', 'org_member', 'manager']
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(user) {
                        return {
                            id: user.id,
                            text: user.name + ' (' + user.email + ')',
                            name: user.name,
                            email: user.email,
                            role: user.role,
                            role_display: user.role_display,
                            organization: user.organization || 'Не указана',
                            has_active_subscriptions: user.has_active_subscriptions || false
                        };
                    })
                };
            },
            cache: true
        }
    });

    // Обработка изменения фильтра по организации
    $('#filter_organization_id').on('change', function() {
        // Очищаем выбранного пользователя
        $('#user_id').val(null).trigger('change');
        
        // Скрываем все последующие блоки
        $('#userInfo').hide();
        $('#subscriptionStep').hide();
        $('#reportStep').hide();
        $('#subscription_id').empty().prop('disabled', true).trigger('change');
        $('#subscriptionInfo').hide();
        $('#noSubscriptionWarning').hide();
        $('#submitBtn').prop('disabled', true);
        
        // Сбрасываем счетчик подписок
        $('#userSubscriptionsCount').text('0');
    });

    // Инициализация Select2 для подписок
    $('#subscription_id').select2({
        theme: 'default',
        language: 'ru',
        placeholder: 'Выберите подписку',
        allowClear: true,
        width: '100%',
        disabled: true
    });

    // Инициализация Select2 для типов отчетов
    $('#report_type_id').select2({
        placeholder: 'Выберите тип отчета...',
        allowClear: true,
        language: 'ru',
        width: '100%'
    });

    // Обработка выбора пользователя
    $('#user_id').on('select2:select', function(e) {
        var data = e.params.data;
        
        // Показываем информацию о пользователе
        $('#userInfo').show();
        $('#userRole').text(data.role_display || 'Не указана');
        $('#userOrganization').text(data.organization || 'Не указана');
        $('#userEmail').text(data.email || 'Не указан');
        
        // Загружаем подписки пользователя
        loadUserSubscriptions(data.id);
    });

    $('#user_id').on('select2:clear', function() {
        // Скрываем все блоки
        $('#userInfo').hide();
        $('#subscriptionStep').hide();
        $('#reportStep').hide();
        $('#subscription_id').empty().prop('disabled', true).trigger('change');
        $('#subscriptionInfo').hide();
        $('#noSubscriptionWarning').hide();
        $('#submitBtn').prop('disabled', true);
        
        // Сбрасываем значения
        $('#userSubscriptionsCount').text('0');
    });

    // Функция загрузки подписок пользователя
    function loadUserSubscriptions(userId) {
        $.ajax({
            url: '{{ url("api/users") }}/' + userId + '/subscriptions',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.subscriptions && response.subscriptions.length > 0) {
                    // Показываем шаг выбора подписки
                    $('#subscriptionStep').show();
                    $('#noSubscriptionWarning').hide();
                    
                    // Обновляем счетчик подписок
                    $('#userSubscriptionsCount').text(response.subscriptions.length);
                    
                    // Заполняем select подписками
                    let options = '<option value="">Выберите подписку</option>';
                    $.each(response.subscriptions, function(index, sub) {
                        let statusClass = sub.status === 'active' ? 'success' : 
                                         (sub.status === 'expired' ? 'danger' : 'warning');
                        let statusText = sub.status === 'active' ? 'Активна' :
                                        (sub.status === 'expired' ? 'Истекла' : 
                                        (sub.status === 'pending' ? 'Ожидает' : 'Приостановлена'));
                        
                        let endDate = sub.ends_at || 'Бессрочно';
                        let limitsCount = sub.limits_count || 0;
                        
                        options += `<option value="${sub.id}" 
                                         data-status="${sub.status}"
                                         data-status-class="${statusClass}"
                                         data-status-text="${statusText}"
                                         data-ends-at="${endDate}"
                                         data-remaining-days="${sub.remaining_days || ''}"
                                         data-limits-count="${limitsCount}">`;
                        options += `Подписка #${sub.id} - ${statusText}`;
                        if (sub.ends_at) {
                            options += ` до ${sub.ends_at}`;
                        } else {
                            options += ` (бессрочно)`;
                        }
                        options += ` - отчетов: ${limitsCount}`;
                        options += `</option>`;
                    });
                    
                    $('#subscription_id').html(options).prop('disabled', false).trigger('change');
                    
                } else {
                    // Нет активных подписок
                    $('#subscriptionStep').hide();
                    $('#noSubscriptionWarning').show();
                    $('#submitBtn').prop('disabled', true);
                    $('#userSubscriptionsCount').text('0');
                }
            },
            error: function(xhr, status, error) {
                console.error('Ошибка загрузки подписок:', error);
                $('#subscriptionStep').hide();
                $('#noSubscriptionWarning').show();
                $('#submitBtn').prop('disabled', true);
                $('#userSubscriptionsCount').text('0');
            }
        });
    }

    // Обработка выбора подписки
    $('#subscription_id').on('change', function() {
        var selected = $(this).find(':selected');
        
        if (selected.val()) {
            $('#subscriptionInfo').show();
            $('#reportStep').show();
            $('#submitBtn').prop('disabled', false);
            
            // Отображаем информацию о подписке
            let statusClass = selected.data('status-class') || 'secondary';
            let statusText = selected.data('status-text') || 'Неизвестно';
            let endsAt = selected.data('ends-at') || '—';
            let remainingDays = selected.data('remaining-days');
            let limitsCount = selected.data('limits-count') || 0;
            
            $('#subscriptionStatus').html('<span class="badge bg-' + statusClass + '">' + statusText + '</span>');
            $('#subscriptionEnds').text(endsAt);
            
            if (remainingDays) {
                $('#subscriptionRemaining').text(remainingDays + ' дн.');
            } else {
                $('#subscriptionRemaining').text('∞');
            }
            
            $('#subscriptionLimitsCount').text(limitsCount);
            
            // Прогресс-бар (примерный)
            if (remainingDays && remainingDays !== 'null') {
                let totalDays = 365;
                let usedPercentage = Math.min(100, Math.round(((totalDays - remainingDays) / totalDays) * 100));
                $('#subscriptionProgress').show();
                $('#subscriptionProgressBar').css('width', usedPercentage + '%');
                $('#subscriptionProgressBar').removeClass('bg-success bg-warning bg-danger');
                
                if (usedPercentage > 80) {
                    $('#subscriptionProgressBar').addClass('bg-danger');
                } else if (usedPercentage > 50) {
                    $('#subscriptionProgressBar').addClass('bg-warning');
                } else {
                    $('#subscriptionProgressBar').addClass('bg-success');
                }
            } else {
                $('#subscriptionProgress').hide();
            }
        } else {
            $('#subscriptionInfo').hide();
            $('#reportStep').hide();
            $('#submitBtn').prop('disabled', true);
        }
    });

    // Обработка выбора типа отчета
    $('#report_type_id').on('select2:select', function(e) {
        var selected = $(this).find(':selected');
        var isApiOnly = selected.data('api') === 'true';
        
        if (isApiOnly) {
            $('#reportTypeInfo').show();
            $('#reportTypeMessage').text('Выбранный тип отчета доступен только через API. В интерфейсе он будет отображаться, но создавать отчеты через форму нельзя.');
        } else {
            $('#reportTypeInfo').hide();
        }
    });

    $('#report_type_id').on('select2:clear', function() {
        $('#reportTypeInfo').hide();
    });

    // Валидация формы перед отправкой
    $('#limitForm').on('submit', function(e) {
        var userId = $('#user_id').val();
        var subscriptionId = $('#subscription_id').val();
        var reportTypeId = $('#report_type_id').val();
        var quantity = parseInt($('#quantity').val());
        
        if (!userId) {
            e.preventDefault();
            alert('Ошибка: Выберите пользователя');
            return false;
        }
        
        if (!subscriptionId) {
            e.preventDefault();
            alert('Ошибка: Выберите подписку');
            return false;
        }
        
        if (!reportTypeId) {
            e.preventDefault();
            alert('Ошибка: Выберите тип отчета');
            return false;
        }
        
        if (quantity <= 0) {
            e.preventDefault();
            alert('Ошибка: Количество должно быть больше 0');
            return false;
        }
        
        // Блокируем кнопку отправки
        $('#submitBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i> Создание...');
    });

    // Быстрые значения
    window.setDefaultValues = function() {
        $('#quantity').val(10);
        $('#date_created').val('{{ now()->addDays(7)->format("Y-m-d") }}');
    };
});
</script>
@endpush