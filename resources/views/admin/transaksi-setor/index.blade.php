@extends('layouts.app')

@section('title', 'Transaksi Setor')
@section('page-title', 'Transaksi Setor Sampah')
@section('breadcrumb', 'Daftar semua transaksi setor sampah')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-arrow-down" style="color: var(--success); margin-right: 8px;"></i>Riwayat Setor Sampah</h3>
        <a href="{{ route('admin.transaksi-setor.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Setor Baru
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode Transaksi</th>
                        <th>Nasabah</th>
                        <th>Petugas</th>
                        <th class="text-right">Total Nilai</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksis as $trx)
                        <tr>
                            <td>
                                <p class="text-bold" style="font-size: 13px;">{{ $trx->created_at->format('d/m/Y') }}</p>
                                <p class="text-muted text-sm">{{ $trx->created_at->format('H:i') }} WIB</p>
                            </td>
                            <td>
                                <span style="font-family: monospace; font-weight: 600; font-size: 12px; color: var(--primary);">
                                    {{ $trx->kode_transaksi }}
                                </span>
                            </td>
                            <td>
                                <p class="text-bold">{{ $trx->nasabah->nama }}</p>
                                <p class="text-muted text-sm">{{ $trx->nasabah->no_anggota }}</p>
                            </td>
                            <td class="text-muted">{{ $trx->petugas->name }}</td>
                            <td class="text-right">
                                <span class="text-bold text-success" style="font-size: 14px;">
                                    +Rp {{ number_format($trx->total_nilai, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.transaksi-setor.show', $trx) }}" class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Belum ada transaksi setor</p>
                                    <a href="{{ route('admin.transaksi-setor.create') }}" class="btn btn-primary btn-sm" style="margin-top: 12px;">
                                        <i class="fas fa-plus"></i> Buat Setor Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transaksis->hasPages())
    <div class="card-footer">
        <div class="pagination-wrapper">
            <span class="text-sm text-muted">Menampilkan {{ $transaksis->firstItem() }}–{{ $transaksis->lastItem() }} dari {{ $transaksis->total() }}</span>
            {{ $transaksis->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
