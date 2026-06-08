@extends('layouts.app')

@section('title', 'Detail Nasabah')
@section('page-title', 'Detail Nasabah')
@section('breadcrumb', 'Nasabah › ' . $nasabah->nama)

@section('content')
{{-- Profile Card --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 16px;">
            <div class="d-flex align-center gap-2">
                <div class="user-avatar" style="width:56px; height:56px; font-size:20px;">
                    {{ strtoupper(substr($nasabah->nama, 0, 1)) }}
                </div>
                <div>
                    <h3 style="font-size: 18px; margin-bottom: 4px;">{{ $nasabah->nama }}</h3>
                    <p class="text-muted text-sm">{{ $nasabah->no_anggota }} &bull; NIK: {{ $nasabah->nik }}</p>
                </div>
            </div>
            <div class="d-flex align-center gap-2">
                @if($nasabah->is_active)
                    <span class="badge badge-success" style="padding: 6px 14px; font-size: 12px;">✓ Aktif</span>
                @else
                    <span class="badge badge-danger" style="padding: 6px 14px; font-size: 12px;">✗ Nonaktif</span>
                @endif
                <a href="{{ route('admin.nasabah.edit', $nasabah) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Info Grid --}}
<div class="grid-2 mb-3">
    <div class="card">
        <div class="card-header"><h3>Informasi Pribadi</h3></div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <label>No. Anggota</label>
                    <p style="color: var(--primary);">{{ $nasabah->no_anggota }}</p>
                </div>
                <div class="detail-item">
                    <label>NIK</label>
                    <p>{{ $nasabah->nik }}</p>
                </div>
                <div class="detail-item">
                    <label>No. HP</label>
                    <p>{{ $nasabah->no_hp }}</p>
                </div>
                <div class="detail-item">
                    <label>Terdaftar</label>
                    <p>{{ $nasabah->created_at->translatedFormat('d M Y') }}</p>
                </div>
            </div>
            <div class="detail-item" style="margin-top: 14px;">
                <label>Alamat</label>
                <p>{{ $nasabah->alamat }}</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Informasi Tabungan</h3></div>
        <div class="card-body">
            <div style="text-align: center; padding: 16px 0;">
                <p class="text-muted text-sm" style="margin-bottom: 4px;">Saldo Tabungan</p>
                <p style="font-size: 32px; font-weight: 800; color: var(--primary);">
                    Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}
                </p>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px;">
                <div style="text-align: center; padding: 12px; background: #ecfdf5; border-radius: 8px;">
                    <p class="text-sm text-muted">Total Setor</p>
                    <p class="text-bold">{{ $nasabah->transaksiSetors->count() }}x</p>
                </div>
                <div style="text-align: center; padding: 12px; background: #fef2f2; border-radius: 8px;">
                    <p class="text-sm text-muted">Total Tarik</p>
                    <p class="text-bold">{{ $nasabah->transaksiTariks->count() }}x</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Transactions --}}
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
                                'detail' => $s->detailSetorSampahs->map(fn($d) => $d->jenisSampah->nama)->join(', '),
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
                            <td>{{ $trx->tanggal->format('d/m/Y H:i') }}</td>
                            <td><span class="text-bold" style="font-size: 12px;">{{ $trx->kode }}</span></td>
                            <td>
                                @if($trx->is_setor)
                                    <span class="badge badge-success">↓ Setor</span>
                                @else
                                    <span class="badge badge-danger">↑ Tarik</span>
                                @endif
                            </td>
                            <td class="text-muted text-sm">{{ \Illuminate\Support\Str::limit($trx->detail, 40) }}</td>
                            <td class="text-right text-bold {{ $trx->is_setor ? 'text-success' : 'text-danger' }}">
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

<div style="margin-top: 16px;">
    <a href="{{ route('admin.nasabah.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
    </a>
</div>
@endsection
