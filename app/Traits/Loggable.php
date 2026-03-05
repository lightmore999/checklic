<?php

namespace App\Traits;

use App\Models\UserOrganizationLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

trait Loggable
{
    /**
     * Логировать действие
     */
    protected function log(
        string $action,
        $entity,
        ?array $oldData = null,
        ?array $newData = null,
        ?string $batchId = null
    ): void {
        // Определяем тип сущности
        $entityClass = get_class($entity);
        
        if ($entityClass === \App\Models\User::class) {
            $entityType = 'user';
        } elseif ($entityClass === \App\Models\Organization::class) {
            $entityType = 'organization';
        } elseif ($entityClass === \App\Models\Manager::class) {
            $entityType = 'manager';
        } elseif ($entityClass === \App\Models\OrgOwnerProfile::class) {
            $entityType = 'org_owner';
        } elseif ($entityClass === \App\Models\OrgMemberProfile::class) {
            $entityType = 'org_member';
        } else {
            $entityType = strtolower(class_basename($entity));
        }

        // Получаем ID текущего пользователя
        $userId = auth()->id();

        // Получаем IP и User Agent
        $ipAddress = Request::ip();
        $userAgent = Request::userAgent();

        // Если batchId не передан, создаем новый
        if (!$batchId) {
            $batchId = (string) Str::uuid();
        }

        UserOrganizationLog::create([
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entity->id,
            'action' => $action,
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'batch_id' => $batchId,
        ]);
    }

    /**
     * Логировать создание
     */
    protected function logCreate($entity, ?string $batchId = null): void
    {
        $this->log('create', $entity, null, $entity->toArray(), $batchId);
    }

    /**
     * Логировать обновление
     */
    protected function logUpdate($entity, array $oldData, ?string $batchId = null): void
    {
        $this->log('update', $entity, $oldData, $entity->fresh()->toArray(), $batchId);
    }

    /**
     * Логировать удаление
     */
    protected function logDelete($entity, ?string $batchId = null): void
    {
        $this->log('delete', $entity, $entity->toArray(), null, $batchId);
    }

    /**
     * Логировать изменение статуса
     */
    protected function logStatusChange($entity, string $oldStatus, string $newStatus, ?string $batchId = null): void
    {
        $this->log('status_change', $entity, 
            ['status' => $oldStatus], 
            ['status' => $newStatus], 
            $batchId
        );
    }

    /**
     * Логировать массовое действие (например, импорт)
     */
    protected function logBulk(string $action, array $entities, ?string $batchId = null): void
    {
        $batchId = $batchId ?? (string) Str::uuid();
        
        foreach ($entities as $entity) {
            $this->log($action, $entity, null, $entity->toArray(), $batchId);
        }
    }
}