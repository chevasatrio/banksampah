<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransaksiTarikRequest;
use App\Models\Nasabah;
use App\Models\TransaksiTarik;
use App\Services\TransaksiTarikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TransaksiTarikController extends Controller
{
    public function __construct(
        private TransaksiTarikService $tarikService
    ) {}

    /**
     * Display a listing of withdrawal transactions.
     */
    public function index(): View
    {
        $transaksis = TransaksiTarik::with(['nasabah', 'petugas'])
            ->latest()
            ->paginate(15);

        return view('admin.transaksi-tarik.index', compact('transaksis'));
    }

    /**
     * Show the form for creating a new withdrawal transaction.
     */
    public function create(): View
    {
        $nasabahs = Nasabah::where('is_active', true)
            ->where('saldo', '>', 0)
            ->orderBy('nama')
            ->get();

        return view('admin.transaksi-tarik.create', compact('nasabahs'));
    }

    /**
     * Process and store a withdrawal transaction.
     */
    public function store(TransaksiTarikRequest $request): RedirectResponse
    {
        $nasabah = Nasabah::findOrFail($request->nasabah_id);

        $transaksi = $this->tarikService->prosesTarik(
            nasabah: $nasabah,
            jumlah: (float) $request->jumlah,
            petugas: $request->user(),
            keterangan: $request->keterangan,
        );

        return redirect()
            ->route('admin.transaksi-tarik.index')
            ->with('success', 'Penarikan berhasil dicatat. Jumlah: Rp ' . number_format($transaksi->jumlah, 0, ',', '.'));
    }
}
