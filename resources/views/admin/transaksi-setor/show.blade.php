@extends('layouts.app')

@section('title', 'Detail Transaksi Setor')
@section('page-title', 'Detail Transaksi')
@section('breadcrumb', 'Transaksi Setor › ' . $transaksiSetor->kode_transaksi)

@section('content')
{{-- Transaction Header --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 16px;">
            <div>
                <p class="text-muted text-sm">Kode Transaksi</p>
                <h3 style="font-family: monospace; font-size: 20px; color: var(--primary); letter-spacing: 1px;">
                    {{ $transaksiSetor->kode_transaksi }}
                </h3>
            </div>
            <div class="text-right">
                <p class="text-muted text-sm">Total Nilai</p>
                <p style="font-size: 28px; font-weight: 800; color: var(--success);">
                    Rp {{ number_format($transaksiSetor->total_nilai, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Info Grid --}}
<div class="grid-2 mb-3">
    <div class="card">
        <div class="card-header"><h3>Informasi Nasabah</h3></div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Nama</label>
                    <p>{{ $transaksiSetor->nasabah->nama }}</p>
                </div>
                <div class="detail-item">
                    <label>No. Anggota</label>
                    <p style="color: var(--primary);">{{ $transaksiSetor->nasabah->no_anggota }}</p>
                </div>
                <div class="detail-item">
                    <label>Saldo Saat Ini</label>
                    <p>Rp {{ number_format($transaksiSetor->nasabah->saldo, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3>Informasi Transaksi</h3></div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Tanggal</label>
                    <p>{{ $transaksiSetor->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                </div>
                <div class="detail-item">
                    <label>Petugas</label>
                    <p>{{ $transaksiSetor->petugas->name }}</p>
                </div>
                <div class="detail-item">
                    <label>Catatan</label>
                    <p>{{ $transaksiSetor->catatan ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Detail Items --}}
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fas fa-list" style="color: var(--accent); margin-right: 8px;"></i>Detail Sampah Disetor</h3>
        <span class="badge badge-info">{{ $transaksiSetor->detailSetorSampahs->count() }} jenis</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis Sampah</th>
                        <th class="text-right">Harga/KG</th>
                        <th class="text-right">Berat (KG)</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaksiSetor->detailSetorSampahs as $i => $detail)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td class="text-bold">{{ $detail->jenisSampah->nama }}</td>
                            <td class="text-right text-muted">Rp {{ number_format($detail->harga_saat_itu, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($detail->berat_kg, 2) }} KG</td>
                            <td class="text-right text-bold" style="color: var(--primary);">
                                Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: var(--primary-50);">
                        <td colspan="3"></td>
                        <td class="text-right text-bold">
                            {{ number_format($transaksiSetor->detailSetorSampahs->sum('berat_kg'), 2) }} KG
                        </td>
                        <td class="text-right" style="font-size: 16px; font-weight: 800; color: var(--primary);">
                            Rp {{ number_format($transaksiSetor->total_nilai, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Actions --}}
<div class="d-flex gap-2">
    <a href="{{ route('admin.transaksi-setor.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
    <button onclick="window.print()" class="btn btn-info">
        <i class="fas fa-print"></i> Cetak Bukti
    </button>
</div>
@endsection
