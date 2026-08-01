@extends('layouts.contentNavbarLayout')

@section('title', 'OvallHR Control Center')

@section('content')
{{--
  Halaman ini adalah launcher, bukan salinan modul. Semua tombol mengarah ke
  modul sumber agar validation, approval, audit trail, dan permission yang
  sudah ada tetap menjadi satu sumber kebenaran.
--}}
<div class="card mb-4 border-0" style="background: linear-gradient(120deg, #102b50, #1d5b93); color: #fff;">
  <div class="card-body p-4 p-lg-5">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
      <div>
        <div class="text-uppercase small opacity-75 mb-2">PT Ovall Solusindo Mandiri</div>
        <h3 class="text-white mb-2"><i class="ti ti-device-mobile-cog me-2"></i>OvallHR Control Center</h3>
        <p class="mb-0 opacity-75">Satu pusat kontrol untuk data, approval, konten, dan rilis yang tampil di APK OvallHR.</p>
      </div>
      <div class="align-self-lg-center text-lg-end">
        <div class="small opacity-75">Konteks perusahaan</div>
        <strong>{{ $company->name }}</strong>
      </div>
    </div>
  </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

{{-- Kartu ditampilkan sesuai permission: HR melihat yang relevan, Owner/Developer melihat seluruh kontrol. --}}
<div class="row g-4 mb-4">
  @can('employees.view')<div class="col-md-6 col-xl-3"><a class="card h-100 text-decoration-none" href="{{ route('employees.index') }}"><div class="card-body"><i class="ti ti-users text-primary fs-2"></i><h5 class="mt-3 mb-1">Pegawai & Akun</h5><p class="text-muted mb-0 small">Data pegawai yang dapat masuk ke OvallHR.</p></div></a></div>@endcan
  @can('attendance.view')<div class="col-md-6 col-xl-3"><a class="card h-100 text-decoration-none" href="{{ route('hr.settings.index') }}"><div class="card-body"><i class="ti ti-shield-check text-primary fs-2"></i><h5 class="mt-3 mb-1">Aturan Presensi</h5><p class="text-muted mb-0 small">GPS, selfie, radius kantor, retention, dan tanggal gaji.</p></div></a></div>@endcan
  @if(auth()->user()->is_developer)<div class="col-md-6 col-xl-3"><a class="card h-100 text-decoration-none" href="{{ route('hr.attendance-locations.index') }}"><div class="card-body"><i class="ti ti-map-pin-cog text-primary fs-2"></i><h5 class="mt-3 mb-1">Lokasi Presensi</h5><p class="text-muted mb-0 small">Titik PT OSM, site/branch, dan divisi bebas lokasi.</p></div></a></div>@endif
  @can('attendance.shift.view')<div class="col-md-6 col-xl-3"><a class="card h-100 text-decoration-none" href="{{ route('attendance.shifts.index') }}"><div class="card-body"><i class="ti ti-clock-hour-8 text-primary fs-2"></i><h5 class="mt-3 mb-1">Shift Kerja</h5><p class="text-muted mb-0 small">Jam kerja dan toleransi yang dipakai presensi APK.</p></div></a></div>@endcan
  @can('attendance.shift.assignment.view')<div class="col-md-6 col-xl-3"><a class="card h-100 text-decoration-none" href="{{ route('attendance.shift-assignments.index') }}"><div class="card-body"><i class="ti ti-calendar-user text-primary fs-2"></i><h5 class="mt-3 mb-1">Jadwal Pegawai</h5><p class="text-muted mb-0 small">Penugasan shift yang tampil pada menu Jadwal.</p></div></a></div>@endcan
  @can('attendance.view')<div class="col-md-6 col-xl-3"><a class="card h-100 text-decoration-none" href="{{ route('attendance.index') }}"><div class="card-body"><i class="ti ti-fingerprint text-primary fs-2"></i><h5 class="mt-3 mb-1">Monitoring Absensi</h5><p class="text-muted mb-0 small">Pusat operasional dan master absensi.</p></div></a></div>@endcan
  @can('attendance.view')<div class="col-md-6 col-xl-3"><a class="card h-100 text-decoration-none" href="{{ route('ovallhr.control-center.work-tracking') }}"><div class="card-body"><i class="ti ti-route-2 text-primary fs-2"></i><h5 class="mt-3 mb-1">Tracking Jam Kerja</h5><p class="text-muted mb-0 small">Peta rute presensi aktif dan lembur. Khusus HR/Owner.</p></div></a></div>@endcan
  @can('hr-request.view')<div class="col-md-6 col-xl-3"><a class="card h-100 text-decoration-none" href="{{ route('hr.requests.index') }}"><div class="card-body"><i class="ti ti-file-check text-primary fs-2"></i><h5 class="mt-3 mb-1">Pengajuan & Approval</h5><p class="text-muted mb-0 small">Cuti, sakit, izin, lembur, kasbon, piutang, dan klaim.</p></div></a></div>@endcan
  @can('payroll.view')<div class="col-md-6 col-xl-3"><a class="card h-100 text-decoration-none" href="{{ route('hr.payroll.index') }}"><div class="card-body"><i class="ti ti-receipt-2 text-primary fs-2"></i><h5 class="mt-3 mb-1">Payroll & Slip</h5><p class="text-muted mb-0 small">Approval payroll dan slip yang dapat dibaca APK.</p></div></a></div>@endcan
  @can('kpi.view')<div class="col-md-6 col-xl-3"><a class="card h-100 text-decoration-none" href="{{ route('hr.kpi.index') }}"><div class="card-body"><i class="ti ti-chart-line text-primary fs-2"></i><h5 class="mt-3 mb-1">KPI & Bonus</h5><p class="text-muted mb-0 small">Nilai, approval, dan bonus yang tampil ke pegawai.</p></div></a></div>@endcan
  @can('payroll.view')<div class="col-md-6 col-xl-3"><a class="card h-100 text-decoration-none" href="{{ route('hr.compensation.index') }}"><div class="card-body"><i class="ti ti-cash-banknote text-primary fs-2"></i><h5 class="mt-3 mb-1">Gaji & Tunjangan</h5><p class="text-muted mb-0 small">Master komponen kompensasi payroll OvallHR.</p></div></a></div>@endcan
  @can('mobile-release.view')<div class="col-md-6 col-xl-3"><a class="card h-100 text-decoration-none" href="#mobile-branding"><div class="card-body"><i class="ti ti-palette text-primary fs-2"></i><h5 class="mt-3 mb-1">Tampilan & Preview</h5><p class="text-muted mb-0 small">Nama aplikasi, warna, logo, dan preview sebelum rilis.</p></div></a></div>@endcan
  @can('mobile-release.view')<div class="col-md-6 col-xl-3"><a class="card h-100 text-decoration-none" href="{{ route('settings.mobile-releases.index') }}"><div class="card-body"><i class="ti ti-cloud-upload text-primary fs-2"></i><h5 class="mt-3 mb-1">Rilis & Fitur APK</h5><p class="text-muted mb-0 small">Versi, URL update wajib, dan feature toggle OvallHR.</p></div></a></div>@endcan
</div>

@can('mobile-release.manage')
{{-- Preview browser selalu memakai field branding yang sama dengan API mobile. --}}
<div class="card mb-4" id="mobile-branding">
  <div class="card-header d-flex flex-column flex-lg-row justify-content-between gap-2"><div><h5 class="mb-1"><i class="ti ti-palette me-2"></i>Tampilan OvallHR & Preview</h5><p class="mb-0 text-muted">Ubah branding dari AppOEMS. Perubahan tampil pada APK yang mendukung remote config setelah login/buka ulang.</p></div><a target="_blank" class="btn btn-label-primary align-self-lg-center" href="{{ route('ovallhr.control-center.preview') }}"><i class="ti ti-device-mobile me-1"></i>Buka Preview</a></div>
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data" action="{{ route('ovallhr.control-center.branding.update') }}" class="row g-3">@csrf @method('PUT')
      <div class="col-md-4"><label class="form-label">Nama aplikasi</label><input class="form-control" name="app_name" maxlength="40" required value="{{ old('app_name', $branding['app_name']) }}"></div>
      <div class="col-md-4"><label class="form-label">Nama perusahaan</label><input class="form-control" name="company_label" maxlength="120" required value="{{ old('company_label', $branding['company_label']) }}"></div>
      <div class="col-md-4"><label class="form-label">Subjudul / welcome text</label><input class="form-control" name="welcome_text" maxlength="160" value="{{ old('welcome_text', $branding['welcome_text']) }}"></div>
      <div class="col-md-3"><label class="form-label">Warna utama</label><div class="input-group"><input class="form-control form-control-color" type="color" value="{{ old('primary_color', $branding['primary_color']) }}" oninput="document.getElementById('primaryColor').value=this.value"><input id="primaryColor" class="form-control" name="primary_color" required pattern="#[0-9A-Fa-f]{6}" value="{{ old('primary_color', $branding['primary_color']) }}"></div></div>
      <div class="col-md-3"><label class="form-label">Warna navy / sekunder</label><div class="input-group"><input class="form-control form-control-color" type="color" value="{{ old('secondary_color', $branding['secondary_color']) }}" oninput="document.getElementById('secondaryColor').value=this.value"><input id="secondaryColor" class="form-control" name="secondary_color" required pattern="#[0-9A-Fa-f]{6}" value="{{ old('secondary_color', $branding['secondary_color']) }}"></div></div>
      <div class="col-md-3"><label class="form-label">Upload logo</label><input class="form-control" type="file" name="logo" accept="image/png,image/jpeg,image/webp"></div>
      <div class="col-md-3"><label class="form-label">atau URL logo</label><input class="form-control" type="url" name="logo_url" placeholder="https://..." value="{{ old('logo_url', $branding['logo_url']) }}"></div>
      <div class="col-12"><button class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan Branding Live</button><span class="small text-muted ms-2">Fitur/menu diatur dari tombol Rilis & Fitur APK; fitur baru yang belum ada di source tetap butuh APK baru.</span></div>
    </form>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header"><h5 class="mb-1"><i class="ti ti-cake me-2"></i>Ucapan Ulang Tahun Pegawai</h5><p class="mb-0 text-muted">Muncul otomatis hanya di dashboard pegawai yang berulang tahun. Bonus atau merchandise tetap keputusan HR, tidak dibuat otomatis.</p></div>
  <div class="card-body"><form method="POST" action="{{ route('ovallhr.control-center.birthday.update') }}" class="row g-3">@csrf @method('PUT')
    <div class="col-md-3 d-flex align-items-end"><label class="form-check mb-2"><input class="form-check-input" type="checkbox" name="birthday_enabled" value="1" @checked(old('birthday_enabled', $birthdaySettings['enabled']))><span class="form-check-label">Aktifkan ucapan otomatis</span></label></div>
    <div class="col-md-9"><label class="form-label">Judul</label><input class="form-control" name="birthday_title" required maxlength="120" value="{{ old('birthday_title', $birthdaySettings['title']) }}"><div class="form-text">Gunakan [[employee_name]] untuk nama pegawai.</div></div>
    <div class="col-md-8"><label class="form-label">Pesan hangat</label><textarea class="form-control" rows="3" name="birthday_message" required maxlength="500">{{ old('birthday_message', $birthdaySettings['message']) }}</textarea></div>
    <div class="col-md-4"><label class="form-label">Catatan apresiasi HR (opsional)</label><textarea class="form-control" rows="3" name="birthday_reward_note" maxlength="300">{{ old('birthday_reward_note', $birthdaySettings['reward_note']) }}</textarea></div>
    <div class="col-12"><button class="btn btn-primary">Simpan Template Ulang Tahun</button></div>
  </form></div>
</div>

<div class="card mb-4">
  <div class="card-header"><h5 class="mb-1"><i class="ti ti-speakerphone me-2"></i>Pengumuman OvallHR</h5><p class="mb-0 text-muted">Terbit langsung ke Beranda dan menu Info APK setelah karyawan melakukan refresh.</p></div>
  <div class="card-body">
    <form method="POST" action="{{ route('ovallhr.control-center.announcements.store') }}" class="row g-3">@csrf
      <div class="col-md-4"><label class="form-label">Judul</label><input class="form-control" name="title" maxlength="120" required value="{{ old('title') }}"></div>
      <div class="col-md-3"><label class="form-label">Berakhir pada (opsional)</label><input class="form-control" type="date" name="expires_at" value="{{ old('expires_at') }}"></div>
      <div class="col-md-5"><label class="form-label">Isi pengumuman</label><input class="form-control" name="message" maxlength="1000" required value="{{ old('message') }}"></div>
      <div class="col-12"><button class="btn btn-primary"><i class="ti ti-send me-1"></i>Terbitkan ke OvallHR</button></div>
    </form>
  </div>
  <div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Pengumuman</th><th>Masa berlaku</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($announcements as $announcement)<tr><td><strong>{{ $announcement->title }}</strong><div class="small text-muted">{{ $announcement->message }}</div></td><td>{{ $announcement->expires_at?->format('d M Y') ?? 'Tanpa batas' }}</td><td><span class="badge bg-label-{{ $announcement->is_active ? 'success' : 'secondary' }}">{{ $announcement->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td><form method="POST" action="{{ route('ovallhr.control-center.announcements.toggle', $announcement) }}">@csrf<button class="btn btn-sm btn-label-primary">{{ $announcement->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">Belum ada pengumuman untuk OvallHR.</td></tr>@endforelse
  </tbody></table></div>
  @if($announcements->hasPages())<div class="card-body">{{ $announcements->links() }}</div>@endif
</div>
@endcan
@endsection
