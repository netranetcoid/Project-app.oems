@csrf

@php
    $selectedKey = old('template_key', $contractType->template_key ?? '');
@endphp

<div class="alert alert-info d-flex align-items-start gap-2 mb-4" role="alert">
    <i class="ti ti-file-pencil fs-4"></i>
    <div>
        <strong>Master Kontrak adalah satu-satunya editor naskah.</strong><br>
        Isi di bawah memakai struktur lengkap 15 pasal dari format <code>hr/contracts/print</code>. Edit seluruh pasal di sini; kontrak baru menerima snapshot naskah ini dan kontrak lama tidak berubah.
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label for="template_key" class="form-label">Kategori master kontrak</label>
        <select id="template_key" name="template_key" class="form-select @error('template_key') is-invalid @enderror" required>
            <option value="">-- Pilih kategori --</option>
            @foreach($templateReferences as $key => $reference)
                <option value="{{ $key }}" @selected($selectedKey === $key)>{{ $reference['label'] }}</option>
            @endforeach
        </select>
        <div class="form-text">Kategori mengatur alur sistem. Naskah dan semua pasalnya tetap editable.</div>
        @error('template_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="code" class="form-label">Kode master</label>
        <input id="code" type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $contractType->code ?? '') }}" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="default_duration_month" class="form-label">Durasi default (bulan)</label>
        <input id="default_duration_month" type="number" min="1" max="60" name="default_duration_month" class="form-control @error('default_duration_month') is-invalid @enderror" value="{{ old('default_duration_month', $contractType->default_duration_month ?? '') }}">
        @error('default_duration_month')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label for="name" class="form-label">Nama master kontrak</label>
        <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $contractType->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2">
        <label for="color" class="form-label">Warna badge</label>
        <select id="color" name="color" class="form-select">
            @foreach(['primary','success','warning','danger','info','secondary'] as $color)
                <option value="{{ $color }}" @selected(old('color', $contractType->color ?? 'primary') === $color)>{{ ucfirst($color) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label for="is_active" class="form-label">Status</label>
        <select id="is_active" name="is_active" class="form-select">
            <option value="1" @selected((string) old('is_active', $contractType->is_active ?? 1) === '1')>Aktif</option>
            <option value="0" @selected((string) old('is_active', $contractType->is_active ?? 1) === '0')>Nonaktif</option>
        </select>
    </div>
</div>

<div class="card border mt-4">
    <div class="card-header border-0 pb-0">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start">
            <div>
                <h5 class="mb-1"><i class="ti ti-file-description me-2"></i>Naskah Master Kontrak — 15 Pasal</h5>
                <p class="small text-muted mb-0">Sumber isi adalah struktur kontrak HR yang dipakai saat cetak/PDF. Tidak ada panel acuan atau preview terpisah.</p>
            </div>
            @if(!empty($contractType))
                <span class="badge bg-label-primary">Versi master: {{ $contractType->template_version }}</span>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-warning small py-2">
            Token yang tersedia: <code>[[employee_name]]</code>, <code>[[employee_no]]</code>, <code>[[position_name]]</code>, <code>[[division_name]]</code>, <code>[[branch_name]]</code>, <code>[[start_date]]</code>, <code>[[end_date]]</code>, <code>[[probation_end_date]]</code>, <code>[[duration_month]]</code>, <code>[[basic_salary]]</code>.
        </div>
        <div class="mb-3">
            <label for="legal_basis" class="form-label">Dasar / catatan review HR/legal</label>
            <textarea id="legal_basis" name="legal_basis" rows="3" class="form-control @error('legal_basis') is-invalid @enderror" placeholder="Catatan legal internal dan tanggal review.">{{ old('legal_basis', $contractType->legal_basis ?? '') }}</textarea>
            @error('legal_basis')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <label for="template_body" class="form-label fw-semibold">Isi lengkap pasal kontrak (editable)</label>
        <textarea id="template_body" name="template_body" rows="48" class="form-control font-monospace @error('template_body') is-invalid @enderror" placeholder="Naskah 15 pasal akan dibuat saat Master Kontrak baru disimpan.">{{ old('template_body', $contractType->template_body ?? '') }}</textarea>
        @error('template_body')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="alert alert-success small mt-3 mb-0">
            <strong>Aturan aman:</strong> perubahan ini hanya berlaku pada kontrak yang dibuat atau diperpanjang setelah master disimpan. Dokumen kontrak yang sudah diterbitkan tetap memakai versi snapshot sebelumnya.
        </div>
    </div>
</div>

<div class="mt-4">
    <label for="description" class="form-label">Deskripsi internal</label>
    <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $contractType->description ?? '') }}</textarea>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary"><i class="ti ti-device-floppy"></i> Simpan Master Kontrak</button>
    <a href="{{ route('master.contract-types.index') }}" class="btn btn-label-secondary">Kembali</a>
</div>
