@extends('layouts.contentNavbarLayout')

@section('title', 'Mobile Release Center')

@section('content')
<div class="container-fluid">
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger"><strong>Data belum disimpan.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

  <div class="card mb-4">
    <div class="card-header"><h4 class="mb-1">Mobile Release Center</h4><p class="mb-0 text-muted">Kelola versi APK dan menu OvallHR dari AppOEMS.</p></div>
    <div class="card-body">
      <form method="POST" action="{{ route('settings.mobile-releases.store') }}" enctype="multipart/form-data" class="row g-3">@csrf
        <div class="col-md-3"><label class="form-label">Versi</label><input name="version_name" class="form-control" placeholder="1.0.1" required></div>
        <div class="col-md-2"><label class="form-label">Version code</label><input name="version_code" type="number" min="1" class="form-control" required></div>
        <div class="col-md-2"><label class="form-label">Min. version</label><input name="minimum_version_code" type="number" min="1" value="1" class="form-control" required></div>
        <div class="col-md-5"><label class="form-label">URL APK / Play Store</label><input name="download_url" type="url" class="form-control" placeholder="https://..."><small class="text-muted">Isi URL eksternal, atau unggah APK di bawah.</small></div>
        <div class="col-md-5"><label class="form-label">Unggah APK OvallHR</label><input name="apk_file" type="file" accept=".apk,application/vnd.android.package-archive" class="form-control"><small class="text-muted">Maks. 200 MB. File disimpan di VPS AppOEMS.</small></div>
        <div class="col-md-8"><label class="form-label">Catatan rilis</label><textarea name="release_notes" class="form-control" rows="2" placeholder="Perbaikan dan fitur baru..."></textarea></div>
        <div class="col-md-4 d-flex align-items-end gap-3"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_force_update" value="1"><span class="form-check-label">Update wajib</span></label><button name="publish_now" value="1" class="btn btn-primary">Publikasi Rilis</button><button class="btn btn-outline-secondary">Simpan Draft</button></div>
      </form>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header"><h4 class="mb-1"><i class="ti ti-layout-grid me-2"></i>Menu OvallHR</h4><p class="mb-0 text-muted">Klik satu tombol untuk tampilkan atau sembunyikan. Semua menu di bawah sudah didukung APK; tanpa pengaturan khusus, status awalnya aktif.</p></div>
    <div class="card-body">
      <div class="alert alert-info small"><i class="ti ti-info-circle me-1"></i> Setelah diubah, karyawan cukup tutup-buka atau login ulang ke OvallHR. Tidak perlu rilis APK baru untuk pengaturan tampil/sembunyi ini.</div>
      <div class="row g-3">
        @foreach($featureCatalog as $item)
          @php($feature = $item['feature'])
          <div class="col-md-6 col-xl-4">
            <div class="border rounded p-3 h-100 {{ $item['is_enabled'] ? 'border-success' : 'border-secondary bg-lighter' }}">
              <div class="d-flex gap-3 align-items-start">
                <span class="avatar"><span class="avatar-initial rounded bg-label-{{ $item['is_enabled'] ? 'success' : 'secondary' }}"><i class="{{ $item['icon'] }}"></i></span></span>
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between gap-2"><div><strong>{{ $feature?->name ?: $item['name'] }}</strong><div class="small text-muted">{{ $item['key'] }}</div></div><span class="badge bg-label-{{ $item['is_enabled'] ? 'success' : 'secondary' }}">{{ $item['is_enabled'] ? 'Tampil' : 'Disembunyikan' }}</span></div>
                  <p class="small text-muted mt-2 mb-3">{{ $feature?->description ?: $item['description'] }}</p>
                  <form method="POST" action="{{ route('settings.mobile-features.toggle-known', $item['key']) }}" class="d-inline">@csrf<button class="btn btn-sm btn-{{ $item['is_enabled'] ? 'outline-danger' : 'success' }}"><i class="ti ti-{{ $item['is_enabled'] ? 'eye-off' : 'eye' }} me-1"></i>{{ $item['is_enabled'] ? 'Sembunyikan' : 'Tampilkan' }}</button></form>
                  <button class="btn btn-sm btn-label-primary ms-1" type="button" data-bs-toggle="collapse" data-bs-target="#feature-edit-{{ $item['key'] }}"><i class="ti ti-edit me-1"></i>Edit</button>
                  <div class="collapse mt-3" id="feature-edit-{{ $item['key'] }}">
                    <form method="POST" action="{{ route('settings.mobile-features.update-known', $item['key']) }}" class="border-top pt-3">@csrf @method('PUT')
                      <label class="form-label small">Nama di pusat kontrol</label><input class="form-control form-control-sm mb-2" name="name" maxlength="255" required value="{{ $feature?->name ?: $item['name'] }}">
                      <label class="form-label small">Keterangan admin</label><textarea class="form-control form-control-sm mb-2" name="description" rows="2" maxlength="1000">{{ $feature?->description ?: $item['description'] }}</textarea>
                      <button class="btn btn-sm btn-primary">Simpan Edit</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Riwayat Rilis Android</h5></div><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Versi</th><th>Minimum</th><th>Status</th><th>Aturan</th><th>URL</th><th></th></tr></thead><tbody>
  @forelse($releases as $release)<tr><td><strong>{{ $release->version_name }}</strong><div class="small text-muted">code {{ $release->version_code }}</div></td><td>{{ $release->minimum_version_code }}</td><td><span class="badge bg-label-{{ $release->status === 'published' ? 'success' : 'secondary' }}">{{ ucfirst($release->status) }}</span></td><td>{{ $release->is_force_update ? 'Wajib' : 'Opsional' }}</td><td>@if($release->download_url)<a href="{{ $release->download_url }}" target="_blank">Buka</a>@else - @endif</td><td>@if($release->status !== 'published')<form method="POST" action="{{ route('settings.mobile-releases.publish', $release) }}">@csrf<button class="btn btn-sm btn-primary">Publikasikan</button></form>@endif</td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-4">Belum ada rilis.</td></tr>@endforelse
  </tbody></table></div><div class="card-body">{{ $releases->links() }}</div></div>

  @if($customFeatures->isNotEmpty())
    <div class="card"><div class="card-header"><h5 class="mb-0">Feature Lanjutan</h5></div><div class="card-body"><p class="small text-muted">Data lama/custom tetap disimpan di sini. Menu aplikasi hanya dikendalikan dari panel Menu OvallHR di atas.</p><div class="row g-3">@foreach($customFeatures as $feature)<div class="col-md-4"><div class="border rounded p-3"><strong>{{ $feature->name }}</strong><div class="small text-muted">{{ $feature->key }}</div><p class="small mb-2">{{ $feature->description }}</p><form method="POST" action="{{ route('settings.mobile-features.toggle', $feature) }}">@csrf<button class="btn btn-sm btn-{{ $feature->is_enabled ? 'success' : 'secondary' }}">{{ $feature->is_enabled ? 'Aktif' : 'Nonaktif' }}</button></form></div></div>@endforeach</div></div></div>
  @endif
</div>
@endsection
