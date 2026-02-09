<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class OrgOwnerProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'organization_id',
        'manager_id',
    ];

    /**
     * Пользователь-владелец
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Организация, которой владеет пользователь
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Менеджер, создавший этого владельца
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Подчиненные сотрудники (org_member)
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(OrgMemberProfile::class, 'boss_id', 'user_id');
    }

    /**
     * Проверка, активен ли аккаунт владельца
     * (используем subscription_ends_at из таблицы organizations)
     */
    public function isAccountActive(): bool
    {
        return $this->organization->isActive();
    }

    /**
     * Проверка, истекла ли подписка
     */
    public function isSubscriptionExpired(): bool
    {
        return $this->organization->isExpired();
    }

    /**
     * Получить оставшееся время подписки
     */
    public function getRemainingSubscriptionDays(): ?int
    {
        return $this->organization->getRemainingSubscriptionDays();
    }

    /**
     * Получить дату окончания подписки
     */
    public function getSubscriptionEndsAt(): ?Carbon
    {
        return $this->organization->subscription_ends_at;
    }

    /**
     * Получить активных подчиненных
     */
    public function activeSubordinates()
    {
        return $this->subordinates()->where('is_active', true)->get();
    }

    /**
     * Получить количество активных подчиненных
     */
    public function getActiveSubordinatesCount(): int
    {
        return $this->subordinates()->where('is_active', true)->count();
    }
}