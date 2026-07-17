@extends('layouts.contentNavbarLayout')

@section('title', 'Mobile Release Center')

@section('content')
<div class="container-fluid">
  <div class="card mb-4">
    <div class="card-header"><h4 class="mb-1">Mobile Release Center</h4><p class="mb-0 text-muted">Kelola versi OvallHR, update wajib, dan feature toggle dari AppOEMS.</p></div>
    <div class="card-body">
      <form method="POST" action="{{ route('settings.mobile-releases.store') }}" class="row g-3">@csrf
        <div class="col-md-3"><label class="form-label">Versi</label><input name="version_name" class="form-control" placeholder="1.0.1" required></div>
        <div class="col-md-2"><label class="form-label">Version code</label><input name="version_code" type="number" min="1" class="form-control" required></div>
        <div class="col-md-2"><label class="form-label">Min. version</label><input name="minimum_version_code" type="number" min="1" value="1" class="form-control" required></div>
        <div class="col-md-5"><label class="form-label">URL APK / Play Store</label><input name="download_url" type="url" class="form-control" placeholder="https://..."></div>
        <div class="col-md-8"><label class="form-label">Catatan rilis</label><textarea name="release_notes" class="form-control" rows="2" placeholder="Perbaikan dan fitur baru..."></textarea></div>
        <div class="col-md-4 d-flex align-items-end gap-3"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_force_update" value="1"><span class="form-check-label">Update wajib</span></label><button name="publish_now" value="1" class="btn btn-primary">Publikasi Rilis</button><button class="btn btn-outline-secondary">Simpan Draft</button></div>
      </form>
    </div>
  </div>
  <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Riwayat Rilis Android</h5></div><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Versi</th><th>Minimum</th><th>Status</th><th>Aturan</th><th>URL</th><th></th></tr></thead><tbody>
  @forelse($releases as $release)<tr><td><strong>{{ $release->version_name }}</strong><div class="small text-muted">code {{ $release->version_code }}</div></td><td>{{ $release->minimum_version_code }}</td><td><span class="badge bg-label-{{ $release->status === 'published' ? 'success' : 'secondary' }}">{{ ucfirst($release->status) }}</span></td><td>{{ $release->is_force_update ? 'Wajib' : 'Opsional' }}</td><td>@if($release->download_url)<a href="{{ $release->download_url }}" target="_blank">Buka</a>@else - @endif</td><td>@if($release->status !== 'published')<form method="POST" action="{{ route('settings.mobile-releases.publish', $release) }}">@csrf<button class="btn btn-sm btn-primary">Publikasikan</button></form>@endif</td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-4">Belum ada rilis.</td></tr>@endforelse
  </tbody></table></div><div class="card-body">{{ $releases->links() }}</div></div>
  <div class="card"><div class="card-header"><h5 class="mb-0">Feature Toggle Mobile</h5></div><div class="card-body"><form method="POST" action="{{ route('settings.mobile-features.store') }}" class="row g-3">@csrf<div class="col-md-3"><input name="key" class="form-control" placeholder="misal: overtime" required></div><div class="col-md-3"><input name="name" class="form-control" placeholder="Nama fitur" required></div><div class="col-md-3"><input name="description" class="form-control" placeholder="Keterangan"></div><div class="col-md-2"><input name="value_json" class="form-control" placeholder='{"limit": 3}'></div><div class="col-md-1 d-flex align-items-center"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_enabled" value="1"><span class="visually-hidden">Aktif</span></label></div><div class="col-12"><button class="btn btn-outline-primary">Simpan Feature</button></div></form><hr><div class="row g-3">@forelse($features as $feature)<div class="col-md-4"><div class="border rounded p-3"><div class="d-flex justify-content-between"><strong>{{ $feature->name }}</strong><form method="POST" action="{{ route('settings.mobile-features.toggle', $feature) }}">@csrf<button class="btn btn-sm btn-{{ $feature->is_enabled ? 'success' : 'secondary' }}">{{ $feature->is_enabled ? 'Aktif' : 'Nonaktif' }}</button></form></div><div class="small text-muted">{{ $feature->key }}</div><div class="small mt-2">{{ $feature->description }}</div></div></div>@empty<div class="text-muted">Belum ada feature toggle.</div>@endforelse</div></div></div>
</div>
@endsection
