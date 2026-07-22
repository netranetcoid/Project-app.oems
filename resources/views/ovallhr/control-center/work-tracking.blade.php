@extends('layouts.contentNavbarLayout')

@section('title', 'Tracking & Perjalanan OvallHR')

@section('content')
@php
  // Kilometer valid saja; titik review/blocked selalu bernilai 0 km di server.
  $totalKm = round($tracks->sum('distance_from_previous_meters') / 1000, 2);
  $reviewCount = $tracks->where('integrity_status', 'review')->count();
  $blockedCount = $tracks->where('integrity_status', 'blocked')->count();
  // Disusun terlebih dahulu agar Blade tidak perlu mem-parsing closure di @json.
  $mapPoints = $tracks->map(function ($track) use ($timezone) {
    return [
      'lat' => (float) $track->latitude,
      'lng' => (float) $track->longitude,
      'session' => implode(':', [$track->employee_id, $track->work_mode, $track->attendance_id ?: 0, $track->overtime_attendance_id ?: 0]),
      'name' => $track->employee?->name,
      'email' => $track->account_email ?: $track->employee?->user?->email,
      'time' => $track->captured_at->copy()->setTimezone($timezone)->format('H:i:s'),
      'mode' => $track->work_mode,
      'status' => $track->integrity_status,
      'flags' => $track->risk_flags ?? [],
      // Kegiatan hanya berasal dari task yang benar-benar dibuat perusahaan.
      'activity' => $activeTasks->get($track->employee_id)?->title,
    ];
  })->values();
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
  .tracking-detail { min-height: 560px; background: #f8fafc; border-radius: .5rem; padding: 1.25rem; }
  .tracking-motor { width: 52px; height: 42px; filter: drop-shadow(0 4px 5px rgba(15, 23, 42, .35)); }
  .tracking-stop { min-width: 31px; height: 31px; display: grid; place-items: center; border-radius: 7px 7px 7px 0; transform: rotate(-45deg); background: #f59e0b; border: 2px solid #fff; box-shadow: 0 3px 10px rgba(15, 23, 42, .3); color: #fff; font-size: 12px; font-weight: 800; }
  .tracking-stop span { transform: rotate(45deg); }
  .tracking-pulse { width: 16px; height: 16px; border-radius: 50%; background: #16a34a; border: 3px solid #fff; box-shadow: 0 0 0 7px rgba(22, 163, 74, .22); }
  .tracking-stat { border-left: 3px solid #0ea5e9; padding-left: .75rem; margin-top: 1rem; }
  .journey-row { cursor: pointer; }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h4 class="mb-1">Tracking & Perjalanan Kerja</h4>
    <p class="text-muted mb-0">Rute, posisi terakhir, lama berhenti, dan status integritas. Hanya HR/Owner berizin.</p>
  </div>
  <a href="{{ route('ovallhr.control-center.index') }}" class="btn btn-label-secondary">Kembali</a>
</div>

<div class="card mb-4"><div class="card-body"><form class="row g-3">
  <div class="col-md-5"><label class="form-label">Pegawai</label><select name="employee_id" class="form-select"><option value="">Semua pegawai</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected($employeeId === $employee->id)>{{ $employee->employee_no }} - {{ $employee->name }}</option>@endforeach</select></div>
  <div class="col-md-4"><label class="form-label">Tanggal perjalanan</label><input class="form-control" type="date" name="date" value="{{ $date }}"></div>
  <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100">Tampilkan</button></div>
</form></div></div>

<div class="row g-4 mb-4">
  <div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted">Jarak valid</span><h3 class="mb-0 mt-1">{{ number_format($totalKm, 2, ',', '.') }} km</h3></div></div></div>
  <div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted">Sesi perjalanan</span><h3 class="mb-0 mt-1">{{ $journeys->count() }}</h3></div></div></div>
  <div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted">Perlu review</span><h3 class="mb-0 mt-1 text-warning">{{ $reviewCount }}</h3></div></div></div>
  <div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted">Mock GPS terdeteksi</span><h3 class="mb-0 mt-1 text-danger">{{ $blockedCount }}</h3></div></div></div>
</div>

<div class="card mb-4">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2"><div><h5 class="mb-0">Rute perjalanan</h5><small class="text-muted">Garis tipis = rute valid. Ikon motor = titik terakhir. Penanda kuning hanya muncul saat berhenti minimal 10 menit.</small></div><span class="badge bg-label-primary">{{ $date }}</span></div>
  <div class="card-body"><div class="row g-3"><div class="col-lg-8"><div id="workTrackingMap" style="height:560px" class="rounded"></div></div><div class="col-lg-4"><div id="routeDetail" class="tracking-detail"><h5 class="mb-2">Detail perjalanan</h5><p class="text-muted mb-0">Peta tidak menampilkan semua sampel GPS. Klik motor atau titik berhenti untuk melihat detail lokasi dan task aktif.</p></div></div></div></div>
</div>

<div class="card"><div class="card-header"><h5 class="mb-0">Daftar perjalanan</h5></div><div class="table-responsive text-nowrap"><table class="table table-hover align-middle"><thead><tr><th>Pegawai / akun</th><th>Jenis</th><th>Durasi</th><th>Jarak valid</th><th>Titik terakhir</th><th>Status lokasi</th><th>Integritas</th></tr></thead><tbody>
@forelse($journeys as $journey)
  @php
    $duration = $journey['duration_seconds'];
    $durationLabel = sprintf('%02d:%02d:%02d', intdiv($duration, 3600), intdiv($duration % 3600, 60), $duration % 60);
    $stopLabel = $journey['is_stopped'] ? 'Berhenti ' . sprintf('%02d:%02d:%02d', intdiv($journey['stop_seconds'], 3600), intdiv($journey['stop_seconds'] % 3600, 60), $journey['stop_seconds'] % 60) : 'Bergerak / belum 10 menit';
    $badge = match($journey['integrity_status']) { 'blocked' => 'bg-label-danger', 'review' => 'bg-label-warning', default => 'bg-label-success' };
    $label = match($journey['integrity_status']) { 'blocked' => 'Fake GPS / diblokir', 'review' => 'Perlu review', default => 'Tervalidasi' };
  @endphp
  <tr class="journey-row" data-session="{{ $journey['session_key'] }}"><td><strong>{{ $journey['employee_name'] }}</strong><br><small class="text-muted">{{ $journey['employee_code'] }} - {{ $journey['account_email'] ?: 'email belum tersedia' }}</small></td><td>{{ $journey['mode'] === 'overtime' ? 'Lembur' : 'Jam kerja' }}</td><td>{{ $durationLabel }}</td><td><strong>{{ number_format($journey['distance_km'], 2, ',', '.') }} km</strong><br><small class="text-muted">{{ $journey['point_count'] }} titik</small></td><td>{{ $journey['last_seen_at']->format('H:i:s') }}<br><small class="text-muted">{{ number_format($journey['last_latitude'], 6) }}, {{ number_format($journey['last_longitude'], 6) }}</small></td><td>{{ $stopLabel }}</td><td><span class="badge {{ $badge }}">{{ $label }}</span></td></tr>
@empty
  <tr><td colspan="7" class="text-center text-muted py-5">Belum ada perjalanan terekam pada tanggal ini.</td></tr>
@endforelse
</tbody></table></div><div class="card-body border-top"><small class="text-muted">Jarak dihitung server dari titik GPS valid. Lama berhenti memakai radius 35 meter dan hanya berlaku selama sesi kerja/lembur.</small></div></div>
@endsection

@section('page-script')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  const points = @json($mapPoints);
  const map = L.map('workTrackingMap').setView([-6.6127551, 106.7554874], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: 'OpenStreetMap' }).addTo(map);
  const detail = document.getElementById('routeDetail');
  const sessions = points.reduce((all, point) => { (all[point.session] ||= []).push(point); return all; }, {});
  const routes = Object.values(sessions);
  const routeBySession = Object.fromEntries(routes.map((route) => [route[0].session, route]));
  const bounds = [];
  const colorFor = (status) => status === 'blocked' ? '#dc2626' : (status === 'review' ? '#f59e0b' : '#0284c7');
  const statusLabel = (status) => status === 'blocked' ? 'Fake GPS terdeteksi' : (status === 'review' ? 'Perlu review HR' : 'Tervalidasi');
  const distanceMeters = (a, b) => { const r = 6371000, lat = (b.lat-a.lat)*Math.PI/180, lng = (b.lng-a.lng)*Math.PI/180; const x = Math.sin(lat/2)**2 + Math.cos(a.lat*Math.PI/180)*Math.cos(b.lat*Math.PI/180)*Math.sin(lng/2)**2; return r*2*Math.atan2(Math.sqrt(x), Math.sqrt(1-x)); };
  const toSeconds = (value) => { const [h,m,s]=value.split(':').map(Number); return h*3600+m*60+s; };
  const stopInfo = (route) => { const last = route.at(-1); let first = last; for(let i=route.length-1;i>=0;i--){ if(distanceMeters(last,route[i])>35) break; first=route[i]; } return {seconds:Math.max(0,toSeconds(last.time)-toSeconds(first.time))}; };
  const duration = (seconds) => `${String(Math.floor(seconds/3600)).padStart(2,'0')}:${String(Math.floor(seconds%3600/60)).padStart(2,'0')}:${String(seconds%60).padStart(2,'0')}`;
  const stopPoints = (route) => { const stops=[]; let from=0; for(let i=1;i<=route.length;i++){ const outside=i===route.length || distanceMeters(route[from],route[i])>35; if(!outside) continue; const first=route[from], last=route[i-1], seconds=Math.max(0,toSeconds(last.time)-toSeconds(first.time)); if(seconds>=600) stops.push({...last, stopSeconds:seconds}); from=i; } return stops; };
  const showDetail = (route, selectedPoint = null) => { const last=route.at(-1), point=selectedPoint || last, stop=stopInfo(route), isLastPoint=point === last, stopped=Boolean(point.stopSeconds) || (isLastPoint && stop.seconds>=600), stopSeconds=point.stopSeconds || stop.seconds; const activity=point.activity ? point.activity : 'Belum ada task aktif/dilaporkan'; detail.innerHTML=`<div class="d-flex align-items-center gap-2"><span class="tracking-pulse"></span><div><h5 class="mb-0">${point.name || 'Pegawai'}</h5><small class="text-muted">${point.email || '-'}</small></div></div><div class="tracking-stat"><small class="text-muted d-block">${isLastPoint ? 'Titik terakhir' : (stopped ? 'Titik berhenti' : 'Titik perjalanan')}</small><strong>${point.lat.toFixed(6)}, ${point.lng.toFixed(6)}</strong><small class="text-muted d-block mt-1">Terekam ${point.time} - ${point.mode === 'overtime' ? 'Lembur' : 'Jam kerja'}</small></div><div class="tracking-stat"><small class="text-muted d-block">Status lokasi</small><strong>${stopped ? 'Berhenti sekitar '+duration(stopSeconds) : 'Bergerak / belum 10 menit'}</strong><small class="text-muted d-block mt-1">Radius berhenti maksimal 35 m.</small></div><div class="tracking-stat"><small class="text-muted d-block">Kegiatan (task aktif)</small><strong>${activity}</strong><small class="text-muted d-block mt-1">GPS hanya menunjukkan lokasi; kegiatan berasal dari task, bukan tebakan sistem.</small></div><div class="tracking-stat"><small class="text-muted d-block">Validasi sistem</small><strong class="${point.status==='blocked'?'text-danger':point.status==='review'?'text-warning':'text-success'}">${statusLabel(point.status)}</strong></div>`; };
  const motorSvg = '<svg class="tracking-motor" viewBox="0 0 104 84" aria-label="Posisi motor"><circle cx="25" cy="62" r="13" fill="#0f172a"/><circle cx="80" cy="62" r="13" fill="#0f172a"/><circle cx="25" cy="62" r="6" fill="#e2e8f0"/><circle cx="80" cy="62" r="6" fill="#e2e8f0"/><path d="M25 58h20l10-23h18l10 23H48" fill="none" stroke="#0f4c81" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/><path d="M52 32l11-13h12" fill="none" stroke="#0f4c81" stroke-width="7" stroke-linecap="round"/><circle cx="58" cy="16" r="8" fill="#f2b705"/><path d="M43 39h22" stroke="#38bdf8" stroke-width="6" stroke-linecap="round"/></svg>';
  routes.forEach((route)=>{ const valid=route.filter((point)=>point.status==='accepted'); if(valid.length>1){const line=L.polyline(valid.map((point)=>[point.lat,point.lng]),{color:'#0284c7',weight:3,opacity:.72}).addTo(map);line.on('click',()=>showDetail(route));} if(route.length){const start=route[0];L.circleMarker([start.lat,start.lng],{radius:5,color:'#16a34a',fillColor:'#16a34a',fillOpacity:1}).addTo(map).bindTooltip('Mulai');} const last=route.at(-1); stopPoints(route).filter((point)=>point.time!==last.time).forEach((point)=>{const marker=L.marker([point.lat,point.lng],{icon:L.divIcon({className:'tracking-stop-wrap',html:'<div class="tracking-stop"><span>P</span></div>',iconSize:[31,31],iconAnchor:[15,30]})}).addTo(map);marker.on('click',()=>showDetail(route,point));}); const motor=L.marker([last.lat,last.lng],{icon:L.divIcon({className:'tracking-motor-wrap',html:motorSvg,iconSize:[52,42],iconAnchor:[26,34]})}).addTo(map);motor.on('click',()=>showDetail(route));route.forEach((point)=>bounds.push([point.lat,point.lng])); });
  document.querySelectorAll('.journey-row').forEach((row)=>row.addEventListener('click',()=>{const route=routeBySession[row.dataset.session];if(route){showDetail(route);map.flyTo([route.at(-1).lat,route.at(-1).lng],16,{duration:.7});}}));
  if(bounds.length){map.fitBounds(bounds,{padding:[32,32]});showDetail(routes.at(-1));}
</script>
@endsection
