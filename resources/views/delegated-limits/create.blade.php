@extends('layouts.app')

@section('title', 'Делегирование лимита')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-share-alt"></i> Делегирование лимита
                    </h3>
                </div>
                
                <div class="card-body">
                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('delegated-limits.store') }}" id="delegateForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Выбор лимита для делегирования -->
                                <div class="form-group">
                                    <label for="limit_id" class="font-weight-bold">
                                        <i class="fas fa-tachometer-alt"></i> Лимит для делегирования *
                                    </label>
                                    <select name="limit_id" id="limit_id" 
                                            class="form-control select2 @error('limit_id') is-invalid @enderror" 
                                            required
                                            data-placeholder="Выберите лимит...">
                                        <option value=""></option>
                                        @foreach($limits as $limit)
                                            <option value="{{ $limit->id }}" 
                                                {{ old('limit_id', $selectedLimitId) == $limit->id ? 'selected' : '' }}
                                                data-owner="{{ $limit->user->name }}"
                                                data-type="{{ $limit->reportType->name }}"
                                                data-quantity="{{ $limit->quantity }}"
                                                data-date="{{ $limit->date_created->format('d.m.Y') }}">
                                                {{ $limit->reportType->name }} 
                                                ({{ $limit->user->name }})
                                                - {{ $limit->quantity }} шт.
                                                - {{ $limit->date_created->format('d.m.Y') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('limit_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Выберите лимит, который хотите делегировать
                                    </small>
                                </div>

                                <!-- Информация о выбранном лимите -->
                                <div class="card border-info mb-3" id="limitInfo" style="display: none;">
                                    <div class="card-header bg-info text-white py-2">
                                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Информация о лимите</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Владелец:</strong><br>
                                                <span id="limitOwner"></span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Тип отчета:</strong><br>
                                                <span id="limitType"></span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Доступно:</strong><br>
                                                <span class="badge bg-success" id="limitQuantity"></span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Дата:</strong><br>
                                                <span id="limitDate"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Выбор получателя -->
                                <div class="form-group">
                                    <label for="user_id" class="font-weight-bold">
                                        <i class="fas fa-user"></i> Получатель лимита *
                                    </label>
                                    <select name="user_id" id="user_id" 
                                            class="form-control select2 @error('user_id') is-invalid @enderror" 
                                            required
                                            data-placeholder="Выберите получателя...">
                                        <option value=""></option>
                                        @foreach($availableUsers as $availableUser)
                                            <option value="{{ $availableUser->id }}" 
                                                {{ old('user_id') == $availableUser->id ? 'selected' : '' }}
                                                data-role="{{ $availableUser->role }}"
                                                data-email="{{ $availableUser->email }}">
                                                {{ $availableUser->name }} 
                                                <small class="text-muted">({{ $availableUser->email }})</small>
                                                - {{ $availableUser->getRoleDisplayName() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Выберите сотрудника, которому делегируете лимит
                                    </small>
                                </div>

                                <!-- Количество для делегирования -->
                                <div class="form-group">
                                    <label for="quantity" class="font-weight-bold">
                                        <i class="fas fa-sort-amount-up"></i> Количество для делегирования *
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="quantity" id="quantity" 
                                               class="form-control @error('quantity') is-invalid @enderror"
                                               value="{{ old('quantity', 1) }}" 
                                               min="1" 
                                               max="1000"
                                               required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">шт.</span>
                                        </div>
                                    </div>
                                    @error('quantity')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Максимально доступно: <span id="maxQuantity">0</span> шт.
                                    </small>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary mr-2" onclick="setQuantity(5)">
                                            +5
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary mr-2" onclick="setQuantity(10)">
                                            +10
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setMaxQuantity()">
                                            Максимум
                                        </button>
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
                                            <i class="fas fa-share-alt"></i> Делегировать лимит
                                        </button>
                                    </div>
                                    <div>
                                        <a href="{{ route('delegated-limits.index') }}" class="btn btn-secondary">
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
                        <strong>Владелец организации</strong> может делегировать свои лимиты только своим сотрудникам.<br>
                        <strong>Менеджер/Администратор</strong> может делегировать лимиты владельцев своим сотрудникам.
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
        
        // Показ информации о выбранном лимите
        $('#limit_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            if (selectedOption.val()) {
                $('#limitOwner').text(selectedOption.data('owner'));
                $('#limitType').text(selectedOption.data('type'));
                $('#limitQuantity').text(selectedOption.data('quantity') + ' шт.');
                $('#limitDate').text(selectedOption.data('date'));
                $('#maxQuantity').text(selectedOption.data('quantity'));
                $('#quantity').attr('max', selectedOption.data('quantity'));
                $('#limitInfo').show();
            } else {
                $('#limitInfo').hide();
                $('#maxQuantity').text('0');
            }
        });
        
        // Установка количества
        window.setQuantity = function(amount) {
            var current = parseInt($('#quantity').val()) || 1;
            var max = parseInt($('#quantity').attr('max')) || 1000;
            var newValue = current + amount;
            
            if (newValue < 1) newValue = 1;
            if (newValue > max) newValue = max;
            
            $('#quantity').val(newValue);
        };
        
        window.setMaxQuantity = function() {
            var max = parseInt($('#quantity').attr('max')) || 1000;
            $('#quantity').val(max);
        };
        
        // Если есть ошибки или предварительно выбранный лимит
        @if(old('limit_id') || $selectedLimitId)
            $('#limit_id').trigger('change');
        @endif
        
        // Валидация формы
        $('#delegateForm').on('submit', function(e) {
            var quantity = parseInt($('#quantity').val()) || 0;
            var max = parseInt($('#quantity').attr('max')) || 0;
            
            if (quantity > max) {
                e.preventDefault();
                alert('Нельзя делегировать больше, чем доступно у владельца!');
                return false;
            }
            
            if (quantity <= 0) {
                e.preventDefault();
                alert('Количество должно быть больше 0!');
                return false;
            }
        });
    });
</script>
@endpush