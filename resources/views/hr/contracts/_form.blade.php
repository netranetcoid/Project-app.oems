@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Pegawai</label>
        <select name="employee_id" class="form-select" required @disabled(!empty($contract))>
            <option value="">-- Pilih Pegawai --</option>
            @foreach($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('employee_id', $contract->employee_id ?? '') == $employee->id)>
                    {{ $employee->employee_no }} - {{ $employee->name }}
                </option>
            @endforeach
        </select>
        @if(!empty($contract))<input type="hidden" name="employee_id" value="{{ $contract->employee_id }}">@endif
    </div>
    <div class="col-md-6">
        <label class="form-label">Master Kontrak</label>
        <select name="contract_type_id" class="form-select" required>
            <option value="">-- Pilih Master Kontrak --</option>
            @foreach($contractTypes as $type)
                <option value="{{ $type->id }}" @selected(old('contract_type_id', $contract->contract_type_id ?? '') == $type->id)>
                    {{ $type->name }} · versi {{ $type->template_version }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Tanggal mulai</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $contract->start_date ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Tanggal selesai</label>
        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $contract->end_date ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Lama (bulan)</label>
        <input type="number" min="1" name="duration_month" class="form-control" value="{{ old('duration_month', $contract->duration_month ?? '') }}">
    </div>
</div>

<div class="alert alert-info d-flex gap-2 mt-4 mb-3">
    <i class="ti ti-lock fs-4"></i>
    <div>
        <strong>Dokumen kontrak dikunci dari Master Kontrak.</strong><br>
        Form pegawai hanya mengatur pegawai, periode, dan administrasi. Untuk mengubah pasal/dokumen, buka <a href="{{ route('master.contract-types.index') }}" class="alert-link">Master Kontrak</a>, simpan versinya, lalu gunakan master tersebut pada kontrak baru atau perpanjangan.
    </div>
</div>

@if(!empty($contract) && filled($contract->contract_body))
    <div class="card border mb-3">
        <div class="card-body py-3 d-flex justify-content-between align-items-center">
            <div><strong>Snapshot dokumen tersimpan</strong><div class="small text-muted">Versi master {{ data_get($contract->settings, 'template_version', '-') }} dipakai saat kontrak ini dibuat.</div></div>
            <a class="btn btn-sm btn-label-primary" target="_blank" href="{{ route('hr.contracts.pdf', $contract) }}"><i class="ti ti-file-type-pdf"></i> Lihat PDF</a>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        <label class="form-label">Nomor surat internal</label>
        <input type="text" name="letter_no" class="form-control" value="{{ old('letter_no', $contract->letter_no ?? '') }}">
    </div>
    @if(!empty($contract))
        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                @foreach(['draft','waiting','approved','signed','active','expired','terminated','extended'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $contract->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
    @endif
</div>

<div class="mt-3">
    <label class="form-label">Catatan internal HR</label>
    <textarea name="notes" rows="4" class="form-control">{{ old('notes', $contract->notes ?? '') }}</textarea>
</div>

<div class="mt-4">
    <button class="btn btn-primary"><i class="ti ti-device-floppy"></i> Simpan Kontrak</button>
    <a href="{{ route('hr.contracts.index') }}" class="btn btn-label-secondary">Kembali</a>
</div>
