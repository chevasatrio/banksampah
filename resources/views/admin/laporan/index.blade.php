@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan Transaksi')
@section('breadcrumb', 'Laporan transaksi bank sampah')

@section('content')
{{-- Filter Card --}}
<div class="card mb-3">
    <div class="card-body">
        <form action="{{ route('admin.laporan.index') }}" method="GET" class="d-flex align-center gap-2" style="flex-wrap: wrap;">
            <div class="form-group mb-0" style="min-width: 160px;">
                <label for="dari" class="text-sm">Dari Tanggal</label>
                <input type="date" id="dari" name="dari" class="form-control"
                       value="{{ $dari->format('Y-m-d') }}">
            </div>
            <div class="form-group mb-0" style="min-width: 160px;">
                <label for="sampai" class="text-sm">Sampai Tanggal</label>
                <input type="date" id="sampai" name="sampai" class="form-control"
                       value="{{ $sampai->format('Y-m-d') }}">
            </div>
            <div class="form-group mb-0" style="min-width: 160px;">
                <label for="tipe" class="text-sm">Tipe Transaksi</label>
                <select id="tipe" name="tipe" class="form-control">
                    <option value="">Semua</option>
                    <option value="setor" {{ $tipe === 'setor' ? 'selected' : '' }}>Setor Sampah</option>
                    <option value="tarik" {{ $tipe === 'tarik' ? 'selected' : '' }}>Tarik Saldo</option>
                </select>
            </div>
            <div class="form-group mb-0" style="padding-top: 20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Summary Stats --}}
@php
    $totalSetor = isset($laporan['setor']) ? $laporan['setor']->sum('total_nilai') : 0;
    $totalTarik = isset($laporan['tarik']) ? $laporan['tarik']->sum('jumlah') : 0;
    $countSetor = isset($laporan['setor']) ? $laporan['setor']->count() : 0;
    $countTarik = isset($laporan['tarik']) ? $laporan['tarik']->count() : 0;
@endphp
<div class="stat-grid">
    @if(!$tipe || $tipe === 'setor')
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
        <div class="stat-info">
            <p class="stat-label">Total Setor</p>
            <p class="stat-value">{{ $countSetor }}</p>
            <p class="stat-desc">Rp {{ number_format($totalSetor, 0, ',', '.') }}</p>
        </div>
    </div>
    @endif
    @if(!$tipe || $tipe === 'tarik')
    <div class="stat-card red">
        <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
        <div class="stat-info">
            <p class="stat-label">Total Tarik</p>
            <p class="stat-value">{{ $countTarik }}</p>
            <p class="stat-desc">Rp {{ number_format($totalTarik, 0, ',', '.') }}</p>
        </div>
    </div>
    @endif
    @if(!$tipe)
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-balance-scale"></i></div>
        <div class="stat-info">
            <p class="stat-label">Selisih</p>
            <p class="stat-value {{ $totalSetor - $totalTarik >= 0 ? 'text-success' : 'text-danger' }}">
                Rp {{ number_format(abs($totalSetor - $totalTarik), 0, ',', '.') }}
            </p>
            <p class="stat-desc">{{ $totalSetor >= $totalTarik ? 'Surplus' : 'Defisit' }}</p>
        </div>
    </div>
    @endif
</div>

{{-- Tabel Setor --}}
@if(isset($laporan['setor']))
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fas fa-arrow-down" style="color: var(--success); margin-right: 8px;"></i>Transaksi Setor</h3>
        <span class="badge badge-success">{{ $laporan['setor']->count() }} transaksi</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode</th>
                        <th>Nasabah</th>
                        <th>Petugas</th>
                        <th>Detail Sampah</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporan['setor'] as $trx)
                        <tr>
                            <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                            <td><span style="font-family: monospace; font-size: 12px; font-weight: 600;">{{ $trx->kode_transaksi }}</span></td>
                            <td>
                                <p class="text-bold">{{ $trx->nasabah->nama }}</p>
                                <p class="text-muted text-sm">{{ $trx->nasabah->no_anggota }}</p>
                            </td>
                            <td class="text-muted">{{ $trx->petugas->name }}</td>
                            <td class="text-sm text-muted">
                                @foreach($trx->detailSetorSampahs as $d)
                                    {{ $d->jenisSampah->nama }} ({{ number_format($d->berat_kg, 1) }}kg){{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </td>
                            <td class="text-right text-bold text-success">
                                Rp {{ number_format($trx->total_nilai, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state"><p>Tidak ada transaksi setor pada periode ini</p></div></td></tr>
                    @endforelse
                </tbody>
                @if($laporan['setor']->isNotEmpty())
                <tfoot>
                    <tr style="background: #ecfdf5;">
                        <td colspan="5" class="text-right text-bold">Total Setor:</td>
                        <td class="text-right text-bold text-success" style="font-size: 15px;">
                            Rp {{ number_format($totalSetor, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endif

{{-- Tabel Tarik --}}
@if(isset($laporan['tarik']))
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fas fa-arrow-up" style="color: var(--danger); margin-right: 8px;"></i>Transaksi Tarik</h3>
        <span class="badge badge-danger">{{ $laporan['tarik']->count() }} transaksi</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode</th>
                        <th>Nasabah</th>
                        <th>Petugas</th>
                        <th>Keterangan</th>
                        <th class="text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporan['tarik'] as $trx)
                        <tr>
                            <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                            <td><span style="font-family: monospace; font-size: 12px; font-weight: 600;">{{ $trx->kode_transaksi }}</span></td>
                            <td>
                                <p class="text-bold">{{ $trx->nasabah->nama }}</p>
                                <p class="text-muted text-sm">{{ $trx->nasabah->no_anggota }}</p>
                            </td>
                            <td class="text-muted">{{ $trx->petugas->name }}</td>
                            <td class="text-muted text-sm">{{ $trx->keterangan ?? '-' }}</td>
                            <td class="text-right text-bold text-danger">
                                -Rp {{ number_format($trx->jumlah, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state"><p>Tidak ada transaksi tarik pada periode ini</p></div></td></tr>
                    @endforelse
                </tbody>
                @if($laporan['tarik']->isNotEmpty())
                <tfoot>
                    <tr style="background: #fef2f2;">
                        <td colspan="5" class="text-right text-bold">Total Tarik:</td>
                        <td class="text-right text-bold text-danger" style="font-size: 15px;">
                            Rp {{ number_format($totalTarik, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endif

{{-- Print Button --}}
<div class="d-flex gap-2">
    <button onclick="window.print()" class="btn btn-info">
        <i class="fas fa-print"></i> Cetak Laporan
    </button>
</div>
@endsection
