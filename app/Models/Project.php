<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    protected $fillable = ['name', 'target', 'date_start', 'date_end'];
    
    public static function getAverageDaysByTargetLast($limit = 5)
    {
        // Предполагаем, что есть индекс на (id) или (created_at)
        $results = DB::table('projects')
            ->whereNotNull('target')
            ->whereNotNull('date_start')
            ->whereNotNull('date_end')
            ->where('id', '>=', function($query) use ($limit) {
                // Находим минимальный ID из последних $limit записей
                $query->select('id')
                    ->from('projects')
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->orderBy('id', 'asc')
                    ->limit(1);
            })
            ->select('target')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (date_end::timestamp - date_start::timestamp)) / 86400) as average_days')
            ->groupBy('target')
            ->orderBy('target')
            ->get();
        
        $formattedResults = [];
        
        foreach ($results as $result) {
            $formattedResults[$result->target] = round($result->average_days ?? 0, 2);
        }
        
        return $formattedResults;
    }
}