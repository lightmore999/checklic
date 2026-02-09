<?php

namespace App\Http\Controllers;

use App\Models\ReportType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Показать форму создания отчета
     */
    public function create()
    {
        // Получаем типы отчетов доступные через интерфейс
        $reportTypes = ReportType::where('only_api', false)->get();
        
        return view('reports.create', compact('reportTypes'));
    }
    
    /**
     * Обработка создания отчета (заглушка)
     */
    public function store(Request $request)
    {
        // Валидация базовых полей
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'region' => 'nullable|string|max:100',
            'passport_series' => 'nullable|string|max:4',
            'passport_number' => 'nullable|string|max:6',
            'passport_date' => 'nullable|date',
            'vehicle_number' => 'nullable|string|max:20',
            'cadastral_number' => 'nullable|string|max:50',
            'property_type' => 'nullable|in:land,building,premises',
        ]);
        
        // Получаем выбранные типы отчетов (если бы у нас была форма)
        // Пока что используем заглушку
        $selectedTypes = [1, 2]; // Пример выбранных типов
        
        // Собираем информацию о созданных отчетах
        $createdReports = [];
        foreach ($selectedTypes as $typeId) {
            $type = ReportType::find($typeId);
            if ($type) {
                $createdReports[] = [
                    'type' => $type->name,
                    'data' => $this->extractDataForType($typeId, $validated)
                ];
            }
        }
        
        return redirect()->route('reports.create')
            ->with('success', 'Отчеты успешно созданы (демо-режим)')
            ->with('demo_data', [
                'selected_types' => $createdReports,
                'common_data' => $validated,
                'created_by' => Auth::user()->name,
                'created_at' => now()->format('d.m.Y H:i'),
                'total_reports' => count($createdReports),
            ]);
    }
    
    /**
     * Извлечение данных для конкретного типа отчета
     */
    private function extractDataForType($typeId, $data)
    {
        $config = [
            1 => ['name', 'birth_date', 'region'], // CL:Базовый V1
            2 => ['name', 'birth_date', 'region', 'passport_series', 'passport_number', 'passport_date'], // CL:Паспорт V1
            3 => ['vehicle_number'], // AI:АвтоИстория V1
            4 => ['cadastral_number', 'property_type'] // CL:Недвижимость
        ];
        
        $result = [];
        if (isset($config[$typeId])) {
            foreach ($config[$typeId] as $field) {
                if (!empty($data[$field])) {
                    $result[$field] = $data[$field];
                }
            }
        }
        
        return $result;
    }
}