<?php

namespace App\Services;

use App\Models\DetailSetorSampah;
use App\Models\Nasabah;
use App\Models\TransaksiSetor;
use App\Models\TransaksiTarik;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanService
{
    /**
     * Get dashboard statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistikDashboard(): array
    {
        $today = Carbon::today();

        return [
            'total_nasabah' => Nasabah::where('is_active', true)->count(),
            'total_nasabah_semua' => Nasabah::count(),
            'total_sampah_kg' => DetailSetorSampah::sum('berat_kg'),
            'total_tabungan_aktif' => Nasabah::where('is_active', true)->sum('saldo'),
            'total_transaksi_setor' => TransaksiSetor::count(),
            'total_transaksi_tarik' => TransaksiTarik::count(),
            'transaksi_setor_hari_ini' => TransaksiSetor::whereDate('created_at', $today)->count(),
            'transaksi_tarik_hari_ini' => TransaksiTarik::whereDate('created_at', $today)->count(),
            'nilai_setor_hari_ini' => TransaksiSetor::whereDate('created_at', $today)->sum('total_nilai'),
            'nilai_tarik_hari_ini' => TransaksiTarik::whereDate('created_at', $today)->sum('jumlah'),
            'sampah_per_kategori' => $this->getSampahPerKategori(),
            'transaksi_bulanan' => $this->getTransaksiBulanan(),
        ];
    }

    /**
     * Get total waste collected grouped by category.
     *
     * @return Collection
     */
    public function getSampahPerKategori(): Collection
    {
        return DB::table('detail_setor_sampahs')
            ->join('jenis_sampahs', 'detail_setor_sampahs.jenis_sampah_id', '=', 'jenis_sampahs.id')
            ->join('kategori_sampahs', 'jenis_sampahs.kategori_id', '=', 'kategori_sampahs.id')
            ->select(
                'kategori_sampahs.nama as kategori',
                DB::raw('SUM(detail_setor_sampahs.berat_kg) as total_berat'),
                DB::raw('SUM(detail_setor_sampahs.subtotal) as total_nilai')
            )
            ->groupBy('kategori_sampahs.id', 'kategori_sampahs.nama')
            ->get();
    }

    /**
     * Get monthly transaction summary for the current year.
     *
     * @return Collection
     */
    public function getTransaksiBulanan(): Collection
    {
        $year = now()->year;

        return TransaksiSetor::selectRaw('MONTH(created_at) as bulan, COUNT(*) as jumlah_transaksi, SUM(total_nilai) as total_nilai')
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get();
    }

    /**
     * Get transaction report filtered by date range and type.
     *
     * @param  Carbon       $dari
     * @param  Carbon       $sampai
     * @param  string|null  $tipe  'setor', 'tarik', or null for both
     * @return array<string, Collection>
     */
    public function getLaporanTransaksi(Carbon $dari, Carbon $sampai, ?string $tipe = null): array
    {
        $result = [];

        if ($tipe === null || $tipe === 'setor') {
            $result['setor'] = TransaksiSetor::with(['nasabah', 'petugas', 'detailSetorSampahs.jenisSampah'])
                ->whereBetween('created_at', [$dari->startOfDay(), $sampai->endOfDay()])
                ->orderByDesc('created_at')
                ->get();
        }

        if ($tipe === null || $tipe === 'tarik') {
            $result['tarik'] = TransaksiTarik::with(['nasabah', 'petugas'])
                ->whereBetween('created_at', [$dari->startOfDay(), $sampai->endOfDay()])
                ->orderByDesc('created_at')
                ->get();
        }

        return $result;
    }

    /**
     * Get summary report for a specific nasabah.
     *
     * @param  int  $nasabahId
     * @return array<string, mixed>
     */
    public function getLaporanPerNasabah(int $nasabahId): array
    {
        $nasabah = Nasabah::findOrFail($nasabahId);

        return [
            'nasabah' => $nasabah,
            'total_setor' => $nasabah->transaksiSetors()->sum('total_nilai'),
            'total_tarik' => $nasabah->transaksiTariks()->sum('jumlah'),
            'jumlah_transaksi_setor' => $nasabah->transaksiSetors()->count(),
            'jumlah_transaksi_tarik' => $nasabah->transaksiTariks()->count(),
            'total_berat_kg' => DetailSetorSampah::whereIn(
                'transaksi_setor_id',
                $nasabah->transaksiSetors()->pluck('id')
            )->sum('berat_kg'),
            'transaksi_terakhir' => $nasabah->transaksiSetors()
                ->with('detailSetorSampahs.jenisSampah')
                ->latest()
                ->take(10)
                ->get(),
        ];
    }
}
