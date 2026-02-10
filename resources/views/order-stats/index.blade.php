<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статистика заказов</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Заголовок и фильтр -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="mb-0">Статистика заказов</h1>
                @if($period !== 'all')
                    <p class="text-muted">
                        Период: 
                        @php
                            $periodNames = [
                                '1hour' => 'последний час',
                                '24hours' => 'последние 24 часа',
                                '7days' => 'последние 7 дней',
                                '1month' => 'последний месяц',
                                'all' => 'все время'
                            ];
                        @endphp
                        {{ $periodNames[$period] }}
                    </p>
                @endif
            </div>
            <div class="col-md-4">
                <form method="GET" action="{{ route('order-stats') }}" class="mb-3">
                    <div class="input-group">
                        <select name="period" class="form-select" onchange="this.form.submit()">
                            <option value="all" {{ $period == 'all' ? 'selected' : '' }}>Все время</option>
                            <option value="1hour" {{ $period == '1hour' ? 'selected' : '' }}>Последний час</option>
                            <option value="24hours" {{ $period == '24hours' ? 'selected' : '' }}>Последние 24 часа</option>
                            <option value="7days" {{ $period == '7days' ? 'selected' : '' }}>Последние 7 дней</option>
                            <option value="1month" {{ $period == '1month' ? 'selected' : '' }}>Последний месяц</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Общая статистика -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow">
                    <div class="card-body text-center p-4">
                        <h6 class="text-muted mb-2">📊 ОБЩЕЕ СРЕДНЕЕ ВРЕМЯ ОБРАБОТКИ</h6>
                        <h1 class="display-4 mb-3">{{ $avgTime }}</h1>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted mb-0">
                                    <small>Всего заказов:</small><br>
                                    <span class="fw-bold fs-5">{{ $totalOrders }}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-0">
                                    <small>Формат:</small><br>
                                    <span class="fw-bold">Дни:Часы:Минуты:Секунды</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Статистика по статусам -->
        <h3 class="text-center mb-4">Распределение по статусам</h3>
        
        <div class="row g-4 mb-5">
            <!-- OK -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                            <span>✅</span>
                        </div>
                        <h6 class="text-muted mb-2">OK</h6>
                        <h2 class="mb-0">{{ $statusStats['ok'] }}</h2>
                        @if($totalOrders > 0)
                            <p class="text-muted mb-0">
                                <small>{{ round($statusStats['ok'] / $totalOrders * 100, 1) }}%</small>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- WAIT -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                            <span>⏳</span>
                        </div>
                        <h6 class="text-muted mb-2">ОЖИДАНИЕ</h6>
                        <h2 class="mb-0">{{ $statusStats['wait'] }}</h2>
                        @if($totalOrders > 0)
                            <p class="text-muted mb-0">
                                <small>{{ round($statusStats['wait'] / $totalOrders * 100, 1) }}%</small>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- ERROR -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                            <span>❌</span>
                        </div>
                        <h6 class="text-muted mb-2">ОШИБКИ</h6>
                        <h2 class="mb-0">{{ $statusStats['error'] }}</h2>
                        @if($totalOrders > 0)
                            <p class="text-muted mb-0">
                                <small>{{ round($statusStats['error'] / $totalOrders * 100, 1) }}%</small>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- PROCESSING -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                            <span>⚙️</span>
                        </div>
                        <h6 class="text-muted mb-2">В РАБОТЕ</h6>
                        <h2 class="mb-0">{{ $statusStats['processing'] }}</h2>
                        @if($totalOrders > 0)
                            <p class="text-muted mb-0">
                                <small>{{ round($statusStats['processing'] / $totalOrders * 100, 1) }}%</small>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Пояснение -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">📝 Как считается время</h6>
                <ul class="mb-0">
                    <li><strong>Общее среднее время</strong> = (сумма времени всех заказов) / (количество заказов)</li>
                    <li>Время = разница между <code>created_at</code> и <code>updated_at</code></li>
                    <li>Если <code>updated_at</code> = NULL, используется текущее время</li>
                    <li>Фильтр применяется только к дате создания заказа (<code>created_at</code>)</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        // Авто-обновление при выборе периода
        document.querySelector('select[name="period"]').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html>