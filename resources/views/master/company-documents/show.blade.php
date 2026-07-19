@extends('layouts.contentNavbarLayout')
@section('title', 'Preview Dokumen')
@section('content')
<div class="container-fluid">
  <div class="card mb-4"><div class="card-body d-flex flex-column flex-lg-row justify-content-between gap-3"><div><h4 class="mb-1">{{ $companyDocument->name }}</h4><div class="text-muted">{{ $companyDocument->code }} · Template v{{ $companyDocument->template_version }} · {{ $companyDocument->is_active ? 'Aktif' : 'Nonaktif' }}</div></div><div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="{{ route('master.company-documents.index') }}">Kembali</a><a class="btn btn-warning" href="{{ route('master.company-documents.edit', $companyDocument) }}"><i class="ri ri-edit-line me-1"></i>Edit Master</a></div></div></div>
  <div class="row">
    <div class="col-lg-4"><div class="card"><div class="card-header"><h5 class="mb-0">Data sebelum cetak</h5></div><div class="card-body"><form method="GET" target="_blank" action="{{ route('master.company-documents.print', $companyDocument) }}">
      <div class="mb-3"><label class="form-label">Nomor dokumen</label><input class="form-control" name="document_no" placeholder="Contoh: 001/OSM/VII/2026"></div>
      <div class="mb-3"><label class="form-label">Tanggal</label><input class="form-control" name="document_date" value="{{ now()->translatedFormat('d F Y') }}"></div>
      <div class="mb-3"><label class="form-label">Perihal</label><input class="form-control" name="subject" value="{{ $companyDocument->subject }}"></div>
      <div class="mb-3"><label class="form-label">Penerima / Mitra</label><input class="form-control" name="recipient_name" placeholder="Nama penerima"></div>
      <div class="mb-3"><label class="form-label">Nama pegawai</label><input class="form-control" name="employee_name" placeholder="Untuk surat tugas/keterangan"></div>
      <div class="mb-3"><label class="form-label">Jabatan / Divisi</label><input class="form-control mb-2" name="position_name" placeholder="Jabatan"><input class="form-control" name="division_name" placeholder="Divisi"></div>
      <div class="mb-3"><label class="form-label">Tujuan / Ruang lingkup</label><input class="form-control mb-2" name="destination" placeholder="Tujuan / alamat"><textarea class="form-control" name="scope" rows="2" placeholder="Ruang lingkup"></textarea></div>
      <div class="mb-3"><label class="form-label">Catatan / Isi khusus</label><textarea class="form-control" name="notes" rows="3"></textarea></div>
      <button class="btn btn-primary w-100"><i class="ri ri-printer-line me-1"></i>Preview & Cetak</button>
      <div class="form-text mt-2">Halaman cetak akan terbuka di tab baru. Gunakan Print / Save as PDF dari browser.</div>
    </form></div></div></div>
    <div class="col-lg-8"><div class="card"><div class="card-header"><h5 class="mb-0">Catatan</h5></div><div class="card-body"><p>{{ $companyDocument->description ?: 'Tidak ada keterangan.' }}</p><div class="alert alert-warning mb-0"><i class="ri ri-error-warning-line me-1"></i>Pastikan nomor surat, data pihak, dan isi khusus diisi sebelum cetak. Template legal/HR wajib direview pihak berwenang sebelum ditandatangani.</div></div></div></div>
  </div>
</div>
@endsection
