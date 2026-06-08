@extends('layouts.app')

@section('title', 'Setor Sampah Baru')
@section('page-title', 'Setor Sampah')
@section('breadcrumb', 'Transaksi Setor › Input Baru')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-arrow-down" style="color: var(--success); margin-right: 8px;"></i>Form Setor Sampah</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.transaksi-setor.store') }}" method="POST" id="form-setor">
            @csrf

            {{-- Pilih Nasabah --}}
            <div class="form-row">
                <div class="form-group">
                    <label for="nasabah_id">Nasabah <span class="required">*</span></label>
                    <select id="nasabah_id" name="nasabah_id" class="form-control @error('nasabah_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Nasabah --</option>
                        @foreach($nasabahs as $nsb)
                            <option value="{{ $nsb->id }}" {{ old('nasabah_id') == $nsb->id ? 'selected' : '' }}
                                    data-saldo="{{ $nsb->saldo }}">
                                {{ $nsb->nama }} ({{ $nsb->no_anggota }}) — Saldo: Rp {{ number_format($nsb->saldo, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('nasabah_id')<p class="invalid-feedback">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="catatan">Catatan</label>
                    <input type="text" id="catatan" name="catatan" class="form-control"
                           value="{{ old('catatan') }}" placeholder="Catatan tambahan (opsional)">
                </div>
            </div>

            {{-- Items Sampah --}}
            <div style="margin-bottom: 8px;">
                <label style="font-size: 13px; font-weight: 700;">Detail Sampah <span class="required">*</span></label>
            </div>
            <div id="setor-items">
                {{-- Item Template --}}
                <div class="setor-item" data-index="0">
                    <button type="button" class="remove-item" onclick="removeItem(this)" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="form-row">
                        <div class="form-group mb-0">
                            <label>Jenis Sampah</label>
                            <select name="items[0][jenis_sampah_id]" class="form-control jenis-select" required onchange="updateHarga(this)">
                                <option value="">-- Pilih Jenis --</option>
                                @foreach($jenisSampahs as $js)
                                    <option value="{{ $js->id }}" data-harga="{{ $js->harga_per_kg }}" data-kategori="{{ $js->kategori->nama }}">
                                        {{ $js->nama }} ({{ $js->kategori->nama }}) — Rp {{ number_format($js->harga_per_kg, 0, ',', '.') }}/KG
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label>Berat (KG)</label>
                            <input type="number" name="items[0][berat_kg]" class="form-control berat-input"
                                   placeholder="0.00" step="0.01" min="0.01" required oninput="hitungSubtotal(this)">
                        </div>
                        <div class="form-group mb-0">
                            <label>Subtotal</label>
                            <input type="text" class="form-control subtotal-display" readonly placeholder="Rp 0"
                                   style="background: var(--bg); font-weight: 700; color: var(--primary);">
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-secondary" onclick="addItem()" style="margin-bottom: 20px;">
                <i class="fas fa-plus"></i> Tambah Jenis Sampah
            </button>

            {{-- Total --}}
            <div style="background: var(--primary-50); border: 2px solid var(--primary-100); border-radius: var(--radius-sm); padding: 16px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 14px; font-weight: 600;">Total Nilai Setor:</span>
                <span id="grand-total" style="font-size: 24px; font-weight: 800; color: var(--primary);">Rp 0</span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Transaksi
                </button>
                <a href="{{ route('admin.transaksi-setor.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let itemIndex = 1;

    // Jenis sampah data for cloning
    const jenisOptions = document.querySelector('.jenis-select').innerHTML;

    function addItem() {
        const container = document.getElementById('setor-items');
        const item = document.createElement('div');
        item.className = 'setor-item';
        item.dataset.index = itemIndex;
        item.innerHTML = `
            <button type="button" class="remove-item" onclick="removeItem(this)">
                <i class="fas fa-times"></i>
            </button>
            <div class="form-row">
                <div class="form-group mb-0">
                    <label>Jenis Sampah</label>
                    <select name="items[${itemIndex}][jenis_sampah_id]" class="form-control jenis-select" required onchange="updateHarga(this)">
                        ${jenisOptions}
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label>Berat (KG)</label>
                    <input type="number" name="items[${itemIndex}][berat_kg]" class="form-control berat-input"
                           placeholder="0.00" step="0.01" min="0.01" required oninput="hitungSubtotal(this)">
                </div>
                <div class="form-group mb-0">
                    <label>Subtotal</label>
                    <input type="text" class="form-control subtotal-display" readonly placeholder="Rp 0"
                           style="background: var(--bg); font-weight: 700; color: var(--primary);">
                </div>
            </div>
        `;
        container.appendChild(item);
        itemIndex++;
        updateRemoveButtons();
    }

    function removeItem(btn) {
        btn.closest('.setor-item').remove();
        updateRemoveButtons();
        updateGrandTotal();
    }

    function updateRemoveButtons() {
        const items = document.querySelectorAll('.setor-item');
        items.forEach(item => {
            const removeBtn = item.querySelector('.remove-item');
            removeBtn.style.display = items.length > 1 ? 'block' : 'none';
        });
    }

    function updateHarga(select) {
        hitungSubtotal(select.closest('.setor-item').querySelector('.berat-input'));
    }

    function hitungSubtotal(beratInput) {
        const item = beratInput.closest('.setor-item');
        const select = item.querySelector('.jenis-select');
        const subtotalDisplay = item.querySelector('.subtotal-display');
        const selectedOption = select.options[select.selectedIndex];

        const harga = parseFloat(selectedOption?.dataset?.harga || 0);
        const berat = parseFloat(beratInput.value || 0);
        const subtotal = harga * berat;

        subtotalDisplay.value = 'Rp ' + subtotal.toLocaleString('id-ID');
        subtotalDisplay.dataset.value = subtotal;

        updateGrandTotal();
    }

    function updateGrandTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal-display').forEach(el => {
            total += parseFloat(el.dataset.value || 0);
        });
        document.getElementById('grand-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }
</script>
@endpush
@endsection
