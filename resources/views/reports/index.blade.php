@extends('layouts.app')

@section('title', 'Мои отчеты')
@section('page-icon', 'bi-file-earmark-text')

@section('content')
@php
    if (!function_exists('trans_choice')) {
        function trans_choice($string, $number) {
            $words = explode('|', $string);
            $cases = [2, 0, 1, 1, 1, 2];
            return $words[ ($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)] ];
        }
    }
    
    $currentUser = Auth::user();
    $isAdmin = $currentUser->isAdmin();
    $isManager = $currentUser->isManager();
    $isOrgOwner = $currentUser->isOrgOwner();
    $isOrgMember = $currentUser->isOrgMember();
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">
            <i class="bi bi-file-earmark-text text-primary"></i> 
            @if(request('user_id') && request('user_id') != Auth::id())
                Отчеты пользователя
            @elseif(request('organization_id'))
                Отчеты организации
            @else
                Мои отчеты
            @endif
        </h1>
        <a href="{{ route('reports.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Новый отчет
        </a>
    </div>

    <!-- Фильтры -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="bi bi-funnel me-2"></i> Фильтры
                <button class="btn btn-sm btn-link float-end" type="button" data-bs-toggle="collapse" data-bs-target="#filters">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </h5>
        </div>
        <div class="collapse show" id="filters">
            <div class="card-body" style="padding: 1.5rem;">
                <form method="GET" action="{{ route('reports.index') }}" id="filterForm">
                    <div class="row">
                        <!-- Первая строка: Основные фильтры -->
                        
                        {{-- Фильтр по организации - показываем только админам и менеджерам --}}
                        @if($isAdmin || $isManager)
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Организация</label>
                            <select name="organization_id" class="form-select" id="organizationFilter">
                                <option value="">Все организации</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>
                                        {{ $org->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        
                        {{-- Фильтр по пользователю - показываем только админам и менеджерам --}}
                        @if($isAdmin || $isManager)
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Пользователь</label>
                            <select name="user_id" class="form-select" id="userFilter">
                                <option value="">Все пользователи</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }}
                                        @if($u->id == Auth::id()) (Я) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        
                        {{-- Корректируем ширину колонок в зависимости от того, какие фильтры показаны --}}
                        @if($isAdmin || $isManager)
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Тип отчета</label>
                                <select name="report_type_id" class="form-select">
                                    <option value="">Все типы</option>
                                    @foreach($reportTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('report_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Статус</label>
                                <select name="status" class="form-select">
                                    <option value="">Все статусы</option>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            {{-- Для владельцев и сотрудников - только тип отчета и статус, но в 6 колонок --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Тип отчета</label>
                                <select name="report_type_id" class="form-select">
                                    <option value="">Все типы</option>
                                    @foreach($reportTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('report_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Статус</label>
                                <select name="status" class="form-select">
                                    <option value="">Все статусы</option>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        
                        <!-- Вторая строка: Поиск по ФИО и паспорту -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Фамилия</label>
                            <input type="text" name="last_name" class="form-control" 
                                placeholder="Иванов" value="{{ request('last_name') }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Имя</label>
                            <input type="text" name="first_name" class="form-control" 
                                placeholder="Иван" value="{{ request('first_name') }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Отчество</label>
                            <input type="text" name="patronymic" class="form-control" 
                                placeholder="Иванович" value="{{ request('patronymic') }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Паспорт</label>
                            <input type="text" name="passport" class="form-control" 
                                placeholder="4500 123456" value="{{ request('passport') }}">
                        </div>
                        
                        <!-- Третья строка: Специфические фильтры -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Номер ТС</label>
                            <input type="text" name="vehicle_number" class="form-control" 
                                placeholder="А123ВС77" value="{{ request('vehicle_number') }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Кадастровый номер</label>
                            <input type="text" name="cadastral_number" class="form-control" 
                                placeholder="77:01:0001001:1234" value="{{ request('cadastral_number') }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">ИНН</label>
                            <input type="text" name="inn" class="form-control" 
                                placeholder="1650377119" value="{{ request('inn') }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Дата от</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        
                        <!-- Четвертая строка: Дата до и кнопки -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Дата до</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        
                        {{-- Пустые колонки для выравнивания в зависимости от количества фильтров --}}
                        @if($isAdmin || $isManager)
                            <div class="col-md-6 mb-3">
                                <!-- Пусто для выравнивания -->
                            </div>
                        @else
                            <div class="col-md-3 mb-3">
                                <!-- Пусто для выравнивания -->
                            </div>
                        @endif
                        
                        <div class="col-md-3 mb-3 d-flex align-items-end justify-content-end">
                            <div class="d-flex gap-2 w-100">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="bi bi-search me-1"></i> Применить
                                </button>
                                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary" title="Сбросить">
                                    <i class="bi bi-x-circle"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Таблица отчетов (без изменений) -->
     
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i> Список отчетов
            </h5>
            <div class="text-muted">
                Всего: {{ $reports->total() }} {{ trans_choice('отчет|отчета|отчетов', $reports->total()) }}
            </div>
        </div>
        
        <div class="card-body p-0">
            @if($reports->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th style="min-width: 150px;">Тип отчета</th>
                                <th style="min-width: 250px;">Данные</th>
                                <th style="min-width: 120px;">Статус</th>
                                <th style="min-width: 100px;">Пользователь</th>
                                <th style="min-width: 150px;">Дата создания</th>
                                <th style="width: 80px;" class="text-center">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                @php
                                    $user = $report->user;
                                    
                                    // Определяем маршрут к профилю пользователя
                                    $userProfileRoute = null;
                                    if ($user) {
                                        if ($user->isOrgOwner() && $user->orgOwnerProfile) {
                                            $userProfileRoute = route('admin.organization.show', $user->orgOwnerProfile->organization_id);
                                        } elseif ($user->isOrgMember() && $user->orgMemberProfile) {
                                            $userProfileRoute = route('admin.org-members.show', [
                                                $user->orgMemberProfile->organization_id,
                                                $user->orgMemberProfile->id
                                            ]);
                                        } elseif ($user->isManager()) {
                                            $userProfileRoute = route('admin.managers.show', $user->id);
                                        }
                                    }
                                    
                                    // Собираем данные для отображения
                                    $displayData = [];
                                    
                                    if ($report->last_name || $report->first_name || $report->patronymic) {
                                        $fio = trim($report->last_name . ' ' . $report->first_name . ' ' . $report->patronymic);
                                        $displayData[] = ['icon' => 'bi-person', 'text' => $fio];
                                    }
                                    
                                    if ($report->birth_date) {
                                        $displayData[] = ['icon' => 'bi-calendar', 'text' => 'ДР: ' . $report->birth_date->format('d.m.Y')];
                                    }
                                    
                                    if ($report->passport_series || $report->passport_number) {
                                        $passport = trim($report->passport_series . ' ' . $report->passport_number);
                                        $displayData[] = ['icon' => 'bi-passport', 'text' => $passport];
                                    }
                                    
                                    if ($report->passport_date) {
                                        $displayData[] = ['icon' => 'bi-calendar-check', 'text' => 'выдан: ' . $report->passport_date->format('d.m.Y')];
                                    }
                                    
                                    if ($report->vehicle_number) {
                                        $displayData[] = ['icon' => 'bi-car-front', 'text' => $report->vehicle_number];
                                    }
                                    
                                    if ($report->cadastral_number) {
                                        $displayData[] = ['icon' => 'bi-house', 'text' => $report->cadastral_number];
                                    }
                                    
                                    if ($report->property_type) {
                                        $propertyTypes = [
                                            'land' => 'Земельный участок',
                                            'building' => 'Здание',
                                            'premises' => 'Помещение'
                                        ];
                                        $displayData[] = ['icon' => 'bi-tag', 'text' => $propertyTypes[$report->property_type] ?? $report->property_type];
                                    }
                                    
                                    if ($report->inn) {
                                        $displayData[] = ['icon' => 'bi-building', 'text' => 'ИНН: ' . $report->inn];
                                    }
                                    
                                    if ($report->region) {
                                        $displayData[] = ['icon' => 'bi-geo-alt', 'text' => $report->region];
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">#{{ $report->id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-primary me-2">
                                                {{ substr($report->reportType->name, 0, 2) }}
                                            </span>
                                            <div>
                                                <div class="fw-bold">{{ $report->reportType->name }}</div>
                                                <small class="text-muted">ID: {{ $report->report_type_id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            @foreach($displayData as $item)
                                                <div class="text-truncate mb-1" style="max-width: 300px;" 
                                                     title="{{ $item['text'] }}">
                                                    <i class="bi {{ $item['icon'] }} me-1 text-secondary"></i> 
                                                    {{ $item['text'] }}
                                                </div>
                                            @endforeach
                                            
                                            @if(empty($displayData))
                                                <span class="text-muted">Нет данных</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @switch($report->status)
                                            @case('pending')
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock"></i> В ожидании
                                                </span>
                                                
                                                <!-- Прогресс для контрагентов -->
                                                @if($report->isContragent())
                                                    @php
                                                        $progress = $report->getApiProgress();
                                                    @endphp
                                                    @if($progress['total'] > 0)
                                                        <div class="mt-2" style="min-width: 120px;">
                                                            <div class="d-flex justify-content-between small mb-1">
                                                                <span class="fw-semibold">{{ $progress['completed'] }}/{{ $progress['total'] }}</span>
                                                                <span class="text-muted">{{ $progress['percentage'] }}%</span>
                                                            </div>
                                                            <div class="progress" style="height: 6px;">
                                                                @php
                                                                    $progressClass = $progress['percentage'] >= 100 ? 'bg-success' : 
                                                                                    ($progress['percentage'] > 60 ? 'bg-info' : 'bg-warning');
                                                                @endphp
                                                                <div class="progress-bar {{ $progressClass }}" 
                                                                    style="width: {{ $progress['percentage'] }}%"
                                                                    role="progressbar">
                                                                </div>
                                                            </div>
                                                            @if($progress['failed'] > 0)
                                                                <div class="small text-danger mt-1">
                                                                    <i class="bi bi-exclamation-triangle-fill"></i> 
                                                                    {{ $progress['failed'] }} с ошибкой
                                                                </div>
                                                            @endif
                                                            @if($progress['pending'] > 0)
                                                                <div class="small text-muted">
                                                                    {{ $progress['pending'] }} в очереди
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endif
                                                @break
                                                
                                            @case('completed')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Завершен
                                                </span>
                                                @if($report->isContragent())
                                                    @php
                                                        $progress = $report->getApiProgress();
                                                    @endphp
                                                    @if($progress['total'] > 0)
                                                        <div class="small text-success mt-1">
                                                            <i class="bi bi-check-circle-fill"></i> 
                                                            Получено {{ $progress['completed'] }}/{{ $progress['total'] }} источников
                                                            @if($progress['failed'] > 0)
                                                                <span class="text-danger">({{ $progress['failed'] }} ошибок)</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endif
                                                @break
                                                
                                            @case('failed')
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle"></i> Ошибка
                                                </span>
                                                @if($report->isContragent() && $report->getMetaData('errors'))
                                                    <div class="small text-danger mt-1">
                                                        <i class="bi bi-exclamation-triangle-fill"></i> 
                                                        Ошибка при обработке
                                                    </div>
                                                @endif
                                                @break
                                                
                                            @case('cancelled')
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-slash-circle"></i> Отменен
                                                </span>
                                                @break
                                        @endswitch
                                        
                                        @if($report->processed_at)
                                            <div class="small text-muted mt-1">
                                                {{ $report->processed_at->format('H:i') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 28px; height: 28px; color: white; font-size: 0.7rem;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                @if($userProfileRoute)
                                                    <a href="{{ $userProfileRoute }}" class="small fw-semibold text-decoration-none">
                                                        {{ $user->name }}
                                                    </a>
                                                @else
                                                    <div class="small fw-semibold">{{ $user->name }}</div>
                                                @endif
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $report->created_at->format('d.m.Y') }}</div>
                                        <div class="small text-muted">{{ $report->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('reports.show', $report) }}" 
                                               class="btn btn-sm btn-info rounded-circle d-flex align-items-center justify-content-center"
                                               style="width: 32px; height: 32px;"
                                               title="Просмотр отчета"
                                               data-bs-toggle="tooltip">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-file-earmark-x text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-muted mb-3">Отчеты не найдены</h4>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['user_id', 'organization_id', 'report_type_id', 'status', 'last_name', 'first_name', 'patronymic', 'passport', 'vehicle_number', 'cadastral_number', 'inn', 'date_from', 'date_to']))
                            Попробуйте изменить параметры фильтрации
                        @else
                            У вас еще нет созданных отчетов
                        @endif
                    </p>
                    <a href="{{ route('reports.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Создать первый отчет
                    </a>
                </div>
            @endif
        </div>
        
        <!-- КАСТОМНАЯ ПАГИНАЦИЯ -->
        @if($reports->hasPages() && $reports->count() > 0)
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="text-muted small order-2 order-md-1">
                        Показано с {{ $reports->firstItem() }} по {{ $reports->lastItem() }} 
                        из {{ $reports->total() }} {{ trans_choice('отчет|отчета|отчетов', $reports->total()) }}
                    </div>
                    
                    <!-- Простая кастомная пагинация -->
                    <div class="custom-pagination order-1 order-md-2">
                        @if($reports->currentPage() > 1)
                            <a href="{{ $reports->previousPageUrl() }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-chevron-left"></i> Назад
                            </a>
                        @endif
                        
                        <span class="mx-3">
                            Страница {{ $reports->currentPage() }} из {{ $reports->lastPage() }}
                        </span>
                        
                        @if($reports->hasMorePages())
                            <a href="{{ $reports->nextPageUrl() }}" class="btn btn-sm btn-outline-primary">
                                Вперед <i class="bi bi-chevron-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @elseif($reports->total() > 0 && !$reports->hasPages())
            <div class="card-footer bg-white border-top py-3">
                <div class="text-muted small text-center">
                    Всего {{ $reports->total() }} {{ trans_choice('отчет|отчета|отчетов', $reports->total()) }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Стили для карточки */
    .card {
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0,0,0,0.125);
    }
    
    .card-header .btn-link {
        color: #6c757d;
        text-decoration: none;
        padding: 0;
    }
    
    .card-header .btn-link:hover {
        color: #0d6efd;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    /* Стили для полей ввода */
    .form-label {
        font-size: 0.9rem;
        margin-bottom: 0.3rem;
        color: #495057;
    }
    
    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
    }
    
    .input-group .form-control {
        border-left: none;
    }
    
    .input-group .form-control:focus {
        border-color: #ced4da;
        box-shadow: none;
    }
    
    .input-group:focus-within {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        border-radius: 0.375rem;
    }
    
    .input-group:focus-within .input-group-text {
        border-color: #86b7fe;
    }
    
    .input-group:focus-within .form-control {
        border-color: #86b7fe;
    }
    
    /* Отступы между строками */
    .row.g-3 {
        --bs-gutter-y: 1rem;
    }
    
    /* Стили для кнопок */
    .btn-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        border: none;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2);
    }
    
    .btn-outline-secondary {
        border: 1px solid #ced4da;
    }
    
    .btn-outline-secondary:hover {
        background-color: #f8f9fa;
        color: #dc3545;
        border-color: #dc3545;
    }
    
    /* Адаптивность */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }
        
        .row.g-3 {
            --bs-gutter-y: 0.75rem;
        }
        
        .btn-primary, .btn-outline-secondary {
            padding: 0.5rem;
        }
    }
    /* Стили для карточки */
    .card {
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0,0,0,0.125);
    }
    
    .card-body {
        padding: 0;
    }
    
    .card-footer {
        background-color: #fff;
        border-top: 1px solid rgba(0,0,0,0.125);
    }
    
    /* Стили для таблицы */
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        font-weight: 600;
        color: #495057;
        border-bottom-width: 1px;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    /* Стили для кастомной пагинации */
    .custom-pagination {
        display: flex;
        align-items: center;
    }
    
    .custom-pagination .btn {
        min-width: 80px;
    }
    
    .custom-pagination .btn i {
        font-size: 0.8rem;
    }
    
    .custom-pagination .mx-3 {
        font-size: 0.95rem;
        color: #6c757d;
    }
    
    /* Стили для ссылок на пользователей */
    .table a {
        color: #0d6efd;
        transition: color 0.2s;
    }
    
    .table a:hover {
        color: #0a58ca;
        text-decoration: underline !important;
    }
    
    /* Стили для строк с данными */
    .text-truncate {
        max-width: 280px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .text-truncate i {
        width: 18px;
        display: inline-block;
    }
    
    /* Адаптивность */
    @media (max-width: 768px) {
        .card-footer .d-flex {
            flex-direction: column;
            text-align: center;
        }
        
        .custom-pagination {
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .custom-pagination .btn {
            min-width: 70px;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .custom-pagination .mx-3 {
            margin: 0.5rem 0 !important;
        }
        
        .table td, .table th {
            white-space: nowrap;
        }
        
        .text-truncate {
            max-width: 200px !important;
        }
    }
    
    /* Стили для бейджей */
    .badge {
        font-weight: 500;
    }
    
    .badge.bg-warning {
        color: #212529;
    }
    
    /* Стили для кнопок действий */
    .btn-sm.rounded-circle {
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-sm.rounded-circle i {
        font-size: 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // Авто-обновление каждые 30 секунд для отчетов в обработке
    @if($reports->contains('status', 'processing') || $reports->contains('status', 'pending'))
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    });
    @endif
    
    // Инициализация тултипов
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    
    // Авто-сабмит формы при изменении фильтров
    document.addEventListener('DOMContentLoaded', function() {
        // Фильтр организации
        const orgFilter = document.getElementById('organizationFilter');
        if (orgFilter) {
            orgFilter.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
        
        // Фильтр статуса
        const statusFilter = document.querySelector('select[name="status"]');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
        
        // Фильтр пользователя
        const userFilter = document.getElementById('userFilter');
        if (userFilter) {
            userFilter.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
        
        // Добавляем задержку для текстовых полей
        const textInputs = ['last_name', 'first_name', 'patronymic', 'passport', 'vehicle_number', 'cadastral_number', 'inn'];
        let timers = {};
        
        textInputs.forEach(name => {
            const input = document.querySelector(`input[name="${name}"]`);
            if (input) {
                input.addEventListener('input', function() {
                    clearTimeout(timers[name]);
                    timers[name] = setTimeout(() => {
                        document.getElementById('filterForm').submit();
                    }, 500);
                });
            }
        });
        
        // Фильтры дат
        const dateInputs = ['date_from', 'date_to'];
        dateInputs.forEach(name => {
            const input = document.querySelector(`input[name="${name}"]`);
            if (input) {
                input.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            }
        });
    });
</script>
@endpush