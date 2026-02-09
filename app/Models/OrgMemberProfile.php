<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgMemberProfile extends Model
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
        'boss_id',
        'manager_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Пользователь-сотрудник
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Организация, в которой работает сотрудник
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Непосредственный начальник (владелец организации)
     */
    public function boss(): BelongsTo
    {
        return $this->belongsTo(User::class, 'boss_id');
    }

    /**
     * Менеджер, контролирующий организацию
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Проверка, активен ли сотрудник
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->user->is_active;
    }

    /**
     * Активировать сотрудника
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Деактивировать сотрудника
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Проверка, может ли сотрудник работать (организация активна)
     */
    public function canWork(): bool
    {
        return $this->isActive() && $this->organization->isActive();
    }
}