<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KategoriSampahRequest;
use App\Models\KategoriSampah;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KategoriSampahController extends Controller
{
    /**
     * Display a listing of waste categories.
     */
    public function index(): View
    {
        $kategoris = KategoriSampah::withCount('jenisSampahs')->latest()->get();

        return view('admin.kategori-sampah.index', compact('kategoris'));
    }

    /**
     * Store a newly created waste category.
     */
    public function store(KategoriSampahRequest $request): RedirectResponse
    {
        KategoriSampah::create($request->validated());

        return redirect()
            ->route('admin.kategori-sampah.index')
            ->with('success', 'Kategori sampah berhasil ditambahkan.');
    }

    /**
     * Update the specified waste category.
     */
    public function update(KategoriSampahRequest $request, KategoriSampah $kategoriSampah): RedirectResponse
    {
        $kategoriSampah->update($request->validated());

        return redirect()
            ->route('admin.kategori-sampah.index')
            ->with('success', 'Kategori sampah berhasil diperbarui.');
    }

    /**
     * Remove the specified waste category.
     */
    public function destroy(KategoriSampah $kategoriSampah): RedirectResponse
    {
        // Prevent deletion if there are waste types linked to this category
        if ($kategoriSampah->jenisSampahs()->exists()) {
            return redirect()
                ->route('admin.kategori-sampah.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki jenis sampah terkait.');
        }

        $kategoriSampah->delete();

        return redirect()
            ->route('admin.kategori-sampah.index')
            ->with('success', 'Kategori sampah berhasil dihapus.');
    }
}
