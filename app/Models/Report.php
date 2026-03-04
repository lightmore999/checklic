<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    /**
     * Название таблицы
     */
    protected $table = 'reports';

    /**
     * Поля, которые можно массово назначать
     */
    protected $fillable = [
        'user_id',
        'report_type_id',
        'status',
        
        // Данные запроса
        'last_name',
        'first_name',
        'patronymic',
        'birth_date',
        'region',
        'passport_series',
        'passport_number',
        'passport_date',
        'vehicle_number',
        'cadastral_number',
        'property_type',
        'inn',
        
        // Результаты от API
        'response_data',
        'processed_data',
        'api_statuses',
        'api_responses',
        'meta_data',
        
        // Системные
        'quantity_used',
        'limit_id',
        'delegated_limit_id',
    ];

    /**
     * Поля дат
     */
    protected $dates = [
        'birth_date',
        'passport_date',
        'created_at',
        'updated_at',
        'processed_at',
    ];

    /**
     * Приведение типов
     */
    protected $casts = [
        'response_data' => 'array',
        'processed_data' => 'array',
        'api_statuses' => 'array',
        'api_responses' => 'array',
        'meta_data' => 'array',
        'birth_date' => 'date',
        'passport_date' => 'date',
        'quantity_used' => 'integer',
    ];

    /**
     * Значения по умолчанию
     */
    protected $attributes = [
        'status' => 'pending',
        'quantity_used' => 1,
    ];

    /**
     * Статусы отчетов
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_PARTIAL = 'partial';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    
    // ID типа отчета Контрагенты
    const CONTRAGENT_TYPE_ID = 6;

    /**
     * Отношение к пользователю (создателю отчета)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Отношение к типу отчета
     */
    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }

    /**
     * Отношение к лимиту (если использовался основной лимит)
     */
    public function limit()
    {
        return $this->belongsTo(Limit::class);
    }

    /**
     * Отношение к делегированному лимиту (если использовался делегированный)
     */
    public function delegatedLimit()
    {
        return $this->belongsTo(DelegatedLimit::class);
    }

    // ========== МЕТОДЫ ДЛЯ ПРОВЕРКИ СТАТУСОВ ==========

    /**
     * Проверка, находится ли отчет в ожидании
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Проверка, обрабатывается ли отчет
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Проверка, завершен ли отчет полностью
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Проверка, частично ли готов отчет
     */
    public function isPartial(): bool
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    /**
     * Проверка, готов ли отчет (полностью или частично)
     */
    public function isReady(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_PARTIAL]);
    }

    /**
     * Проверка, провален ли отчет
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Проверка, отменен ли отчет
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Проверка, можно ли обрабатывать отчет
     */
    public function canBeProcessed(): bool
    {
        return $this->isPending();
    }

    // ========== МЕТОДЫ ДЛЯ ОБНОВЛЕНИЯ СТАТУСА ==========

    /**
     * Обновить статус отчета
     */
    public function updateStatus(string $status): bool
    {
        $this->status = $status;
        
        if (in_array($status, [self::STATUS_COMPLETED, self::STATUS_PARTIAL, self::STATUS_FAILED])) {
            $this->processed_at = now();
        }
        
        return $this->save();
    }

    /**
     * Отметить как обрабатываемый
     */
    public function markAsProcessing(): bool
    {
        return $this->updateStatus(self::STATUS_PROCESSING);
    }

    /**
     * Отметить как завершенный
     */
    public function markAsCompleted(): bool
    {
        return $this->updateStatus(self::STATUS_COMPLETED);
    }

    /**
     * Отметить как частично завершенный
     */
    public function markAsPartial(): bool
    {
        return $this->updateStatus(self::STATUS_PARTIAL);
    }

    /**
     * Отметить как проваленный
     */
    public function markAsFailed(): bool
    {
        return $this->updateStatus(self::STATUS_FAILED);
    }

    /**
     * Отметить как отмененный
     */
    public function markAsCancelled(): bool
    {
        return $this->updateStatus(self::STATUS_CANCELLED);
    }

    // ========== МЕТОДЫ ДЛЯ РАБОТЫ С API СТАТУСАМИ ==========

    /**
     * Инициализировать статусы API запросов
     */
    public function initializeApiStatuses(array $endpoints): self
    {
        $statuses = [];
        foreach ($endpoints as $endpoint) {
            $statuses[$endpoint] = 'pending';
        }
        
        $this->api_statuses = $statuses;
        $this->save();
        
        return $this;
    }

    /**
     * Обновить статус конкретного API запроса
     */
    public function updateApiStatus(string $endpoint, string $status): self
    {
        $statuses = $this->api_statuses ?? [];
        $statuses[$endpoint] = $status;
        $this->api_statuses = $statuses;
        $this->save();
        
        return $this;
    }

    /**
     * Сохранить ответ от API
     */
    public function saveApiResponse(string $endpoint, array $response): self
    {
        $responses = $this->api_responses ?? [];
        $responses[$endpoint] = $response; // $response уже массив
        $this->api_responses = $responses;
        $this->save();
        
        return $this;
    }

    /**
     * Проверить, все ли API запросы завершены
     */
    public function allApiRequestsCompleted(): bool
    {
        if (empty($this->api_statuses)) {
            return false;
        }
        
        foreach ($this->api_statuses as $status) {
            if ($status === 'pending') {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Проверить, есть ли успешные запросы
     */
    public function hasSuccessfulRequests(): bool
    {
        if (empty($this->api_statuses)) {
            return false;
        }
        
        foreach ($this->api_statuses as $status) {
            if ($status === 'completed') {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Получить статистику выполнения запросов
     */
    public function getApiProgress(): array
    {
        if (empty($this->api_statuses)) {
            return [
                'total' => 0,
                'completed' => 0,
                'failed' => 0,
                'pending' => 0,
                'percentage' => 0,
            ];
        }
        
        $total = count($this->api_statuses);
        $completed = 0;
        $failed = 0;
        $pending = 0;
        
        foreach ($this->api_statuses as $status) {
            if ($status === 'completed') {
                $completed++;
            } elseif ($status === 'failed') {
                $failed++;
            } else {
                $pending++;
            }
        }
        
        return [
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'pending' => $pending,
            'percentage' => $total > 0 ? round(($completed + $failed) / $total * 100) : 0,
        ];
    }

    // ========== МЕТОДЫ ДЛЯ РАБОТЫ С ДАННЫМИ ==========

    /**
     * Получить полный номер паспорта
     */
    public function getPassportFullAttribute(): string
    {
        $series = $this->passport_series ?? '';
        $number = $this->passport_number ?? '';
        
        return trim($series . ' ' . $number);
    }

    /**
     * Получить полное ФИО
     */
    public function getFullNameAttribute(): string
    {
        $lastName = $this->last_name ?? '';
        $firstName = $this->first_name ?? '';
        $patronymic = $this->patronymic ?? '';
        
        return trim($lastName . ' ' . $firstName . ' ' . $patronymic);
    }

    /**
     * Получить использованный лимит (основной или делегированный)
     */
    public function getUsedLimit()
    {
        return $this->delegatedLimit ?? $this->limit;
    }

    /**
     * Получить данные запроса в виде массива
     */
    public function getRequestData(): array
    {
        $data = [
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'patronymic' => $this->patronymic,
            'region' => $this->region,
            'passport_series' => $this->passport_series,
            'passport_number' => $this->passport_number,
            'vehicle_number' => $this->vehicle_number,
            'cadastral_number' => $this->cadastral_number,
            'property_type' => $this->property_type,
            'inn' => $this->inn,
        ];
        
        if ($this->birth_date) {
            $data['birth_date'] = $this->birth_date->format('Y-m-d');
        } else {
            $data['birth_date'] = null;
        }
        
        if ($this->passport_date) {
            $data['passport_date'] = $this->passport_date->format('Y-m-d');
        } else {
            $data['passport_date'] = null;
        }
        
        return $data;
    }

    /**
     * Получить данные для API запроса (только заполненные поля)
     */
    public function getApiRequestData(): array
    {
        $data = [];
        
        if (!empty($this->last_name)) {
            $data['last_name'] = $this->last_name;
        }
        
        if (!empty($this->first_name)) {
            $data['first_name'] = $this->first_name;
        }
        
        if (!empty($this->patronymic)) {
            $data['patronymic'] = $this->patronymic;
        }
        
        if ($this->birth_date) {
            $data['birth_date'] = $this->birth_date->format('Y-m-d');
        }
        
        if (!empty($this->region)) {
            $data['region'] = $this->region;
        }
        
        if (!empty($this->inn)) {
            $data['inn'] = $this->inn;
        }
        
        if (!empty($this->passport_series)) {
            $data['passport_series'] = $this->passport_series;
        }
        
        if (!empty($this->passport_number)) {
            $data['passport_number'] = $this->passport_number;
        }
        
        if ($this->passport_date) {
            $data['passport_date'] = $this->passport_date->format('Y-m-d');
        }
        
        if (!empty($this->vehicle_number)) {
            $data['vehicle_number'] = $this->vehicle_number;
        }
        
        if (!empty($this->cadastral_number)) {
            $data['cadastral_number'] = $this->cadastral_number;
        }
        
        if (!empty($this->property_type)) {
            $data['property_type'] = $this->property_type;
        }
        
        return $data;
    }

    /**
     * Проверка, является ли отчет Контрагентами
     */
    public function isContragent(): bool
    {
        return $this->report_type_id == self::CONTRAGENT_TYPE_ID;
    }

    // ========== МЕТОДЫ ДЛЯ РАБОТЫ С ОБРАБОТАННЫМИ ДАННЫМИ ==========

    /**
     * Сохранить обработанные данные
     */
    public function setProcessedData(array $data): self
    {
        $this->processed_data = $data;
        $this->save();
        
        return $this;
    }

    /**
     * Получить обработанные данные
     */
    public function getProcessedData(): ?array
    {
        return $this->processed_data;
    }

    /**
     * Проверить, есть ли обработанные данные
     */
    public function hasProcessedData(): bool
    {
        return !empty($this->processed_data);
    }

    // ========== МЕТОДЫ ДЛЯ РАБОТЫ С МЕТА-ДАННЫМИ ==========

    /**
     * Сохранить мета-данные
     */
    public function setMetaData(array $data): self
    {
        $this->meta_data = $data;
        $this->save();
        
        return $this;
    }

    /**
     * Добавить мета-данные
     */
    public function addMetaData(string $key, $value): self
    {
        $meta = $this->meta_data ?? [];
        $meta[$key] = $value;
        $this->meta_data = $meta;
        $this->save();
        
        return $this;
    }

    /**
     * Получить мета-данные
     */
    public function getMetaData(?string $key = null)
    {
        if ($key === null) {
            return $this->meta_data;
        }
        
        return $this->meta_data[$key] ?? null;
    }

    // ========== SCOPES ДЛЯ ПОИСКА ==========

    /**
     * Scope для поиска по фамилии
     */
    public function scopeByLastName($query, $lastName)
    {
        return $query->where('last_name', 'like', "%{$lastName}%");
    }

    /**
     * Scope для поиска по номеру паспорта
     */
    public function scopeByPassport($query, $series = null, $number = null)
    {
        if ($series && $number) {
            return $query->where('passport_series', $series)
                         ->where('passport_number', $number);
        }
        
        return $query;
    }

    /**
     * Scope для поиска по номеру ТС
     */
    public function scopeByVehicleNumber($query, $vehicleNumber)
    {
        return $query->where('vehicle_number', 'like', "%{$vehicleNumber}%");
    }

    /**
     * Scope для поиска по кадастровому номеру
     */
    public function scopeByCadastralNumber($query, $cadastralNumber)
    {
        return $query->where('cadastral_number', 'like', "%{$cadastralNumber}%");
    }

    /**
     * Scope для поиска по ИНН
     */
    public function scopeByInn($query, $inn)
    {
        return $query->where('inn', $inn);
    }

    /**
     * Scope для отчетов пользователя
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope для отчетов по типу
     */
    public function scopeByReportType($query, $reportTypeId)
    {
        return $query->where('report_type_id', $reportTypeId);
    }

    /**
     * Scope для отчетов по статусу
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope для недавних отчетов
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope для ожидающих обработки
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope для готовых отчетов
     */
    public function scopeReady($query)
    {
        return $query->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_PARTIAL]);
    }
}