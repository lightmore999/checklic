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
        'our_organization', // ДОБАВЛЕНО (или company_name)
        'inn',
        'manager_id',
        'status',
        'max_employees',
    ];

    protected $casts = [
        'max_employees' => 'integer',
    ];

    /**
     * Менеджер, отвечающий за организацию
     */
    public function manager(): BelongsTo
    {
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
     * Обновить статус организации
     */
    public function updateStatus(string $status): void
    {
        $this->update(['status' => $status]);
    }

    public function members()
    {
        return $this->hasMany(OrgMemberProfile::class, 'organization_id');
    }

    /**
     * Проверка, можно ли добавить еще сотрудников
     */
    public function canAddMoreEmployees(): bool
    {
        if ($this->max_employees === null) {
            return true;
        }

        $currentCount = $this->members()->count();
        return $currentCount < $this->max_employees;
    }

    /**
     * Получить количество доступных мест для сотрудников
     */
    public function getAvailableEmployeeSlots(): ?int
    {
        if ($this->max_employees === null) {
            return null;
        }

        $currentCount = $this->members()->count();
        return max(0, $this->max_employees - $currentCount);
    }

    /**
     * Проверка, заполнен ли ИНН
     */
    public function hasInn(): bool
    {
        return !empty($this->inn);
    }

    /**
     * Получить полное название организации (оба поля)
     */
    public function getFullNameAttribute(): string
    {
        if ($this->our_organization) {
            return $this->our_organization . ' (' . $this->name . ')';
        }
        return $this->name;
    }
}
