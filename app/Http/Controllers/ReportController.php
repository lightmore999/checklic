<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportType;
use App\Models\DelegatedLimit;
use App\Models\Limit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * Показать список отчетов
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Начинаем запрос с подгрузкой связей
        $query = Report::with(['reportType', 'user'])
            ->orderBy('created_at', 'desc');
        
        // Фильтрация по пользователю (если указан в запросе)
        if ($request->filled('user_id')) {
            // Админ, менеджер или владелец могут смотреть чужие отчеты
            if ($user->isAdmin() || $user->isManager() || $user->isOrgOwner()) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            // Если пользователь не указан, показываем отчеты текущего пользователя
            $query->where('user_id', $user->id);
        }
        
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
                    // Если введен полный номер (4500123456)
                    $series = substr($passport, 0, 4);
                    $number = substr($passport, 4, 6);
                    $query->where('passport_series', $series)
                        ->where('passport_number', $number);
                } else {
                    // Поиск по части номера
                    $query->where('passport_series', 'like', "%{$passport}%")
                        ->orWhere('passport_number', 'like', "%{$passport}%");
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
        
        // Данные для фильтров
        $reportTypes = ReportType::all();
        $users = [];
        
        // Получаем пользователей для фильтра (для админов, менеджеров, владельцев)
        if ($user->isAdmin() || $user->isManager()) {
            $users = User::where('is_active', true)->orderBy('name')->get();
        } elseif ($user->isOrgOwner()) {
            // Владелец видит своих сотрудников
            $organizationId = $user->orgOwnerProfile->organization_id ?? null;
            if ($organizationId) {
                $users = User::whereHas('orgMemberProfile', function($q) use ($organizationId, $user) {
                        $q->where('organization_id', $organizationId)
                        ->where('boss_id', $user->id);
                    })
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
                
                // Добавляем самого владельца
                $users->prepend($user);
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
        
        return view('reports.index', compact('reports', 'reportTypes', 'users', 'statuses'));
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

        \Log::info('ВЕСЬ REQUEST: ' . json_encode($request->all()));

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
        // Минимальные требования для каждого типа отчета
        $minimalRequirements = [
            1 => ['last_name', 'first_name'], // CL:Базовый V1 - нужно хотя бы Фамилия и Имя
            2 => ['passport_series', 'passport_number'], // CL:Паспорт V1 - серия и номер паспорта
            3 => ['vehicle_number'], // AI:АвтоИстория V1 - номер ТС
            4 => ['cadastral_number', 'property_type'], // CL:Недвижимость - кадастровый номер и тип
        ];
        
        if (!isset($minimalRequirements[$reportTypeId])) {
            return true; // Если нет требований, все ок
        }
        
        foreach ($minimalRequirements[$reportTypeId] as $field) {
            if (empty($request->$field)) {
                return false;
            }
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
        
        // Определяем, какие поля НУЖНЫ для каждого типа отчета
        $fieldMapping = [
            // CL:Базовый V1 - нужны только эти поля
            1 => ['last_name', 'first_name', 'patronymic', 'birth_date', 'region'],
            
            // CL:Паспорт V1 - нужны эти поля + паспортные данные
            2 => ['last_name', 'first_name', 'patronymic', 'birth_date', 'region', 
                'passport_series', 'passport_number', 'passport_date'],
            
            // AI:АвтоИстория V1 - нужен только номер ТС
            3 => ['vehicle_number'],
            
            // CL:Недвижимость - нужны только данные недвижимости
            4 => ['cadastral_number', 'property_type'],
        ];
        
        if (isset($fieldMapping[$reportTypeId])) {
            foreach ($fieldMapping[$reportTypeId] as $field) {
                // Если поле есть в запросе - добавляем его (даже если оно пустое)
                if ($request->has($field)) {
                    $data[$field] = $request->$field;
                }
            }
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
        // Сначала ищем делегированный лимит
        $delegatedLimit = DelegatedLimit::where('user_id', $user->id)
            ->where('is_active', true)
            ->whereHas('limit', function($q) use ($reportTypeId) {
                $q->where('report_type_id', $reportTypeId);
            })
            ->first();
        
        if ($delegatedLimit && $delegatedLimit->getAvailableQuantity() > 0) {
            return $delegatedLimit;
        }
        
        // Если нет делегированного, ищем собственный (только для владельцев)
        if ($user->isOrgOwner()) {
            $limit = Limit::where('user_id', $user->id)
                ->where('report_type_id', $reportTypeId)
                ->first();
            
            if ($limit && $limit->getAvailableQuantity() > 0) {
                return $limit;
            }
        }
        
        return null; // Разрешаем отсутствие лимита
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
}