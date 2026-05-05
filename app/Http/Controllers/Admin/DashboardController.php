<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $usersByStatus = User::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'activeUsers' => (int) $usersByStatus->get(User::STATUS_ACTIVE, 0),
            'pendingUsers' => (int) $usersByStatus->get(User::STATUS_PENDING, 0),
            'recentUsers' => User::query()
                ->latest()
                ->limit(8)
                ->get(),
            'roleLabels' => User::roleOptions(),
            'statusLabels' => User::statusOptions(),
        ]);
    }
}
