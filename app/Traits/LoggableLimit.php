<?php

namespace App\Traits;

use App\Models\LimitSubscriptionLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

trait LoggableLimit
{
    /**
     * Логировать действие с лимитом
     */
    protected function logLimit(
        $action,
        $entity,
        $oldData = null,
        $newData = null,
        $quantityChange = null,
        $oldQuantity = null,
        $newQuantity = null,
        $oldEndsAt = null,
        $newEndsAt = null,
        $batchId = null
    ) {
        // Определяем тип сущности
        $entityClass = get_class($entity);
        $entityType = 'unknown';
        
        if ($entityClass === 'App\Models\Limit') {
            $entityType = 'limit';
        } elseif ($entityClass === 'App\Models\DelegatedLimit') {
            $entityType = 'delegated_limit';
        } elseif ($entityClass === 'App\Models\Subscription') {
            $entityType = 'subscription';
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

        LimitSubscriptionLog::create([
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entity->id,
            'action' => $action,
            'old_data' => $oldData,
            'new_data' => $newData,
            'quantity_change' => $quantityChange,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'old_ends_at' => $oldEndsAt,
            'new_ends_at' => $newEndsAt,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'batch_id' => $batchId,
        ]);
    }

    /**
     * Логировать создание
     */
    protected function logCreateLimit($entity, $batchId = null)
    {
        // Получаем количество из созданного лимита
        $quantity = $entity->quantity ?? 0;
        
        $this->logLimit(
            'create', 
            $entity, 
            null, 
            $entity->toArray(), 
            $quantity,           // quantity_change = количество
            null,                // old_quantity = null (до создания ничего не было)
            $quantity,           // new_quantity = количество
            null, 
            null, 
            $batchId
        );
    }

    /**
     * Логировать обновление
     */
    protected function logUpdateLimit($entity, $oldData, $batchId = null)
    {
        $this->logLimit('update', $entity, $oldData, $entity->fresh()->toArray(), null, null, null, null, null, $batchId);
    }

    /**
     * Логировать удаление
     */
    protected function logDeleteLimit($entity, $batchId = null)
    {
        $this->logLimit('delete', $entity, $entity->toArray(), null, null, null, null, null, null, $batchId);
    }

    /**
     * Логировать изменение количества лимита
     */
    protected function logQuantityChange($entity, $oldQuantity, $newQuantity, $action = 'update', $batchId = null)
    {
        $change = $newQuantity - $oldQuantity;
        
        $actionName = 'update';
        if ($action === 'use_quantity') {
            $actionName = 'use_quantity';
        } elseif ($action === 'return_quantity') {
            $actionName = 'return_quantity';
        }
        
        $this->logLimit(
            $actionName,
            $entity,
            ['quantity' => $oldQuantity],
            ['quantity' => $newQuantity],
            $change,
            $oldQuantity,
            $newQuantity,
            null,
            null,
            $batchId
        );
    }

    /**
     * Логировать использование лимита (уменьшение)
     */
    protected function logUseQuantity($entity, $amount, $oldQuantity, $newQuantity, $batchId = null)
    {
        $this->logQuantityChange($entity, $oldQuantity, $newQuantity, 'use_quantity', $batchId);
    }

    /**
     * Логировать возврат лимита (увеличение)
     */
    protected function logReturnQuantity($entity, $amount, $oldQuantity, $newQuantity, $batchId = null)
    {
        $this->logQuantityChange($entity, $oldQuantity, $newQuantity, 'return_quantity', $batchId);
    }

    /**
     * Логировать делегирование лимита
     */
    protected function logDelegate($entity, $userId, $quantity, $batchId = null)
    {
        $this->logLimit(
            'delegate',
            $entity,
            null,
            [
                'delegated_to' => $userId,
                'quantity' => $quantity,
                'limit_id' => $entity->id
            ],
            -$quantity,          // quantity_change = отрицательное (лимит ушел из основного)
            null,
            null,
            null,
            null,
            $batchId
        );
    }

    /**
     * Логировать изменение статуса подписки
     */
    protected function logSubscriptionStatus($subscription, $oldStatus, $newStatus, $batchId = null)
    {
        $action = 'update';
        
        if ($newStatus === 'active') {
            $action = 'activate';
        } elseif ($newStatus === 'suspended') {
            $action = 'suspend';
        } elseif ($newStatus === 'cancelled') {
            $action = 'cancel';
        }

        $this->logLimit(
            $action,
            $subscription,
            ['status' => $oldStatus],
            ['status' => $newStatus],
            null,
            null,
            null,
            null,
            null,
            $batchId
        );
    }

    /**
     * Логировать продление подписки
     */
    protected function logSubscriptionExtend($subscription, $oldEndsAt, $newEndsAt, $batchId = null)
    {
        $this->logLimit(
            'extend',
            $subscription,
            ['ends_at' => $oldEndsAt],
            ['ends_at' => $newEndsAt],
            null,       
            null,
            null,
            $oldEndsAt,
            $newEndsAt,
            $batchId
        );
    }
}