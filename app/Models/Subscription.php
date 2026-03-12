<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',           // FK на пользователя (владельца организации)
        'name',              // Название подписки
        'ends_at',           // дата окончания подписки
        'starts_at',         // дата начала подписки
        'status',            // статус: active, suspended, expired, cancelled, pending
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ends_at' => 'datetime',
        'starts_at' => 'datetime',
    ];

    /**
     * Пользователь (владелец организации), которому принадлежит подписка
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Проверка, активна ли подписка
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    /**
     * Проверка, истекла ли подписка
     */
    public function isExpired(): bool
    {
        if (!$this->ends_at) {
            return false;
        }
        
        return $this->ends_at->isPast();
    }

    /**
     * Проверка, скоро ли истечет подписка (менее 7 дней)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->ends_at || $this->isExpired()) {
            return false;
        }
        
        return $this->ends_at->diffInDays(now()) <= 7;
    }

    /**
     * Получить количество дней до окончания подписки (для отображения)
     * Положительное значение - осталось дней
     * Отрицательное значение - просрочено дней
     * Пример: если заканчивается 16.03, сегодня 12.03 → 4 дня осталось (16-12)
     *         если заканчивается 16.03, сегодня 16.03 → 0 дней (истекает сегодня)
     *         если заканчивается 16.03, сегодня 17.03 → -1 день (просрочена на 1 день)
     */
    public function getRemainingDays(): ?int
    {
        if (!$this->ends_at) {
            return null;
        }
        
        $now = now()->startOfDay();
        $endDate = $this->ends_at->startOfDay();
        
        // Разница в днях (положительная если endDate в будущем)
        return $now->diffInDays($endDate, false);
    }

    /**
     * Продлить подписку
     */
    public function extend(int $days): void
    {
        if ($this->ends_at) {
            $this->ends_at = $this->ends_at->addDays($days);
        } else {
            $this->ends_at = now()->addDays($days);
        }
        
        $this->status = 'active';
        $this->save();
    }

    /**
     * Активировать подписку
     */
    public function activate(): void
    {
        $this->status = 'active';
        $this->save();
    }

    /**
     * Приостановить подписку
     */
    public function suspend(): void
    {
        $this->status = 'suspended';
        $this->save();
    }

    /**
     * Отменить подписку
     */
    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->save();
    }

    /**
     * Получить статус в человекочитаемом формате
     */
    public function getStatusTextAttribute(): string
    {
        $status = $this->status;
        
        if ($status === 'active') {
            return 'Активна';
        } elseif ($status === 'suspended') {
            return 'Приостановлена';
        } elseif ($status === 'expired') {
            return 'Истекла';
        } elseif ($status === 'cancelled') {
            return 'Отменена';
        } elseif ($status === 'pending') {
            return 'Ожидает';
        }
        
        return $status;
    }

    /**
     * Получить класс бейджа для статуса
     */
    public function getStatusBadgeClassAttribute(): string
    {
        $status = $this->status;
        
        if ($status === 'active') {
            return 'success';
        } elseif ($status === 'suspended') {
            return 'warning';
        } elseif ($status === 'expired') {
            return 'danger';
        } elseif ($status === 'cancelled') {
            return 'secondary';
        } elseif ($status === 'pending') {
            return 'info';
        }
        
        return 'secondary';
    }

    /**
     * Получить отображаемое название подписки
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }
        
        return 'Подписка #' . $this->id;
    }

    public function limits()
    {
        return $this->hasMany(Limit::class, 'subscription_id');
    }

    /**
     * @deprecated Удалить этот метод, используйте getRemainingDays()
     */
    public function getDaysAfterEnd(): ?int
    {
        return $this->getRemainingDays();
    }
}