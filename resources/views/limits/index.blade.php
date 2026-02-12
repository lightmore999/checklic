@extends('layouts.app')

@section('title', 'Управление лимитами')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Лимиты</h3>
                    <div>
                        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                            <a href="{{ route('limits.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Создать лимит
                            </a>
                            <a href="{{ route('limits.bulk-create') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-layer-group"></i> Массовое создание
                            </a>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Фильтры -->
                    <form method="GET" class="mb-4" id="filterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Организация</label>
                                <select name="organization_id" class="form-control select2-organization">
                                    <option value="">Все организации</option>
                                    @foreach($organizations ?? [] as $org)
                                        <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>
                                            {{ $org->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label>Пользователь</label>
                                <select name="user_id" class="form-control select2-user" data-placeholder="Поиск пользователя...">
                                    <option value="">Все пользователи</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }}) - {{ $user->getRoleDisplayName() ?? 'Нет роли' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Можно ввести имя или email для поиска</small>
                            </div>
                            
                            <div class="col-md-2">
                                <label>Тип отчета</label>
                                <select name="report_type_id" class="form-control">
                                    <option value="">Все типы</option>
                                    @foreach($reportTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('report_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label>Дата</label>
                                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                            </div>
                            
                            <div class="col-md-2">
                                <label>Статус</label>
                                <select name="status" class="form-control">
                                    <option value="">Все</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                                    <option value="exhausted" {{ request('status') == 'exhausted' ? 'selected' : '' }}>Исчерпанные</option>
                                </select>
                            </div>
                            
                            <div class="col-md-12 mt-3">
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Применить фильтры
                                    </button>
                                    <a href="{{ route('limits.index') }}" class="btn btn-secondary ml-2">
                                        <i class="fas fa-undo"></i> Сбросить
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Активные фильтры -->
                    @if(request()->anyFilled(['organization_id', 'user_id', 'report_type_id', 'date', 'status']))
                        <div class="alert alert-info py-2 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-filter mr-2"></i>
                                <span>Активные фильтры:</span>
                                @if(request('organization_id'))
                                    @php 
                                        $org = isset($organizations) ? $organizations->firstWhere('id', request('organization_id')) : null; 
                                    @endphp
                                    <span class="badge badge-info ml-2">Организация: {{ $org->name ?? 'ID: ' . request('organization_id') }}</span>
                                @endif
                                @if(request('user_id'))
                                    @php 
                                        $usr = $users->firstWhere('id', request('user_id')); 
                                    @endphp
                                    <span class="badge badge-info ml-2">Пользователь: {{ $usr->name ?? 'ID: ' . request('user_id') }}</span>
                                @endif
                                @if(request('report_type_id'))
                                    @php 
                                        $type = $reportTypes->firstWhere('id', request('report_type_id')); 
                                    @endphp
                                    <span class="badge badge-info ml-2">Тип отчета: {{ $type->name ?? 'ID: ' . request('report_type_id') }}</span>
                                @endif
                                @if(request('date'))
                                    <span class="badge badge-info ml-2">Дата: {{ \Carbon\Carbon::parse(request('date'))->format('d.m.Y') }}</span>
                                @endif
                                @if(request('status'))
                                    <span class="badge badge-info ml-2">Статус: {{ request('status') == 'active' ? 'Активные' : 'Исчерпанные' }}</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Таблица лимитов -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Пользователь</th>
                                    <th>Создатель</th>
                                    <th>Организация</th>
                                    <th>Тип отчета</th>
                                    <th>Количество</th>
                                    <th>Использовано</th>
                                    <th>Доступно</th>
                                    <th>Делегировано</th>
                                    <th>Дата</th>
                                    <th>Статус</th>
                                    <th>Создан</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($limits as $limit)
                                    <tr>
                                        <td>{{ $limit->id }}</td>
                                        <td>
                                            @if($limit->user)
                                                <strong>{{ $limit->user->name ?? 'Не указан' }}</strong><br>
                                                <small class="text-muted">{{ $limit->user->email ?? '' }}</small><br>
                                                <span class="badge bg-{{ $limit->user->getRoleColor() ?? 'secondary' }}">
                                                    {{ $limit->user->getRoleDisplayName() ?? 'Нет роли' }}
                                                </span>
                                            @else
                                                <span class="text-muted">Пользователь не указан</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($limit->user)
                                                @php
                                                    $orgOwner = $limit->user->orgOwnerProfile ?? null;
                                                    $orgMember = $limit->user->orgMemberProfile ?? null;
                                                    $organization = $orgOwner ?? $orgMember;
                                                @endphp
                                                @if($organization)
                                                    <strong>{{ $organization->name ?? '' }}</strong><br>
                                                    <small class="text-muted">
                                                        @if($orgOwner)
                                                            <span class="badge bg-primary">Руководитель</span>
                                                        @elseif($orgMember)
                                                            <span class="badge bg-info">Сотрудник</span>
                                                        @endif
                                                    </small>
                                                @else
                                                    <span class="text-muted">Не указана</span>
                                                @endif
                                            @else
                                                <span class="text-muted">Не указана</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($limit->creator)
                                                <strong>{{ $limit->creator->name ?? 'Не указан' }}</strong><br>
                                                <small class="text-muted">{{ $limit->creator->email ?? '' }}</small><br>
                                                <span class="badge bg-{{ $limit->creator->getRoleColor() ?? 'secondary' }}">
                                                    {{ $limit->creator->getRoleDisplayName() ?? 'Нет роли' }}
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-robot"></i> Система
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $limit->reportType->name ?? 'Не указан' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $limit->quantity }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">
                                                {{ $limit->used_quantity }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $limit->getAvailableQuantity() > 0 ? 'success' : 'danger' }}">
                                                {{ $limit->getAvailableQuantity() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($limit->delegatedVersions && $limit->delegatedVersions->count() > 0)
                                                <div class="delegated-info">
                                                    <button type="button" class="btn btn-xs btn-info" data-toggle="collapse" 
                                                            data-target="#delegated-{{ $limit->id }}">
                                                        <i class="fas fa-share-alt"></i> 
                                                        {{ $limit->delegatedVersions->count() }}
                                                    </button>
                                                    <div class="mt-2">
                                                        <small>Всего делегировано: 
                                                            <strong>{{ $limit->delegatedVersions->sum('quantity') }}</strong>
                                                        </small><br>
                                                        <small>Использовано: 
                                                            <strong>{{ $limit->delegatedVersions->sum('used_quantity') }}</strong>
                                                        </small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">Нет</span>
                                            @endif
                                        </td>
                                        <td>{{ $limit->date_created ? $limit->date_created->format('d.m.Y') : 'Не указана' }}</td>
                                        <td>
                                            @if($limit->isExhausted())
                                                <span class="badge bg-danger">Исчерпан</span>
                                            @else
                                                <span class="badge bg-success">Активен</span>
                                            @endif
                                        </td>
                                        <td>{{ $limit->created_at ? $limit->created_at->format('d.m.Y H:i') : '' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if(auth()->user()->isAdmin())
                                                    <a href="{{ route('limits.edit', $limit) }}" class="btn btn-sm btn-warning" title="Редактировать">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('limits.destroy', $limit) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить лимит?')" title="Удалить">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($limit->user)
                                                    <a href="{{ route('users.limits', $limit->user) }}" class="btn btn-sm btn-info" title="Все лимиты пользователя">
                                                        <i class="fas fa-user"></i>
                                                    </a>
                                                @endif
                                                @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                                    <button type="button" class="btn btn-sm btn-secondary" 
                                                            data-toggle="modal" data-target="#delegateModal{{ $limit->id }}" title="Делегировать">
                                                        <i class="fas fa-share-alt"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- Детали делегированных лимитов (скрытый блок) -->
                                    @if($limit->delegatedVersions && $limit->delegatedVersions->count() > 0)
                                        <tr class="collapse" id="delegated-{{ $limit->id }}">
                                            <td colspan="12" class="p-0">
                                                <div class="p-3 bg-light">
                                                    <h6 class="mb-3"><i class="fas fa-share-alt mr-2"></i>Делегированные лимиты:</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <thead class="thead-light">
                                                                <tr>
                                                                    <th>Пользователь</th>
                                                                    <th>Организация</th>
                                                                    <th>Количество</th>
                                                                    <th>Использовано</th>
                                                                    <th>Доступно</th>
                                                                    <th>Статус</th>
                                                                    <th>Дата создания</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($limit->delegatedVersions as $delegated)
                                                                    <tr>
                                                                        <td>
                                                                            @if($delegated->user)
                                                                                <strong>{{ $delegated->user->name ?? 'Не указан' }}</strong><br>
                                                                                <small>{{ $delegated->user->email ?? '' }}</small>
                                                                            @else
                                                                                <span class="text-muted">Не указан</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            @if($delegated->user)
                                                                                @php
                                                                                    $delOrgOwner = $delegated->user->orgOwnerProfile ?? null;
                                                                                    $delOrgMember = $delegated->user->orgMemberProfile ?? null;
                                                                                    $delOrganization = $delOrgOwner ?? $delOrgMember;
                                                                                @endphp
                                                                                @if($delOrganization)
                                                                                    {{ $delOrganization->name ?? '' }}
                                                                                @else
                                                                                    <span class="text-muted">Не указана</span>
                                                                                @endif
                                                                            @else
                                                                                <span class="text-muted">Не указана</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>{{ $delegated->quantity }}</td>
                                                                        <td>{{ $delegated->used_quantity }}</td>
                                                                        <td>
                                                                            <span class="badge bg-{{ $delegated->getAvailableQuantity() > 0 ? 'success' : 'danger' }}">
                                                                                {{ $delegated->getAvailableQuantity() }}
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            @if($delegated->isExhausted())
                                                                                <span class="badge bg-danger">Исчерпан</span>
                                                                            @elseif($delegated->isActive())
                                                                                <span class="badge bg-success">Активен</span>
                                                                            @else
                                                                                <span class="badge bg-secondary">Неактивен</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>{{ $delegated->created_at ? $delegated->created_at->format('d.m.Y H:i') : '' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                                            <p class="text-muted mb-0">Лимиты не найдены</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Информация о пагинации -->
                    @if($limits->total() > 0)
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <p class="text-muted mb-0">
                                Показано {{ $limits->firstItem() ?? 0 }} - {{ $limits->lastItem() ?? 0 }} 
                                из {{ $limits->total() }} лимитов
                            </p>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $limits->appends(request()->query())->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальные окна для делегирования -->
@foreach($limits as $limit)
    @if((auth()->user()->isAdmin() || auth()->user()->isManager()) && $limit->getAvailableQuantity() > 0)
        <div class="modal fade" id="delegateModal{{ $limit->id }}" tabindex="-1" role="dialog" aria-labelledby="delegateModalLabel{{ $limit->id }}" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('limits.delegate', $limit) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="delegateModalLabel{{ $limit->id }}">
                                <i class="fas fa-share-alt mr-2"></i>Делегировать лимит #{{ $limit->id }}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <strong>Лимит:</strong> {{ $limit->reportType->name ?? 'Не указан' }}<br>
                                <strong>Доступно для делегирования:</strong> {{ $limit->getAvailableQuantity() }}
                            </div>
                            
                            <div class="form-group">
                                <label>Пользователь <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-control select2-delegate" 
                                        data-exclude-user-id="{{ $limit->user_id }}" required>
                                    <option value="">Поиск пользователя...</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Количество <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control" 
                                       min="1" max="{{ $limit->getAvailableQuantity() }}" 
                                       value="1" required>
                                <small class="text-muted">
                                    Максимум доступно: {{ $limit->getAvailableQuantity() }}
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-share-alt mr-1"></i>Делегировать
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: calc(2.25rem + 2px);
        border: 1px solid #ced4da;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: calc(2.25rem + 2px);
    }
    .btn-group .btn {
        margin-right: 2px;
    }
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 85%;
        padding: 0.4em 0.6em;
    }
    .delegated-info .btn-xs {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
    }
    .collapse:not(.show) {
        display: none;
    }
    .collapse.show {
        display: table-row;
    }
    .bg-success {
        background-color: #28a745 !important;
    }
    .bg-danger {
        background-color: #dc3545 !important;
    }
    .bg-warning {
        background-color: #ffc107 !important;
    }
    .bg-info {
        background-color: #17a2b8 !important;
    }
    .bg-secondary {
        background-color: #6c757d !important;
    }
    .bg-primary {
        background-color: #007bff !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ru.js"></script>
<script>
    $(document).ready(function() {
        // Инициализация Select2 для пользователей
        $('.select2-user').select2({
            theme: 'default',
            language: 'ru',
            placeholder: 'Поиск пользователя...',
            allowClear: true,
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route("users.search") }}',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        search: params.term || '',
                        organization_id: $('select[name="organization_id"]').val() // Передаем выбранную организацию
                    };
                },
                processResults: function(data) {
                    let results = [{
                        id: '',
                        text: 'Все пользователи'
                    }];
                    
                    results = results.concat(data);
                    
                    return {
                        results: results
                    };
                },
                cache: true
            }
        });

        // Инициализация Select2 для организаций
        $('.select2-organization').select2({
            theme: 'default',
            language: 'ru',
            placeholder: 'Выберите организацию',
            allowClear: true,
            width: '100%'
        });

        // При изменении организации - обновляем список пользователей
        $('select[name="organization_id"]').on('change', function() {
            // Очищаем и обновляем select пользователей
            $('.select2-user').val(null).trigger('change');
            $('#filterForm').submit();
        });

        // Автоматическая отправка формы при изменении полей
        $('select[name="report_type_id"], select[name="status"], input[name="date"]').on('change', function() {
            $('#filterForm').submit();
        });

        // Отправка формы при изменении пользователя
        $('.select2-user').on('change', function() {
            $('#filterForm').submit();
        });
    });

    // Инициализация Select2 для делегирования
    $('.select2-delegate').each(function() {
        let excludeUserId = $(this).data('exclude-user-id');
        
        $(this).select2({
            theme: 'default',
            language: 'ru',
            placeholder: 'Поиск пользователя...',
            allowClear: true,
            width: '100%',
            dropdownParent: $(this).closest('.modal'),
            minimumInputLength: 0,
            ajax: {
                url: '{{ route("users.search") }}',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        search: params.term || '',
                        organization_id: $('select[name="organization_id"]').val() // Для делегирования тоже передаем организацию
                    };
                },
                processResults: function(data) {
                    // Исключаем текущего пользователя
                    let filtered = data.filter(function(user) {
                        return user.id != excludeUserId;
                    });
                    
                    return {
                        results: filtered
                    };
                },
                cache: true
            }
        });
    });
</script>
@endpush

@endsection