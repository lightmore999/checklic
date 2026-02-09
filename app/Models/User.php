<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',        
        'is_active',   
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Scope для активных пользователей
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для пользователей по роли
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Проверка роли пользователя
     */
    public function hasRole($role): bool
    {
        return $this->role === $role;
    }

    /**
     * Проверка, является ли пользователь администратором
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Проверка, является ли пользователь менеджером
     */
    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    /**
     * Проверка, является ли пользователь владельцем организации
     */
    public function isOrgOwner(): bool
    {
        return $this->hasRole('org_owner');
    }

    /**
     * Проверка, является ли пользователь сотрудником организации
     */
    public function isOrgMember(): bool
    {
        return $this->hasRole('org_member');
    }

    /**
     * Активация пользователя
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Деактивация пользователя
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    // ==================== СВЯЗИ ====================

    /**
     * Связь с профилем менеджера (если пользователь является менеджером)
     */
    public function managerProfile()
    {
        return $this->hasOne(Manager::class, 'user_id');
    }

    /**
     * Связь с менеджерами, созданными этим администратором
     */
    public function createdManagers()
    {
        return $this->hasMany(Manager::class, 'admin_id');
    }

    /**
     * Связь с профилем владельца организации (если пользователь является org_owner)
     */
    public function orgOwnerProfile()
    {
        return $this->hasOne(OrgOwnerProfile::class, 'user_id');
    }

    /**
     * Связь с профилем сотрудника организации (если пользователь является org_member)
     */
    public function orgMemberProfile()
    {
        return $this->hasOne(OrgMemberProfile::class, 'user_id');
    }

    /**
     * Связь с сотрудниками, которые подчиняются этому владельцу
     */
    public function subordinates()
    {
        return $this->hasMany(OrgMemberProfile::class, 'boss_id', 'id');
    }

    /**
     * Связь с организациями, которые контролирует менеджер
     */
    public function managedOrganizations()
    {
        return $this->hasMany(Organization::class, 'manager_id');
    }

    // ==================== ПРОВЕРКИ ====================

    /**
     * Проверка, имеет ли пользователь профиль менеджера
     */
    public function isManagerProfile(): bool
    {
        return $this->managerProfile !== null;
    }

    /**
     * Проверка, имеет ли пользователь профиль владельца организации
     */
    public function isOrgOwnerProfile(): bool
    {
        return $this->orgOwnerProfile !== null;
    }

    /**
     * Проверка, имеет ли пользователь профиль сотрудника организации
     */
    public function isOrgMemberProfile(): bool
    {
        return $this->orgMemberProfile !== null;
    }

    /**
     * Проверка, является ли администратором (имеет созданных менеджеров)
     */
    public function hasCreatedManagers(): bool
    {
        return $this->createdManagers()->exists();
    }

    // ==================== ПОЛЕЗНЫЕ МЕТОДЫ ====================

    /**
     * Получить организацию пользователя (если он owner или member)
     */
    public function getOrganization()
    {
        if ($this->isOrgOwner()) {
            return $this->orgOwnerProfile->organization ?? null;
        }
        
        if ($this->isOrgMember()) {
            return $this->orgMemberProfile->organization ?? null;
        }
        
        return null;
    }

    /**
     * Получить профиль организации (owner или member)
     */
    public function getOrgProfile()
    {
        if ($this->isOrgOwner()) {
            return $this->orgOwnerProfile;
        }
        
        if ($this->isOrgMember()) {
            return $this->orgMemberProfile;
        }
        
        return null;
    }

    /**
     * Получить количество созданных менеджеров (только для админов)
     */
    public function getCreatedManagersCount(): int
    {
        if ($this->isAdmin()) {
            return $this->createdManagers()->count();
        }
        return 0;
    }

    /**
     * Получить информацию о пользователе для отображения
     */
    public function getDisplayInfo(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'role_display' => $this->getRoleDisplayName(),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->format('d.m.Y H:i'),
        ];
    }

    /**
     * Получить отображаемое название роли
     */
    public function getRoleDisplayName(): string
    {
        $roles = [
            'admin' => 'Администратор',
            'manager' => 'Менеджер',
            'org_owner' => 'Владелец организации',
            'org_member' => 'Сотрудник организации',
        ];
        
        return $roles[$this->role] ?? $this->role;
    }

    /**
     * Получить цвет для роли (для badge)
     */
    public function getRoleColor(): string
    {
        $colors = [
            'admin' => 'danger',
            'manager' => 'primary',
            'org_owner' => 'success',
            'org_member' => 'warning',
        ];
        
        return $colors[$this->role] ?? 'secondary';
    }
}