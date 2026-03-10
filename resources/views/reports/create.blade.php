@extends('layouts.app')

@section('title', 'Создание отчета')
@section('page-icon', 'bi-file-earmark-plus')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">
        <i class="bi bi-file-earmark-plus text-primary me-2"></i>
        Создание нового отчета
    </h5>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Назад
    </a>
</div>

<!-- ВКЛАДКИ: Одиночное создание / Массовая загрузка -->
<ul class="nav nav-tabs custom-tabs mb-4" id="reportTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link custom-tab-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#single" type="button" role="tab">
            <i class="bi bi-pencil-square me-2"></i>Одиночное создание
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link custom-tab-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk" type="button" role="tab">
            <i class="bi bi-file-earmark-spreadsheet me-2"></i>Массовая загрузка из Excel/CSV
        </button>
    </li>
</ul>

<div class="tab-content" id="reportTabsContent">
    
    <!-- === ВКЛАДКА 1: ОДИНОЧНОЕ СОЗДАНИЕ === -->
    <div class="tab-pane fade show active" id="single" role="tabpanel">
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0">
                            <i class="bi bi-pencil text-success me-2"></i>
                            Создание одиночного отчета
                        </h6>
                    </div>
                    
                    <div class="card-body">
                        <form method="POST" action="{{ route('reports.store') }}" id="reportForm">
                            @csrf
                            
                            <!-- Скрытое поле для региона (JSON) -->
                            <input type="hidden" name="regions" id="regions_input" value="">
                            
                            <div class="row">
                                <!-- Выбор типа отчета -->
                                <div class="col-md-4">
                                    <div class="card border-light shadow-sm mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="bi bi-list-check text-primary me-2"></i>
                                                Выберите тип отчета
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="reportTypesContainer">
                                                @foreach($reportTypes as $type)
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input report-type-radio" 
                                                               type="radio" 
                                                               value="{{ $type->id }}" 
                                                               id="type_{{ $type->id }}"
                                                               name="report_type"
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
                                                Выберите один тип отчета для создания
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Поля для заполнения -->
                                <div class="col-md-8">
                                    <div id="dynamicFields"></div>
                                    <div id="validationMessage" class="alert alert-warning d-none">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <span id="validationText"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Кнопки -->
                            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-secondary" onclick="resetForm()">
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
    </div>
    
    <!-- === ВКЛАДКА 2: МАССОВАЯ ЗАГРУЗКА === -->
    <div class="tab-pane fade" id="bulk" role="tabpanel">
        <!-- ... (код массовой загрузки без изменений) ... -->
    </div>
</div>

<!-- === ШАБЛОНЫ ПОЛЕЙ === -->
<template id="fieldPersonalInfo">
    <div class="card border-light shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-person me-2"></i>Персональные данные</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Фамилия <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           name="last_name" 
                           id="last_name"
                           placeholder="Иванов"
                           value="{{ old('last_name') }}"
                           pattern="[А-Яа-яЁё\-]+"
                           title="Только русские буквы и дефис"
                           oninput="this.value = this.value.replace(/[^А-Яа-яЁё\-]/g, '')">
                    <div class="form-text text-muted">Только русские буквы и дефис</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Имя <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           name="first_name" 
                           id="first_name"
                           placeholder="Иван"
                           value="{{ old('first_name') }}"
                           pattern="[А-Яа-яЁё\-]+"
                           title="Только русские буквы и дефис"
                           oninput="this.value = this.value.replace(/[^А-Яа-яЁё\-]/g, '')">
                    <div class="form-text text-muted">Только русские буквы и дефис</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Отчество <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           name="patronymic" 
                           id="patronymic"
                           placeholder="Иванович"
                           value="{{ old('patronymic') }}"
                           pattern="[А-Яа-яЁё\-]+"
                           title="Только русские буквы и дефис"
                           oninput="this.value = this.value.replace(/[^А-Яа-яЁё\-]/g, '')">
                    <div class="form-text text-muted">Только русские буквы и дефис</div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Дата рождения</label>
                    <input type="date" 
                           class="form-control" 
                           name="birth_date" 
                           id="birth_date"
                           value="{{ old('birth_date') }}"
                           min="1900-01-01"
                           max="{{ date('Y-m-d') }}">
                    <div class="form-text text-muted">Дата должна быть в диапазоне 1900 - текущая</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Регионы поиска</label>
                    <div class="selected-regions mb-2" id="selectedRegionsContainer"></div>
                    
                    <!-- Поиск регионов -->
                    <div class="region-search-wrapper">
                        <input type="text" 
                               class="form-control" 
                               id="regionSearch" 
                               placeholder="Введите название региона для поиска..."
                               autocomplete="off">
                        <div id="regionDropdown" class="region-dropdown"></div>
                    </div>
                    <div class="form-text text-muted">Выберите до 3 регионов для поиска</div>
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
                           id="passport_series"
                           placeholder="4500" 
                           maxlength="4"
                           value="{{ old('passport_series') }}"
                           pattern="[0-9]{4}"
                           title="4 цифры"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)">
                    <div class="form-text text-muted">4 цифры</div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Номер <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           name="passport_number" 
                           id="passport_number"
                           placeholder="123456" 
                           maxlength="6"
                           value="{{ old('passport_number') }}"
                           pattern="[0-9]{6}"
                           title="6 цифр"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)">
                    <div class="form-text text-muted">6 цифр</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Дата выдачи</label>
                    <input type="date" 
                           class="form-control" 
                           name="passport_date" 
                           id="passport_date"
                           value="{{ old('passport_date') }}"
                           min="1990-01-01"
                           max="{{ date('Y-m-d') }}">
                    <div class="form-text text-muted">Дата должна быть в диапазоне 1990 - текущая</div>
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
                           id="vehicle_number"
                           placeholder="А123ВС77"
                           value="{{ old('vehicle_number') }}"
                           style="text-transform: uppercase"
                           pattern="[А-ЯA-Z0-9]+"
                           title="Только буквы и цифры"
                           oninput="this.value = this.value.replace(/[^А-ЯA-Z0-9]/g, '').toUpperCase()">
                    <div class="form-text text-muted">Только буквы и цифры, автоматически в верхнем регистре</div>
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
                           id="cadastral_number"
                           placeholder="77:01:0001001:1234"
                           value="{{ old('cadastral_number') }}"
                           pattern="\d{2}:\d{2}:[\d]{5,7}:[\d]+"
                           title="Формат: XX:XX:XXXXXXX:XXXX"
                           oninput="this.value = this.value.replace(/[^0-9:]/g, '')">
                    <div class="form-text text-muted">Формат: XX:XX:XXXXXXX:XXXX (только цифры и двоеточия)</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Тип недвижимости <span class="text-danger">*</span></label>
                    <select class="form-select" name="property_type" id="property_type">
                        <option value="">Выберите тип</option>
                        <option value="land" {{ old('property_type') == 'land' ? 'selected' : '' }}>Земельный участок</option>
                        <option value="building" {{ old('property_type') == 'building' ? 'selected' : '' }}>Здание</option>
                        <option value="premises" {{ old('property_type') == 'premises' ? 'selected' : '' }}>Помещение</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="fieldContragentInfo">
    <div class="card border-light shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-building me-2"></i>Данные для Контрагентов</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">ИНН <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           name="inn" 
                           id="inn_field"
                           placeholder="123456789012" 
                           maxlength="12"
                           value="{{ old('inn') }}"
                           pattern="\d{10}|\d{12}"
                           title="10 или 12 цифр"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12)">
                    <div class="form-text text-muted">10 или 12 цифр</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // =========================================
        // 1. ИНИЦИАЛИЗАЦИЯ ДАННЫХ
        // =========================================
        
        // Список регионов
        const regionsList = [
            { id: 1, name: 'Республика Адыгея' },
            { id: 2, name: 'Республика Алтай' },
            { id: 3, name: 'Республика Башкортостан' },
            { id: 4, name: 'Республика Бурятия' },
            { id: 5, name: 'Республика Дагестан' },
            { id: 6, name: 'Республика Ингушетия' },
            { id: 7, name: 'Кабардино-Балкарская Республика' },
            { id: 8, name: 'Республика Калмыкия' },
            { id: 9, name: 'Карачаево-Черкесская Республика' },
            { id: 10, name: 'Республика Карелия' },
            { id: 11, name: 'Республика Коми' },
            { id: 90, name: 'Республика Крым' },
            { id: 12, name: 'Республика Марий Эл' },
            { id: 13, name: 'Республика Мордовия' },
            { id: 14, name: 'Республика Саха (Якутия)' },
            { id: 15, name: 'Республика Северная Осетия - Алания' },
            { id: 16, name: 'Республика Татарстан' },
            { id: 17, name: 'Республика Тыва' },
            { id: 18, name: 'Удмуртская Республика' },
            { id: 19, name: 'Республика Хакасия' },
            { id: 20, name: 'Чеченская Республика' },
            { id: 21, name: 'Чувашская Республика' },
            { id: 22, name: 'Алтайский край' },
            { id: 75, name: 'Забайкальский край' },
            { id: 41, name: 'Камчатский край' },
            { id: 23, name: 'Краснодарский край' },
            { id: 24, name: 'Красноярский край' },
            { id: 59, name: 'Пермский край' },
            { id: 25, name: 'Приморский край' },
            { id: 26, name: 'Ставропольский край' },
            { id: 27, name: 'Хабаровский край' },
            { id: 28, name: 'Амурская область' },
            { id: 29, name: 'Архангельская область' },
            { id: 30, name: 'Астраханская область' },
            { id: 31, name: 'Белгородская область' },
            { id: 32, name: 'Брянская область' },
            { id: 33, name: 'Владимирская область' },
            { id: 34, name: 'Волгоградская область' },
            { id: 35, name: 'Вологодская область' },
            { id: 36, name: 'Воронежская область' },
            { id: 37, name: 'Ивановская область' },
            { id: 38, name: 'Иркутская область' },
            { id: 39, name: 'Калининградская область' },
            { id: 40, name: 'Калужская область' },
            { id: 42, name: 'Кемеровская область' },
            { id: 43, name: 'Кировская область' },
            { id: 44, name: 'Костромская область' },
            { id: 45, name: 'Курганская область' },
            { id: 46, name: 'Курская область' },
            { id: 47, name: 'Ленинградская область' },
            { id: 48, name: 'Липецкая область' },
            { id: 49, name: 'Магаданская область' },
            { id: 50, name: 'Московская область' },
            { id: 51, name: 'Мурманская область' },
            { id: 52, name: 'Нижегородская область' },
            { id: 53, name: 'Новгородская область' },
            { id: 54, name: 'Новосибирская область' },
            { id: 55, name: 'Омская область' },
            { id: 56, name: 'Оренбургская область' },
            { id: 57, name: 'Орловская область' },
            { id: 58, name: 'Пензенская область' },
            { id: 60, name: 'Псковская область' },
            { id: 61, name: 'Ростовская область' },
            { id: 62, name: 'Рязанская область' },
            { id: 63, name: 'Самарская область' },
            { id: 64, name: 'Саратовская область' },
            { id: 65, name: 'Сахалинская область' },
            { id: 66, name: 'Свердловская область' },
            { id: 67, name: 'Смоленская область' },
            { id: 68, name: 'Тамбовская область' },
            { id: 69, name: 'Тверская область' },
            { id: 70, name: 'Томская область' },
            { id: 71, name: 'Тульская область' },
            { id: 72, name: 'Тюменская область' },
            { id: 73, name: 'Ульяновская область' },
            { id: 74, name: 'Челябинская область' },
            { id: 76, name: 'Ярославская область' },
            { id: 77, name: 'Москва' },
            { id: 78, name: 'Санкт-Петербург' },
            { id: 79, name: 'Еврейская АО' },
            { id: 80, name: 'Ненецкий АО' },
            { id: 81, name: 'Ханты-Мансийский АО' },
            { id: 82, name: 'Чукотский АО' },
            { id: 83, name: 'Ямало-Ненецкий АО' },
            { id: 92, name: 'г. Севастополь' }
        ];
        
        // Конфигурация блоков для каждого типа отчета
        const reportConfig = {
            1: ['personalInfo'],
            2: ['personalInfo', 'passportInfo'],
            3: ['vehicleInfo'],
            4: ['propertyInfo'],
            6: ['contragentInfo']
        };
        
        const blockTemplates = {
            'personalInfo': 'fieldPersonalInfo',
            'passportInfo': 'fieldPassportInfo',
            'vehicleInfo': 'fieldVehicleInfo',
            'propertyInfo': 'fieldPropertyInfo',
            'contragentInfo': 'fieldContragentInfo'
        };
        
        let addedBlocks = new Set();
        let selectedRegions = [];
        
        // =========================================
        // 2. ФУНКЦИИ ВАЛИДАЦИИ
        // =========================================
        
        function validateField(input) {
            const value = input.value.trim();
            const fieldName = input.name;
            let isValid = true;
            let errorMessage = '';
            
            // Проверка на обязательность
            if (input.required && !value) {
                isValid = false;
                errorMessage = 'Поле обязательно для заполнения';
            }
            
            // Проверка по регулярному выражению
            if (isValid && input.pattern) {
                const regex = new RegExp(input.pattern);
                if (!regex.test(value)) {
                    isValid = false;
                    errorMessage = input.title || 'Неверный формат';
                }
            }
            
            // Специфические проверки
            if (isValid) {
                switch (fieldName) {
                    case 'last_name':
                    case 'first_name':
                    case 'patronymic':
                        if (value.length < 2) {
                            isValid = false;
                            errorMessage = 'Минимальная длина 2 символа';
                        }
                        break;
                    case 'passport_series':
                        if (value.length !== 4) {
                            isValid = false;
                            errorMessage = 'Должно быть 4 цифры';
                        }
                        break;
                    case 'passport_number':
                        if (value.length !== 6) {
                            isValid = false;
                            errorMessage = 'Должно быть 6 цифр';
                        }
                        break;
                    case 'birth_date':
                    case 'passport_date':
                        if (value) {
                            const date = new Date(value);
                            const minDate = fieldName === 'birth_date' ? new Date('1900-01-01') : new Date('1990-01-01');
                            const maxDate = new Date();
                            
                            if (date < minDate || date > maxDate) {
                                isValid = false;
                                errorMessage = fieldName === 'birth_date' 
                                    ? 'Дата должна быть между 1900 и сегодня' 
                                    : 'Дата должна быть между 1990 и сегодня';
                            }
                        }
                        break;
                    case 'inn':
                        if (value && value.length !== 10 && value.length !== 12) {
                            isValid = false;
                            errorMessage = 'ИНН должен содержать 10 или 12 цифр';
                        }
                        break;
                }
            }
            
            // Визуальное отображение ошибки
            const feedbackDiv = input.nextElementSibling?.classList.contains('invalid-feedback') 
                ? input.nextElementSibling 
                : document.createElement('div');
            
            if (!isValid) {
                input.classList.add('is-invalid');
                if (!feedbackDiv.classList.contains('invalid-feedback')) {
                    feedbackDiv.className = 'invalid-feedback';
                    input.parentNode.appendChild(feedbackDiv);
                }
                feedbackDiv.textContent = errorMessage;
            } else {
                input.classList.remove('is-invalid');
                if (feedbackDiv.classList.contains('invalid-feedback')) {
                    feedbackDiv.remove();
                }
            }
            
            return isValid;
        }
        
        function validateForm() {
            const selectedRadio = document.querySelector('.report-type-radio:checked');
            const errors = [];
            const invalidFields = [];
            
            if (!selectedRadio) {
                showValidation('Выберите тип отчета');
                return false;
            }
            
            // Валидируем все видимые поля
            const visibleInputs = document.querySelectorAll('#dynamicFields input:not([type="hidden"]), #dynamicFields select');
            
            visibleInputs.forEach(input => {
                if (!validateField(input)) {
                    invalidFields.push(input);
                }
            });
            
            if (invalidFields.length > 0) {
                invalidFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                invalidFields[0].focus();
                showValidation('Пожалуйста, исправьте ошибки в полях');
                return false;
            }
            
            const typeId = parseInt(selectedRadio.value);
            
            // Проверка обязательных полей по типу отчета
            switch(typeId) {
                case 1: // CL:Базовый V1
                    const lastName = document.getElementById('last_name')?.value;
                    const firstName = document.getElementById('first_name')?.value;
                    const patronymic = document.getElementById('patronymic')?.value;
                    const birthDate = document.querySelector('input[name="birth_date"]')?.value;
                    
                    if (!lastName) errors.push('Базовый отчет: Фамилия обязательна');
                    if (!firstName) errors.push('Базовый отчет: Имя обязательно');
                    if (!patronymic) errors.push('Базовый отчет: Отчество обязательно');
                    if (!birthDate) errors.push('Базовый отчет: Дата рождения обязательна');
                    break;
                    
                case 2: // CL:Паспорт V1
                    const pLastName = document.getElementById('last_name')?.value;
                    const pFirstName = document.getElementById('first_name')?.value;
                    const pPatronymic = document.getElementById('patronymic')?.value;
                    const pBirthDate = document.querySelector('input[name="birth_date"]')?.value;
                    const pSeries = document.querySelector('input[name="passport_series"]')?.value;
                    const pNumber = document.querySelector('input[name="passport_number"]')?.value;
                    const pDate = document.querySelector('input[name="passport_date"]')?.value;
                    
                    if (!pLastName) errors.push('Паспортный отчет: Фамилия обязательна');
                    if (!pFirstName) errors.push('Паспортный отчет: Имя обязательно');
                    if (!pPatronymic) errors.push('Паспортный отчет: Отчество обязательно');
                    if (!pBirthDate) errors.push('Паспортный отчет: Дата рождения обязательна');
                    if (!pSeries) errors.push('Паспортный отчет: Серия паспорта обязательна');
                    if (!pNumber) errors.push('Паспортный отчет: Номер паспорта обязателен');
                    if (!pDate) errors.push('Паспортный отчет: Дата выдачи паспорта обязательна');
                    
                    // Проверка формата
                    if (pSeries && pSeries.length !== 4) {
                        errors.push('Паспортный отчет: Серия должна быть 4 цифры');
                    }
                    if (pNumber && pNumber.length !== 6) {
                        errors.push('Паспортный отчет: Номер должен быть 6 цифр');
                    }
                    break;
                    
                case 3: // AI:АвтоИстория V1
                    const vehicle = document.querySelector('input[name="vehicle_number"]')?.value;
                    if (!vehicle) {
                        errors.push('Автоотчет: Номер транспортного средства обязателен');
                    }
                    break;
                    
                case 4: // CL:Недвижимость
                    const cadastral = document.querySelector('input[name="cadastral_number"]')?.value;
                    const propertyType = document.querySelector('select[name="property_type"]')?.value;
                    
                    if (!cadastral) errors.push('Отчет по недвижимости: Кадастровый номер обязателен');
                    if (!propertyType) errors.push('Отчет по недвижимости: Тип недвижимости обязателен');
                    break;
                    
                case 6: // Контрагенты
                    const inn = document.querySelector('input[name="inn"]')?.value;
                    if (!inn) {
                        errors.push('Контрагенты: ИНН обязателен');
                    } else if (inn.length !== 10 && inn.length !== 12) {
                        errors.push('Контрагенты: ИНН должен содержать 10 или 12 цифр');
                    }
                    break;
            }
            
            const uniqueErrors = [...new Set(errors)];
            
            if (uniqueErrors.length > 0) {
                showValidation(uniqueErrors.join('<br>'));
                return false;
            }
            
            return true;
        }
        
        function showValidation(message) {
            const validationMsg = document.getElementById('validationMessage');
            const validationText = document.getElementById('validationText');
            if (validationMsg && validationText) {
                validationText.innerHTML = message;
                validationMsg.classList.remove('d-none');
                validationMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        
        // =========================================
        // 3. ФУНКЦИИ ДЛЯ РАБОТЫ С ПОЛЯМИ
        // =========================================
        
        function addBlock(blockId) {
            const container = document.getElementById('dynamicFields');
            const template = document.getElementById(blockTemplates[blockId]);
            if (template) {
                const clone = template.content.cloneNode(true);
                container.appendChild(clone);
            }
        }
        
        function updateFormFields() {
            const container = document.getElementById('dynamicFields');
            const submitBtn = document.getElementById('submitBtn');
            const validationMsg = document.getElementById('validationMessage');
            const selectedRadio = document.querySelector('.report-type-radio:checked');
            
            container.innerHTML = '';
            addedBlocks.clear();
            selectedRegions = [];
            
            if (validationMsg) {
                validationMsg.classList.add('d-none');
            }
            
            if (selectedRadio) {
                const typeId = parseInt(selectedRadio.value);
                
                // Показываем информационное сообщение
                container.innerHTML = `
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle fs-4 me-3"></i>
                            <div>
                                <h6 class="mb-1">Выбран тип отчета:</h6>
                                <p class="mb-0"><strong>${selectedRadio.dataset.name}</strong></p>
                            </div>
                        </div>
                    </div>
                `;
                
                // Добавляем нужные блоки полей
                if (reportConfig[typeId]) {
                    reportConfig[typeId].forEach(blockId => {
                        if (!addedBlocks.has(blockId)) {
                            addBlock(blockId);
                            addedBlocks.add(blockId);
                        }
                    });
                }
                
                // Если добавлен блок personalInfo, инициализируем поиск регионов
                if (addedBlocks.has('personalInfo')) {
                    initRegionSearch();
                }
                
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }
        
        window.resetForm = function() {
            document.querySelectorAll('.report-type-radio').forEach(radio => {
                radio.checked = false;
            });
            document.getElementById('dynamicFields').innerHTML = '';
            document.getElementById('submitBtn').disabled = true;
            
            const validationMsg = document.getElementById('validationMessage');
            if (validationMsg) validationMsg.classList.add('d-none');
            
            addedBlocks.clear();
            selectedRegions = [];
            document.getElementById('regions_input').value = '';
            
            // Очищаем все поля ввода
            document.querySelectorAll('input[type="text"], input[type="date"], select').forEach(field => {
                field.value = '';
            });
        };
        
        // =========================================
        // 4. ФУНКЦИИ ДЛЯ РАБОТЫ С РЕГИОНАМИ
        // =========================================
        
        function initRegionSearch() {
            const searchInput = document.getElementById('regionSearch');
            const dropdown = document.getElementById('regionDropdown');
            const container = document.getElementById('selectedRegionsContainer');
            
            if (!searchInput || !dropdown || !container) return;
            
            // Обновляем отображение выбранных регионов
            updateSelectedRegionsDisplay();
            
            // Поиск при вводе
            searchInput.addEventListener('input', function() {
                const search = this.value.toLowerCase().trim();
                
                if (search.length < 2) {
                    dropdown.innerHTML = '';
                    dropdown.classList.remove('show');
                    return;
                }
                
                const results = regionsList.filter(region => 
                    region.name.toLowerCase().includes(search) &&
                    !selectedRegions.some(r => r.id === region.id)
                );
                
                if (results.length > 0) {
                    dropdown.innerHTML = results.map(region => 
                        `<div class="region-item" data-id="${region.id}" data-name="${region.name}">${region.name}</div>`
                    ).join('');
                    dropdown.classList.add('show');
                } else {
                    dropdown.innerHTML = '<div class="region-item text-muted">Ничего не найдено</div>';
                    dropdown.classList.add('show');
                }
            });
            
            // Выбор региона из выпадающего списка
            dropdown.addEventListener('click', function(e) {
                const item = e.target.closest('.region-item');
                if (!item || item.classList.contains('text-muted')) return;
                
                const id = parseInt(item.dataset.id);
                const name = item.dataset.name;
                
                if (selectedRegions.length < 3 && !selectedRegions.some(r => r.id === id)) {
                    selectedRegions.push({ id, name });
                    updateSelectedRegionsDisplay();
                    updateRegionsInput();
                }
                
                searchInput.value = '';
                dropdown.innerHTML = '';
                dropdown.classList.remove('show');
            });
            
            // Закрытие дропдауна при клике вне
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });
        }
        
        function updateSelectedRegionsDisplay() {
            const container = document.getElementById('selectedRegionsContainer');
            if (!container) return;
            
            if (selectedRegions.length === 0) {
                container.innerHTML = '<div class="text-muted small">Регионы не выбраны (поиск по всей России)</div>';
                return;
            }
            
            container.innerHTML = selectedRegions.map((region, index) => `
                <div class="selected-region-tag">
                    <span>${region.name}</span>
                    <i class="bi bi-x-circle" onclick="removeRegion(${index})"></i>
                </div>
            `).join('');
        }
        
        window.removeRegion = function(index) {
            selectedRegions.splice(index, 1);
            updateSelectedRegionsDisplay();
            updateRegionsInput();
        };
        
        function updateRegionsInput() {
            const regionsInput = document.getElementById('regions_input');
            if (regionsInput) {
                regionsInput.value = JSON.stringify(selectedRegions.map(r => r.name));
            }
        }
        
        // =========================================
        // 5. ОБРАБОТЧИКИ СОБЫТИЙ
        // =========================================
        
        // Обработчик радио-кнопок для выбора типа отчета
        document.querySelectorAll('.report-type-radio').forEach(radio => {
            radio.addEventListener('change', updateFormFields);
        });
        
        // Обработчик отправки формы
        document.getElementById('reportForm').addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            return true;
        });
        
        // =========================================
        // 6. ЛОГИКА ДЛЯ МАССОВОЙ ЗАГРУЗКИ
        // =========================================
        
        const excelFile = document.getElementById('excelFile');
        const bulkCheckboxes = document.querySelectorAll('.bulk-report-type-checkbox');
        const bulkSubmitBtn = document.getElementById('bulkSubmitBtn');
        const previewSection = document.getElementById('previewSection');
        const headerRowInput = document.getElementById('header_row');
        
        function updateBulkSubmitButton() {
            const hasFile = excelFile && excelFile.files.length > 0;
            const hasTypes = Array.from(bulkCheckboxes).some(cb => cb.checked);
            if (bulkSubmitBtn) {
                bulkSubmitBtn.disabled = !(hasFile && hasTypes);
            }
        }
        
        // Слушатели для массовой загрузки
        if (bulkCheckboxes.length > 0) {
            bulkCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkSubmitButton);
            });
        }
        
        if (excelFile) {
            excelFile.addEventListener('change', function(e) {
                updateBulkSubmitButton();
                if (this.files.length > 0) {
                    previewExcelFile(this.files[0]);
                } else {
                    previewSection?.classList.add('d-none');
                }
            });
        }
        
        if (headerRowInput) {
            headerRowInput.addEventListener('change', function() {
                if (excelFile && excelFile.files.length > 0) {
                    previewExcelFile(excelFile.files[0]);
                }
            });
        }
        
        // Функция предпросмотра Excel/CSV
        function previewExcelFile(file) {
            const previewLoader = document.getElementById('previewLoader');
            const previewContent = document.getElementById('previewContent');
            
            if (previewLoader) previewLoader.classList.remove('d-none');
            if (previewContent) previewContent.innerHTML = '';
            previewSection.classList.remove('d-none');
            
            const formData = new FormData();
            formData.append('excel_file', file);
            formData.append('header_row', headerRowInput ? headerRowInput.value : 1);
            
            fetch('{{ route("reports.preview") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (previewLoader) previewLoader.classList.add('d-none');
                
                if (data.success) {
                    displayPreview(data);
                } else {
                    previewContent.innerHTML = `<div class="alert alert-danger">${data.error || 'Ошибка чтения файла'}</div>`;
                }
            })
            .catch(error => {
                if (previewLoader) previewLoader.classList.add('d-none');
                previewContent.innerHTML = `<div class="alert alert-danger">Ошибка загрузки: ${error.message}</div>`;
            });
        }
        
        function displayPreview(data) {
            const previewContent = document.getElementById('previewContent');
            
            let html = `
                <div class="alert alert-success mb-3">
                    <i class="bi bi-check-circle me-2"></i>
                    Найдено записей для обработки: <strong>${data.rowCount}</strong>
                </div>
            `;
            
            if (data.headers && data.headers.length > 0) {
                html += `<div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>`;
                
                data.headers.forEach(header => {
                    html += `<th>${header ? header : '<em class="text-muted">пусто</em>'}</th>`;
                });
                
                html += `</tr></thead><tbody>`;
                
                if (data.previewRows && data.previewRows.length > 0) {
                    data.previewRows.forEach(row => {
                        html += '<tr>';
                        row.forEach(cell => {
                            html += `<td>${cell !== null && cell !== '' ? cell : '<em class="text-muted">—</em>'}</td>`;
                        });
                        html += '</tr>';
                    });
                }
                
                if (data.rowCount > 5) {
                    html += `<tr><td colspan="${data.headers.length}" class="text-center text-muted">
                        <i class="bi bi-three-dots"></i> и еще ${data.rowCount - 5} записей
                    </td></tr>`;
                }
                
                html += `</tbody></table></div>`;
            } else {
                html += `<div class="alert alert-warning">Не найдены заголовки колонок</div>`;
            }
            
            previewContent.innerHTML = html;
        }
        
        window.resetBulkForm = function() {
            bulkCheckboxes.forEach(cb => { cb.checked = false; });
            if (excelFile) excelFile.value = '';
            if (bulkSubmitBtn) bulkSubmitBtn.disabled = true;
            previewSection?.classList.add('d-none');
            if (headerRowInput) headerRowInput.value = 1;
        };
        
    });
</script>

@push('styles')
<style>
    /* Стили для вкладок - ИСПРАВЛЕНИЕ ПРОБЛЕМЫ */
    .custom-tabs {
        border-bottom: 1px solid #dee2e6;
        background-color: #f8f9fa;
        padding: 0.5rem 0.5rem 0 0.5rem;
        border-radius: 0.25rem 0.25rem 0 0;
        margin-bottom: 1.5rem !important;
    }
    
    .custom-tab-link {
        color: #495057 !important;
        background-color: #e9ecef !important;
        border: 1px solid #ced4da !important;
        border-bottom: none !important;
        margin-right: 0.25rem;
        padding: 0.75rem 1.25rem;
        font-weight: 500;
        transition: all 0.2s;
        border-radius: 0.375rem 0.375rem 0 0 !important;
        position: relative;
        top: 1px;
    }
    
    .custom-tab-link i {
        margin-right: 0.5rem;
    }
    
    .custom-tab-link:hover:not(.active) {
        background-color: #dee2e6 !important;
        border-color: #adb5bd !important;
        color: #212529 !important;
    }
    
    .custom-tab-link.active {
        color: #ffffff !important;
        background-color: #0d6efd !important;
        border-color: #0d6efd #0d6efd #fff !important;
        font-weight: 600 !important;
        box-shadow: 0 -2px 4px rgba(13, 110, 253, 0.1);
    }
    
    .custom-tab-link.active i {
        color: #ffffff !important;
    }
    
    /* Остальные стили (если их нет) */
    .region-search-wrapper {
        position: relative;
    }
    
    .region-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 250px;
        overflow-y: auto;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        z-index: 1000;
        display: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .region-dropdown.show {
        display: block;
    }
    
    .region-item {
        padding: 8px 12px;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .region-item:hover {
        background-color: #f8f9fa;
    }
    
    .selected-region-tag {
        background-color: #e7f1ff;
        border: 1px solid #0d6efd;
        border-radius: 16px;
        padding: 4px 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
        margin-right: 8px;
        margin-bottom: 8px;
    }
    
    .selected-region-tag i {
        color: #6c757d;
        cursor: pointer;
        font-size: 0.8rem;
    }
    
    .selected-region-tag i:hover {
        color: #dc3545;
    }
    
    /* Стили для валидации */
    .is-invalid {
        border-color: #dc3545 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
    
    .is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
    
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }
</style>
@endpush

@endsection