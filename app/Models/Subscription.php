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
     * Получить оставшееся время подписки в днях
     */
    public function getRemainingDays(): ?int
    {
        if (!$this->ends_at) {
            return null;
        }
        
        if ($this->isExpired()) {
            return 0;
        }
        
        return now()->diffInDays($this->ends_at, false);
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
        return match($this->status) {
            'active' => 'Активна',
            'suspended' => 'Приостановлена',
            'expired' => 'Истекла',
            'cancelled' => 'Отменена',
            'pending' => 'Ожидает',
            default => $this->status,
        };
    }

    /**
     * Получить класс бейджа для статуса
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'suspended' => 'warning',
            'expired' => 'danger',
            'cancelled' => 'secondary',
            'pending' => 'info',
            default => 'secondary',
        };
    }

    public function limits()
    {
        return $this->hasMany(Limit::class, 'subscription_id');
    }
}