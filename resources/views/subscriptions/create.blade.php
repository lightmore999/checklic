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
                    
                    <form action="{{ route('subscriptions.store') }}" method="POST" id="createSubscriptionForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Пользователь -->
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">
                                        <i class="bi bi-person me-1"></i>
                                        Пользователь *
                                    </label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" 
                                            id="user_id" name="user_id" required>
                                        <option value="">Выберите пользователя</option>
                                        @foreach($users as $userOption)
                                            <option value="{{ $userOption->id }}" 
                                                {{ old('user_id') == $userOption->id ? 'selected' : '' }}
                                                data-role="{{ $userOption->role }}">
                                                {{ $userOption->name }} ({{ $userOption->email }}) - 
                                                <span class="text-muted">{{ $userOption->getRoleDisplayName() }}</span>
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Выберите пользователя, которому будет принадлежать подписка</div>
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
                        
                        <!-- Информационный блок -->
                        <div class="alert alert-info mt-3" id="userInfo" style="display: none;">
                            <i class="bi bi-info-circle me-2"></i>
                            <span id="userInfoText"></span>
                        </div>
                        
                        <div class="alert alert-warning mt-3" id="activeSubscriptionWarning" style="display: none;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Внимание!</strong> У выбранного пользователя уже есть активная подписка.
                            <span id="activeSubscriptionDetails"></span>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const userSelect = document.getElementById('user_id');
    const statusSelect = document.getElementById('status');
    const startsAtInput = document.getElementById('starts_at');
    const endsAtInput = document.getElementById('ends_at');
    const userInfo = document.getElementById('userInfo');
    const userInfoText = document.getElementById('userInfoText');
    const activeSubscriptionWarning = document.getElementById('activeSubscriptionWarning');
    const activeSubscriptionDetails = document.getElementById('activeSubscriptionDetails');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('createSubscriptionForm');
    
    // Объект для хранения информации о пользователях
    const users = {
        @foreach($users as $userOption)
            {{ $userOption->id }}: {
                name: '{{ $userOption->name }}',
                email: '{{ $userOption->email }}',
                role: '{{ $userOption->role }}',
                roleDisplay: '{{ $userOption->getRoleDisplayName() }}'
            },
        @endforeach
    };
    
    // При выборе пользователя
    userSelect.addEventListener('change', function() {
        const userId = this.value;
        
        if (userId) {
            const user = users[userId];
            
            // Показываем информацию о пользователе
            userInfo.style.display = 'block';
            userInfoText.innerHTML = `Выбран пользователь: <strong>${user.name}</strong> (${user.email}) - ${user.roleDisplay}`;
            
            // Проверяем наличие активной подписки (через API)
            checkActiveSubscription(userId);
        } else {
            userInfo.style.display = 'none';
            activeSubscriptionWarning.style.display = 'none';
        }
    });
    
    // Проверка активной подписки
    function checkActiveSubscription(userId) {
        fetch(`/api/users/${userId}/subscription/check`)
            .then(response => response.json())
            .then(data => {
                if (data.has_active_subscription) {
                    const sub = data.subscription;
                    let details = '';
                    
                    if (sub.ends_at) {
                        details = `Действует до: ${sub.ends_at}`;
                        if (sub.remaining_days !== null) {
                            details += ` (осталось ${sub.remaining_days} дн.)`;
                        }
                    } else {
                        details = 'Бессрочная';
                    }
                    
                    activeSubscriptionDetails.innerHTML = details;
                    activeSubscriptionWarning.style.display = 'block';
                    
                    // Если пытаемся создать активную подписку, показываем предупреждение
                    if (statusSelect.value === 'active') {
                        // Можно добавить логику блокировки
                    }
                } else {
                    activeSubscriptionWarning.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Ошибка при проверке подписки:', error);
            });
    }
    
    // Валидация дат
    function validateDates() {
        const startsAt = startsAtInput.value;
        const endsAt = endsAtInput.value;
        
        if (startsAt && endsAt) {
            if (new Date(endsAt) <= new Date(startsAt)) {
                endsAtInput.setCustomValidity('Дата окончания должна быть позже даты начала');
                return false;
            } else {
                endsAtInput.setCustomValidity('');
            }
        }
        
        if (endsAt) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const endDate = new Date(endsAt);
            
            if (endDate <= today) {
                endsAtInput.setCustomValidity('Дата окончания должна быть в будущем');
                return false;
            } else {
                endsAtInput.setCustomValidity('');
            }
        }
        
        return true;
    }
    
    startsAtInput.addEventListener('change', validateDates);
    endsAtInput.addEventListener('change', validateDates);
    
    // Предотвращение двойной отправки
    form.addEventListener('submit', function(e) {
        if (!validateDates()) {
            e.preventDefault();
            return false;
        }
        
        const userId = userSelect.value;
        const status = statusSelect.value;
        
        if (!userId || !status) {
            e.preventDefault();
            alert('Пожалуйста, заполните все обязательные поля');
            return false;
        }
        
        // Дополнительная проверка при создании активной подписки
        if (status === 'active' && activeSubscriptionWarning.style.display === 'block') {
            if (!confirm('У пользователя уже есть активная подписка. Продолжить создание новой?')) {
                e.preventDefault();
                return false;
            }
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Создание...';
    });
    
    // Триггерим изменение при загрузке, если есть старое значение
    if (userSelect.value) {
        userSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush

@push('styles')
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
</style>
@endpush
@endsection