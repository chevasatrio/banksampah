<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the nasabah dashboard with balance and transaction history.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $nasabah = Nasabah::where('user_id', $user->id)->firstOrFail();

        $nasabah->load([
            'transaksiSetors' => fn ($q) => $q->with('detailSetorSampahs.jenisSampah')->latest()->take(10),
            'transaksiTariks' => fn ($q) => $q->latest()->take(10),
        ]);

        $totalSetor = $nasabah->transaksiSetors()->sum('total_nilai');
        $totalTarik = $nasabah->transaksiTariks()->sum('jumlah');

        return view('nasabah.dashboard.index', compact('nasabah', 'totalSetor', 'totalTarik'));
    }
}
