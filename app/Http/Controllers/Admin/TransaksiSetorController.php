<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransaksiSetorRequest;
use App\Models\JenisSampah;
use App\Models\Nasabah;
use App\Models\TransaksiSetor;
use App\Services\TransaksiSetorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TransaksiSetorController extends Controller
{
    public function __construct(
        private TransaksiSetorService $setorService
    ) {}

    /**
     * Display a listing of deposit transactions.
     */
    public function index(): View
    {
        $transaksis = TransaksiSetor::with(['nasabah', 'petugas'])
            ->latest()
            ->paginate(15);

        return view('admin.transaksi-setor.index', compact('transaksis'));
    }

    /**
     * Show the form for creating a new deposit transaction.
     */
    public function create(): View
    {
        $nasabahs = Nasabah::where('is_active', true)->orderBy('nama')->get();
        $jenisSampahs = JenisSampah::where('is_active', true)->with('kategori')->orderBy('nama')->get();

        return view('admin.transaksi-setor.create', compact('nasabahs', 'jenisSampahs'));
    }

    /**
     * Process and store a deposit transaction.
     */
    public function store(TransaksiSetorRequest $request): RedirectResponse
    {
        $nasabah = Nasabah::findOrFail($request->nasabah_id);

        $transaksi = $this->setorService->prosesSetor(
            nasabah: $nasabah,
            items: $request->items,
            petugas: $request->user(),
            catatan: $request->catatan,
        );

        return redirect()
            ->route('admin.transaksi-setor.show', $transaksi)
            ->with('success', 'Transaksi berhasil dicatat. Total: Rp ' . number_format($transaksi->total_nilai, 0, ',', '.'));
    }

    /**
     * Display the specified deposit transaction.
     */
    public function show(TransaksiSetor $transaksiSetor): View
    {
        $transaksiSetor->load(['nasabah', 'petugas', 'detailSetorSampahs.jenisSampah']);

        return view('admin.transaksi-setor.show', compact('transaksiSetor'));
    }
}
