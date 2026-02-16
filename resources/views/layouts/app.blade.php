<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CheckLic')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        .sidebar {
            background-color: #ffffff;
            min-height: 100vh;
            color: #333;
            border-right: 1px solid #dee2e6;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }
        
        .nav-link {
            color: #495057 !important;
            padding: 10px 15px;
            margin: 2px 10px;
            border-radius: 5px;
            transition: all 0.2s;
        }
        
        .nav-link:hover {
            color: #0d6efd !important;
            background-color: rgba(13, 110, 253, 0.05);
        }
        
        .nav-link.active {
            color: #ffffff !important;
            background-color: #0d6efd;
            font-weight: 500;
        }
        
        .nav-icon {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }
        
        .main-content {
            padding: 20px;
            background-color: #f8f9fa;
        }
        
        .user-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
        }
        
        .sidebar-section {
            color: #6c757d;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .logout-btn {
            border: 1px solid #dc3545;
        }
        
        .logout-btn:hover {
            background-color: #dc3545;
            color: white !important;
        }

        /* Select2 Custom Styles */
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 2px);
            border: 1px solid #ced4da;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(2.25rem + 2px);
            color: #495057;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2.25rem + 2px);
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #0d6efd;
        }
        .select2-container--default .select2-search__field {
            border-radius: 4px !important;
            border: 1px solid #ced4da !important;
            padding: 6px !important;
        }
        .select2-container--default .select2-results__option {
            padding: 8px 12px;
        }
        .select2-dropdown {
            border-color: #ced4da;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <!-- User Info -->
                <div class="p-3 text-center user-info">
                    @auth
                        <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-2" 
                             style="width: 60px; height: 60px; color: white; font-size: 20px;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <h6 class="mb-1 fw-bold">{{ Auth::user()->name }}</h6>
                        <small class="text-muted">
                            @if(Auth::user()->isAdmin())
                                <span class="badge bg-danger">Администратор</span>
                            @elseif(Auth::user()->role === 'manager')
                                <span class="badge bg-primary">Менеджер</span>
                            @elseif(Auth::user()->role === 'org_owner')
                                <span class="badge bg-success">Владелец</span>
                            @elseif(Auth::user()->role === 'org_member')
                                <span class="badge bg-info">Сотрудник</span>
                            @else
                                <span class="badge bg-secondary">Пользователь</span>
                            @endif
                        </small>
                    @else
                        <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-2" 
                             style="width: 60px; height: 60px; color: white; font-size: 20px;">
                            <i class="bi bi-person"></i>
                        </div>
                        <h6 class="mb-1">Гость</h6>
                        <small class="text-muted">
                            <span class="badge bg-secondary">Не авторизован</span>
                        </small>
                    @endauth
                </div>

                <!-- Navigation -->
                <nav class="nav flex-column p-3">
                    @auth
                        <!-- Admin Section -->
                    @if(Auth::user()->isAdmin())
                        <div class="sidebar-section px-3 py-2">Администрирование</div>
                        <a href="{{ route('admin.dashboard') }}" 
                        class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            Панель админа
                        </a>   
                        
                        <!-- ДОБАВЛЕНО: Список организаций -->
                        <a href="{{ route('admin.organizations.list') }}" 
                        class="nav-link {{ request()->routeIs('admin.organizations.list') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-buildings"></i>
                            Все организации
                        </a>
                        
                        <a href="{{ route('limits.index') }}" 
                        class="nav-link {{ request()->routeIs('limits.index') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-pie-chart"></i>
                            Управление отчетами
                        </a>
                    @endif

                    <!-- Manager Section -->
                    @if(Auth::user()->role === 'manager')
                        <div class="sidebar-section px-3 py-2">Управление</div>
                        <a href="{{ route('manager.dashboard') }}" 
                        class="nav-link {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer"></i>
                            Панель менеджера
                        </a>
                        
                        <!-- ДОБАВЛЕНО: Мои организации -->
                        <a href="{{ route('manager.organizations.list') }}" 
                        class="nav-link {{ request()->routeIs('manager.organizations.list') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-buildings"></i>
                            Мои организации
                        </a>
                        
                        <a href="{{ route('limits.index') }}" 
                        class="nav-link {{ request()->routeIs('limits.index') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-pie-chart"></i>
                            Управление отчетами
                        </a>
                        
                    @endif
                        <!-- Owner Section -->
                        @if(Auth::user()->role === 'org_owner')
                            <div class="sidebar-section px-3 py-2">Управление</div>
                            <a href="{{ route('owner.dashboard') }}" 
                               class="nav-link {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-speedometer"></i>
                                Панель владельца
                            </a>
                        @endif
                        
                        <!-- Member Section -->
                        @if(Auth::user()->role === 'org_member')
                            <div class="sidebar-section px-3 py-2">Рабочее место</div>
                            
                            <a href="{{ route('member.profile') }}" 
                               class="nav-link {{ request()->routeIs('member.profile') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-person"></i>
                                Мой профиль
                            </a>
                        @endif
                        
                        <!-- Common Section -->
                        <div class="sidebar-section px-3 py-2">Отчеты</div>
                        <a href="{{ route('reports.create') }}" 
                           class="nav-link {{ request()->routeIs('reports.create') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-file-earmark-plus"></i>
                            Создать отчет
                        </a>
                        
                        <a href="{{ route('reports.index') }}" 
                           class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-files"></i>
                            Мои отчеты
                        </a>
                        
                        <!-- Logout -->
                        <div class="mt-4 pt-3 border-top">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm w-100 logout-btn">
                                    <i class="bi bi-box-arrow-right"></i> Выйти из системы
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Для неавторизованных пользователей -->
                        <div class="sidebar-section px-3 py-2">Гость</div>
                        <a href="{{ route('login') }}" 
                           class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-box-arrow-in-right"></i>
                            Войти в систему
                        </a>
                    @endauth
                </nav>
            </div>
            
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 main-content">
                <!-- Page Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1 class="h2 fw-bold text-dark">@yield('title', 'CheckLic')</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        @yield('header-buttons')
                    </div>
                </div>
                
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-octagon me-2"></i>
                        <strong>Ошибка!</strong> Пожалуйста, исправьте следующие ошибки:
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <!-- Main Content -->
                <div class="content-wrapper">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- JavaScript в правильном порядке -->
    
    <!-- 1. jQuery (ОБЯЗАТЕЛЬНО ПЕРВЫМ) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- 2. Bootstrap (зависит от jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- 3. SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- 4. Select2 (зависит от jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ru.js"></script>
    
    <!-- 5. Custom Scripts -->
    <script>
        // Проверка загрузки jQuery и Select2
        $(document).ready(function() {
            console.log('✓ jQuery version:', $.fn.jquery);
            console.log('✓ Select2 loaded:', typeof $.fn.select2 !== 'undefined');
            console.log('✓ SweetAlert2 loaded:', typeof Swal !== 'undefined');
        });

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    
    <!-- 6. Stack скриптов из дочерних шаблонов -->
    @stack('scripts')
</body>
</html>