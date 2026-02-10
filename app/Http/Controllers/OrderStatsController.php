<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderStatsController extends Controller
{
    /**
     * Показать статистику по заказам
     */
    public function index(Request $request)
    {
        // Получаем выбранный период
        $period = $request->input('period', 'all');
        
        // Фильтруем заказы по периоду
        $query = Order::query();
        
        switch ($period) {
            case '1hour':
                $query->where('created_at', '>=', Carbon::now()->subHour());
                break;
            case '24hours':
                $query->where('created_at', '>=', Carbon::now()->subDay());
                break;
            case '7days':
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
                break;
            case '1month':
                $query->where('created_at', '>=', Carbon::now()->subMonth());
                break;
            // 'all' - без фильтра
        }
        
        // Получаем отфильтрованные заказы
        $orders = $query->get();
        $totalOrders = $orders->count();
        
        // Статистика по статусам (только количество)
        $statusStats = [
            'ok' => $orders->where('status', 'ok')->count(),
            'wait' => $orders->where('status', 'wait')->count(),
            'error' => $orders->where('status', 'error')->count(),
            'processing' => $orders->where('status', 'processing')->count(),
        ];
        
        // Рассчитываем ОБЩЕЕ среднее время для всех заказов
        $totalSeconds = 0;
        $validOrders = 0;
        
        foreach ($orders as $order) {
            // Определяем конечное время
            $endTime = $order->updated_at;
            
            // Если updated_at = null, используем текущее время
            if (is_null($endTime)) {
                $endTime = now();
            }
            
            // Рассчитываем разницу в секундах
            $diffSeconds = $endTime->diffInSeconds($order->created_at);
            
            // Добавляем к общей сумме
            $totalSeconds += $diffSeconds;
            $validOrders++;
        }
        
        // Форматируем общее среднее время
        $avgTime = $validOrders > 0 ? $this->formatTime($totalSeconds / $validOrders) : '00:00';
        
        return view('order-stats.index', compact(
            'statusStats',
            'avgTime',
            'totalOrders',
            'period'
        ));
    }
    
    /**
     * Форматирует секунды
     */
    private function formatTime($seconds)
    {
        $seconds = intval($seconds);
        
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        
        if ($days > 0) {
            return sprintf("%dд %02d:%02d:%02d", $days, $hours, $minutes, $seconds);
        } elseif ($hours > 0) {
            return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        } else {
            return sprintf("%02d:%02d", $minutes, $seconds);
        }
    }
}