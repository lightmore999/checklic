<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportType;
use App\Models\DelegatedLimit;
use App\Models\Limit;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ReportController extends Controller
{
    /**
     * Показать список отчетов
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Начинаем запрос с подгрузкой связей
        $query = Report::with(['reportType', 'user', 'user.orgOwnerProfile', 'user.orgMemberProfile'])
            ->orderBy('created_at', 'desc');
        
        // === БАЗОВАЯ ФИЛЬТРАЦИЯ ПО ПРАВАМ ДОСТУПА ===
        
        // 1. АДМИНИСТРАТОР - видит всё
        if ($user->isAdmin()) {
            // Без ограничений
        }
        // 2. МЕНЕДЖЕР - только организации, которые он ведет, и их сотрудников
        elseif ($user->isManager()) {
            $query->whereHas('user', function($q) use ($user) {
                // Пользователь является владельцем организации, которую ведет менеджер
                $q->whereHas('orgOwnerProfile', function($q2) use ($user) {
                    $q2->whereHas('organization', function($q3) use ($user) {
                        $q3->whereHas('manager', function($q4) use ($user) {
                            $q4->where('user_id', $user->id);
                        });
                    });
                })
                // ИЛИ пользователь является сотрудником организации, которую ведет менеджер
                ->orWhereHas('orgMemberProfile', function($q2) use ($user) {
                    $q2->whereHas('organization', function($q3) use ($user) {
                        $q3->whereHas('manager', function($q4) use ($user) {
                            $q4->where('user_id', $user->id);
                        });
                    });
                })
                // ИЛИ это сам менеджер
                ->orWhere('id', $user->id);
            });
        }
        // 3. ВЛАДЕЛЕЦ ОРГАНИЗАЦИИ - только свои отчеты и отчеты подчиненных
        elseif ($user->isOrgOwner()) {
            $organizationId = $user->orgOwnerProfile->organization_id ?? null;
            
            $query->whereHas('user', function($q) use ($user, $organizationId) {
                // Сам владелец
                $q->where('id', $user->id)
                // ИЛИ его подчиненные
                ->orWhereHas('orgMemberProfile', function($q2) use ($organizationId, $user) {
                    $q2->where('organization_id', $organizationId)
                        ->where('boss_id', $user->id);
                });
            });
        }
        // 4. ОБЫЧНЫЙ ПОЛЬЗОВАТЕЛЬ - только свои отчеты
        else {
            $query->where('user_id', $user->id);
        }
        
        // === ФИЛЬТРАЦИЯ ПО ОРГАНИЗАЦИИ ===
        if ($request->filled('organization_id')) {
            $organizationId = $request->organization_id;
            
            // Проверяем доступ к организации
            $hasAccess = false;
            
            if ($user->isAdmin()) {
                $hasAccess = true;
            } elseif ($user->isManager()) {
                // Менеджер имеет доступ только к своим организациям
                $hasAccess = Organization::where('id', $organizationId)
                    ->whereHas('manager', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })->exists();
            } elseif ($user->isOrgOwner()) {
                // Владелец имеет доступ только к своей организации
                $ownerOrgId = $user->orgOwnerProfile->organization_id ?? null;
                $hasAccess = ($ownerOrgId == $organizationId);
            }
            
            if ($hasAccess) {
                $query->whereHas('user', function($q) use ($organizationId) {
                    // Пользователь является владельцем этой организации
                    $q->whereHas('orgOwnerProfile', function($q2) use ($organizationId) {
                        $q2->where('organization_id', $organizationId);
                    })
                    // ИЛИ пользователь является сотрудником этой организации
                    ->orWhereHas('orgMemberProfile', function($q2) use ($organizationId) {
                        $q2->where('organization_id', $organizationId);
                    });
                });
            }
        }
        
        // === ФИЛЬТРАЦИЯ ПО ПОЛЬЗОВАТЕЛЮ ===
        if ($request->filled('user_id')) {
            $targetUserId = $request->user_id;
            
            // Проверяем, имеет ли текущий пользователь доступ к отчетам этого пользователя
            $hasAccess = false;
            
            if ($user->isAdmin()) {
                $hasAccess = true;
            }
            elseif ($user->isManager()) {
                // Проверяем, относится ли пользователь к организациям менеджера
                $hasAccess = User::where('id', $targetUserId)
                    ->where(function($q) use ($user) {
                        $q->whereHas('orgOwnerProfile.organization.manager', function($q2) use ($user) {
                            $q2->where('user_id', $user->id);
                        })->orWhereHas('orgMemberProfile.organization.manager', function($q2) use ($user) {
                            $q2->where('user_id', $user->id);
                        })->orWhere('id', $user->id);
                    })->exists();
            }
            elseif ($user->isOrgOwner()) {
                $organizationId = $user->orgOwnerProfile->organization_id ?? null;
                $hasAccess = User::where('id', $targetUserId)
                    ->where(function($q) use ($user, $organizationId) {
                        $q->where('id', $user->id)
                        ->orWhereHas('orgMemberProfile', function($q2) use ($organizationId, $user) {
                            $q2->where('organization_id', $organizationId)
                                ->where('boss_id', $user->id);
                        });
                    })->exists();
            }
            
            if ($hasAccess) {
                $query->where('user_id', $targetUserId);
            } elseif (!$request->filled('organization_id')) {
                // Если нет доступа и не выбран фильтр организации - показываем только свои
                $query->where('user_id', $user->id);
            }
        }
        
        // === ОСТАЛЬНЫЕ ФИЛЬТРЫ ===
        
        // Фильтрация по типу отчета
        if ($request->filled('report_type_id')) {
            $query->where('report_type_id', $request->report_type_id);
        }
        
        // Фильтрация по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Фильтрация по дате
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Фильтрация по ФИО
        if ($request->filled('search_name')) {
            $search = $request->search_name;
            $query->where(function($q) use ($search) {
                $q->where('last_name', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('patronymic', 'like', "%{$search}%");
            });
        }
        
        // Фильтрация по паспорту
        if ($request->filled('passport')) {
            $passport = preg_replace('/[^0-9]/', '', $request->passport);
            if (strlen($passport) >= 4) {
                if (strlen($passport) === 10) {
                    $series = substr($passport, 0, 4);
                    $number = substr($passport, 4, 6);
                    $query->where('passport_series', $series)
                        ->where('passport_number', $number);
                } else {
                    $query->where(function($q) use ($passport) {
                        $q->where('passport_series', 'like', "%{$passport}%")
                        ->orWhere('passport_number', 'like', "%{$passport}%");
                    });
                }
            }
        }
        
        // Фильтрация по номеру ТС
        if ($request->filled('vehicle_number')) {
            $query->where('vehicle_number', 'like', "%{$request->vehicle_number}%");
        }
        
        // Фильтрация по кадастровому номеру
        if ($request->filled('cadastral_number')) {
            $query->where('cadastral_number', 'like', "%{$request->cadastral_number}%");
        }
        
        $reports = $query->paginate(20)->withQueryString();
        
        // === ДАННЫЕ ДЛЯ ФИЛЬТРОВ ===
        
        // Организации для фильтра
        $organizations = collect();
        
        if ($user->isAdmin()) {
            // Админ видит все организации
            $organizations = Organization::orderBy('name')->get();
        } elseif ($user->isManager()) {
            // Менеджер видит только свои организации
            $organizations = Organization::whereHas('manager', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->orderBy('name')->get();
        } elseif ($user->isOrgOwner()) {
            // Владелец видит только свою организацию
            $organizationId = $user->orgOwnerProfile->organization_id ?? null;
            if ($organizationId) {
                $organizations = Organization::where('id', $organizationId)->get();
            }
        }
        
        // Пользователи для фильтра
        $users = collect();
        
        if ($request->filled('organization_id')) {
            // Если выбрана организация - показываем только пользователей этой организации
            $organizationId = $request->organization_id;
            
            // Проверяем доступ к организации
            $hasAccess = false;
            
            if ($user->isAdmin()) {
                $hasAccess = true;
            } elseif ($user->isManager()) {
                $hasAccess = Organization::where('id', $organizationId)
                    ->whereHas('manager', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })->exists();
            } elseif ($user->isOrgOwner()) {
                $ownerOrgId = $user->orgOwnerProfile->organization_id ?? null;
                $hasAccess = ($ownerOrgId == $organizationId);
            }
            
            if ($hasAccess) {
                $users = User::where('is_active', true)
                    ->where(function($q) use ($organizationId) {
                        // Владельцы организации
                        $q->whereHas('orgOwnerProfile', function($q2) use ($organizationId) {
                            $q2->where('organization_id', $organizationId);
                        })
                        // Сотрудники организации
                        ->orWhereHas('orgMemberProfile', function($q2) use ($organizationId) {
                            $q2->where('organization_id', $organizationId);
                        });
                    })
                    ->orderBy('name')
                    ->get();
            }
        } else {
            // Если организация не выбрана - показываем пользователей на основе роли
            if ($user->isAdmin()) {
                $users = User::where('is_active', true)->orderBy('name')->get();
            } elseif ($user->isManager()) {
                $organizationIds = Organization::whereHas('manager', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->pluck('id');
                
                $users = User::where('id', $user->id)
                    ->orWhereHas('orgOwnerProfile', function($q) use ($organizationIds) {
                        $q->whereIn('organization_id', $organizationIds);
                    })
                    ->orWhereHas('orgMemberProfile', function($q) use ($organizationIds) {
                        $q->whereIn('organization_id', $organizationIds);
                    })
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
            } elseif ($user->isOrgOwner()) {
                $organizationId = $user->orgOwnerProfile->organization_id ?? null;
                if ($organizationId) {
                    $users = User::where('id', $user->id)
                        ->orWhereHas('orgMemberProfile', function($q) use ($organizationId, $user) {
                            $q->where('organization_id', $organizationId)
                            ->where('boss_id', $user->id);
                        })
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();
                }
            }
        }
        
        // Статусы для фильтра
        $statuses = [
            Report::STATUS_PENDING => 'В ожидании',
            Report::STATUS_PROCESSING => 'В обработке',
            Report::STATUS_COMPLETED => 'Завершен',
            Report::STATUS_FAILED => 'Ошибка',
            Report::STATUS_CANCELLED => 'Отменен',
        ];
        
        $reportTypes = ReportType::all();
        
        return view('reports.index', compact(
            'reports', 
            'reportTypes', 
            'users', 
            'statuses',
            'organizations'
        ));
    }

    /**
     * Показать форму создания отчета
     */
    public function create()
    {
        $user = Auth::user();
        $reportTypes = ReportType::where('only_api', false)->get();
        
        // Группируем типы отчетов по категориям
        $groupedReportTypes = $reportTypes->groupBy(function($item) {
            // Извлекаем префикс (например: "CL:", "AI:")
            if (strpos($item->name, ':') !== false) {
                return explode(':', $item->name)[0];
            }
            return 'Other';
        });
        
        // Получаем доступные лимиты (для информации)
        $availableLimits = collect();
        
        if ($user) {
            // Проверяем делегированные лимиты
            $delegatedLimits = DelegatedLimit::where('user_id', $user->id)
                ->where('is_active', true)
                ->with('limit.reportType')
                ->get();
            
            foreach ($delegatedLimits as $delegated) {
                if ($delegated->getAvailableQuantity() > 0) {
                    $availableLimits->push([
                        'type' => 'delegated',
                        'id' => $delegated->id,
                        'report_type_id' => $delegated->limit->report_type_id,
                        'report_type_name' => $delegated->limit->reportType->name,
                        'available' => $delegated->getAvailableQuantity(),
                    ]);
                }
            }
            
            // Проверяем собственные лимиты (только для владельцев)
            if ($user->isOrgOwner()) {
                $limits = Limit::where('user_id', $user->id)
                    ->with('reportType')
                    ->get();
                
                foreach ($limits as $limit) {
                    if ($limit->getAvailableQuantity() > 0) {
                        $availableLimits->push([
                            'type' => 'limit',
                            'id' => $limit->id,
                            'report_type_id' => $limit->report_type_id,
                            'report_type_name' => $limit->reportType->name,
                            'available' => $limit->getAvailableQuantity(),
                        ]);
                    }
                }
            }
        }
        
        return view('reports.create', compact(
            'reportTypes', 
            'groupedReportTypes',
            'availableLimits'
        ));
    }


    public function store(Request $request)
    {
        $user = Auth::user();


        $validator = Validator::make($request->all(), [
            'report_types' => 'required|array|min:1',
            'report_types.*' => 'exists:report_types,id',
            
            // Все поля делаем nullable, так как проверка будет на фронтенде
            'last_name' => 'nullable|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'patronymic' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'region' => 'nullable|string|max:100',
            'passport_series' => 'nullable|string|max:4',
            'passport_number' => 'nullable|string|max:6',
            'passport_date' => 'nullable|date',
            'vehicle_number' => 'nullable|string|max:50',
            'cadastral_number' => 'nullable|string|max:50',
            'property_type' => 'nullable|string|max:50',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $createdReports = [];
        $errors = [];
        
        // Для каждого выбранного типа отчета создаем отдельный отчет
        foreach ($request->report_types as $reportTypeId) {
            try {
                $reportType = ReportType::findOrFail($reportTypeId);
                
                // Проверяем, есть ли у пользователя лимит для этого типа отчета
                $availableLimit = $this->getUserLimitForReportType($user, $reportTypeId);
                
                if (!$availableLimit) {
                    $errors[] = "Нет доступного лимита для отчета: {$reportType->name}";
                    continue;
                }
                
                // УБРАЛ проверку обязательных полей, так как она на фронтенде
                // Проверяем только минимальные требования для каждого типа
                if (!$this->validateMinimalRequirements($reportTypeId, $request)) {
                    $errors[] = "Не заполнены минимальные требования для отчета: {$reportType->name}";
                    continue;
                }
                
                // Создаем отчет
                $reportData = $this->prepareReportData($request, $reportTypeId);
                $reportData['user_id'] = $user->id;
                $reportData['report_type_id'] = $reportTypeId;
                $reportData['status'] = Report::STATUS_PENDING;
                $reportData['quantity_used'] = 1;
                
                // Привязываем использованный лимит
                if ($availableLimit instanceof DelegatedLimit) {
                    $reportData['delegated_limit_id'] = $availableLimit->id;
                } else {
                    $reportData['limit_id'] = $availableLimit->id;
                }
                
                $report = Report::create($reportData);

                
                // Списываем лимит
                if ($availableLimit instanceof DelegatedLimit) {
                    $availableLimit->useQuantity(1);
                } else {
                    $availableLimit->useQuantity(1);
                }
                
                $createdReports[] = $report;
                
            } catch (\Exception $e) {
                $errors[] = "Ошибка при создании отчета: " . $e->getMessage();
            }
        }
        
        if (!empty($errors) && empty($createdReports)) {
            // Если все отчеты завершились ошибкой
            return redirect()->back()
                ->with('error', implode('<br>', $errors))
                ->withInput();
        }
        
        $message = count($createdReports) . ' отчет(ов) успешно создан.';
        if (!empty($errors)) {
            $message .= '<br>Ошибки: ' . implode('<br>', $errors);
        }
        
        return redirect()->route('reports.index')
            ->with('success', $message);
    }

    /**
     * Проверка минимальных требований для каждого типа отчета
     * Более мягкая проверка, чем была ранее
     */
    private function validateMinimalRequirements($reportTypeId, $request)
    {
        switch ($reportTypeId) {
            case 1: // CL:Базовый V1 - ФИО + дата + регион
                if (empty($request->last_name) || 
                    empty($request->first_name) || 
                    empty($request->patronymic) || 
                    empty($request->birth_date) || 
                    empty($request->region)) {
                    return false;
                }
                break;
                
            case 2: // CL:Паспорт V1 - ВСЕ ПОЛЯ
                // ФИО + дата + регион
                if (empty($request->last_name) || 
                    empty($request->first_name) || 
                    empty($request->patronymic) || 
                    empty($request->birth_date) || 
                    empty($request->region)) {
                    return false;
                }
                
                // Паспортные данные
                if (empty($request->passport_series) || 
                    empty($request->passport_number) || 
                    empty($request->passport_date)) {
                    return false;
                }
                
                // Проверка формата
                if (strlen($request->passport_series) !== 4 || 
                    strlen($request->passport_number) !== 6) {
                    return false;
                }
                break;
                
            case 3: // AI:АвтоИстория V1 - только номер ТС
                if (empty($request->vehicle_number)) {
                    return false;
                }
                break;
                
            case 4: // CL:Недвижимость - кадастр + тип
                if (empty($request->cadastral_number) || 
                    empty($request->property_type)) {
                    return false;
                }
                break;
        }
        
        return true;
    }

    /**
     * Подготовить данные отчета (только нужные поля для типа)
     * Теперь можем оставлять пустые значения, если поле не нужно
     */
    private function prepareReportData($request, $reportTypeId)
    {
        $data = [];
        
        // Базовые поля - всегда сохраняем, если они есть
        $data['last_name'] = $request->last_name ?? null;
        $data['first_name'] = $request->first_name ?? null;
        $data['patronymic'] = $request->patronymic ?? null;
        $data['birth_date'] = $request->birth_date ?? null;
        $data['region'] = $request->region ?? null;
        
        // Специфичные поля в зависимости от типа
        switch ($reportTypeId) {
            case 2: // Паспорт
                $data['passport_series'] = $request->passport_series ?? null;
                $data['passport_number'] = $request->passport_number ?? null;
                $data['passport_date'] = $request->passport_date ?? null;
                break;
                
            case 3: // Авто
                $data['vehicle_number'] = $request->vehicle_number ?? null;
                break;
                
            case 4: // Недвижимость
                $data['cadastral_number'] = $request->cadastral_number ?? null;
                $data['property_type'] = $request->property_type ?? null;
                break;
        }
        
        return $data;
    }

    /**
     * Показать конкретный отчет
     */
    public function show(Report $report)
    {
        $user = Auth::user();
        
        // Проверка прав доступа
        if (!$this->canViewReport($user, $report)) {
            abort(403, 'Доступ запрещен');
        }
        
        return view('reports.show', compact('report'));
    }

    /**
     * Получить доступные лимиты пользователя
     */
    private function getAvailableLimits($user)
    {
        $availableLimits = collect();
        
        // Проверяем делегированные лимиты
        $delegatedLimits = DelegatedLimit::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('limit.reportType')
            ->get();
        
        foreach ($delegatedLimits as $delegated) {
            if ($delegated->getAvailableQuantity() > 0) {
                $availableLimits->push([
                    'type' => 'delegated',
                    'id' => $delegated->id,
                    'report_type_id' => $delegated->limit->report_type_id,
                    'report_type_name' => $delegated->limit->reportType->name,
                    'available' => $delegated->getAvailableQuantity(),
                ]);
            }
        }
        
        // Проверяем собственные лимиты (только для владельцев)
        if ($user->isOrgOwner()) {
            $limits = Limit::where('user_id', $user->id)
                ->with('reportType')
                ->get();
            
            foreach ($limits as $limit) {
                if ($limit->getAvailableQuantity() > 0) {
                    $availableLimits->push([
                        'type' => 'limit',
                        'id' => $limit->id,
                        'report_type_id' => $limit->report_type_id,
                        'report_type_name' => $limit->reportType->name,
                        'available' => $limit->getAvailableQuantity(),
                    ]);
                }
            }
        }
        
        return $availableLimits;
    }

    /**
     * Получить лимит пользователя для конкретного типа отчета
     * Теперь может возвращать null, если лимита нет
     */
    private function getUserLimitForReportType($user, $reportTypeId)
    {
        // 1. ДЛЯ ВСЕХ: Проверяем собственные лимиты пользователя
        $limit = Limit::where('user_id', $user->id)
            ->where('report_type_id', $reportTypeId)
            ->orderBy('date_created', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($limit && $limit->getAvailableQuantity() > 0) {
            return $limit;
        }
        
        // 2. ТОЛЬКО ДЛЯ СОТРУДНИКОВ: Проверяем делегированные лимиты
        if ($user->isOrgMember()) {  // или любая проверка на роль сотрудника
            $delegatedLimit = DelegatedLimit::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereHas('limit', function($q) use ($reportTypeId) {
                    $q->where('report_type_id', $reportTypeId);
                })
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($delegatedLimit && $delegatedLimit->getAvailableQuantity() > 0) {
                return $delegatedLimit;
            }
        }
        
        return null;
    }

    /**
     * Проверить обязательные поля для типа отчета
     */
    private function validateRequiredFields($reportTypeId, $request)
    {
        // Определяем обязательные поля для каждого типа отчета
        $requiredFields = [
            1 => ['last_name', 'first_name'], // CL:Базовый V1
            2 => ['passport_series', 'passport_number'], // CL:Паспорт V1
            3 => ['vehicle_number'], // AI:АвтоИстория V1
            4 => ['cadastral_number', 'property_type'], // CL:Недвижимость
        ];
        
        if (!isset($requiredFields[$reportTypeId])) {
            return true; // Если нет требований, все ок
        }
        
        foreach ($requiredFields[$reportTypeId] as $field) {
            if (empty($request->$field)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Проверка прав на просмотр отчета
     */
    private function canViewReport($user, $report)
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }
        
        if ($user->isOrgOwner()) {
            // Владелец может видеть отчеты своих сотрудников
            if ($report->user->isOrgMember()) {
                $memberProfile = $report->user->orgMemberProfile;
                return $memberProfile && 
                       $memberProfile->organization_id == $user->orgOwnerProfile->organization_id &&
                       $memberProfile->boss_id == $user->id;
            }
        }
        
        // Сотрудник может видеть только свои отчеты
        if ($user->isOrgMember()) {
            return $report->user_id == $user->id;
        }
        
        return false;
    }

    public function cancel(Report $report)
    {
        $user = Auth::user();
        
        // Проверка прав
        if (!$this->canViewReport($user, $report)) {
            abort(403, 'Доступ запрещен');
        }
        
        // Отменить можно только отчеты в ожидании
        if (!$report->isPending()) {
            return redirect()->back()
                ->with('error', 'Можно отменить только отчеты в статусе "В ожидании"');
        }
        
        try {
            // Возвращаем лимит
            if ($report->delegated_limit_id) {
                $delegatedLimit = DelegatedLimit::find($report->delegated_limit_id);
                if ($delegatedLimit) {
                    $delegatedLimit->returnQuantity($report->quantity_used);
                }
            } elseif ($report->limit_id) {
                $limit = Limit::find($report->limit_id);
                if ($limit) {
                    $limit->returnQuantity($report->quantity_used);
                }
            }
            
            // Меняем статус
            $report->markAsCancelled();
            
            return redirect()->back()
                ->with('success', 'Отчет успешно отменен. Лимит возвращен.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка при отмене отчета: ' . $e->getMessage());
        }
    }

        // ========== НОВЫЕ МЕТОДЫ ДЛЯ МАССОВОЙ ЗАГРУЗКИ ==========

    /**
     * Массовое создание отчетов из Excel/CSV
     */
    public function bulkStore(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'bulk_report_types' => 'required|array|min:1',
            'bulk_report_types.*' => 'exists:report_types,id',
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
            'header_row' => 'nullable|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $reportTypeIds = $request->input('bulk_report_types');
        $headerRow = $request->input('header_row', 1);
        
        try {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            if (empty($rows)) {
                return redirect()->back()
                    ->with('error', 'Файл не содержит данных')
                    ->withInput();
            }
            
            // Получаем заголовки
            $headerRowIndex = $headerRow - 1;
            if (!isset($rows[$headerRowIndex])) {
                return redirect()->back()
                    ->with('error', 'Указанная строка заголовков не найдена')
                    ->withInput();
            }
            
            $headers = array_map(function($header) {
                return strtolower(trim($header));
            }, $rows[$headerRowIndex]);
            
            // Удаляем строку заголовков
            array_splice($rows, 0, $headerRow);
            
            // Проверяем лимиты ДО начала обработки
            $limitsCache = [];
            foreach ($reportTypeIds as $typeId) {
                $limit = $this->getUserLimitForReportType($user, $typeId);
                if (!$limit) {
                    $reportType = ReportType::find($typeId);
                    return redirect()->back()
                        ->with('error', "Нет доступного лимита для типа отчета: {$reportType->name}")
                        ->withInput();
                }
                
                // Проверяем, хватит ли лимита на все строки
                $rowCount = 0;
                foreach ($rows as $row) {
                    if (!empty(array_filter($row))) $rowCount++;
                }
                
                if ($limit->getAvailableQuantity() < $rowCount) {
                    $reportType = ReportType::find($typeId);
                    return redirect()->back()
                        ->with('error', "Недостаточно лимита для типа {$reportType->name}. Нужно: {$rowCount}, доступно: {$limit->getAvailableQuantity()}")
                        ->withInput();
                }
                
                $limitsCache[$typeId] = $limit;
            }
            
            $createdCount = 0;
            $errors = [];
            $rowNumber = $headerRow + 1;
            
            // Обрабатываем каждую строку
            foreach ($rows as $rowIndex => $row) {
                $currentRowNumber = $rowNumber + $rowIndex;
                
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Преобразуем строку в ассоциативный массив
                $rowData = [];
                foreach ($headers as $colIndex => $headerName) {
                    if (!empty($headerName) && isset($row[$colIndex])) {
                        $rowData[$headerName] = $row[$colIndex];
                    }
                }
                
                // Для каждого типа отчета создаем запись
                foreach ($reportTypeIds as $typeId) {
                    try {
                        $reportType = ReportType::find($typeId);
                        
                        // Валидация минимальных требований
                        if (!$this->validateBulkRowRequirements($typeId, $rowData, $currentRowNumber, $errors)) {
                            continue;
                        }
                        
                        // Подготавливаем данные
                        $reportData = $this->prepareBulkReportData($rowData, $typeId);
                        $reportData['user_id'] = $user->id;
                        $reportData['report_type_id'] = $typeId;
                        $reportData['status'] = Report::STATUS_PENDING;
                        $reportData['quantity_used'] = 1;
                        
                        // Привязываем лимит
                        $limit = $limitsCache[$typeId];
                        if ($limit instanceof DelegatedLimit) {
                            $reportData['delegated_limit_id'] = $limit->id;
                        } else {
                            $reportData['limit_id'] = $limit->id;
                        }
                        
                        // Создаем отчет
                        Report::create($reportData);
                        $limit->useQuantity(1);
                        
                        $createdCount++;
                        
                    } catch (\Exception $e) {
                        $errors[] = "Строка {$currentRowNumber}, тип {$reportType->name}: " . $e->getMessage();
                    }
                }
            }
            
            // Формируем результат
            if ($createdCount === 0) {
                return redirect()->back()
                    ->with('error', 'Не удалось создать ни одного отчета. ' . implode(' ', $errors))
                    ->withInput();
            }
            
            $message = "✅ Успешно создано отчетов: {$createdCount}";
            if (!empty($errors)) {
                $message .= "<br><br>⚠️ Ошибки в строках:<br>" . implode('<br>', array_slice($errors, 0, 20));
                if (count($errors) > 20) {
                    $message .= "<br>... и еще " . (count($errors) - 20) . " ошибок";
                }
            }
            
            return redirect()->route('reports.index')
                ->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Bulk upload error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Ошибка при обработке файла: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Предпросмотр Excel файла
     */
    public function previewExcel(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'excel_file' => 'required|file|mimes:xlsx,xls,csv',
                'header_row' => 'nullable|integer|min:1',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['success' => false, 'error' => $validator->errors()->first()]);
            }
            
            $headerRow = $request->input('header_row', 1);
            $file = $request->file('excel_file');
            
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            if (empty($rows)) {
                return response()->json(['success' => false, 'error' => 'Файл не содержит данных']);
            }
            
            $headerRowIndex = $headerRow - 1;
            $headers = isset($rows[$headerRowIndex]) ? $rows[$headerRowIndex] : [];
            
            // Первые 5 строк для предпросмотра
            $previewRows = [];
            $rowCount = 0;
            
            for ($i = $headerRowIndex + 1; $i < count($rows) && $rowCount < 5; $i++) {
                if (!empty(array_filter($rows[$i]))) {
                    $previewRows[] = array_slice($rows[$i], 0, 8);
                    $rowCount++;
                }
            }
            
            // Общее количество записей
            $totalRows = 0;
            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                if (!empty(array_filter($rows[$i]))) {
                    $totalRows++;
                }
            }
            
            return response()->json([
                'success' => true,
                'headers' => array_slice($headers, 0, 8),
                'previewRows' => $previewRows,
                'rowCount' => $totalRows,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Preview error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Ошибка чтения файла']);
        }
    }

    /**
     * Валидация строки из Excel
     */
    private function validateBulkRowRequirements($reportTypeId, $rowData, $rowNumber, &$errors)
    {
        $reportType = ReportType::find($reportTypeId);
        $typeName = $reportType ? $reportType->name : "Тип {$reportTypeId}";
        
        switch ($reportTypeId) {
            case 1: // CL:Базовый V1
                $missing = [];
                if (empty($rowData['last_name'])) $missing[] = 'фамилия';
                if (empty($rowData['first_name'])) $missing[] = 'имя';
                if (empty($rowData['patronymic'])) $missing[] = 'отчество';
                if (empty($rowData['birth_date'])) $missing[] = 'дата рождения';
                if (empty($rowData['region'])) $missing[] = 'регион';
                
                if (!empty($missing)) {
                    $errors[] = "Строка {$rowNumber}, {$typeName}: отсутствуют: " . implode(', ', $missing);
                    return false;
                }
                break;
                
            case 2: // CL:Паспорт V1
                $missing = [];
                if (empty($rowData['last_name'])) $missing[] = 'фамилия';
                if (empty($rowData['first_name'])) $missing[] = 'имя';
                if (empty($rowData['patronymic'])) $missing[] = 'отчество';
                if (empty($rowData['birth_date'])) $missing[] = 'дата рождения';
                if (empty($rowData['region'])) $missing[] = 'регион';
                if (empty($rowData['passport_series'])) $missing[] = 'серия паспорта';
                if (empty($rowData['passport_number'])) $missing[] = 'номер паспорта';
                if (empty($rowData['passport_date'])) $missing[] = 'дата выдачи паспорта';
                
                if (!empty($missing)) {
                    $errors[] = "Строка {$rowNumber}, {$typeName}: отсутствуют: " . implode(', ', $missing);
                    return false;
                }
                
                // Проверка формата
                $series = preg_replace('/[^0-9]/', '', $rowData['passport_series']);
                $number = preg_replace('/[^0-9]/', '', $rowData['passport_number']);
                
                if (strlen($series) !== 4) {
                    $errors[] = "Строка {$rowNumber}, {$typeName}: серия паспорта должна быть 4 цифры";
                    return false;
                }
                if (strlen($number) !== 6) {
                    $errors[] = "Строка {$rowNumber}, {$typeName}: номер паспорта должен быть 6 цифр";
                    return false;
                }
                break;
                
            case 3: // AI:АвтоИстория V1
                if (empty($rowData['vehicle_number'])) {
                    $errors[] = "Строка {$rowNumber}, {$typeName}: номер ТС обязателен";
                    return false;
                }
                break;
                
            case 4: // CL:Недвижимость
                $missing = [];
                if (empty($rowData['cadastral_number'])) $missing[] = 'кадастровый номер';
                if (empty($rowData['property_type'])) $missing[] = 'тип недвижимости';
                
                if (!empty($missing)) {
                    $errors[] = "Строка {$rowNumber}, {$typeName}: отсутствуют: " . implode(', ', $missing);
                    return false;
                }
                break;
        }
        
        return true;
    }

    /**
     * Подготовка данных из Excel
     */
    private function prepareBulkReportData($rowData, $reportTypeId)
    {
        $data = [
            'last_name' => $rowData['last_name'] ?? null,
            'first_name' => $rowData['first_name'] ?? null,
            'patronymic' => $rowData['patronymic'] ?? null,
            'birth_date' => $this->formatExcelDate($rowData['birth_date'] ?? null),
            'region' => $rowData['region'] ?? null,
        ];
        
        switch ($reportTypeId) {
            case 2: // Паспорт
                $data['passport_series'] = preg_replace('/[^0-9]/', '', $rowData['passport_series'] ?? '');
                $data['passport_number'] = preg_replace('/[^0-9]/', '', $rowData['passport_number'] ?? '');
                $data['passport_date'] = $this->formatExcelDate($rowData['passport_date'] ?? null);
                break;
                
            case 3: // Авто
                $data['vehicle_number'] = strtoupper(trim($rowData['vehicle_number'] ?? ''));
                break;
                
            case 4: // Недвижимость
                $data['cadastral_number'] = $rowData['cadastral_number'] ?? null;
                $data['property_type'] = $rowData['property_type'] ?? null;
                break;
        }
        
        return $data;
    }

    /**
     * Форматирование даты из Excel
     */
    private function formatExcelDate($date)
    {
        if (empty($date)) {
            return null;
        }
        
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        
        if (is_numeric($date)) {
            try {
                return Date::excelToDateTimeObject($date)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }
        
        try {
            return date('Y-m-d', strtotime($date));
        } catch (\Exception $e) {
            return null;
        }
    }
}