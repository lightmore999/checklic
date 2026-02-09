<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'only_api',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'only_api' => 'boolean',
    ];

    /**
     * Проверка, доступен ли тип отчета через API
     */
    public function isApiOnly(): bool
    {
        return $this->only_api;
    }

    /**
     * Проверка, доступен ли тип отчета через интерфейс
     */
    public function isAvailableInInterface(): bool
    {
        return !$this->only_api;
    }

    /**
     * Получить типы отчетов доступные через интерфейс
     */
    public static function getAvailableInInterface()
    {
        return self::where('only_api', false)->get();
    }

    /**
     * Получить типы отчетов доступные только через API
     */
    public static function getApiOnly()
    {
        return self::where('only_api', true)->get();
    }

    /**
     * Поиск по названию
     */
    public static function findByName(string $name)
    {
        return self::where('name', $name)->first();
    }

    /**
     * Проверка существования типа отчета
     */
    public static function existsByName(string $name): bool
    {
        return self::where('name', $name)->exists();
    }
}