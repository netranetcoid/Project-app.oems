@csrf

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Pegawai</label>
        <select name="employee_id" class="form-select" required>
            <option value="">-- Pilih Pegawai --</option>
            @foreach($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('employee_id', $contract->employee_id ?? '') == $employee->id)>
                    {{ $employee->employee_no }} - {{ $employee->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Jenis Kontrak</label>
        <select name="contract_type_id" id="contract_type_id" class="form-select" required>
            <option value="">-- Pilih Jenis --</option>
            @foreach($contractTypes as $type)
                <option value="{{ $type->id }}" data-template='@json($type->template_body)' data-version="{{ $type->template_version }}" @selected(old('contract_type_id', $contract->contract_type_id ?? '') == $type->id)>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Tanggal Mulai</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $contract->start_date ?? '') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Tanggal Selesai</label>
        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $contract->end_date ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Lama (Bulan)</label>
        <input type="number" name="duration_month" class="form-control" value="{{ old('duration_month', $contract->duration_month ?? '') }}">
    </div>
</div>

{{--
  Dokumen kontrak adalah snapshot per pegawai. Kolom kiri hanya referensi
  template master; kolom kanan yang disimpan dan nanti dipakai cetak/PDF.
--}}
<div class="row mt-2">
    <div class="col-lg-5 mb-3">
        <div class="card h-100 border">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">Acuan template master</h6>
                    <small class="text-muted">Referensi awal, tidak ikut berubah saat dokumen pegawai diedit.</small>
                </div>
                <span id="template-version" class="badge bg-label-primary">Pilih jenis</span>
            </div>
            <div class="card-body">
                <pre id="contract-template-reference" class="mb-0 text-wrap small" style="white-space: pre-wrap; min-height: 420px;">Pilih jenis kontrak untuk melihat naskah acuan.</pre>
            </div>
        </div>
    </div>
    <div class="col-lg-7 mb-3">
        <label for="contract_body" class="form-label fw-semibold">Dokumen kontrak yang akan disimpan</label>
        <div class="alert alert-warning py-2 small">
            Edit poin/pasal langsung di sini. Isi ini disimpan khusus untuk kontrak pegawai dan menjadi dasar cetak/PDF.
        </div>
        <textarea id="contract_body" name="contract_body" rows="24" class="form-control font-monospace @error('contract_body') is-invalid @enderror" placeholder="Pilih jenis kontrak lalu gunakan template master sebagai awal dokumen...">{{ old('contract_body', $contract->contract_body ?? '') }}</textarea>
        @error('contract_body')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="d-flex gap-2 mt-2">
            <button id="copy-template-to-document" type="button" class="btn btn-sm btn-label-primary"><i class="ti ti-file-import"></i> Gunakan template sebagai awal</button>
            <small class="text-muted align-self-center">Tidak menimpa isi tanpa konfirmasi.</small>
        </div>
    </div>
</div>

<div class="row mt-1">
    <div class="col-md-12">
        <label class="form-label">Catatan internal HR</label>
        <textarea name="notes" rows="4" class="form-control">{{ old('notes', $contract->notes ?? '') }}</textarea>
    </div>
</div>

<div class="mt-4">
    <button class="btn btn-primary"><i class="ti ti-device-floppy"></i> Simpan Kontrak</button>
    <a href="{{ route('hr.contracts.index') }}" class="btn btn-label-secondary">Kembali</a>
</div>

@push('page-script')
<script>
  // Template master ditampilkan sebagai pembanding; teks yang disimpan tetap
  // berada di textarea "contract_body" milik kontrak pegawai.
  document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('contract_type_id');
    const reference = document.getElementById('contract-template-reference');
    const version = document.getElementById('template-version');
    const editor = document.getElementById('contract_body');
    const copyButton = document.getElementById('copy-template-to-document');
    function selectedTemplate() {
      const option = typeSelect.options[typeSelect.selectedIndex];
      if (!option || !option.value) return '';
      try { return JSON.parse(option.dataset.template || '""'); } catch (_) { return ''; }
    }
    function renderReference() {
      const option = typeSelect.options[typeSelect.selectedIndex];
      reference.textContent = selectedTemplate() || 'Template belum diisi pada Master Jenis Kontrak.';
      version.textContent = option && option.value ? ('Versi ' + (option.dataset.version || '1')) : 'Pilih jenis';
    }
    typeSelect.addEventListener('change', renderReference);
    copyButton.addEventListener('click', function () {
      const template = selectedTemplate();
      if (!template) return alert('Template master untuk jenis kontrak ini masih kosong.');
      if (editor.value.trim() && !confirm('Ganti isi dokumen saat ini dengan template master? Perubahan yang belum disimpan akan hilang.')) return;
      editor.value = template;
      editor.focus();
    });
    renderReference();
  });
</script>
@endpush
