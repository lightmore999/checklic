@extends('layouts.app')

@section('title', 'Редактирование подписки')
@section('page-icon', 'bi-pencil')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-pencil text-info me-2"></i>
            Редактирование подписки #{{ $subscription->id }}
        </h2>
        <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>
            Назад к списку
        </a>
    </div>

    <!-- Флеш-сообщения -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Информация о подписке -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0">
                <i class="bi bi-info-circle me-2"></i>
                Информация о подписке
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted d-block">ID подписки</small>
                    <span class="fw-semibold">#{{ $subscription->id }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Текущее название</small>
                    <span class="fw-semibold">{{ $subscription->name ?? '—' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Пользователь</small>
                    <span class="fw-semibold">{{ $subscription->user->name ?? '—' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Общая сумма лимитов</small>
                    <span class="fw-semibold fs-5 text-primary">{{ $totalLimits }}</span>
                    <span class="text-muted"> шт.</span>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <small class="text-muted d-block">Важно</small>
                    <span class="text-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Общая сумма лимитов должна остаться неизменной ({{ $totalLimits }}). 
                        Вы можете перераспределять количество между типами отчетов.
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Форма редактирования -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0">
                <i class="bi bi-pencil me-2"></i>
                Редактирование
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('subscriptions.update', $subscription) }}" id="limitsForm">
                @csrf
                @method('PUT')
                
                <!-- Название подписки -->
                <div class="mb-4">
                    <label for="name" class="form-label fw-semibold">
                        <i class="bi bi-tag me-1"></i>
                        Название подписки
                    </label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $subscription->name) }}"
                           placeholder="Введите название подписки (необязательно)">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">

                <!-- Таблица лимитов -->
                <h6 class="mb-3">
                    <i class="bi bi-file-text me-2"></i>
                    Лимиты по типам отчетов
                </h6>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Тип отчета</th>
                                <th class="text-center" width="120">Текущее</th>
                                <th class="text-center" width="120">Использовано</th>
                                <th class="text-center" width="120">Новое значение</th>
                                <th class="text-center" width="200">Баланс</th>
                            </tr>
                        </thead>
                        <tbody id="limitsTableBody">
                            @foreach($limitsData as $reportTypeId => $data)
                                @if(!$data['only_api'] || $data['current_quantity'] > 0)
                                <tr>
                                    <td>
                                        <strong>{{ $data['name'] }}</strong>
                                        @if($data['only_api'])
                                            <span class="badge bg-warning ms-2">API</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-semibold">{{ $data['current_quantity'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $data['used_quantity'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" 
                                               class="form-control form-control-sm text-center limit-input" 
                                               name="limits[{{ $reportTypeId }}]" 
                                               value="{{ $data['current_quantity'] }}"
                                               min="0"
                                               data-original="{{ $data['current_quantity'] }}"
                                               data-used="{{ $data['used_quantity'] }}"
                                               data-name="{{ $data['name'] }}"
                                               step="1"
                                               style="width: 100px; margin: 0 auto;">
                                        @if($data['used_quantity'] > 0)
                                            <small class="text-muted d-block">мин. {{ $data['used_quantity'] }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $change = $data['current_quantity'] - $data['used_quantity'];
                                            $class = $change > 0 ? 'text-success' : ($change < 0 ? 'text-danger' : 'text-muted');
                                            $sign = $change > 0 ? '+' : '';
                                        @endphp
                                        <span class="{{ $class }} balance-indicator" data-report="{{ $reportTypeId }}">
                                            {{ $sign }}{{ number_format($data['current_quantity'] - $data['used_quantity'], 0, ',', ' ') }}
                                        </span>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td><strong>Итого:</strong></td>
                                <td class="text-center"><strong id="totalCurrent">{{ $totalLimits }}</strong></td>
                                <td class="text-center"><strong id="totalUsed">{{ collect($limitsData)->sum('used_quantity') }}</strong></td>
                                <td class="text-center"><strong id="totalNew">{{ $totalLimits }}</strong></td>
                                <td class="text-center">
                                    <span id="balanceTotal" class="fw-bold text-success">0</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Предупреждение о несоответствии -->
                <div class="alert alert-warning d-none mt-3" id="warningMessage">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="warningText"></span>
                </div>

                <!-- Кнопки действий -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>
                        Отмена
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-save me-1"></i>
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .limit-input {
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .limit-input.changed {
        border-color: #ffc107;
        background-color: #fff3cd;
    }
    .limit-input.error {
        border-color: #dc3545;
        background-color: #f8d7da;
    }
    .balance-indicator {
        font-weight: 500;
        transition: color 0.2s;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.limit-input');
    const totalNewSpan = document.getElementById('totalNew');
    const warningMessage = document.getElementById('warningMessage');
    const warningText = document.getElementById('warningText');
    const form = document.getElementById('limitsForm');
    const originalTotal = {{ $totalLimits }};

    function updateTotals() {
        let total = 0;
        let hasError = false;
        let errorMessages = [];

        inputs.forEach(input => {
            const value = parseInt(input.value) || 0;
            total += value;

            const minValue = parseInt(input.dataset.used);
            if (value < minValue) {
                input.classList.add('error');
                input.classList.remove('changed');
                hasError = true;
                errorMessages.push(`"${input.dataset.name}" не может быть меньше ${minValue} (уже использовано)`);
            } else {
                input.classList.remove('error');
                
                if (value !== parseInt(input.dataset.original)) {
                    input.classList.add('changed');
                } else {
                    input.classList.remove('changed');
                }
            }

            const reportId = input.name.match(/\[(\d+)\]/)[1];
            const balanceSpan = document.querySelector(`.balance-indicator[data-report="${reportId}"]`);
            if (balanceSpan) {
                const balance = value - parseInt(input.dataset.used);
                balanceSpan.textContent = balance > 0 ? '+' + balance : balance;
                balanceSpan.className = balance > 0 ? 'text-success balance-indicator' : 
                                       (balance < 0 ? 'text-danger balance-indicator' : 'text-muted balance-indicator');
            }
        });

        totalNewSpan.textContent = total;

        if (total !== originalTotal) {
            hasError = true;
            errorMessages.push(`Общая сумма должна быть ${originalTotal}, а сейчас ${total}`);
        }

        if (hasError) {
            warningMessage.classList.remove('d-none');
            warningText.textContent = errorMessages.join('. ');
            document.getElementById('submitBtn').disabled = true;
        } else {
            warningMessage.classList.add('d-none');
            document.getElementById('submitBtn').disabled = false;
        }

        totalNewSpan.className = total !== originalTotal ? 'text-danger' : '';
    }

    inputs.forEach(input => {
        input.addEventListener('input', updateTotals);
        input.addEventListener('change', updateTotals);
    });

    form.addEventListener('submit', function(e) {
        let total = 0;
        inputs.forEach(input => {
            total += parseInt(input.value) || 0;
        });

        if (total !== originalTotal) {
            e.preventDefault();
            alert(`Общая сумма лимитов должна быть ${originalTotal}. Сейчас ${total}`);
        }
    });

    updateTotals();
});
</script>
@endpush
@endsection