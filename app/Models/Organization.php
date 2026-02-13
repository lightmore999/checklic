<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'manager_id',
        'subscription_ends_at',
        'status',
    ];

    protected $casts = [
        'subscription_ends_at' => 'datetime',
    ];

    /**
     * Менеджер, отвечающий за организацию
     */
    public function manager(): BelongsTo
    {
        // Менеджер - это пользователь (User), а не модель Manager
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Владелец организации
     */
    public function owner()
    {
        return $this->hasOne(OrgOwnerProfile::class, 'organization_id');
    }

    /**
     * Проверка, активна ли организация
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Проверка, истекла ли подписка
     */
    public function isExpired(): bool
    {
        if (!$this->subscription_ends_at) {
            return false;
        }
        
        return $this->subscription_ends_at->isPast();
    }

    /**
     * Получить оставшееся время подписки в днях
     */
    public function getRemainingSubscriptionDays(): ?int
    {
        if (!$this->subscription_ends_at) {
            return null;
        }
        
        return Carbon::now()->diffInDays($this->subscription_ends_at, false);
    }

    /**
     * Проверка, скоро ли истечет подписка (менее 7 дней)
     */
    public function isSubscriptionExpiringSoon(): bool
    {
        $remainingDays = $this->getRemainingSubscriptionDays();
        
        return $remainingDays !== null && $remainingDays > 0 && $remainingDays <= 7;
    }

    /**
     * Обновить статус организации
     */
    public function updateStatus(string $status): void
    {
        $this->update(['status' => $status]);
    }

    /**
     * Продлить подписку
     */
    public function extendSubscription(int $days): void
    {
        $newDate = $this->subscription_ends_at 
            ? $this->subscription_ends_at->addDays($days)
            : Carbon::now()->addDays($days);
            
        $this->update(['subscription_ends_at' => $newDate]);
    }

    public function members()
    {
        return $this->hasMany(OrgMemberProfile::class, 'organization_id');
    }
}