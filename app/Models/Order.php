<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'provider',
        'status',
    ];

    /**
     * Статусы заказов
     */
    public const STATUS_OK = 'ok';
    public const STATUS_WAIT = 'wait';
    public const STATUS_ERROR = 'error';
    public const STATUS_PROCESSING = 'processing';

    /**
     * Получить все возможные статусы
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_OK,
            self::STATUS_WAIT,
            self::STATUS_ERROR,
            self::STATUS_PROCESSING,
        ];
    }
}