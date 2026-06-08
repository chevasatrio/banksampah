@extends('layouts.app')

@section('title', 'Kategori Sampah')
@section('page-title', 'Kategori Sampah')
@section('breadcrumb', 'Kelola kategori jenis sampah')

@section('content')
<div class="grid-2">
    {{-- Form Tambah --}}
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-plus-circle" style="color: var(--primary); margin-right: 8px;"></i>Tambah Kategori</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.kategori-sampah.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="nama">Nama Kategori <span class="required">*</span></label>
                    <input type="text" id="nama" name="nama" class="form-control @error('nama') is-invalid @enderror"
                           value="{{ old('nama') }}" placeholder="Contoh: Organik" required>
                    @error('nama')<p class="invalid-feedback">{{ $message }}</p>@enderror
                </div>
                <div class="form-group mb-0">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" placeholder="Deskripsi kategori (opsional)">{{ old('deskripsi') }}</textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Daftar Kategori --}}
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-layer-group" style="color: var(--accent); margin-right: 8px;"></i>Daftar Kategori</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Jenis Sampah</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategoris as $kategori)
                        <tr>
                            <td>
                                <p class="text-bold">{{ $kategori->nama }}</p>
                                <p class="text-muted text-sm">{{ \Illuminate\Support\Str::limit($kategori->deskripsi, 50) }}</p>
                            </td>
                            <td><span class="badge badge-info">{{ $kategori->jenis_sampahs_count }} jenis</span></td>
                            <td>
                                <div class="btn-group" style="justify-content: center;">
                                    <form action="{{ route('admin.kategori-sampah.destroy', $kategori) }}" method="POST"
                                          onsubmit="return confirm('Yakin hapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <div class="empty-state"><i class="fas fa-layer-group"></i><p>Belum ada kategori</p></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
