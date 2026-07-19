@extends('layouts.contentNavbarLayout')

@section('title', 'Pusat Lokasi Presensi')

@section('content')
{{-- Developer-only. This is deliberately a single dashboard so HR cannot hunt
     through several master menus or accidentally mix Solo with head office. --}}
<div class="card border-0 mb-4" style="background:linear-gradient(120deg,#102b50,#1d5b93);color:#fff">
  <div class="card-body p-4 p-lg-5 d-flex flex-column flex-lg-row justify-content-between gap-3">
    <div><div class="text-uppercase small opacity-75 mb-2">Developer only</div><h3 class="text-white mb-2"><i class="ti ti-map-pin-cog me-2"></i>Pusat Lokasi Presensi</h3><p class="mb-0 opacity-75">Satu dashboard untuk kantor utama PT OSM, seluruh Branch/Site, dan pengecualian Divisi.</p></div>
    <div class="align-self-lg-center text-lg-end"><div class="small opacity-75">Company aktif</div><strong>{{ $company->name }}</strong><div class="small opacity-75 mt-1">Prioritas: Divisi → Site/Branch → Kantor utama</div></div>
  </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="alert alert-info d-flex gap-2 mb-4"><i class="ti ti-shield-check fs-4"></i><div><strong>Mode Geofence</strong> mewajibkan karyawan berada dalam radius titik yang dipilih. <strong>Mode Bebas lokasi</strong> dipakai untuk divisi lapangan/khusus: aplikasi tetap menyimpan selfie, waktu, dan GPS sebagai bukti, tetapi tidak membandingkannya dengan satu titik kantor.</div></div>

<div class="row g-4">
  <div class="col-xl-4">
    <div class="card h-100"><div class="card-header"><h5 class="mb-1">Tambah Kebijakan Lokasi</h5><p class="mb-0 text-muted small">Satu policy aktif untuk satu scope. Ubah policy yang ada bila scope sudah terdaftar.</p></div><div class="card-body">
      <form method="POST" action="{{ route('hr.attendance-locations.store') }}" class="row g-3">@csrf
        <div class="col-12"><label class="form-label">Cakupan</label><select class="form-select" name="scope_type" id="scopeType" required><option value="company">Kantor Utama PT OSM</option><option value="branch">Branch / Site</option><option value="division">Divisi khusus</option></select></div>
        <div class="col-12 d-none" id="scopeBranch"><label class="form-label">Pilih Branch / Site</label><select class="form-select" name="branch_scope_id"><option value="">Pilih site</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }} — {{ $branch->city ?: $branch->code }}</option>@endforeach</select></div>
        <div class="col-12 d-none" id="scopeDivision"><label class="form-label">Pilih Divisi</label><select class="form-select" name="division_scope_id"><option value="">Pilih divisi</option>@foreach($divisions as $division)<option value="{{ $division->id }}">{{ $division->name }}</option>@endforeach</select></div>
        <input type="hidden" name="scope_id" id="scopeId">
        <div class="col-12"><label class="form-label">Nama kebijakan</label><input class="form-control" name="name" required maxlength="120" placeholder="Contoh: Site Solo / Teknisi Lapangan"></div>
        <div class="col-12"><label class="form-label">Mode</label><select class="form-select" name="mode" id="locationMode"><option value="geofence">Geofence — wajib di radius titik</option><option value="anywhere">Bebas lokasi — tanpa titik kantor</option></select></div>
        <div class="col-md-6 geofenceField"><label class="form-label">Latitude</label><input class="form-control" name="latitude" inputmode="decimal" placeholder="-7.xxxxx"></div>
        <div class="col-md-6 geofenceField"><label class="form-label">Longitude</label><input class="form-control" name="longitude" inputmode="decimal" placeholder="110.xxxxx"></div>
        <div class="col-12 geofenceField"><label class="form-label">Radius (meter)</label><input class="form-control" type="number" min="1" max="50000" name="radius_meter" value="150"></div>
        <div class="col-12"><label class="form-label">Catatan</label><textarea class="form-control" rows="2" name="notes" maxlength="2000" placeholder="Contoh: Site Solo, karyawan wajib selfie dan GPS."></textarea></div>
        <div class="col-12"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><span class="form-check-label">Aktifkan kebijakan ini</span></label></div>
        <div class="col-12"><button class="btn btn-primary w-100"><i class="ti ti-device-floppy me-1"></i>Simpan Kebijakan</button></div>
      </form>
    </div></div>
  </div>
  <div class="col-xl-8">
    <div class="card"><div class="card-header d-flex flex-column flex-md-row justify-content-between gap-2"><div><h5 class="mb-1">Kebijakan Aktif & Tersimpan</h5><p class="mb-0 text-muted small">Penghapusan akan mengembalikan scope ke fallback: data Branch lalu kantor utama.</p></div><span class="badge bg-label-primary align-self-md-center">{{ $policies->count() }} policy</span></div>
      <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Cakupan</th><th>Lokasi / mode</th><th>Koordinat</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
        @forelse($policies as $policy)<tr>
          <td><div class="fw-medium">{{ $policy->name }}</div><span class="badge bg-label-{{ $policy->scope_type === 'company' ? 'primary' : ($policy->scope_type === 'branch' ? 'info' : 'warning') }}">{{ $policy->scope_type === 'company' ? 'Kantor Utama' : ($policy->scope_type === 'branch' ? 'Branch / Site' : 'Divisi khusus') }}</span></td>
          <td>@if($policy->mode === 'anywhere')<span class="text-success"><i class="ti ti-map-pin-off me-1"></i>Bebas lokasi</span><div class="small text-muted">Selfie + GPS tetap menjadi bukti</div>@else<span class="text-primary"><i class="ti ti-map-pin-check me-1"></i>Geofence {{ $policy->radius_meter }} m</span><div class="small text-muted">Titik wajib dipatuhi</div>@endif</td>
          <td class="small">{{ $policy->latitude !== null ? number_format((float)$policy->latitude, 7, '.', '') . ', ' . number_format((float)$policy->longitude, 7, '.', '') : '—' }}</td>
          <td><span class="badge bg-label-{{ $policy->is_active ? 'success' : 'secondary' }}">{{ $policy->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
          <td class="text-end"><button class="btn btn-sm btn-label-primary" type="button" data-bs-toggle="collapse" data-bs-target="#editPolicy{{ $policy->id }}"><i class="ti ti-edit"></i> Edit</button></td>
        </tr>
        <tr class="collapse" id="editPolicy{{ $policy->id }}"><td colspan="5" class="bg-lighter"><form method="POST" action="{{ route('hr.attendance-locations.update', $policy) }}" class="row g-2 p-2">@csrf @method('PUT')
          <input type="hidden" name="scope_type" value="{{ $policy->scope_type }}"><input type="hidden" name="scope_id" value="{{ $policy->scope_id }}">
          <div class="col-md-3"><label class="form-label small">Nama</label><input class="form-control form-control-sm" name="name" required value="{{ $policy->name }}"></div>
          <div class="col-md-2"><label class="form-label small">Mode</label><select class="form-select form-select-sm" name="mode"><option value="geofence" @selected($policy->mode === 'geofence')>Geofence</option><option value="anywhere" @selected($policy->mode === 'anywhere')>Bebas lokasi</option></select></div>
          <div class="col-md-2"><label class="form-label small">Latitude</label><input class="form-control form-control-sm" name="latitude" value="{{ $policy->latitude }}"></div><div class="col-md-2"><label class="form-label small">Longitude</label><input class="form-control form-control-sm" name="longitude" value="{{ $policy->longitude }}"></div><div class="col-md-1"><label class="form-label small">Radius</label><input class="form-control form-control-sm" name="radius_meter" type="number" value="{{ $policy->radius_meter }}"></div>
          <div class="col-md-2 d-flex align-items-end gap-1"><label class="form-check small mb-2"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($policy->is_active)><span class="form-check-label">Aktif</span></label><button class="btn btn-sm btn-primary mb-1">Simpan</button></div>
          <div class="col-12"><input class="form-control form-control-sm" name="notes" value="{{ $policy->notes }}" placeholder="Catatan"></div></form><form method="POST" action="{{ route('hr.attendance-locations.destroy', $policy) }}" class="px-2 pb-2" onsubmit="return confirm('Hapus policy ini? Scope akan kembali memakai fallback.')">@csrf @method('DELETE')<button class="btn btn-sm btn-label-danger">Hapus policy</button></form></td></tr>
        @empty<tr><td colspan="5" class="text-center text-muted py-5">Belum ada policy. Jalankan seeder untuk mengambil default kantor utama dan data Branch/Site yang sudah ada.</td></tr>@endforelse
      </tbody></table></div>
    </div>
  </div>
</div>
@endsection

@push('page-script')
<script>
  // Keeps the form clear: only the relevant selector is submitted as scope_id.
  const scopeType = document.getElementById('scopeType');
  const branch = document.querySelector('[name="branch_scope_id"]');
  const division = document.querySelector('[name="division_scope_id"]');
  const scopeId = document.getElementById('scopeId');
  const syncScope = () => {
    document.getElementById('scopeBranch').classList.toggle('d-none', scopeType.value !== 'branch');
    document.getElementById('scopeDivision').classList.toggle('d-none', scopeType.value !== 'division');
    scopeId.value = scopeType.value === 'branch' ? branch.value : (scopeType.value === 'division' ? division.value : '');
  };
  [scopeType, branch, division].forEach(item => item?.addEventListener('change', syncScope)); syncScope();
  const mode = document.getElementById('locationMode');
  const syncMode = () => document.querySelectorAll('.geofenceField').forEach(item => item.classList.toggle('d-none', mode.value === 'anywhere'));
  mode?.addEventListener('change', syncMode); syncMode();
</script>
@endpush
