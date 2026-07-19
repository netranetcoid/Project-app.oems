@extends('layouts.contentNavbarLayout')
@section('title', 'Aturan HR & Absensi')
@section('content')
@php($settings = is_array($company->settings) ? $company->settings : [])
<div class="mb-4"><h4 class="mb-1">Aturan HR, Absensi & Payroll</h4><p class="text-muted mb-0">Master kebijakan HR. Titik kantor/site/divisi dikelola terpusat oleh Developer melalui Pusat Lokasi Presensi.</p></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('hr.settings.update') }}">@csrf @method('PUT')
<div class="row">
  <div class="col-xl-7 mb-4"><div class="card h-100"><div class="card-header"><h5 class="mb-0">Absensi & Privasi</h5></div><div class="card-body row g-3">
    <div class="col-md-6"><label class="form-label">Radius fallback (meter)</label><input class="form-control" type="number" min="1" max="5000" name="attendance_radius_meter" required value="{{ old('attendance_radius_meter',$company->attendance_radius_meter ?? 150) }}"><div class="form-text">Dipakai hanya bila scope belum memiliki policy Developer.</div></div>
    <div class="col-md-6"><label class="form-label">Retention bukti (hari)</label><input class="form-control" type="number" min="1" max="3650" name="attendance_retention_days" required value="{{ old('attendance_retention_days',$settings['attendance_retention_days'] ?? 60) }}"></div>
    <div class="col-12"><div class="alert alert-primary mb-0 d-flex justify-content-between align-items-center gap-2"><span><i class="ti ti-map-pin-cog me-1"></i>Titik PT OSM, Branch/Site, dan pengecualian Divisi berada di dashboard khusus Developer.</span>@if(auth()->user()->is_developer)<a class="btn btn-sm btn-primary" href="{{ route('hr.attendance-locations.index') }}">Buka Pusat Lokasi</a>@endif</div></div>
    <div class="col-12 d-flex gap-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="attendance_gps_required" value="1" id="gps" @checked(old('attendance_gps_required',$company->attendance_gps_required ?? true))><label class="form-check-label" for="gps">GPS wajib</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="attendance_selfie_required" value="1" id="selfie" @checked(old('attendance_selfie_required',$settings['attendance_selfie_required'] ?? true))><label class="form-check-label" for="selfie">Selfie wajib</label></div></div>
  </div></div></div>
  <div class="col-xl-5 mb-4"><div class="card h-100"><div class="card-header"><h5 class="mb-0">Payroll</h5></div><div class="card-body row g-3">
    <div class="col-md-6"><label class="form-label">Tanggal gaji</label><input class="form-control" type="number" min="1" max="31" name="salary_payment_day" required value="{{ old('salary_payment_day',$company->salary_payment_day ?? 31) }}"><div class="form-text">Akhir bulan menyesuaikan jumlah hari.</div></div>
    <div class="col-md-6"><label class="form-label">Mata uang</label><input class="form-control text-uppercase" maxlength="3" name="default_currency" required value="{{ old('default_currency',$company->default_currency ?? 'IDR') }}"></div>
    <div class="col-12"><div class="alert alert-info mb-0">Bonus KPI tetap dijadwalkan tanggal 15 bulan berikutnya dari modul KPI/payroll.</div></div>
  </div></div></div>
</div>
@if(auth()->user()->can('attendance.update') || auth()->user()->can('payroll.update'))<button class="btn btn-primary">Simpan Aturan</button>@endif
</form>
@endsection
