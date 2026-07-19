@php($current = $companyDocument ?? null)
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card h-100 bg-label-primary">
      <div class="card-body">
        <h5><i class="ri ri-information-line me-1"></i>Aturan penggunaan</h5>
        <p class="small">Dokumen ini adalah master. Edit dan simpan di sini, kemudian gunakan menu Preview/Cetak untuk memasukkan nomor, penerima, pegawai, dan kebutuhan surat.</p>
        <p class="small mb-0">Kontrak pegawai tidak diedit di halaman ini. Kontrak hanya dikelola dari <strong>Master Jenis Kontrak</strong>.</p>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Pakai Template Bawaan</label><select id="reference" class="form-select"><option value="">-- pilih untuk mengisi editor --</option>@foreach($references as $key => $reference)<option value="{{ $key }}">{{ $reference['name'] }}</option>@endforeach</select></div>
      <div class="col-md-3"><label class="form-label">Kode</label><input class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code', $current->code ?? '') }}" placeholder="Contoh: SOP-OPS" required>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
      <div class="col-md-3"><label class="form-label">Kategori</label><select id="category" class="form-select" name="category" required>@foreach($categories as $key => $label)<option value="{{ $key }}" @selected(old('category', $current->category ?? '') === $key)>{{ $label }}</option>@endforeach</select></div>
      <div class="col-12"><label class="form-label">Nama Dokumen</label><input id="name" class="form-control" name="name" value="{{ old('name', $current->name ?? '') }}" required></div>
      <div class="col-12"><label class="form-label">Judul / Perihal Cetak</label><input id="subject" class="form-control" name="subject" value="{{ old('subject', $current->subject ?? '') }}"></div>
      <div class="col-12"><label class="form-label">Keterangan Internal</label><textarea class="form-control" rows="2" name="description">{{ old('description', $current->description ?? '') }}</textarea></div>
      <div class="col-12"><label class="form-label">Isi Dokumen Master <span class="text-danger">*</span></label><textarea id="body" class="form-control font-monospace @error('body') is-invalid @enderror" rows="18" name="body" required>{{ old('body', $current->body ?? '') }}</textarea><div class="form-text">Boleh pakai HTML sederhana dan token: [[document_no]], [[document_date]], [[recipient_name]], [[employee_name]], [[destination]], [[scope]], [[notes]], [[company_legal_name]].</div>@error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
      <div class="col-12"><div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $current->is_active ?? true))><label class="form-check-label">Template aktif dan siap digunakan</label></div></div>
    </div>
  </div>
</div>
<div class="d-flex gap-2 justify-content-end mt-4"><a class="btn btn-outline-secondary" href="{{ route('master.company-documents.index') }}">Batal</a><button class="btn btn-primary"><i class="ri ri-save-line me-1"></i>Simpan Master Dokumen</button></div>

@section('page-script')
<script>
  const refs = @json($references);
  document.getElementById('reference').addEventListener('change', function () {
    const ref = refs[this.value]; if (!ref) return;
    if (document.getElementById('body').value && !confirm('Isi editor akan diganti template bawaan. Lanjutkan?')) return;
    document.querySelector('[name=code]').value = ref.code;
    document.getElementById('category').value = ref.category;
    document.getElementById('name').value = ref.name;
    document.getElementById('subject').value = ref.subject;
    document.getElementById('body').value = ref.body;
  });
</script>
@endsection
