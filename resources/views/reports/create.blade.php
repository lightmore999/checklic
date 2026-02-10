@extends('layouts.app')

@section('title', 'Создание отчета')
@section('page-icon', 'bi-file-earmark-plus')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">
        <i class="bi bi-file-earmark-plus text-primary me-2"></i>
        Создание нового отчета
    </h5>
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Назад
    </a>
</div>

<!-- Уведомления -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-octagon me-2"></i>
        <strong>Ошибки в форме:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-pencil text-success me-2"></i>
                    Создание отчета
                </h6>
            </div>
            
            <div class="card-body">
                <form method="POST" action="{{ route('reports.store') }}" id="reportForm">
                    @csrf
                    
                    <!-- Скрытые поля для частей имени -->
                    <input type="hidden" name="last_name" id="hidden_last_name">
                    <input type="hidden" name="first_name" id="hidden_first_name">
                    <input type="hidden" name="patronymic" id="hidden_patronymic">
                    
                    <div class="row">
                        <!-- Выбор типов отчетов - ТЕПЕРЬ ВНУТРИ ФОРМЫ -->
                        <div class="col-md-4">
                            <div class="card border-light shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bi bi-list-check text-primary me-2"></i>
                                        Выберите типы отчетов
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="reportTypesContainer">
                                        @foreach($reportTypes as $type)
                                            <div class="form-check mb-3">
                                                <input class="form-check-input report-type-checkbox" 
                                                       type="checkbox" 
                                                       value="{{ $type->id }}" 
                                                       id="type_{{ $type->id }}"
                                                       name="report_types[]"
                                                       data-name="{{ $type->name }}">
                                                <label class="form-check-label d-flex align-items-center" for="type_{{ $type->id }}">
                                                    <span class="badge bg-primary me-2">{{ substr($type->name, 0, 2) }}</span>
                                                    <span>{{ $type->name }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <div class="alert alert-info mt-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Можно выбрать несколько типов отчетов
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Поля для заполнения -->
                        <div class="col-md-8">
                            <!-- Динамические поля появятся здесь -->
                            <div id="dynamicFields"></div>
                            
                            <!-- Сообщение о валидации (всегда присутствует, но скрыто) -->
                            <div id="validationMessage" class="alert alert-warning d-none">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <span id="validationText"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Кнопки -->
                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                            <i class="bi bi-x-circle me-1"></i> Сбросить
                        </button>
                        
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="bi bi-check-circle me-1"></i> Создать отчет
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Шаблоны полей -->
<template id="fieldPersonalInfo">
    <div class="card border-light shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-person me-2"></i>Персональные данные</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Фамилия Имя Отчество</label>
                    <input type="text" 
                           class="form-control" 
                           id="full_name" 
                           placeholder="Иванов Иван Иванович"
                           oninput="parseFullName()">
                    <div class="form-text text-muted">Введите ФИО субъекта (не обязательно)</div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Дата рождения</label>
                    <input type="date" class="form-control" name="birth_date">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Регион проживания</label>
                    <input type="text" class="form-control" name="region" placeholder="Например: Москва">
                </div>
            </div>
        </div>
    </div>
</template>

<template id="fieldPassportInfo">
    <div class="card border-light shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-card-checklist me-2"></i>Данные паспорта</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Серия <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           name="passport_series" 
                           placeholder="4500" 
                           maxlength="4"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Номер <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           name="passport_number" 
                           placeholder="123456" 
                           maxlength="6"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Дата выдачи</label>
                    <input type="date" class="form-control" name="passport_date">
                </div>
            </div>
        </div>
    </div>
</template>

<template id="fieldVehicleInfo">
    <div class="card border-light shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-car-front me-2"></i>Данные транспортного средства</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Номер транспортного средства <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           name="vehicle_number" 
                           placeholder="Например: А123ВС77"
                           style="text-transform: uppercase">
                    <div class="form-text text-muted">Введите госномер автомобиля</div>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="fieldPropertyInfo">
    <div class="card border-light shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-house me-2"></i>Данные недвижимости</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Кадастровый номер <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           name="cadastral_number" 
                           placeholder="Например: 77:01:0001001:1234">
                    <div class="form-text text-muted">Формат: XX:XX:XXXXXXX:XXXX</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Тип недвижимости <span class="text-danger">*</span></label>
                    <select class="form-select" name="property_type">
                        <option value="">Выберите тип</option>
                        <option value="land">Земельный участок</option>
                        <option value="building">Здание</option>
                        <option value="premises">Помещение</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Конфигурация блоков для каждого типа отчета
        const reportConfig = {
            // CL:Базовый V1
            1: ['personalInfo'],
            
            // CL:Паспорт V1
            2: ['personalInfo', 'passportInfo'],
            
            // AI:АвтоИстория V1
            3: ['vehicleInfo'],
            
            // CL:Недвижимость
            4: ['propertyInfo']
        };
        
        // Маппинг блоков на их шаблоны
        const blockTemplates = {
            'personalInfo': 'fieldPersonalInfo',
            'passportInfo': 'fieldPassportInfo',
            'vehicleInfo': 'fieldVehicleInfo',
            'propertyInfo': 'fieldPropertyInfo'
        };
        
        // Требования для каждого типа отчета
        const requirements = {
            1: { // CL:Базовый V1
                name: ['first_name', 'last_name'],
                message: 'Для базового отчета заполните Фамилию и Имя'
            },
            2: { // CL:Паспорт V1
                passport: ['passport_series', 'passport_number'],
                message: 'Для паспортного отчета заполните серию и номер паспорта'
            },
            3: { // AI:АвтоИстория V1
                vehicle: ['vehicle_number'],
                message: 'Для отчета по автоистории заполните номер ТС'
            },
            4: { // CL:Недвижимость
                property: ['cadastral_number', 'property_type'],
                message: 'Для отчета по недвижимости заполните кадастровый номер и выберите тип'
            }
        };
        
        // Уникальные блоки, которые уже добавлены
        let addedBlocks = new Set();
        
        // Обработчик изменения чекбоксов
        document.querySelectorAll('.report-type-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateFormFields);
        });
        
        // Обновление полей формы
        function updateFormFields() {
            const container = document.getElementById('dynamicFields');
            const submitBtn = document.getElementById('submitBtn');
            const validationMsg = document.getElementById('validationMessage');
            const selectedTypes = [];
            
            // Собираем выбранные типы
            document.querySelectorAll('.report-type-checkbox:checked').forEach(cb => {
                selectedTypes.push(cb.value);
            });
            
            
            // Очищаем контейнер
            container.innerHTML = '';
            addedBlocks.clear();
            
            // Скрываем сообщение валидации если оно есть
            if (validationMsg) {
                validationMsg.classList.add('d-none');
            }
            
            // Добавляем заголовок если есть выбранные типы
            if (selectedTypes.length > 0) {
                container.innerHTML = `
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle fs-4 me-3"></i>
                            <div>
                                <h6 class="mb-1">Вы выбрали отчеты:</h6>
                                <p class="mb-0">${getSelectedTypesNames()}</p>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Добавляем блоки для каждого выбранного типа
            selectedTypes.forEach(typeId => {
                if (reportConfig[typeId]) {
                    reportConfig[typeId].forEach(blockId => {
                        if (!addedBlocks.has(blockId)) {
                            addBlock(blockId);
                            addedBlocks.add(blockId);
                        }
                    });
                }
            });
            
            // Активируем/деактивируем кнопку отправки
            submitBtn.disabled = selectedTypes.length === 0;
        }
        
        // Добавление блока по ID
        function addBlock(blockId) {
            const container = document.getElementById('dynamicFields');
            const template = document.getElementById(blockTemplates[blockId]);
            
            if (template) {
                const clone = template.content.cloneNode(true);
                container.appendChild(clone);
            }
        }
        
        // Получение названий выбранных типов
        function getSelectedTypesNames() {
            const names = [];
            document.querySelectorAll('.report-type-checkbox:checked').forEach(cb => {
                names.push(`<strong>${cb.dataset.name}</strong>`);
            });
            return names.join(', ');
        }
        
        // Сброс формы
        window.resetForm = function() {
            document.querySelectorAll('.report-type-checkbox').forEach(cb => {
                cb.checked = false;
            });
            document.getElementById('dynamicFields').innerHTML = '';
            document.getElementById('submitBtn').disabled = true;
            
            const validationMsg = document.getElementById('validationMessage');
            if (validationMsg) {
                validationMsg.classList.add('d-none');
            }
            
            addedBlocks.clear();
            
            // Очищаем скрытые поля имени
            document.getElementById('hidden_last_name').value = '';
            document.getElementById('hidden_first_name').value = '';
            document.getElementById('hidden_patronymic').value = '';
            
            // Очищаем поле полного имени если оно существует
            const fullNameInput = document.getElementById('full_name');
            if (fullNameInput) {
                fullNameInput.value = '';
            }
        };
        
        // Валидация формы перед отправкой
        document.getElementById('reportForm').addEventListener('submit', function(e) {
    console.log('=== ОБРАБОТЧИК SUBMIT ВЫЗВАН ===');
    
    const selectedTypes = [];
    const errors = [];
    
    // Собираем выбранные типы
    document.querySelectorAll('.report-type-checkbox:checked').forEach(cb => {
        selectedTypes.push(cb.value);
    });
    
    console.log('Выбранные типы в submit:', selectedTypes);
    
    if (selectedTypes.length === 0) {
        console.log('Нет выбранных типов - отменяем отправку');
        e.preventDefault();
        showValidation('Выберите хотя бы один тип отчета');
        return false;
    }
    
    console.log('Проверяем требования...');
    
    // Проверяем требования для каждого выбранного типа
    selectedTypes.forEach(typeId => {
        const req = requirements[typeId];
        if (req) {
            console.log('Проверка типа', typeId, 'требования:', req);
            
            // Проверка имени
            if (req.name) {
                const lastName = document.getElementById('hidden_last_name').value;
                const firstName = document.getElementById('hidden_first_name').value;
                console.log('Имя проверка:', {lastName, firstName});
                if (!lastName || !firstName) {
                    errors.push(req.message);
                    console.log('Ошибка имени добавлена');
                }
            }
            
            // Проверка паспорта
            if (req.passport) {
                const series = document.querySelector('input[name="passport_series"]')?.value;
                const number = document.querySelector('input[name="passport_number"]')?.value;
                console.log('Паспорт проверка:', {series, number});
                if (!series || !number || series.length !== 4 || number.length !== 6) {
                    if (!errors.includes(req.message)) {
                        errors.push(req.message);
                        console.log('Ошибка паспорта добавлена');
                    }
                }
            }
            
            // Проверка ТС
            if (req.vehicle) {
                const vehicle = document.querySelector('input[name="vehicle_number"]')?.value;
                console.log('ТС проверка:', {vehicle});
                if (!vehicle) {
                    if (!errors.includes(req.message)) {
                        errors.push(req.message);
                        console.log('Ошибка ТС добавлена');
                    }
                }
            }
            
            // Проверка недвижимости
            if (req.property) {
                const cadastral = document.querySelector('input[name="cadastral_number"]')?.value;
                const propertyType = document.querySelector('select[name="property_type"]')?.value;
                console.log('Недвижимость проверка:', {cadastral, propertyType});
                if (!cadastral || !propertyType) {
                    if (!errors.includes(req.message)) {
                        errors.push(req.message);
                        console.log('Ошибка недвижимости добавлена');
                    }
                }
            }
        }
    });
    
    console.log('Найдено ошибок:', errors.length, errors);
    
    // Если есть ошибки - показываем и отменяем отправку
    if (errors.length > 0) {
        console.log('Есть ошибки - отменяем отправку');
        e.preventDefault();
        showValidation(errors.join('<br>'));
        return false;
    }
    
    console.log('Все проверки пройдены - отправляем форму');
    // Если все ок - разрешаем отправку формы
    return true;
});
        
        // Функция показа сообщения валидации
        function showValidation(message) {
            const validationMsg = document.getElementById('validationMessage');
            const validationText = document.getElementById('validationText');
            
            if (validationMsg && validationText) {
                validationText.innerHTML = message;
                validationMsg.classList.remove('d-none');
                
                // Прокрутка к сообщению
                validationMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        
        // Функция разбора полного имени на части
        window.parseFullName = function() {
            const fullNameInput = document.getElementById('full_name');
            if (!fullNameInput) return;
            
            const fullName = fullNameInput.value;
            const parts = fullName.trim().split(/\s+/);
            
            document.getElementById('hidden_last_name').value = parts[0] || '';
            document.getElementById('hidden_first_name').value = parts[1] || '';
            document.getElementById('hidden_patronymic').value = parts[2] || '';
        };
    });

    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            // Удаляем все обработчики авто-скрытия
            alert.classList.remove('alert-dismissible');
            alert.classList.remove('fade');
            alert.classList.remove('show');
            
            // Удаляем кнопку закрытия если есть
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.remove();
            }
        });
    });
</script>
@endsection