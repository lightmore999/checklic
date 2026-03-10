@extends('layouts.app')

@section('title', 'Отчет в обработке')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-hourglass-split me-2"></i>
                        Отчет #{{ $report->id }} в обработке
                    </h5>
                </div>
                
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                    </div>
                    
                    <h4 class="mb-3">Отчет формируется</h4>
                    
                    @if(isset($progress) && $progress['total'] > 0)
                        <div class="mb-4">
                            <p class="text-muted">
                                Выполнено {{ $progress['completed'] + $progress['failed'] }} из {{ $progress['total'] }} запросов
                            </p>
                            <div class="progress mx-auto" style="height: 10px; max-width: 300px;">
                                <div class="progress-bar bg-{{ $progress['percentage'] > 80 ? 'success' : 'info' }}" 
                                     style="width: {{ $progress['percentage'] }}%">
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">{{ $progress['percentage'] }}%</small>
                        </div>
                    @endif
                    
                    <p class="text-muted mb-4">
                        Отчет будет доступен через несколько секунд.<br>
                        Страница обновится автоматически, когда отчет будет готов.
                    </p>
                    
                    <div class="d-flex justify-content-center gap-2">
                        <button onclick="window.location.reload()" class="btn btn-primary">
                            <i class="bi bi-arrow-clockwise me-1"></i> Обновить вручную
                        </button>
                        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-list-ul me-1"></i> К списку отчетов
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Автообновление каждые 5 секунд
    setTimeout(function() {
        window.location.reload();
    }, 5000);
</script>
@endpush