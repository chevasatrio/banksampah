@extends('layouts.app')

@section('title', 'Dashboard Nasabah')
@section('page-title', 'Dashboard Saya')
@section('breadcrumb', 'Selamat datang, {{ $nasabah->nama }}')

@section('content')
{{-- Balance Card --}}
<div class="balance-card">
    <p class="balance-label">Saldo Tabungan</p>
    <p class="balance-amount">Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}</p>
    <p class="balance-name"><i class="fas fa-user"></i> {{ $nasabah->nama }} — {{ $nasabah->no_anggota }}</p>
</div>

{{-- Summary Stats --}}
<div class="stat-grid">
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
        <div class="stat-info">
            <p class="stat-label">Total Setor</p>
            <p class="stat-value">Rp {{ number_format($totalSetor, 0, ',', '.') }}</p>
            <p class="stat-desc">{{ $nasabah->transaksiSetors->count() }} transaksi</p>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
        <div class="stat-info">
            <p class="stat-label">Total Tarik</p>
            <p class="stat-value">Rp {{ number_format($totalTarik, 0, ',', '.') }}</p>
            <p class="stat-desc">{{ $nasabah->transaksiTariks->count() }} transaksi</p>
        </div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-id-card"></i></div>
        <div class="stat-info">
            <p class="stat-label">Info Nasabah</p>
            <p class="stat-value" style="font-size: 16px;">{{ $nasabah->no_anggota }}</p>
            <p class="stat-desc">Sejak {{ $nasabah->created_at->translatedFormat('d M Y') }}</p>
        </div>
    </div>
</div>

{{-- Riwayat Transaksi --}}
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history" style="color: var(--accent); margin-right: 8px;"></i>Riwayat Transaksi Terakhir</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode</th>
                        <th>Tipe</th>
                        <th>Detail</th>
                        <th class="text-right">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $allTransaksi = collect();
                        foreach($nasabah->transaksiSetors as $s) {
                            $allTransaksi->push((object)[
                                'tanggal' => $s->created_at,
                                'kode' => $s->kode_transaksi,
                                'tipe' => 'Setor',
                                'detail' => $s->detailSetorSampahs->map(fn($d) => $d->jenisSampah->nama . ' (' . number_format($d->berat_kg, 1) . 'kg)')->join(', '),
                                'nilai' => $s->total_nilai,
                                'is_setor' => true,
                            ]);
                        }
                        foreach($nasabah->transaksiTariks as $t) {
                            $allTransaksi->push((object)[
                                'tanggal' => $t->created_at,
                                'kode' => $t->kode_transaksi,
                                'tipe' => 'Tarik',
                                'detail' => $t->keterangan ?? '-',
                                'nilai' => $t->jumlah,
                                'is_setor' => false,
                            ]);
                        }
                        $allTransaksi = $allTransaksi->sortByDesc('tanggal');
                    @endphp
                    @forelse($allTransaksi as $trx)
                        <tr>
                            <td>
                                <p class="text-bold" style="font-size: 13px;">{{ $trx->tanggal->format('d/m/Y') }}</p>
                                <p class="text-muted text-sm">{{ $trx->tanggal->format('H:i') }}</p>
                            </td>
                            <td><span style="font-family: monospace; font-size: 12px; font-weight: 600;">{{ $trx->kode }}</span></td>
                            <td>
                                @if($trx->is_setor)
                                    <span class="badge badge-success"><i class="fas fa-arrow-down"></i> Setor</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-arrow-up"></i> Tarik</span>
                                @endif
                            </td>
                            <td class="text-muted text-sm">{{ \Illuminate\Support\Str::limit($trx->detail, 50) }}</td>
                            <td class="text-right text-bold {{ $trx->is_setor ? 'text-success' : 'text-danger' }}" style="font-size: 14px;">
                                {{ $trx->is_setor ? '+' : '-' }}Rp {{ number_format($trx->nilai, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fas fa-receipt"></i>
                                    <p>Belum ada riwayat transaksi</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
