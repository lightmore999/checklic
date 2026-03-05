<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LimitSubscriptionLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'limit_subscription_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
        'action',
        'old_data',
        'new_data',
        'quantity_change',
        'old_quantity',
        'new_quantity',
        'old_ends_at',
        'new_ends_at',
        'ip_address',
        'user_agent',
        'batch_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'old_ends_at' => 'datetime',
        'new_ends_at' => 'datetime',
        'quantity_change' => 'integer',
        'old_quantity' => 'integer',
        'new_quantity' => 'integer',
    ];

    /**
     * Пользователь, совершивший действие
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить связанную сущность (полиморфная связь)
     */
    public function entity()
    {
        return match($this->entity_type) {
            'limit' => $this->belongsTo(Limit::class, 'entity_id'),
            'delegated_limit' => $this->belongsTo(DelegatedLimit::class, 'entity_id'),
            'subscription' => $this->belongsTo(Subscription::class, 'entity_id'),
            default => null,
        };
    }

    /**
     * Получить подписку
     */
    public function subscription()
    {
        if ($this->entity_type === 'subscription') {
            return $this->belongsTo(Subscription::class, 'entity_id');
        }
        
        if ($this->entity_type === 'limit' && $this->entity) {
            return $this->entity->subscription;
        }
        
        if ($this->entity_type === 'delegated_limit' && $this->entity && $this->entity->limit) {
            return $this->entity->limit->subscription;
        }
        
        return null;
    }

    /**
     * Получить тип отчета
     */
    public function reportType()
    {
        if ($this->entity_type === 'limit' && $this->entity) {
            return $this->entity->reportType;
        }
        
        if ($this->entity_type === 'delegated_limit' && $this->entity && $this->entity->limit) {
            return $this->entity->limit->reportType;
        }
        
        return null;
    }

    /**
     * Получить целевого пользователя (для кого действие)
     */
    public function targetUser()
    {
        if ($this->entity_type === 'delegated_limit' && $this->entity) {
            return $this->entity->user;
        }
        
        if ($this->entity_type === 'limit' && $this->entity && $this->entity->subscription) {
            return $this->entity->subscription->user;
        }
        
        return null;
    }

    /**
     * Получить отображаемое количество (для create используем new_quantity)
     */
    public function getDisplayQuantityAttribute()
    {
        if ($this->action === 'create') {
            return $this->new_quantity ?? $this->quantity_change ?? 0;
        }
        return $this->quantity_change ?? 0;
    }

    /**
     * Получить отображаемый баланс до
     */
    public function getDisplayBalanceBeforeAttribute()
    {
        if ($this->action === 'create') {
            return '0';
        }
        return $this->old_quantity ?? '—';
    }

    /**
     * Получить отображаемый баланс после
     */
    public function getDisplayBalanceAfterAttribute()
    {
        if ($this->action === 'create') {
            return $this->new_quantity ?? '—';
        }
        return $this->new_quantity ?? '—';
    }

    /**
     * Получить тип сущности на русском
     */
    public function getEntityTypeNameAttribute(): string
    {
        return match($this->entity_type) {
            'limit' => 'Лимит',
            'delegated_limit' => 'Делегированный лимит',
            'subscription' => 'Подписка',
            default => $this->entity_type,
        };
    }

    /**
     * Получить действие на русском
     */
    public function getActionNameAttribute(): string
    {
        return match($this->action) {
            'create' => 'Создание',
            'update' => 'Изменение',
            'delete' => 'Удаление',
            'activate' => 'Активация',
            'suspend' => 'Приостановка',
            'cancel' => 'Отмена',
            'extend' => 'Продление',
            'use_quantity' => 'Использование',
            'return_quantity' => 'Возврат',
            'delegate' => 'Делегирование',
            default => $this->action,
        };
    }

    /**
     * Получить цвет для операции
     */
    public function getOperationColorAttribute(): string
    {
        return match($this->action) {
            'create', 'return_quantity', 'activate', 'extend' => 'success',
            'use_quantity', 'delete', 'cancel' => 'danger',
            'update', 'delegate' => 'info',
            'suspend' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Получить иконку для операции
     */
    public function getOperationIconAttribute(): string
    {
        return match($this->action) {
            'create' => 'bi-plus-circle',
            'use_quantity' => 'bi-arrow-down-circle',
            'return_quantity' => 'bi-arrow-up-circle',
            'update' => 'bi-pencil',
            'delete' => 'bi-trash',
            'delegate' => 'bi-person-plus',
            'activate' => 'bi-play-circle',
            'suspend' => 'bi-pause-circle',
            'cancel' => 'bi-x-circle',
            'extend' => 'bi-calendar-plus',
            default => 'bi-question-circle',
        };
    }

    /**
     * Проверить, было ли изменение количества
     */
    public function hasQuantityChange(): bool
    {
        return !is_null($this->quantity_change) || $this->action === 'create';
    }

    /**
     * Получить описание изменения количества
     */
    public function getQuantityChangeDescription(): ?string
    {
        if (!$this->hasQuantityChange()) {
            return null;
        }

        if ($this->action === 'create') {
            return "+{$this->new_quantity} (создание)";
        }

        $sign = $this->quantity_change > 0 ? '+' : '';
        return "{$sign}{$this->quantity_change} (было: {$this->old_quantity}, стало: {$this->new_quantity})";
    }

    /**
     * Проверить, было ли изменение даты окончания
     */
    public function hasEndsAtChange(): bool
    {
        return !is_null($this->old_ends_at) || !is_null($this->new_ends_at);
    }

    /**
     * Получить описание изменения даты
     */
    public function getEndsAtChangeDescription(): ?string
    {
        if (!$this->hasEndsAtChange()) {
            return null;
        }

        $old = $this->old_ends_at?->format('d.m.Y') ?? 'не было';
        $new = $this->new_ends_at?->format('d.m.Y') ?? 'не стало';
        
        return "{$old} → {$new}";
    }

    /**
     * Скоуп для логов лимитов
     */
    public function scopeLimits($query)
    {
        return $query->where('entity_type', 'limit');
    }

    /**
     * Скоуп для логов делегированных лимитов
     */
    public function scopeDelegatedLimits($query)
    {
        return $query->where('entity_type', 'delegated_limit');
    }

    /**
     * Скоуп для логов подписок
     */
    public function scopeSubscriptions($query)
    {
        return $query->where('entity_type', 'subscription');
    }

    /**
     * Скоуп для действий с количеством
     */
    public function scopeWithQuantityChange($query)
    {
        return $query->whereNotNull('quantity_change');
    }

    /**
     * Скоуп для конкретного пользователя
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Скоуп для конкретной сущности
     */
    public function scopeForEntity($query, string $type, int $id)
    {
        return $query->where('entity_type', $type)->where('entity_id', $id);
    }
}