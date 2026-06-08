@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Ringkasan Statistik Bank Sampah')

@section('content')
{{-- Stat Cards Row 1 --}}
<div class="stat-grid">
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <p class="stat-label">Total Nasabah Aktif</p>
            <p class="stat-value">{{ number_format($statistik['total_nasabah']) }}</p>
            <p class="stat-desc">dari {{ $statistik['total_nasabah_semua'] }} total nasabah</p>
        </div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-weight-hanging"></i></div>
        <div class="stat-info">
            <p class="stat-label">Total Sampah Terkumpul</p>
            <p class="stat-value">{{ number_format($statistik['total_sampah_kg'], 1) }} <span style="font-size:14px;">KG</span></p>
        </div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-icon"><i class="fas fa-wallet"></i></div>
        <div class="stat-info">
            <p class="stat-label">Total Tabungan Aktif</p>
            <p class="stat-value">Rp {{ number_format($statistik['total_tabungan_aktif'], 0, ',', '.') }}</p>
        </div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon"><i class="fas fa-exchange-alt"></i></div>
        <div class="stat-info">
            <p class="stat-label">Total Transaksi</p>
            <p class="stat-value">{{ number_format($statistik['total_transaksi_setor'] + $statistik['total_transaksi_tarik']) }}</p>
            <p class="stat-desc">{{ $statistik['total_transaksi_setor'] }} setor, {{ $statistik['total_transaksi_tarik'] }} tarik</p>
        </div>
    </div>
</div>

{{-- Today Stats --}}
<div class="stat-grid" style="margin-bottom: 24px;">
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
        <div class="stat-info">
            <p class="stat-label">Setor Hari Ini</p>
            <p class="stat-value">{{ $statistik['transaksi_setor_hari_ini'] }}</p>
            <p class="stat-desc">Rp {{ number_format($statistik['nilai_setor_hari_ini'], 0, ',', '.') }}</p>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
        <div class="stat-info">
            <p class="stat-label">Tarik Hari Ini</p>
            <p class="stat-value">{{ $statistik['transaksi_tarik_hari_ini'] }}</p>
            <p class="stat-desc">Rp {{ number_format($statistik['nilai_tarik_hari_ini'], 0, ',', '.') }}</p>
        </div>
    </div>
</div>

{{-- Content Grid --}}
<div class="grid-2">
    {{-- Sampah per Kategori --}}
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-bar" style="color: var(--primary); margin-right: 8px;"></i>Sampah per Kategori</h3>
        </div>
        <div class="card-body">
            @forelse($statistik['sampah_per_kategori'] as $item)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border-light);">
                    <div>
                        <p style="font-weight: 600; font-size: 13px;">{{ $item->kategori }}</p>
                        <p class="text-sm text-muted">Rp {{ number_format($item->total_nilai, 0, ',', '.') }}</p>
                    </div>
                    <span class="badge badge-info">{{ number_format($item->total_berat, 1) }} KG</span>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Belum ada data sampah</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Transaksi Bulanan --}}
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-calendar-alt" style="color: var(--accent); margin-right: 8px;"></i>Transaksi Bulanan {{ now()->year }}</h3>
        </div>
        <div class="card-body">
            @php
                $namaBulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            @endphp
            @forelse($statistik['transaksi_bulanan'] as $item)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border-light);">
                    <div>
                        <p style="font-weight: 600; font-size: 13px;">{{ $namaBulan[$item->bulan] ?? $item->bulan }}</p>
                        <p class="text-sm text-muted">{{ $item->jumlah_transaksi }} transaksi</p>
                    </div>
                    <span style="font-weight: 700; color: var(--primary);">Rp {{ number_format($item->total_nilai, 0, ',', '.') }}</span>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>Belum ada transaksi tahun ini</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
