<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Limit extends Model
{
    use HasFactory;

    /**
     * Название таблицы
     */
    protected $table = 'limits';

    /**
     * Поля, которые можно массово назначать
     */
    protected $fillable = [
        'user_id',
        'report_type_id',
        'quantity',
        'created_by',
        'used_quantity', 
        'date_created',
    ];

    /**
     * Поля дат
     */
    protected $dates = [
        'date_created',
        'created_at',
        'updated_at',
    ];

    /**
     * Приведение типов
     */
    protected $casts = [
        'date_created' => 'date',
        'quantity' => 'integer',
        'used_quantity' => 'integer', // Добавляем
    ];

    /**
     * Отношение к пользователю
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
     * Проверка, исчерпан ли лимит
     */
    public function isExhausted(): bool
    {
        return $this->getAvailableQuantity() <= 0;
    }

    /**
     * Получить доступное количество
     * (общее количество минус использованное)
     */
    public function getAvailableQuantity(): int
    {
        return $this->quantity - $this->used_quantity;
    }

    /**
     * Уменьшение лимита
     */
    public function decrementLimit(int $amount = 1): bool
    {
        if ($this->getAvailableQuantity() >= $amount) {
            $this->quantity -= $amount;
            return $this->save();
        }
        
        return false;
    }

    /**
     * Увеличение лимита
     */
    public function incrementLimit(int $amount = 1): bool
    {
        $this->quantity += $amount;
        return $this->save();
    }

    /**
     * Использовать часть лимита (при создании отчета)
     */
    public function useQuantity(int $amount = 1): bool
    {
        if ($this->getAvailableQuantity() >= $amount) {
            $this->used_quantity += $amount;
            return $this->save();
        }
        
        return false;
    }

    /**
     * Вернуть использованный лимит (при удалении отчета)
     */
    public function returnQuantity(int $amount = 1): bool
    {
        if ($this->used_quantity >= $amount) {
            $this->used_quantity -= $amount;
            return $this->save();
        }
        
        return false;
    }

    /**
     * Получение лимита пользователя по типу отчета и дате
     */
    public static function getUserLimit(int $userId, int $reportTypeId, string $date = null): ?self
    {
        $date = $date ?: now()->format('Y-m-d');
        
        return self::where('user_id', $userId)
            ->where('report_type_id', $reportTypeId)
            ->where('date_created', $date)
            ->first();
    }

    /**
     * Создание или обновление лимита
     */
    public static function createOrUpdateLimit(int $userId, int $reportTypeId, int $quantity, string $date = null): self
    {
        $date = $date ?: now()->format('Y-m-d');
        
        $limit = self::updateOrCreate(
            [
                'user_id' => $userId,
                'report_type_id' => $reportTypeId,
                'date_created' => $date,
            ],
            [
                'quantity' => $quantity,
                'used_quantity' => 0,
            ]
        );
        
        // Если запись новая и created_by еще не установлен
        if (!$limit->created_by && auth()->check()) {
            $limit->created_by = auth()->id();
            $limit->save();
        }
        
        return $limit;
    }

    /**
     * Проверка доступности лимита
     */
    public static function checkLimit(int $userId, int $reportTypeId, int $requiredAmount = 1, string $date = null): bool
    {
        $limit = self::getUserLimit($userId, $reportTypeId, $date);
        
        if (!$limit) {
            return false; // Лимит не установлен
        }
        
        return $limit->getAvailableQuantity() >= $requiredAmount;
    }

    /**
     * Scope для активных лимитов (есть доступное количество)
     */
    public function scopeActive($query)
    {
        return $query->whereRaw('quantity - used_quantity > 0');
    }

    /**
     * Scope для лимитов пользователя
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope для лимитов по типу отчета
     */
    public function scopeForReportType($query, int $reportTypeId)
    {
        return $query->where('report_type_id', $reportTypeId);
    }

    /**
     * Scope для лимитов по дате
     */
    public function scopeForDate($query, string $date)
    {
        return $query->where('date_created', $date);
    }

    /**
     * Делегированные версии этого лимита
     */
    public function delegatedVersions()
    {
        return $this->hasMany(DelegatedLimit::class, 'limit_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Переопределяем метод create
     */
    protected static function booted()
    {
        static::creating(function ($limit) {
            if (auth()->check() && !$limit->created_by) {
                $limit->created_by = auth()->id();
            }
        });
    }
}