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
        
        // Результат
        'response_data',
        
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
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

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
     * Проверка, завершен ли отчет
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
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

    /**
     * Получить полный номер паспорта
     */
    public function getPassportFullAttribute(): string
    {
        return trim(($this->passport_series ?? '') . ' ' . ($this->passport_number ?? ''));
    }

    /**
     * Получить полное ФИО
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->last_name ?? '') . ' ' . ($this->first_name ?? '') . ' ' . ($this->patronymic ?? ''));
    }

    /**
     * Получить использованный лимит (основной или делегированный)
     */
    public function getUsedLimit()
    {
        return $this->delegatedLimit ?? $this->limit;
    }

    /**
     * Обновить статус отчета
     */
    public function updateStatus(string $status, array $responseData = null): bool
    {
        $this->status = $status;
        
        if ($responseData) {
            $this->response_data = $responseData;
        }
        
        if (in_array($status, [self::STATUS_COMPLETED, self::STATUS_FAILED])) {
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
    public function markAsCompleted(array $responseData): bool
    {
        return $this->updateStatus(self::STATUS_COMPLETED, $responseData);
    }

    /**
     * Отметить как проваленный
     */
    public function markAsFailed(string $errorMessage): bool
    {
        return $this->updateStatus(self::STATUS_FAILED, ['error' => $errorMessage]);
    }

    /**
     * Отметить как отмененный
     */
    public function markAsCancelled(): bool
    {
        return $this->updateStatus(self::STATUS_CANCELLED);
    }

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
     * Получить данные запроса в виде массива
     */
    public function getRequestData(): array
    {
        return [
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'patronymic' => $this->patronymic,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'region' => $this->region,
            'passport_series' => $this->passport_series,
            'passport_number' => $this->passport_number,
            'passport_date' => $this->passport_date?->format('Y-m-d'),
            'vehicle_number' => $this->vehicle_number,
            'cadastral_number' => $this->cadastral_number,
            'property_type' => $this->property_type,
        ];
    }
}