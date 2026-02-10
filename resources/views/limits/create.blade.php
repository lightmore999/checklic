@extends('layouts.app')

@section('title', 'Создание лимита')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-plus-circle"></i> Создание нового лимита
                    </h3>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="{{ route('limits.store') }}" id="limitForm">
                        @csrf
                        
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
                                                {{ old('user_id') == $user->id ? 'selected' : '' }}
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
                                        Пользователь должен быть активен и иметь роль "Владелец организации" или "Сотрудник организации"
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
                                               value="{{ old('quantity', 0) }}" 
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
                                    <small class="form-text text-muted">
                                        Сколько отчетов может сгенерировать пользователь
                                    </small>
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
                                                {{ old('report_type_id') == $type->id ? 'selected' : '' }}>
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
                                </div>

                                <!-- Дата действия -->
                                <div class="form-group">
                                    <label for="date_created" class="font-weight-bold">
                                        <i class="fas fa-calendar-alt"></i> Дата действия лимита *
                                    </label>
                                    <input type="date" name="date_created" id="date_created" 
                                           class="form-control @error('date_created') is-invalid @enderror"
                                           value="{{ old('date_created', now()->format('Y-m-d')) }}" 
                                           min="{{ now()->format('Y-m-d') }}"
                                           required>
                                    @error('date_created')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        На какую дату действует лимит. Не может быть меньше текущей даты.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Информация о выбранном пользователе -->
                        <div class="row mt-3" id="userInfo" style="display: none;">
                            <div class="col-12">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white py-2">
                                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Информация о пользователе</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>Роль:</strong> <span id="userRole"></span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Организация:</strong> <span id="userOrganization"></span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Статус:</strong> 
                                                <span class="badge bg-success" id="userStatus">Активен</span>
                                            </div>
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
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Создать лимит
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="setDefaultValues()">
                                            <i class="fas fa-bolt"></i> Быстрые значения
                                        </button>
                                    </div>
                                    <div>
                                        <a href="{{ route('limits.bulk-create') }}" class="btn btn-info">
                                            <i class="fas fa-layer-group"></i> Массовое создание
                                        </a>
                                        <a href="{{ route('limits.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Вернуться к списку
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="card-footer text-muted">
                    <small>
                        <i class="fas fa-info-circle"></i> 
                        <strong>Администратор</strong> может создавать лимиты для всех пользователей. 
                        <strong>Менеджер</strong> - только для пользователей своих организаций.
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
<script>
    $(document).ready(function() {
        // Инициализация Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
        
        // Показ информации о пользователе при выборе
        $('#user_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            if (selectedOption.val()) {
                $('#userRole').text(selectedOption.data('role-display') || selectedOption.data('role'));
                $('#userOrganization').text(selectedOption.data('organization') || 'Не указана');
                $('#userInfo').show();
            } else {
                $('#userInfo').hide();
            }
        });
        
        // Установка быстрых значений
        window.setDefaultValues = function() {
            $('#quantity').val(10);
            $('#date_created').val('{{ now()->addDays(7)->format("Y-m-d") }}');
            Swal.fire({
                icon: 'success',
                title: 'Установлены значения по умолчанию',
                text: 'Количество: 10, Дата: через 7 дней',
                timer: 2000
            });
        };
        
        // Предотвращение отправки формы при нажатии Enter
        $('#limitForm').on('keypress', function(e) {
            if (e.which === 13 && !$(e.target).is('textarea, select')) {
                e.preventDefault();
                return false;
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
        
        // Если есть ошибки, показываем информацию о выбранном пользователе
        @if(old('user_id'))
            $('#user_id').trigger('change');
        @endif
    });
</script>
@endpush