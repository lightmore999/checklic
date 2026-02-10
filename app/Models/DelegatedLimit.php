<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelegatedLimit extends Model
{
    use HasFactory;

    /**
     * Название таблицы
     */
    protected $table = 'delegated_limits';

    /**
     * Поля, которые можно массово назначать
     */
    protected $fillable = [
        'user_id',
        'limit_id',
        'quantity',
        'used_quantity', // Добавляем
        'is_active',
    ];

    /**
     * Приведение типов
     */
    protected $casts = [
        'quantity' => 'integer',
        'used_quantity' => 'integer', // Добавляем
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Отношение к пользователю (кому делегирован лимит)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Отношение к оригинальному лимиту
     */
    public function limit()
    {
        return $this->belongsTo(Limit::class);
    }

    /**
     * Проверка, активен ли делегированный лимит
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Активировать делегированный лимит
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Деактивировать делегированный лимит
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Получить доступное количество делегированного лимита
     */
    public function getAvailableQuantity(): int
    {
        return $this->quantity - $this->used_quantity;
    }

    /**
     * Проверка, исчерпан ли делегированный лимит
     */
    public function isExhausted(): bool
    {
        return $this->getAvailableQuantity() <= 0;
    }

    /**
     * Уменьшение делегированного лимита
     */
    public function decrementQuantity(int $amount = 1): bool
    {
        if ($this->getAvailableQuantity() >= $amount) {
            $this->quantity -= $amount;
            return $this->save();
        }
        
        return false;
    }

    /**
     * Увеличение делегированного лимита
     */
    public function incrementQuantity(int $amount = 1): bool
    {
        $this->quantity += $amount;
        return $this->save();
    }

    /**
     * Использовать часть делегированного лимита (при создании отчета сотрудником)
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
     * Вернуть использованный делегированный лимит (при удалении отчета)
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
     * Получение делегированного лимита пользователя
     */
    public static function getUserDelegatedLimit(int $userId, int $limitId): ?self
    {
        return self::where('user_id', $userId)
            ->where('limit_id', $limitId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Получение всех делегированных лимитов пользователя
     */
    public static function getUserDelegatedLimits(int $userId)
    {
        return self::where('user_id', $userId)
            ->where('is_active', true)
            ->with('limit.reportType')
            ->get();
    }

    /**
     * Создание или обновление делегированного лимита
     */
    public static function createOrUpdateDelegatedLimit(int $userId, int $limitId, int $quantity): self
    {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'limit_id' => $limitId,
            ],
            [
                'quantity' => $quantity,
                'used_quantity' => 0, // Инициализируем
                'is_active' => true,
            ]
        );
    }

    /**
     * Scope для активных делегированных лимитов
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->whereRaw('quantity - used_quantity > 0');
    }

    /**
     * Scope для лимитов пользователя
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope для конкретного лимита
     */
    public function scopeForLimit($query, int $limitId)
    {
        return $query->where('limit_id', $limitId);
    }
}