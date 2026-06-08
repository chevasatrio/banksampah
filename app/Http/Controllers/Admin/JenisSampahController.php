<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\JenisSampahRequest;
use App\Models\JenisSampah;
use App\Models\KategoriSampah;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JenisSampahController extends Controller
{
    /**
     * Display a listing of waste types grouped by category.
     */
    public function index(): View
    {
        $jenisSampahs = JenisSampah::with('kategori')->latest()->paginate(20);
        $kategoris = KategoriSampah::all();

        return view('admin.jenis-sampah.index', compact('jenisSampahs', 'kategoris'));
    }

    /**
     * Store a newly created waste type.
     */
    public function store(JenisSampahRequest $request): RedirectResponse
    {
        JenisSampah::create($request->validated());

        return redirect()
            ->route('admin.jenis-sampah.index')
            ->with('success', 'Jenis sampah berhasil ditambahkan.');
    }

    /**
     * Update the specified waste type.
     */
    public function update(JenisSampahRequest $request, JenisSampah $jenisSampah): RedirectResponse
    {
        $jenisSampah->update($request->validated());

        return redirect()
            ->route('admin.jenis-sampah.index')
            ->with('success', 'Jenis sampah berhasil diperbarui.');
    }

    /**
     * Toggle waste type active/inactive status.
     */
    public function toggleActive(JenisSampah $jenisSampah): RedirectResponse
    {
        $jenisSampah->update(['is_active' => ! $jenisSampah->is_active]);

        $status = $jenisSampah->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.jenis-sampah.index')
            ->with('success', "Jenis sampah berhasil {$status}.");
    }
}
