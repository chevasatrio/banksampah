@extends('layouts.app')

@section('title', 'Transaksi Tarik')
@section('page-title', 'Transaksi Tarik Saldo')
@section('breadcrumb', 'Daftar semua transaksi penarikan saldo')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-arrow-up" style="color: var(--danger); margin-right: 8px;"></i>Riwayat Tarik Saldo</h3>
        <a href="{{ route('admin.transaksi-tarik.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tarik Baru
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
                        <th class="text-right">Jumlah</th>
                        <th>Keterangan</th>
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
                                <span style="font-family: monospace; font-weight: 600; font-size: 12px; color: var(--accent);">
                                    {{ $trx->kode_transaksi }}
                                </span>
                            </td>
                            <td>
                                <p class="text-bold">{{ $trx->nasabah->nama }}</p>
                                <p class="text-muted text-sm">{{ $trx->nasabah->no_anggota }}</p>
                            </td>
                            <td class="text-muted">{{ $trx->petugas->name }}</td>
                            <td class="text-right">
                                <span class="text-bold text-danger" style="font-size: 14px;">
                                    -Rp {{ number_format($trx->jumlah, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-muted text-sm">{{ \Illuminate\Support\Str::limit($trx->keterangan ?? '-', 30) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fas fa-wallet"></i>
                                    <p>Belum ada transaksi penarikan</p>
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
