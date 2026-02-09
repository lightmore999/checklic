<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Панель менеджера
     */
    public function index(Request $request)
    {
        $manager = Auth::user();
        
        if (!$manager->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        // === СТАТИСТИКА ===
        $stats = [
            'total_organizations' => Organization::whereHas('manager', function($query) use ($manager) {
                $query->where('user_id', $manager->id);
            })->count(),
            
            'active_organizations' => Organization::where('status', 'active')
                ->whereHas('manager', function($query) use ($manager) {
                    $query->where('user_id', $manager->id);
                })->count(),
            
            'pending_organizations' => Organization::where('status', 'pending')
                ->whereHas('manager', function($query) use ($manager) {
                    $query->where('user_id', $manager->id);
                })->count(),
        ];
        
        // === ВСЕ ОРГАНИЗАЦИИ МЕНЕДЖЕРА ===
        $organizations = Organization::whereHas('manager', function($query) use ($manager) {
                $query->where('user_id', $manager->id);
            })
            ->with(['manager.user', 'owner.user', 'members'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // === АДМИН МЕНЕДЖЕРА ===
        $admin = $manager->managerProfile->admin ?? null;
        
        return view('manager.dashboard', compact(
            'manager',
            'stats',
            'organizations',
            'admin'
        ));
    }
}