@extends('layouts.app')

@section('title', 'Редактирование лимита')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-edit"></i> Редактирование лимита #{{ $limit->id }}
                    </h3>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="{{ route('limits.update', $limit) }}" id="limitForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Информация о текущем лимите -->
                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Создан:</strong><br>
                                    {{ $limit->created_at->format('d.m.Y H:i') }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Обновлен:</strong><br>
                                    {{ $limit->updated_at->format('d.m.Y H:i') }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Текущий статус:</strong><br>
                                    @if($limit->isExhausted())
                                        <span class="badge bg-danger">Исчерпан</span>
                                    @else
                                        <span class="badge bg-success">Активен</span>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <strong>Осталось:</strong><br>
                                    <span class="badge bg-{{ $limit->quantity > 0 ? 'primary' : 'danger' }}">
                                        {{ $limit->quantity }} шт.
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> Пожалуйста, исправьте ошибки ниже:
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Выбор пользователя -->
                                <div class="form-group">
                                    <label for="user_id" class="font-weight-bold">
                                        <i class="fas fa-user"></i> Пользователь *
                                    </label>
                                    <select name="user_id" id="user_id" 
                                            class="form-control select2 @error('user_id') is-invalid @enderror" 
                                            required
                                            data-placeholder="Выберите пользователя...">
                                        <option value=""></option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" 
                                                {{ old('user_id', $limit->user_id) == $user->id ? 'selected' : '' }}
                                                data-role="{{ $user->role }}"
                                                data-organization="{{ $user->getOrganization()?->name ?? 'Не указана' }}">
                                                {{ $user->name }} 
                                                <small class="text-muted">({{ $user->email }})</small>
                                                - {{ $user->getRoleDisplayName() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Текущий пользователь: <strong>{{ $limit->user->name }}</strong>
                                    </small>
                                </div>

                                <!-- Количество -->
                                <div class="form-group">
                                    <label for="quantity" class="font-weight-bold">
                                        <i class="fas fa-sort-amount-up"></i> Количество лимитов *
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="quantity" id="quantity" 
                                               class="form-control @error('quantity') is-invalid @enderror"
                                               value="{{ old('quantity', $limit->quantity) }}" 
                                               min="0" 
                                               max="9999"
                                               required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">шт.</span>
                                        </div>
                                    </div>
                                    @error('quantity')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary mr-2" onclick="adjustQuantity(5)">
                                            +5
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary mr-2" onclick="adjustQuantity(10)">
                                            +10
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="adjustQuantity(-5)">
                                            -5
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Тип отчета -->
                                <div class="form-group">
                                    <label for="report_type_id" class="font-weight-bold">
                                        <i class="fas fa-file-alt"></i> Тип отчета *
                                    </label>
                                    <select name="report_type_id" id="report_type_id" 
                                            class="form-control select2 @error('report_type_id') is-invalid @enderror" 
                                            required
                                            data-placeholder="Выберите тип отчета...">
                                        <option value=""></option>
                                        @foreach($reportTypes as $type)
                                            <option value="{{ $type->id }}" 
                                                {{ old('report_type_id', $limit->report_type_id) == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                                @if($type->description)
                                                    <small class="text-muted">- {{ $type->description }}</small>
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('report_type_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Текущий тип: <strong>{{ $limit->reportType->name ?? 'Не указан' }}</strong>
                                    </small>
                                </div>

                                <!-- Дата действия -->
                                <div class="form-group">
                                    <label for="date_created" class="font-weight-bold">
                                        <i class="fas fa-calendar-alt"></i> Дата действия лимита *
                                    </label>
                                    <input type="date" name="date_created" id="date_created" 
                                           class="form-control @error('date_created') is-invalid @enderror"
                                           value="{{ old('date_created', $limit->date_created->format('Y-m-d')) }}" 
                                           min="{{ now()->format('Y-m-d') }}"
                                           required>
                                    @error('date_created')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary mr-2" onclick="setDate('today')">
                                            Сегодня
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mr-2" onclick="setDate('tomorrow')">
                                            Завтра
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDate('week')">
                                            Через неделю
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Дополнительные опции -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card border-secondary">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0"><i class="fas fa-cogs"></i> Дополнительные действия</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="reset_usage" name="reset_usage">
                                            <label class="form-check-label" for="reset_usage">
                                                <strong>Сбросить использование лимита</strong> - установить максимальное значение
                                            </label>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input type="checkbox" class="form-check-input" id="extend_all" name="extend_all">
                                            <label class="form-check-label" for="extend_all">
                                                <strong>Применить ко всем лимитам пользователя</strong> - обновить все лимиты этого пользователя на эту дату
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопки действий -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-save"></i> Обновить лимит
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                            <i class="fas fa-trash"></i> Удалить лимит
                                        </button>
                                    </div>
                                    <div>
                                        <a href="{{ route('users.limits', $limit->user) }}" class="btn btn-info">
                                            <i class="fas fa-list"></i> Все лимиты пользователя
                                        </a>
                                        <a href="{{ route('limits.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Вернуться к списку
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Форма удаления (скрытая) -->
                    <form id="deleteForm" action="{{ route('limits.destroy', $limit) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
                
                <div class="card-footer text-muted">
                    <small>
                        <i class="fas fa-exclamation-triangle text-danger"></i> 
                        <strong>Внимание!</strong> Редактирование лимита может повлиять на работу пользователя. 
                        Изменения вступят в силу немедленно.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: calc(2.25rem + 2px);
        padding: .375rem .75rem;
        border: 1px solid #ced4da;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
        color: #495057;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Инициализация Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
        
        // Показать информацию о выбранном пользователе
        $('#user_id').trigger('change');
        
        // Функция изменения количества
        window.adjustQuantity = function(amount) {
            var current = parseInt($('#quantity').val()) || 0;
            var newValue = current + amount;
            if (newValue < 0) newValue = 0;
            if (newValue > 9999) newValue = 9999;
            $('#quantity').val(newValue);
        };
        
        // Функция установки даты
        window.setDate = function(type) {
            var date = new Date();
            
            switch(type) {
                case 'today':
                    // сегодня - уже установлено
                    break;
                case 'tomorrow':
                    date.setDate(date.getDate() + 1);
                    break;
                case 'week':
                    date.setDate(date.getDate() + 7);
                    break;
            }
            
            var formattedDate = date.toISOString().split('T')[0];
            $('#date_created').val(formattedDate);
        };
        
        // Подтверждение удаления
        window.confirmDelete = function() {
            Swal.fire({
                title: 'Вы уверены?',
                text: "Лимит будет удален безвозвратно!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Да, удалить!',
                cancelButtonText: 'Отмена'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#deleteForm').submit();
                }
            });
        };
        
        // Если выбрана опция сброса, установить максимальное значение
        $('#reset_usage').on('change', function() {
            if (this.checked) {
                var currentValue = parseInt($('#quantity').val()) || 0;
                if (currentValue < 100) {
                    $('#quantity').val(100);
                    Swal.fire({
                        icon: 'info',
                        title: 'Количество установлено',
                        text: 'Установлено значение 100 как максимальное',
                        timer: 1500
                    });
                }
            }
        });
        
        // Валидация даты
        $('#date_created').on('change', function() {
            var selectedDate = new Date(this.value);
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Некорректная дата',
                    text: 'Дата не может быть меньше текущей',
                });
                $(this).val('{{ now()->format("Y-m-d") }}');
            }
        });
    });
</script>
@endpush