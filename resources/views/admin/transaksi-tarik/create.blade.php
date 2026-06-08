@extends('layouts.app')

@section('title', 'Tarik Saldo')
@section('page-title', 'Tarik Saldo')
@section('breadcrumb', 'Transaksi Tarik › Input Baru')

@section('content')
<div class="card" style="max-width: 600px;">
    <div class="card-header">
        <h3><i class="fas fa-arrow-up" style="color: var(--danger); margin-right: 8px;"></i>Form Penarikan Saldo</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.transaksi-tarik.store') }}" method="POST" id="form-tarik">
            @csrf

            <div class="form-group">
                <label for="nasabah_id">Nasabah <span class="required">*</span></label>
                <select id="nasabah_id" name="nasabah_id" class="form-control @error('nasabah_id') is-invalid @enderror" required onchange="updateSaldoInfo()">
                    <option value="">-- Pilih Nasabah --</option>
                    @foreach($nasabahs as $nsb)
                        <option value="{{ $nsb->id }}" data-saldo="{{ $nsb->saldo }}" {{ old('nasabah_id') == $nsb->id ? 'selected' : '' }}>
                            {{ $nsb->nama }} ({{ $nsb->no_anggota }})
                        </option>
                    @endforeach
                </select>
                @error('nasabah_id')<p class="invalid-feedback">{{ $message }}</p>@enderror
            </div>

            {{-- Saldo Info --}}
            <div id="saldo-info" style="display: none; background: var(--primary-50); border: 1px solid var(--primary-100); border-radius: var(--radius-sm); padding: 14px 18px; margin-bottom: 18px;">
                <div class="d-flex justify-between align-center">
                    <span style="font-size: 13px; color: var(--text-secondary);">Saldo Tersedia:</span>
                    <span id="saldo-display" style="font-size: 20px; font-weight: 800; color: var(--primary);">Rp 0</span>
                </div>
            </div>

            <div class="form-group">
                <label for="jumlah">Jumlah Penarikan (Rp) <span class="required">*</span></label>
                <input type="number" id="jumlah" name="jumlah" class="form-control @error('jumlah') is-invalid @enderror"
                       value="{{ old('jumlah') }}" placeholder="Masukkan jumlah penarikan" min="1" step="1" required>
                @error('jumlah')<p class="invalid-feedback">{{ $message }}</p>@enderror
                <p id="saldo-warning" style="display: none; margin-top: 6px; font-size: 12px; color: var(--danger);">
                    <i class="fas fa-exclamation-triangle"></i> Jumlah melebihi saldo tersedia!
                </p>
            </div>

            <div class="form-group">
                <label for="keterangan">Keterangan</label>
                <textarea id="keterangan" name="keterangan" class="form-control"
                          placeholder="Keterangan penarikan (opsional)">{{ old('keterangan') }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="btn-submit">
                    <i class="fas fa-save"></i> Proses Penarikan
                </button>
                <a href="{{ route('admin.transaksi-tarik.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let maxSaldo = 0;

    function updateSaldoInfo() {
        const select = document.getElementById('nasabah_id');
        const selectedOption = select.options[select.selectedIndex];
        const saldoInfo = document.getElementById('saldo-info');
        const saldoDisplay = document.getElementById('saldo-display');

        if (selectedOption.value) {
            maxSaldo = parseFloat(selectedOption.dataset.saldo);
            saldoDisplay.textContent = 'Rp ' + maxSaldo.toLocaleString('id-ID');
            saldoInfo.style.display = 'block';
        } else {
            saldoInfo.style.display = 'none';
            maxSaldo = 0;
        }
        validateJumlah();
    }

    document.getElementById('jumlah').addEventListener('input', validateJumlah);

    function validateJumlah() {
        const jumlah = parseFloat(document.getElementById('jumlah').value || 0);
        const warning = document.getElementById('saldo-warning');
        const btn = document.getElementById('btn-submit');

        if (jumlah > maxSaldo && maxSaldo > 0) {
            warning.style.display = 'block';
            btn.disabled = true;
            btn.style.opacity = '0.5';
        } else {
            warning.style.display = 'none';
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    }

    // Init on page load if old value exists
    updateSaldoInfo();
</script>
@endpush
@endsection
