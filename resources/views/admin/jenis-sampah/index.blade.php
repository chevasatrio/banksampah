@extends('layouts.app')

@section('title', 'Jenis & Harga Sampah')
@section('page-title', 'Jenis & Harga Sampah')
@section('breadcrumb', 'Kelola jenis sampah dan harga per KG')

@section('content')
{{-- Form Tambah --}}
@if(auth()->user()->isAdmin())
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fas fa-plus-circle" style="color: var(--primary); margin-right: 8px;"></i>Tambah Jenis Sampah</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.jenis-sampah.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label for="nama">Nama Jenis Sampah <span class="required">*</span></label>
                    <input type="text" id="nama" name="nama" class="form-control @error('nama') is-invalid @enderror"
                           value="{{ old('nama') }}" placeholder="Contoh: Botol Plastik PET" required>
                    @error('nama')<p class="invalid-feedback">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="kategori_id">Kategori <span class="required">*</span></label>
                    <select id="kategori_id" name="kategori_id" class="form-control @error('kategori_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori_id')<p class="invalid-feedback">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="harga_per_kg">Harga per KG (Rp) <span class="required">*</span></label>
                    <input type="number" id="harga_per_kg" name="harga_per_kg" class="form-control @error('harga_per_kg') is-invalid @enderror"
                           value="{{ old('harga_per_kg') }}" placeholder="2000" min="0" step="100" required>
                    @error('harga_per_kg')<p class="invalid-feedback">{{ $message }}</p>@enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        </form>
    </div>
</div>
@endif

{{-- Daftar Jenis Sampah --}}
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-trash-alt" style="color: var(--accent); margin-right: 8px;"></i>Daftar Harga Sampah</h3>
        <span class="badge badge-info">{{ $jenisSampahs->total() }} jenis</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th class="text-right">Harga/KG</th>
                        <th>Status</th>
                        @if(auth()->user()->isAdmin())
                        <th class="text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($jenisSampahs as $jenis)
                        <tr>
                            <td class="text-bold">{{ $jenis->nama }}</td>
                            <td><span class="badge badge-secondary">{{ $jenis->kategori->nama }}</span></td>
                            <td class="text-right text-bold" style="color: var(--primary);">
                                Rp {{ number_format($jenis->harga_per_kg, 0, ',', '.') }}
                            </td>
                            <td>
                                @if($jenis->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
                            </td>
                            @if(auth()->user()->isAdmin())
                            <td>
                                <div class="btn-group" style="justify-content: center;">
                                    <form action="{{ route('admin.jenis-sampah.toggle-active', $jenis) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn {{ $jenis->is_active ? 'btn-danger' : 'btn-primary' }} btn-xs">
                                            <i class="fas {{ $jenis->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state"><i class="fas fa-recycle"></i><p>Belum ada jenis sampah</p></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($jenisSampahs->hasPages())
    <div class="card-footer">
        {{ $jenisSampahs->links() }}
    </div>
    @endif
</div>
@endsection
