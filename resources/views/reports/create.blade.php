@extends('layouts.app')

@section('title', 'Создание отчета')
@section('page-icon', 'bi-file-earmark-plus')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">
        <i class="bi bi-file-earmark-plus text-primary me-2"></i>
        Создание нового отчета
    </h5>
    <a href="#" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Назад
    </a>
</div>

<div class="row">
    <!-- Выбор типов отчетов -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
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
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-pencil text-success me-2"></i>
                    Заполните данные для отчета
                </h6>
            </div>
            
            <div class="card-body">
                <form method="POST" action="{{ route('reports.store') }}" id="reportForm">
                    @csrf
                    
                    <!-- Динамические поля появятся здесь -->
                    <div id="dynamicFields"></div>
                    
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
                    <input type="text" class="form-control" name="name" placeholder="Иванов Иван Иванович">
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
                    <label class="form-label">Серия</label>
                    <input type="text" class="form-control" name="passport_series" placeholder="4500" maxlength="4">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Номер</label>
                    <input type="text" class="form-control" name="passport_number" placeholder="123456" maxlength="6">
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
                    <label class="form-label">Номер транспортного средства</label>
                    <input type="text" class="form-control" name="vehicle_number" placeholder="Например: А123ВС77">
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
                    <label class="form-label">Кадастровый номер</label>
                    <input type="text" class="form-control" name="cadastral_number" placeholder="Например: 77:01:0001001:1234">
                    <div class="form-text text-muted">Формат: XX:XX:XXXXXXX:XXXX</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Тип недвижимости</label>
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
            const selectedTypes = [];
            
            // Собираем выбранные типы
            document.querySelectorAll('.report-type-checkbox:checked').forEach(cb => {
                selectedTypes.push(cb.value);
            });
            
            // Очищаем контейнер
            container.innerHTML = '';
            addedBlocks.clear();
            
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
            addedBlocks.clear();
        };
    });
</script>
@endsection