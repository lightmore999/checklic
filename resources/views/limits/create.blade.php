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

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Выбор пользователя с AJAX поиском -->
                                <div class="form-group mb-3">
                                    <label for="user_id" class="form-label fw-bold">
                                        <i class="fas fa-user"></i> Пользователь *
                                    </label>
                                    <select name="user_id" id="user_id" 
                                            class="form-control" 
                                            required
                                            style="width: 100%;">
                                        <option value=""></option>
                                    </select>
                                    @error('user_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Количество -->
                                <div class="form-group mb-3">
                                    <label for="quantity" class="form-label fw-bold">
                                        <i class="fas fa-sort-amount-up"></i> Количество лимитов *
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="quantity" id="quantity" 
                                               class="form-control @error('quantity') is-invalid @enderror"
                                               value="{{ old('quantity', 0) }}" 
                                               min="0" 
                                               max="9999"
                                               required>
                                        <span class="input-group-text">шт.</span>
                                    </div>
                                    @error('quantity')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Тип отчета -->
                                <div class="form-group mb-3">
                                    <label for="report_type_id" class="form-label fw-bold">
                                        <i class="fas fa-file-alt"></i> Тип отчета *
                                    </label>
                                    <select name="report_type_id" id="report_type_id" 
                                            class="form-control" 
                                            required
                                            style="width: 100%;">
                                        <option value=""></option>
                                        @foreach($reportTypes as $type)
                                            <option value="{{ $type->id }}" 
                                                {{ old('report_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('report_type_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Дата действия -->
                                <div class="form-group mb-3">
                                    <label for="date_created" class="form-label fw-bold">
                                        <i class="fas fa-calendar-alt"></i> Дата действия лимита *
                                    </label>
                                    <input type="date" name="date_created" id="date_created" 
                                           class="form-control @error('date_created') is-invalid @enderror"
                                           value="{{ old('date_created', now()->format('Y-m-d')) }}" 
                                           min="{{ now()->format('Y-m-d') }}"
                                           required>
                                    @error('date_created')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
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
                                                <span class="badge bg-success">Активен</span>
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
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    /* Минимальные стили для Select2 */
    .select2-container {
        width: 100% !important;
    }
    .select2-selection {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 4px !important;
    }
    .select2-selection__rendered {
        line-height: 38px !important;
        padding-left: 12px !important;
    }
    .select2-selection__arrow {
        height: 38px !important;
    }
    .select2-dropdown {
        border-color: #ced4da !important;
    }
</style>
@endpush

@push('scripts')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/ru.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    console.log('Инициализация...');
    
    // Инициализация Select2 для пользователей
    $('#user_id').select2({
        placeholder: 'Введите имя или email для поиска...',
        allowClear: true,
        minimumInputLength: 0,
        language: 'ru',
        ajax: {
            url: '{{ route("users.search") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    search: params.term || ''
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(user) {
                        return {
                            id: user.id,
                            text: user.name + ' (' + user.email + ')',
                            role_display: user.role_display,
                            organization: user.organization
                        };
                    })
                };
            }
        }
    });

    // Инициализация Select2 для типов отчетов
    $('#report_type_id').select2({
        placeholder: 'Выберите тип отчета...',
        allowClear: true,
        language: 'ru'
    });

    // Обработка выбора пользователя
    $('#user_id').on('select2:select', function(e) {
        var data = e.params.data;
        $('#userRole').text(data.role_display || 'Не указана');
        $('#userOrganization').text(data.organization || 'Не указана');
        $('#userInfo').show();
    });

    $('#user_id').on('select2:clear', function() {
        $('#userInfo').hide();
    });

    // Быстрые значения
    window.setDefaultValues = function() {
        $('#quantity').val(10);
        $('#date_created').val('{{ now()->addDays(7)->format("Y-m-d") }}');
        Swal.fire('Установлены значения по умолчанию', '', 'success');
    };
});
</script>
@endpush