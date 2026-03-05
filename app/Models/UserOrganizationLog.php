<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOrganizationLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_organization_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
        'action',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
        'batch_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    /**
     * Пользователь, совершивший действие
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить сущность, над которой совершено действие (полиморфная связь)
     */
    public function entity()
    {
        return match($this->entity_type) {
            'user' => User::class,
            'organization' => Organization::class,
            'manager' => Manager::class,
            'org_owner' => OrgOwnerProfile::class,
            'org_member' => OrgMemberProfile::class,
            default => null,
        };
    }

    /**
     * Получить читаемое название типа сущности
     */
    public function getEntityTypeNameAttribute(): string
    {
        return match($this->entity_type) {
            'user' => 'Пользователь',
            'organization' => 'Организация',
            'manager' => 'Менеджер',
            'org_owner' => 'Владелец организации',
            'org_member' => 'Сотрудник',
            default => $this->entity_type,
        };
    }

    /**
     * Получить читаемое название действия
     */
    public function getActionNameAttribute(): string
    {
        return match($this->action) {
            'create' => 'Создание',
            'update' => 'Изменение',
            'delete' => 'Удаление',
            'restore' => 'Восстановление',
            'login' => 'Вход в систему',
            'logout' => 'Выход из системы',
            'status_change' => 'Изменение статуса',
            default => $this->action,
        };
    }

    /**
     * Получить изменения в читаемом виде
     */
    public function getChangesAttribute(): array
    {
        if (!$this->old_data || !$this->new_data) {
            return [];
        }

        $changes = [];
        $old = $this->old_data;
        $new = $this->new_data;

        foreach ($new as $key => $value) {
            if (isset($old[$key]) && $old[$key] != $value) {
                $changes[$key] = [
                    'old' => $old[$key],
                    'new' => $value
                ];
            }
        }

        return $changes;
    }

    /**
     * Проверить, было ли изменено конкретное поле
     */
    public function wasFieldChanged(string $field): bool
    {
        return array_key_exists($field, $this->changes);
    }

    /**
     * Получить старое значение поля
     */
    public function getOldValue(string $field)
    {
        return $this->changes[$field]['old'] ?? null;
    }

    /**
     * Получить новое значение поля
     */
    public function getNewValue(string $field)
    {
        return $this->changes[$field]['new'] ?? null;
    }

    /**
     * Скоуп для логов конкретной сущности
     */
    public function scopeForEntity($query, string $type, int $id)
    {
        return $query->where('entity_type', $type)
                     ->where('entity_id', $id);
    }

    /**
     * Скоуп для логов конкретного пользователя (кто совершил действие)
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Скоуп для логов конкретного действия
     */
    public function scopeWithAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Скоуп для логов за период
     */
    public function scopeInPeriod($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}