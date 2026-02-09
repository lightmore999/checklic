<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetStat extends Model
{
    protected $table = 'target_stats';
    
    // Поля, которые можно массово назначать
    protected $fillable = ['target', 'data'];
    
    // Ваш метод (немного адаптируем под текущую модель)
    public static function getTargetStatsElegant()
    {
        $stats = self::whereIn('target', ['ok', 'error', 'wait'])
            ->get()
            ->countBy('target')
            ->toArray();
        
        return array_merge(['ok' => 0, 'error' => 0, 'wait' => 0], $stats);
    }
}