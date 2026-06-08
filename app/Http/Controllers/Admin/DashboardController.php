<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LaporanService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private LaporanService $laporanService
    ) {}

    /**
     * Show the admin dashboard with statistics.
     */
    public function index(): View
    {
        $statistik = $this->laporanService->getStatistikDashboard();

        return view('admin.dashboard.index', compact('statistik'));
    }
}
