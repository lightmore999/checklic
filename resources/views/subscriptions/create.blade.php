@extends('layouts.app')

@section('title', 'Создание подписки')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-stars me-2"></i>
                        Создание новой подписки
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Назад к списку
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Ошибка!</strong> Пожалуйста, исправьте следующие ошибки:
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
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
                    
                    <form action="{{ route('subscriptions.store') }}" method="POST" id="createSubscriptionForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Пользователь с поиском -->
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">
                                        <i class="bi bi-person me-1"></i>
                                        Пользователь *
                                    </label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" 
                                            id="user_id" name="user_id" required
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
                                
                                <!-- Статус -->
                                <div class="mb-3">
                                    <label for="status" class="form-label">
                                        <i class="bi bi-flag me-1"></i>
                                        Статус подписки *
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
                                    <label for="starts_at" class="form-label">
                                        <i class="bi bi-calendar-plus me-1"></i>
                                        Дата начала
                                    </label>
                                    <input type="date" class="form-control @error('starts_at') is-invalid @enderror" 
                                           id="starts_at" name="starts_at" 
                                           value="{{ old('starts_at', now()->format('Y-m-d')) }}">
                                    @error('starts_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Оставьте пустым для автоматической установки на сегодня</div>
                                </div>
                                
                                <!-- Дата окончания -->
                                <div class="mb-3">
                                    <label for="ends_at" class="form-label">
                                        <i class="bi bi-calendar-x me-1"></i>
                                        Дата окончания
                                    </label>
                                    <input type="date" class="form-control @error('ends_at') is-invalid @enderror" 
                                           id="ends_at" name="ends_at" 
                                           value="{{ old('ends_at') }}"
                                           min="{{ now()->addDay()->format('Y-m-d') }}">
                                    @error('ends_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Оставьте пустым для бессрочной подписки</div>
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
                                                <small class="text-muted d-block">Email</small>
                                                <span class="fw-bold" id="userEmail"></span>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <small class="text-muted d-block">Организация</small>
                                                <span class="fw-bold" id="userOrganization"></span>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <small class="text-muted d-block">Активных подписок</small>
                                                <span class="fw-bold" id="userActiveSubscriptions">0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Предупреждение об активной подписке -->
                        <div class="row mt-3" id="activeSubscriptionWarning" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Внимание!</strong> У выбранного пользователя уже есть активная подписка.
                                    <div id="activeSubscriptionDetails" class="mt-2 small"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Кнопки -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Отмена
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check-circle me-1"></i> Создать подписку
                            </button>
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
    .form-label {
        font-weight: 500;
    }
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .alert {
        margin-bottom: 1rem;
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
    /* Компактный вид для результатов поиска */
    .select2-results__option {
        padding: 6px 12px !important;
        font-size: 0.95rem;
    }
    .select2-results__option small {
        color: #6c757d;
        font-size: 0.85rem;
    }
    .subscription-badge {
        background-color: #e7f1ff;
        color: #0d6efd;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.75rem;
        margin-left: 6px;
        white-space: nowrap;
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
    console.log('Инициализация создания подписки...');
    
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
                        return {
                            id: user.id,
                            text: user.name + ' (' + user.email + ')',
                            name: user.name,
                            email: user.email,
                            role: user.role_display,
                            organization: user.organization || 'Нет организации',
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
        
        let status = user.has_active ? '<span class="subscription-badge"><i class="bi bi-check-circle-fill" style="font-size: 0.7rem;"></i> есть подписка</span>' : '';
        
        return $(`
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>${user.name}</strong> <small>${user.email}</small><br>
                    <small class="text-muted"><i class="bi bi-building"></i> ${user.organization} • ${user.role}</small>
                </div>
                <div>${status}</div>
            </div>
        `);
    }

    function formatUserSelection(user) {
        return user.name || user.text;
    }

    // Обработка изменения фильтра по организации
    $('#filter_organization_id').on('change', function() {
        $('#user_id').val(null).trigger('change');
        $('#userInfo').hide();
        $('#activeSubscriptionWarning').hide();
    });

    // Обработка выбора пользователя
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
                        let details = '<ul class="mb-0 mt-1 ps-3">';
                        response.subscriptions.forEach(function(sub) {
                            if (sub.status === 'active') {
                                let endDate = sub.ends_at ? `до ${sub.ends_at}` : 'бессрочно';
                                details += `<li>Подписка #${sub.id} (${endDate})</li>`;
                            }
                        });
                        details += '</ul>';
                        
                        $('#activeSubscriptionDetails').html(details);
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
        
        const warning = document.getElementById('activeSubscriptionWarning');
        if (status === 'active' && warning.style.display === 'block') {
            if (!confirm('У пользователя уже есть активная подписка. Продолжить?')) {
                e.preventDefault();
                return false;
            }
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Создание...';
    });
});
</script>
@endpush