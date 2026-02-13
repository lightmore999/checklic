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
        {!! session('success') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {!! session('error') !!}
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

<!-- ВКЛАДКИ: Одиночное создание / Массовая загрузка -->
<ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#single" type="button" role="tab">
            <i class="bi bi-pencil-square me-2"></i>Одиночное создание
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk" type="button" role="tab">
            <i class="bi bi-file-earmark-spreadsheet me-2"></i>Массовая загрузка из Excel/CSV
        </button>
    </li>
</ul>

<div class="tab-content" id="reportTabsContent">
    
    <!-- === ВКЛАДКА 1: ОДИНОЧНОЕ СОЗДАНИЕ (БЕЗ ИЗМЕНЕНИЙ) === -->
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
                            
                            <!-- Скрытые поля для частей имени -->
                            <input type="hidden" name="last_name" id="hidden_last_name">
                            <input type="hidden" name="first_name" id="hidden_first_name">
                            <input type="hidden" name="patronymic" id="hidden_patronymic">
                            
                            <div class="row">
                                <!-- Выбор типов отчетов -->
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
                                    <div id="dynamicFields"></div>
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
    </div>
    
    <!-- === ВКЛАДКА 2: МАССОВАЯ ЗАГРУЗКА === -->
    <div class="tab-pane fade" id="bulk" role="tabpanel">
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0">
                            <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>
                            Массовая загрузка отчетов из Excel/CSV
                        </h6>
                    </div>
                    
                    <div class="card-body">
                        <form method="POST" action="{{ route('reports.bulk.store') }}" enctype="multipart/form-data" id="bulkUploadForm">
                            @csrf
                            
                            <div class="row">
                                <!-- ЛЕВАЯ КОЛОНКА: Выбор типов отчетов -->
                                <div class="col-md-5">
                                    <div class="card border-light shadow-sm mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="bi bi-list-check text-primary me-2"></i>
                                                Типы отчетов для загрузки
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-warning mb-3">
                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                Каждая запись в файле создаст ВСЕ выбранные типы отчетов
                                            </div>
                                            
                                            <div id="bulkReportTypesContainer">
                                                @foreach($reportTypes as $type)
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input bulk-report-type-checkbox" 
                                                               type="checkbox" 
                                                               value="{{ $type->id }}" 
                                                               id="bulk_type_{{ $type->id }}"
                                                               name="bulk_report_types[]"
                                                               data-name="{{ $type->name }}">
                                                        <label class="form-check-label d-flex align-items-center" for="bulk_type_{{ $type->id }}">
                                                            <span class="badge bg-primary me-2">{{ substr($type->name, 0, 2) }}</span>
                                                            <span>{{ $type->name }}</span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- ПРАВАЯ КОЛОНКА: Загрузка файла и настройки -->
                                <div class="col-md-7">
                                    <div class="card border-light shadow-sm mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="bi bi-upload text-primary me-2"></i>
                                                Загрузка файла
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Блок загрузки файла -->
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Выберите Excel/CSV файл</label>
                                                <div class="border rounded p-4 bg-light text-center" id="dropZone">
                                                    <div class="mb-3">
                                                        <i class="bi bi-file-earmark-excel fs-1 text-success"></i>
                                                        <i class="bi bi-file-earmark-spreadsheet fs-1 text-primary ms-2"></i>
                                                    </div>
                                                    <input type="file" 
                                                           class="form-control" 
                                                           name="excel_file" 
                                                           id="excelFile"
                                                           accept=".xlsx,.xls,.csv"
                                                           required>
                                                    <div class="form-text mt-2">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        Поддерживаемые форматы: .xlsx, .xls, .csv
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Настройки импорта -->
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Строка с заголовками</label>
                                                    <input type="number" 
                                                           class="form-control" 
                                                           name="header_row" 
                                                           id="header_row"
                                                           value="1" 
                                                           min="1"
                                                           step="1">
                                                    <div class="form-text">Номер строки с названиями колонок</div>
                                                </div>
                                            </div>
                                            
                                            <!-- Информация о формате файла -->
                                            <div class="alert alert-info mt-2">
                                                <h6 class="alert-heading"><i class="bi bi-table me-2"></i>Требования к файлу:</h6>
                                                <ul class="mb-0 small">
                                                    <li>Первая строка (настраивается) - заголовки колонок</li>
                                                    <li>Поддерживаемые заголовки: 
                                                        <code>last_name</code>, <code>first_name</code>, <code>patronymic</code>, 
                                                        <code>birth_date</code>, <code>region</code>, <code>passport_series</code>, 
                                                        <code>passport_number</code>, <code>passport_date</code>, 
                                                        <code>vehicle_number</code>, <code>cadastral_number</code>, 
                                                        <code>property_type</code>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Кнопки массовой загрузки -->
                            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-outline-secondary" onclick="resetBulkForm()">
                                    <i class="bi bi-x-circle me-1"></i> Сбросить
                                </button>
                                <button type="submit" class="btn btn-success" id="bulkSubmitBtn" disabled>
                                    <i class="bi bi-cloud-upload me-1"></i> Загрузить и обработать
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- СЕКЦИЯ ПРЕДПРОСМОТРА (появляется после выбора файла) -->
        <div id="previewSection" class="mt-4 d-none">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="bi bi-eye text-info me-2"></i>
                        Предпросмотр данных из файла
                    </h6>
                </div>
                <div class="card-body">
                    <div id="previewLoader" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                        <p class="mt-2">Чтение файла...</p>
                    </div>
                    <div id="previewContent"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- === ШАБЛОНЫ ПОЛЕЙ (БЕЗ ИЗМЕНЕНИЙ) === -->
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
        
        // =========================================
        // 1. ЛОГИКА ДЛЯ ОДИНОЧНОГО СОЗДАНИЯ (БЕЗ ИЗМЕНЕНИЙ)
        // =========================================
        
        // Конфигурация блоков для каждого типа отчета
        const reportConfig = {
            1: ['personalInfo'],
            2: ['personalInfo', 'passportInfo'],
            3: ['vehicleInfo'],
            4: ['propertyInfo']
        };
        
        const blockTemplates = {
            'personalInfo': 'fieldPersonalInfo',
            'passportInfo': 'fieldPassportInfo',
            'vehicleInfo': 'fieldVehicleInfo',
            'propertyInfo': 'fieldPropertyInfo'
        };
        
        const requirements = {
            1: { name: ['first_name', 'last_name'], message: 'Для базового отчета заполните Фамилию и Имя' },
            2: { passport: ['passport_series', 'passport_number'], message: 'Для паспортного отчета заполните серию и номер паспорта' },
            3: { vehicle: ['vehicle_number'], message: 'Для отчета по автоистории заполните номер ТС' },
            4: { property: ['cadastral_number', 'property_type'], message: 'Для отчета по недвижимости заполните кадастровый номер и выберите тип' }
        };
        
        let addedBlocks = new Set();
        
        // Обработчик чекбоксов для одиночного создания
        document.querySelectorAll('.report-type-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateFormFields);
        });
        
        function updateFormFields() {
            const container = document.getElementById('dynamicFields');
            const submitBtn = document.getElementById('submitBtn');
            const validationMsg = document.getElementById('validationMessage');
            const selectedTypes = [];
            
            document.querySelectorAll('.report-type-checkbox:checked').forEach(cb => {
                selectedTypes.push(cb.value);
            });
            
            container.innerHTML = '';
            addedBlocks.clear();
            
            if (validationMsg) {
                validationMsg.classList.add('d-none');
            }
            
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
            
            submitBtn.disabled = selectedTypes.length === 0;
        }
        
        function addBlock(blockId) {
            const container = document.getElementById('dynamicFields');
            const template = document.getElementById(blockTemplates[blockId]);
            if (template) {
                const clone = template.content.cloneNode(true);
                container.appendChild(clone);
            }
        }
        
        function getSelectedTypesNames() {
            const names = [];
            document.querySelectorAll('.report-type-checkbox:checked').forEach(cb => {
                names.push(`<strong>${cb.dataset.name}</strong>`);
            });
            return names.join(', ');
        }
        
        window.resetForm = function() {
            document.querySelectorAll('.report-type-checkbox').forEach(cb => {
                cb.checked = false;
            });
            document.getElementById('dynamicFields').innerHTML = '';
            document.getElementById('submitBtn').disabled = true;
            
            const validationMsg = document.getElementById('validationMessage');
            if (validationMsg) validationMsg.classList.add('d-none');
            
            addedBlocks.clear();
            document.getElementById('hidden_last_name').value = '';
            document.getElementById('hidden_first_name').value = '';
            document.getElementById('hidden_patronymic').value = '';
            
            const fullNameInput = document.getElementById('full_name');
            if (fullNameInput) fullNameInput.value = '';
        };
        
        // Валидация одиночной формы
        document.getElementById('reportForm').addEventListener('submit', function(e) {
            const selectedTypes = [];
            const errors = [];
            
            // Собираем выбранные типы
            document.querySelectorAll('.report-type-checkbox:checked').forEach(cb => {
                selectedTypes.push(parseInt(cb.value));
            });
            
            if (selectedTypes.length === 0) {
                e.preventDefault();
                showValidation('Выберите хотя бы один тип отчета');
                return false;
            }
            
            // ПРОВЕРЯЕМ КАЖДЫЙ ТИП СО ВСЕМИ ЕГО ПОЛЯМИ
            selectedTypes.forEach(typeId => {
                switch(typeId) {
                    case 1: // CL:Базовый V1
                        const lastName = document.getElementById('hidden_last_name')?.value;
                        const firstName = document.getElementById('hidden_first_name')?.value;
                        const patronymic = document.getElementById('hidden_patronymic')?.value; // ИЗМЕНЕНО!
                        const birthDate = document.querySelector('input[name="birth_date"]')?.value;
                        const region = document.querySelector('input[name="region"]')?.value;
                        
                        if (!lastName) errors.push('Базовый отчет: Фамилия обязательна');
                        if (!firstName) errors.push('Базовый отчет: Имя обязательно');
                        if (!patronymic) errors.push('Базовый отчет: Отчество обязательно');
                        if (!birthDate) errors.push('Базовый отчет: Дата рождения обязательна');
                        if (!region) errors.push('Базовый отчет: Регион проживания обязателен');
                        break;

                        
                    case 2: // CL:Паспорт V1 - ВСЕ ПОЛЯ ОБЯЗАТЕЛЬНЫ
                        const pLastName = document.getElementById('hidden_last_name')?.value;
                        const pFirstName = document.getElementById('hidden_first_name')?.value;
                        const pPatronymic = document.querySelector('input[name="patronymic"]')?.value;
                        const pBirthDate = document.querySelector('input[name="birth_date"]')?.value;
                        const pRegion = document.querySelector('input[name="region"]')?.value;
                        const pSeries = document.querySelector('input[name="passport_series"]')?.value;
                        const pNumber = document.querySelector('input[name="passport_number"]')?.value;
                        const pDate = document.querySelector('input[name="passport_date"]')?.value;
                        
                        if (!pLastName) errors.push('Паспортный отчет: Фамилия обязательна');
                        if (!pFirstName) errors.push('Паспортный отчет: Имя обязательно');
                        if (!pPatronymic) errors.push('Паспортный отчет: Отчество обязательно');
                        if (!pBirthDate) errors.push('Паспортный отчет: Дата рождения обязательна');
                        if (!pRegion) errors.push('Паспортный отчет: Регион обязателен');
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
                        
                    case 3: // AI:АвтоИстория V1 - ТОЛЬКО НОМЕР ТС
                        const vehicle = document.querySelector('input[name="vehicle_number"]')?.value;
                        if (!vehicle) {
                            errors.push('Автоотчет: Номер транспортного средства обязателен');
                        }
                        break;
                        
                    case 4: // CL:Недвижимость - ВСЕ ПОЛЯ ОБЯЗАТЕЛЬНЫ
                        const cadastral = document.querySelector('input[name="cadastral_number"]')?.value;
                        const propertyType = document.querySelector('select[name="property_type"]')?.value;
                        
                        if (!cadastral) errors.push('Отчет по недвижимости: Кадастровый номер обязателен');
                        if (!propertyType) errors.push('Отчет по недвижимости: Тип недвижимости обязателен');
                        break;
                }
            });
            
            // Убираем дубликаты ошибок
            const uniqueErrors = [...new Set(errors)];
            
            if (uniqueErrors.length > 0) {
                e.preventDefault();
                showValidation(uniqueErrors.join('<br>'));
                return false;
            }
            
            return true;
        });
        
        function showValidation(message) {
            const validationMsg = document.getElementById('validationMessage');
            const validationText = document.getElementById('validationText');
            if (validationMsg && validationText) {
                validationText.innerHTML = message;
                validationMsg.classList.remove('d-none');
                validationMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        
        window.parseFullName = function() {
            const fullNameInput = document.getElementById('full_name');
            if (!fullNameInput) return;
            
            const fullName = fullNameInput.value.trim();
            const parts = fullName.split(/\s+/);
            
            // Устанавливаем значения в скрытые поля (эти поля УЖЕ ЕСТЬ в форме)
            document.getElementById('hidden_last_name').value = parts[0] || '';
            document.getElementById('hidden_first_name').value = parts[1] || '';
            document.getElementById('hidden_patronymic').value = parts[2] || '';
            
            console.log('ФИО разобрано:', {
                last_name: parts[0],
                first_name: parts[1],
                patronymic: parts[2]
            });
        };
        
        // =========================================
        // 2. ЛОГИКА ДЛЯ МАССОВОЙ ЗАГРУЗКИ (НОВАЯ)
        // =========================================
        
        // Элементы массовой загрузки
        const excelFile = document.getElementById('excelFile');
        const bulkCheckboxes = document.querySelectorAll('.bulk-report-type-checkbox');
        const bulkSubmitBtn = document.getElementById('bulkSubmitBtn');
        const previewSection = document.getElementById('previewSection');
        const headerRowInput = document.getElementById('header_row');
        
        // Функция активации кнопки загрузки
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
                    previewSection.classList.add('d-none');
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
        
        // Отображение предпросмотра
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
        
        // Сброс формы массовой загрузки
        window.resetBulkForm = function() {
            bulkCheckboxes.forEach(cb => { cb.checked = false; });
            if (excelFile) excelFile.value = '';
            if (bulkSubmitBtn) bulkSubmitBtn.disabled = true;
            previewSection.classList.add('d-none');
            
            if (headerRowInput) headerRowInput.value = 1;
        };
        
        // Переключение вкладок - сброс алертов
        const triggerTabList = [].slice.call(document.querySelectorAll('#reportTabs button'));
        triggerTabList.forEach(function(triggerEl) {
            triggerEl.addEventListener('shown.bs.tab', function(event) {
                // Скрываем сообщения валидации при переключении
                const validationMsg = document.getElementById('validationMessage');
                if (validationMsg) validationMsg.classList.add('d-none');
            });
        });
    });
</script>

<!-- Стили для вкладок и предпросмотра -->
<style>
    .nav-tabs .nav-link {
        color: #495057;
        font-weight: 500;
        padding: 0.75rem 1.25rem;
    }
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        font-weight: 600;
        border-bottom: 3px solid #0d6efd;
    }
    .nav-tabs .nav-link i {
        margin-right: 0.5rem;
    }
    #dropZone {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    #dropZone:hover {
        background-color: #e9ecef !important;
        border-color: #0d6efd !important;
    }
    .table-sm td {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

@endsection