@extends('layouts.contentNavbarLayout')

@section('title', 'Tracking & Perjalanan OvallHR')

@section('content')
@php
  // Kilometer valid saja; titik review/blocked selalu bernilai 0 km di server.
  $totalKm = round($tracks->sum('distance_from_previous_meters') / 1000, 2);
  $reviewCount = $tracks->where('integrity_status', 'review')->count();
  $blockedCount = $tracks->where('integrity_status', 'blocked')->count();
  // Dibentuk di PHP agar directive Blade @json tidak perlu mem-parsing
  // arrow-function dan array bersarang di dalam tag script.
  $mapPoints = $tracks->map(function ($track) use ($timezone) {
    return [
      'lat' => (float) $track->latitude,
      'lng' => (float) $track->longitude,
      'employeeId' => $track->employee_id,
      'session' => implode(':', [$track->employee_id, $track->work_mode, $track->attendance_id ?: 0, $track->overtime_attendance_id ?: 0]),
      'name' => $track->employee?->name,
      'email' => $track->account_email ?: $track->employee?->user?->email,
      'time' => $track->captured_at->copy()->setTimezone($timezone)->format('H:i:s'),
      'mode' => $track->work_mode,
      'status' => $track->integrity_status,
      'flags' => $track->risk_flags ?? [],
    ];
  })->values();
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h4 class="mb-1">Tracking & Perjalanan Kerja</h4>
    <p class="text-muted mb-0">Peta, rute, km, dan status integritas. Hanya HR/Owner berizin.</p>
  </div>
  <a href="{{ route('ovallhr.control-center.index') }}" class="btn btn-label-secondary">Kembali</a>
</div>

<div class="card mb-4">
  <div class="card-body">
    <form class="row g-3">
      <div class="col-md-5">
        <label class="form-label">Pegawai</label>
        <select name="employee_id" class="form-select">
          <option value="">Semua pegawai</option>
          @foreach($employees as $employee)
            <option value="{{ $employee->id }}" @selected($employeeId === $employee->id)>
              {{ $employee->employee_no }} — {{ $employee->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Tanggal perjalanan</label>
        <input class="form-control" type="date" name="date" value="{{ $date }}">
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-primary w-100">Tampilkan</button>
      </div>
    </form>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted">Jarak valid</span><h3 class="mb-0 mt-1">{{ number_format($totalKm, 2, ',', '.') }} km</h3></div></div></div>
  <div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted">Sesi perjalanan</span><h3 class="mb-0 mt-1">{{ $journeys->count() }}</h3></div></div></div>
  <div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted">Perlu review</span><h3 class="mb-0 mt-1 text-warning">{{ $reviewCount }}</h3></div></div></div>
  <div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted">Mock GPS terdeteksi</span><h3 class="mb-0 mt-1 text-danger">{{ $blockedCount }}</h3></div></div></div>
</div>

<div class="card mb-4">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <h5 class="mb-0">Rute perjalanan</h5>
      <small class="text-muted">Garis biru = titik GPS valid · kuning = perlu review · merah = fake GPS terdeteksi</small>
    </div>
    <span class="badge bg-label-primary">{{ $date }}</span>
  </div>
  <div class="card-body"><div id="workTrackingMap" style="height:560px" class="rounded"></div></div>
</div>

<div class="card">
  <div class="card-header"><h5 class="mb-0">Daftar perjalanan</h5></div>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle">
      <thead><tr><th>Pegawai / akun</th><th>Jenis</th><th>Mulai — selesai</th><th>Durasi</th><th>Jarak valid</th><th>Integritas</th></tr></thead>
      <tbody>
        @forelse($journeys as $journey)
          @php
            $duration = $journey['duration_seconds'];
            $durationLabel = sprintf('%02d:%02d:%02d', intdiv($duration, 3600), intdiv($duration % 3600, 60), $duration % 60);
            $badge = match($journey['integrity_status']) { 'blocked' => 'bg-label-danger', 'review' => 'bg-label-warning', default => 'bg-label-success' };
            $label = match($journey['integrity_status']) { 'blocked' => 'Fake GPS / diblokir', 'review' => 'Perlu review', default => 'Tervalidasi' };
          @endphp
          <tr>
            <td><strong>{{ $journey['employee_name'] }}</strong><br><small class="text-muted">{{ $journey['employee_code'] }} · {{ $journey['account_email'] ?: 'email belum tersedia' }}</small></td>
            <td>{{ $journey['mode'] === 'overtime' ? 'Lembur' : 'Jam kerja' }}</td>
            <td>{{ $journey['started_at']->format('H:i') }} — {{ $journey['ended_at']->format('H:i') }}</td>
            <td>{{ $durationLabel }}</td>
            <td><strong>{{ number_format($journey['distance_km'], 2, ',', '.') }} km</strong><br><small class="text-muted">{{ $journey['point_count'] }} titik</small></td>
            <td><span class="badge {{ $badge }}">{{ $label }}</span></td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-5">Belum ada perjalanan terekam pada tanggal ini.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-body border-top"><small class="text-muted">Jarak dihitung oleh server dari titik GPS yang lolos validasi, bukan nominal yang dikirim pegawai. Gunakan sebagai acuan review klaim bensin/motor, bukan pencairan otomatis.</small></div>
</div>
@endsection

@section('page-script')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  const points = @json($mapPoints);

  const map = L.map('workTrackingMap').setView([-6.6127551, 106.7554874], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap',
  }).addTo(map);

  const colorFor = (status) => status === 'blocked' ? '#dc2626' : (status === 'review' ? '#f59e0b' : '#0284c7');
  const sessions = points.reduce((grouped, point) => {
    (grouped[point.session] ||= []).push(point);
    return grouped;
  }, {});
  const bounds = [];

  Object.values(sessions).forEach((route) => {
    const validRoute = route.filter((point) => point.status === 'accepted');
    if (validRoute.length > 1) {
      L.polyline(validRoute.map((point) => [point.lat, point.lng]), { color: '#0284c7', weight: 5, opacity: .85 }).addTo(map);
    }
    route.forEach((point, index) => {
      const color = colorFor(point.status);
      const marker = L.circleMarker([point.lat, point.lng], { radius: index === 0 ? 8 : 6, color, fillColor: color, fillOpacity: .92 }).addTo(map);
      const label = point.status === 'blocked' ? 'Fake GPS terdeteksi' : (point.status === 'review' ? 'Perlu review HR' : 'Tervalidasi');
      marker.bindPopup(`<strong>${point.name}</strong><br>${point.email || '-'}<br>${point.time} · ${point.mode === 'overtime' ? 'Lembur' : 'Kerja'}<br><strong>${label}</strong>${point.flags.length ? `<br>${point.flags.join(', ')}` : ''}`);
      bounds.push([point.lat, point.lng]);
    });
  });

  if (bounds.length) map.fitBounds(bounds, { padding: [32, 32] });
</script>
@endsection
