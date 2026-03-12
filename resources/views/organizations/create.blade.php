@extends('layouts.app')

@section('title', 'Создание организации')
@section('page-icon', 'bi-building-add')

@section('content')
<div class="container-fluid py-4">
    <!-- Заголовок с градиентом -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-success text-white shadow-lg" 
                 style="background: linear-gradient(135deg, #198754 0%, #146c43 100%);">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-4" 
                                 style="width: 70px; height: 70px; font-size: 2rem; font-weight: 500; color: white; border: 3px solid rgba(255,255,255,0.3);">
                                <i class="bi bi-building-add"></i>
                            </div>
                            <div>
                                <h1 class="h2 mb-2">Создание новой организации</h1>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-white text-success px-3 py-2">
                                        <i class="bi bi-building me-1"></i>Новая организация
                                    </span>
                                    <span class="badge bg-white bg-opacity-25 px-3 py-2">
                                        <i class="bi bi-calendar me-1"></i>{{ now()->format('d.m.Y') }}
                                    </span>
                                </div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ $user->isAdmin() ? route('admin.dashboard') : route('manager.dashboard') }}" 
                                               class="text-white opacity-75">
                                                Панель {{ $user->isAdmin() ? 'админа' : 'менеджера' }}
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item">
                                            <a href="{{ $user->isAdmin() ? route('admin.organizations.list') : route('manager.organizations.list') }}" 
                                               class="text-white opacity-75">
                                                Организации
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
                            <a href="{{ $user->isAdmin() ? route('admin.organizations.list') : route('manager.organizations.list') }}" 
                               class="btn btn-light">
                                <i class="bi bi-arrow-left me-2"></i>Назад
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Флеш-сообщения -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>
                    <strong>Успешно!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
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

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Основная карточка с формой -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle text-success me-2"></i>
                        Информация об организации
                    </h5>
                </div>
                
                <div class="card-body pt-3">
                    <form method="POST" 
                          action="{{ $user->isAdmin() ? route('admin.organization.store') : route('manager.organization.store') }}" 
                          id="createOrganizationForm">
                        @csrf
                        
                        <!-- Раздел организации -->
                        <div class="mb-4">
                            <h6 class="mb-3 fw-semibold">
                                <i class="bi bi-building text-success me-2"></i>
                                Данные организации
                            </h6>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-tag text-success me-1"></i>
                                        Название организации <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('organization.name') is-invalid @enderror" 
                                           name="organization[name]" value="{{ old('organization.name') }}" 
                                           placeholder="Введите название" required>
                                    @error('organization.name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Уникальное название организации</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-star text-success me-1"></i>
                                        Наша организация <small class="text-muted">(необязательно)</small>
                                    </label>
                                    <input type="text" class="form-control @error('organization.our_organization') is-invalid @enderror" 
                                           name="organization[our_organization]" value="{{ old('organization.our_organization') }}" 
                                           placeholder="Например: ООО Ромашка">
                                    @error('organization.our_organization')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Полное название или бренд организации</small>
                                </div>
                            </div>
                            
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-file-text text-success me-1"></i>
                                        ИНН
                                    </label>
                                    <input type="text" class="form-control @error('organization.inn') is-invalid @enderror" 
                                           name="organization[inn]" value="{{ old('organization.inn') }}" 
                                           placeholder="Введите ИНН" 
                                           maxlength="12"
                                           pattern="[0-9]{10,12}"
                                           title="ИНН должен содержать от 10 до 12 цифр">
                                    @error('organization.inn')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">ИНН организации (10 цифр для юрлиц, 12 для ИП)</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-flag text-success me-1"></i>
                                        Статус <span class="text-danger">*</span>
                                    </label>
                                    @if($user->isAdmin())
                                        <select class="form-select @error('organization.status') is-invalid @enderror" 
                                                name="organization[status]" required>
                                            <option value="">Выберите статус</option>
                                            <option value="active" {{ old('organization.status') == 'active' ? 'selected' : '' }}>Активна</option>
                                            <option value="suspended" {{ old('organization.status') == 'suspended' ? 'selected' : '' }}>Приостановлена</option>
                                            <option value="expired" {{ old('organization.status') == 'expired' ? 'selected' : '' }}>Истекла</option>
                                        </select>
                                    @else
                                        <select class="form-select @error('organization.status') is-invalid @enderror" 
                                                name="organization[status]" required>
                                            <option value="">Выберите статус</option>
                                            <option value="active" {{ old('organization.status') == 'active' ? 'selected' : '' }}>Активна</option>
                                            <option value="inactive" {{ old('organization.status') == 'inactive' ? 'selected' : '' }}>Неактивна</option>
                                        </select>
                                    @endif
                                    @error('organization.status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-people text-success me-1"></i>
                                        Максимальное количество сотрудников
                                    </label>
                                    <input type="number" class="form-control @error('organization.max_employees') is-invalid @enderror" 
                                           name="organization[max_employees]" value="{{ old('organization.max_employees') }}" 
                                           placeholder="Например: 50"
                                           min="1"
                                           step="1">
                                    @error('organization.max_employees')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Оставьте пустым для безлимитного количества</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person-badge text-success me-1"></i>
                                        Ответственный менеджер
                                    </label>
                                    @if($user->isManager())
                                        {{-- Для менеджера - скрытое поле с его ID --}}
                                        <input type="hidden" name="manager_id" value="{{ $user->id }}">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 32px; height: 32px; font-size: 0.9rem; color: #0d6efd;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="fw-semibold">{{ $user->name }}</span>
                                                <small class="text-muted d-block">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-1">Вы являетесь менеджером этой организации</small>
                                    @else
                                        {{-- Для админа показываем выбор менеджера с поиском --}}
                                        <select class="form-select select2-manager @error('manager_id') is-invalid @enderror" 
                                                id="manager_id" name="manager_id" style="width: 100%;">
                                            <option value="">-- Без менеджера --</option>
                                            <option value="{{ $user->id }}" {{ old('manager_id') == $user->id ? 'selected' : '' }}>
                                                Я буду менеджером ({{ $user->name }})
                                            </option>
                                            @foreach($managers as $manager)
                                                <option value="{{ $manager->id }}" 
                                                        {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                                    {{ $manager->name }} ({{ $manager->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('manager_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Начните вводить имя менеджера для поиска</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Раздел владельца -->
                        <div class="mb-4 pt-4 border-top">
                            <h6 class="mb-3 fw-semibold">
                                <i class="bi bi-person text-success me-2"></i>
                                Данные владельца организации
                            </h6>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person-circle text-success me-1"></i>
                                        Имя владельца <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('user.name') is-invalid @enderror" 
                                           name="user[name]" value="{{ old('user.name') }}" 
                                           placeholder="Введите имя" required>
                                    @error('user.name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-envelope text-success me-1"></i>
                                        Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control @error('user.email') is-invalid @enderror" 
                                           name="user[email]" value="{{ old('user.email') }}" 
                                           placeholder="email@example.com" required>
                                    @error('user.email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Номер телефона владельца -->
                            <div class="row g-3 mt-2">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-telephone text-success me-1"></i>
                                        Номер телефона владельца <small class="text-muted">(необязательно)</small>
                                    </label>
                                    <input type="tel" class="form-control @error('user.phone') is-invalid @enderror" 
                                           id="user_phone" name="user[phone]" value="{{ old('user.phone') }}" 
                                           placeholder="+7 (999) 999-99-99">
                                    @error('user.phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Контактный телефон владельца организации</small>
                                </div>
                            </div>
                            
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-key text-success me-1"></i>
                                        Пароль <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('user.password') is-invalid @enderror" 
                                               name="user[password]" placeholder="Минимум 8 символов" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    @error('user.password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-check-circle text-success me-1"></i>
                                        Подтверждение пароля <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" 
                                               name="user[password_confirmation]" placeholder="Повторите пароль" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Информационные подсказки -->
                        <div class="alert alert-info d-flex align-items-center mb-4">
                            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                            <div>
                                <strong>Обратите внимание:</strong>
                                <ul class="mb-0 mt-1 ps-3">
                                    <li><strong>Наша организация</strong> - полное название или бренд, под которым будет видна организация</li>
                                    <li>ИНН должен содержать 10 цифр для юридических лиц или 12 для ИП</li>
                                    <li>Лимит сотрудников можно указать для ограничения количества добавляемых сотрудников</li>
                                    <li>Если лимит не указан, сотрудников можно добавлять без ограничений</li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Кнопки -->
                        <div class="d-flex gap-2 pt-3 border-top">
                            <a href="{{ $user->isAdmin() ? route('admin.organizations.list') : route('manager.organizations.list') }}" 
                               class="btn btn-secondary">
                                <i class="bi bi-x-lg me-2"></i> Отмена
                            </a>
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="bi bi-check-lg me-2"></i> Создать организацию
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
    .bg-gradient-success {
        background: linear-gradient(135deg, #198754 0%, #146c43 100%);
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
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ru.js"></script>
<!-- Inputmask для красивого ввода телефона -->
<script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/inputmask.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Инициализация Select2 для менеджеров
        $('.select2-manager').select2({
            theme: 'default',
            language: 'ru',
            placeholder: 'Поиск менеджера...',
            allowClear: true,
            width: '100%'
        });

        // Маска для телефона владельца
        const phoneField = document.getElementById('user_phone');
        if (phoneField) {
            Inputmask({ 
                mask: ['+7 (999) 999-99-99', '+375 (99) 999-99-99', '+999 (99) 999-99-99'],
                keepStatic: true,
                showMaskOnHover: false,
                clearIncomplete: true
            }).mask(phoneField);
        }

        // Валидация ИНН (только цифры)
        const innField = document.querySelector('input[name="organization[inn]"]');
        if (innField) {
            innField.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
        
        // Валидация максимального количества сотрудников (только положительные числа)
        const maxEmployeesField = document.querySelector('input[name="organization[max_employees]"]');
        if (maxEmployeesField) {
            maxEmployeesField.addEventListener('input', function(e) {
                let value = parseInt(this.value);
                if (value < 1) {
                    this.value = '';
                }
            });
        }
        
        // Переключение видимости пароля
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });
        
        // Валидация формы
        const form = document.getElementById('createOrganizationForm');
        const submitBtn = document.getElementById('submitBtn');
        
        form.addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="user[password]"]').value;
            const confirmPassword = document.querySelector('input[name="user[password_confirmation]"]').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Пароли не совпадают!');
                document.querySelector('input[name="user[password]"]').focus();
                return false;
            }
            
            // Проверка ИНН если он заполнен
            const inn = document.querySelector('input[name="organization[inn]"]').value;
            if (inn && (inn.length < 10 || inn.length > 12)) {
                e.preventDefault();
                alert('ИНН должен содержать от 10 до 12 цифр');
                document.querySelector('input[name="organization[inn]"]').focus();
                return false;
            }
            
            // Проверка максимального количества сотрудников
            const maxEmployees = document.querySelector('input[name="organization[max_employees]"]').value;
            if (maxEmployees && parseInt(maxEmployees) < 1) {
                e.preventDefault();
                alert('Максимальное количество сотрудников должно быть не менее 1');
                document.querySelector('input[name="organization[max_employees]"]').focus();
                return false;
            }
            
            // Проверка поля "Наша организация" (необязательное, только предупреждение)
            const ourOrg = document.querySelector('input[name="organization[our_organization]"]').value;
            if (ourOrg && ourOrg.length > 255) {
                e.preventDefault();
                alert('Название "Наша организация" слишком длинное (максимум 255 символов)');
                document.querySelector('input[name="organization[our_organization]"]').focus();
                return false;
            }
            
            // Блокируем кнопку повторной отправки
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Создание...';
        });
    });
</script>
@endpush