@extends('layouts.app')

@section('title', 'Daftar Nasabah')
@section('page-title', 'Manajemen Nasabah')
@section('breadcrumb', 'Daftar semua nasabah bank sampah')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-users" style="color: var(--primary); margin-right: 8px;"></i>Daftar Nasabah</h3>
        <div class="d-flex gap-2 align-center">
            <form action="{{ route('admin.nasabah.index') }}" method="GET" class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" id="search" value="{{ $search ?? '' }}" placeholder="Cari nama, NIK, no. anggota...">
            </form>
            <a href="{{ route('admin.nasabah.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Nasabah
            </a>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No. Anggota</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>No. HP</th>
                        <th>Saldo</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nasabahs as $nasabah)
                        <tr class="nasabah-row">
                            <td><span class="text-bold" style="color: var(--primary);">{{ $nasabah->no_anggota }}</span></td>
                            <td>{{ $nasabah->nama }}</td>
                            <td><span class="text-muted">{{ $nasabah->nik }}</span></td>
                            <td>{{ $nasabah->no_hp }}</td>
                            <td class="text-bold">Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}</td>
                            <td>
                                @if($nasabah->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" style="justify-content: center;">
                                    <a href="{{ route('admin.nasabah.show', $nasabah) }}" class="btn btn-info btn-xs" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.nasabah.edit', $nasabah) }}" class="btn btn-warning btn-xs" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.nasabah.toggle-active', $nasabah) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn {{ $nasabah->is_active ? 'btn-danger' : 'btn-primary' }} btn-xs"
                                                title="{{ $nasabah->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="fas {{ $nasabah->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <p>Belum ada nasabah terdaftar</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($nasabahs->hasPages())
    <div class="card-footer">
        <div class="pagination-wrapper">
            <span class="text-sm text-muted">Menampilkan {{ $nasabahs->firstItem() }}–{{ $nasabahs->lastItem() }} dari {{ $nasabahs->total() }} nasabah</span>
            {{ $nasabahs->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
