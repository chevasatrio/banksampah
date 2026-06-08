<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LaporanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function __construct(
        private LaporanService $laporanService
    ) {}

    /**
     * Display the report page with filters.
     */
    public function index(Request $request): View
    {
        $dari = $request->input('dari')
            ? Carbon::parse($request->input('dari'))
            : Carbon::now()->startOfMonth();

        $sampai = $request->input('sampai')
            ? Carbon::parse($request->input('sampai'))
            : Carbon::now();

        $tipe = $request->input('tipe'); // 'setor', 'tarik', or null

        $laporan = $this->laporanService->getLaporanTransaksi($dari, $sampai, $tipe);
        $statistik = $this->laporanService->getStatistikDashboard();

        return view('admin.laporan.index', compact('laporan', 'statistik', 'dari', 'sampai', 'tipe'));
    }
}
