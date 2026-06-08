@extends('layouts.app')

@section('title', 'Tambah Nasabah')
@section('page-title', 'Tambah Nasabah')
@section('breadcrumb', 'Nasabah › Tambah Baru')

@section('content')
<div class="card" style="max-width: 700px;">
    <div class="card-header">
        <h3><i class="fas fa-user-plus" style="color: var(--primary); margin-right: 8px;"></i>Form Tambah Nasabah</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.nasabah.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="nama">Nama Lengkap <span class="required">*</span></label>
                <input type="text" id="nama" name="nama" class="form-control @error('nama') is-invalid @enderror"
                       value="{{ old('nama') }}" placeholder="Masukkan nama lengkap" required>
                @error('nama')<p class="invalid-feedback">{{ $message }}</p>@enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nik">NIK (16 digit) <span class="required">*</span></label>
                    <input type="text" id="nik" name="nik" class="form-control @error('nik') is-invalid @enderror"
                           value="{{ old('nik') }}" placeholder="3578011234567890" maxlength="16" required>
                    @error('nik')<p class="invalid-feedback">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="no_hp">Nomor HP <span class="required">*</span></label>
                    <input type="text" id="no_hp" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror"
                           value="{{ old('no_hp') }}" placeholder="081234567890" maxlength="15" required>
                    @error('no_hp')<p class="invalid-feedback">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-group">
                <label for="alamat">Alamat Lengkap <span class="required">*</span></label>
                <textarea id="alamat" name="alamat" class="form-control @error('alamat') is-invalid @enderror"
                          placeholder="Masukkan alamat lengkap (RT/RW, Kelurahan, Kota)" required>{{ old('alamat') }}</textarea>
                @error('alamat')<p class="invalid-feedback">{{ $message }}</p>@enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="{{ route('admin.nasabah.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
