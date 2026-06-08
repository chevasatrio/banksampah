<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NasabahRequest;
use App\Models\Nasabah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NasabahController extends Controller
{
    /**
     * Display a listing of nasabahs with search.
     */
    public function index(Request $request): View
    {
        $query = Nasabah::query()->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_anggota', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        $nasabahs = $query->paginate(15)->withQueryString();

        return view('admin.nasabah.index', compact('nasabahs', 'search'));
    }

    /**
     * Show the form for creating a new nasabah.
     */
    public function create(): View
    {
        return view('admin.nasabah.create');
    }

    /**
     * Store a newly created nasabah.
     */
    public function store(NasabahRequest $request): RedirectResponse
    {
        Nasabah::create($request->validated());

        return redirect()
            ->route('admin.nasabah.index')
            ->with('success', 'Nasabah berhasil ditambahkan.');
    }

    /**
     * Display the specified nasabah.
     */
    public function show(Nasabah $nasabah): View
    {
        $nasabah->load([
            'transaksiSetors' => fn ($q) => $q->with('detailSetorSampahs.jenisSampah')->latest()->take(10),
            'transaksiTariks' => fn ($q) => $q->latest()->take(10),
        ]);

        return view('admin.nasabah.show', compact('nasabah'));
    }

    /**
     * Show the form for editing the specified nasabah.
     */
    public function edit(Nasabah $nasabah): View
    {
        return view('admin.nasabah.edit', compact('nasabah'));
    }

    /**
     * Update the specified nasabah.
     */
    public function update(NasabahRequest $request, Nasabah $nasabah): RedirectResponse
    {
        $nasabah->update($request->validated());

        return redirect()
            ->route('admin.nasabah.index')
            ->with('success', 'Data nasabah berhasil diperbarui.');
    }

    /**
     * Toggle nasabah active/inactive status.
     */
    public function toggleActive(Nasabah $nasabah): RedirectResponse
    {
        $nasabah->update(['is_active' => ! $nasabah->is_active]);

        $status = $nasabah->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.nasabah.index')
            ->with('success', "Nasabah berhasil {$status}.");
    }
}
