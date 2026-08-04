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
       'isActive' => $track->work_mode === 'overtime'
         ? $track->overtimeAttendance?->clock_out_at === null
         : $track->attendance?->clock_out_at === null,
       'lastSeenAt' => $track->captured_at->copy()->setTimezone($timezone)->toIso8601String(),
       'lastSeenAgeSeconds' => max(0, (int) $track->captured_at->copy()->setTimezone($timezone)->diffInSeconds(now($timezone))),
      // Kegiatan hanya berasal dari task yang benar-benar dibuat perusahaan.
      // Fallback menjaga peta tetap dapat dibuka ketika server baru selesai
      // deploy dan worker PHP lama belum memuat controller terbaru.
      'activity' => ($activeTasks ?? collect())->get($track->employee_id)?->title,
    ];
  })->values();
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
  .tracking-detail { min-height: 560px; background: #f8fafc; border-radius: .5rem; padding: 1.25rem; }
  .tracking-motor { width: 52px; height: 42px; filter: drop-shadow(0 4px 5px rgba(15, 23, 42, .35)); }
  .tracking-motor.is-active { color: #0284c7; }
  .tracking-motor.is-out { color: #94a3b8; opacity: .9; }
  .tracking-motor.is-stale { color: #f59e0b; opacity: .95; }
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

<div id="trackingLiveRoot">
<script type="application/json" id="trackingPayload">@json($mapPoints)</script>
<div class="d-flex justify-content-end align-items-center gap-2 mb-2">
  <span class="badge bg-label-success" id="trackingLiveStatus">Live</span>
  <small class="text-muted">Peta dan riwayat diperbarui otomatis tanpa refresh halaman Â· <span id="trackingLastUpdated">{{ now($timezone)->format('H:i:s') }}</span></small>
</div>
<div class="row g-4 mb-4">
  <div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted">Jarak valid</span><h3 class="mb-0 mt-1">{{ number_format($totalKm, 2, ',', '.') }} km</h3></div></div></div>
  <div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted">Sesi perjalanan</span><h3 class="mb-0 mt-1">{{ $journeys->count() }}</h3></div></div></div>
  <div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted">Perlu review</span><h3 class="mb-0 mt-1 text-warning">{{ $reviewCount }}</h3></div></div></div>
  <div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted">Mock GPS terdeteksi</span><h3 class="mb-0 mt-1 text-danger">{{ $blockedCount }}</h3></div></div></div>
</div>

<div class="card mb-4">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2"><div><h5 class="mb-0">Rute perjalanan</h5><small class="text-muted">Motor biru = posisi terbaru saat aktif; abu-abu = terakhir saat out; hijau = titik tujuan yang sudah dijangkau; lingkar kuning = sedang berhenti minimal 10 menit.</small></div><div class="d-flex gap-1"><span class="badge bg-label-info">Aktif</span><span class="badge bg-label-secondary">Out</span><span class="badge bg-label-success">Terjangkau</span><span class="badge bg-label-warning">Berhenti</span><span class="badge bg-label-primary">{{ $date }}</span></div></div>
  <div class="card-body"><div class="row g-3"><div class="col-lg-8"><div id="workTrackingMap" style="height:560px" class="rounded"></div></div><div class="col-lg-4"><div id="routeDetail" class="tracking-detail"><h5 class="mb-2">Detail perjalanan</h5><p class="text-muted mb-0">Peta tidak menampilkan semua sampel GPS. Klik motor atau titik berhenti untuk melihat detail lokasi dan task aktif.</p></div></div></div></div>
</div>

<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><div><h5 class="mb-0">Riwayat perjalanan hari ini</h5><small class="text-muted">Klik pegawai untuk memusatkan peta ke posisi terakhir dan membuka detail sesi.</small></div><span class="badge bg-label-primary">{{ $date }}</span></div><div class="table-responsive text-nowrap"><table class="table table-hover align-middle"><thead><tr><th>Pegawai / akun</th><th>Jenis</th><th>Durasi</th><th>Jarak valid</th><th>Titik terakhir</th><th>Status sesi</th><th>Status lokasi</th><th>Integritas</th></tr></thead><tbody>
@forelse($journeys as $journey)
  @php
    $duration = $journey['duration_seconds'];
    $durationLabel = sprintf('%02d:%02d:%02d', intdiv($duration, 3600), intdiv($duration % 3600, 60), $duration % 60);
     $stopLabel = $journey['is_stale'] ? 'Data stale - tidak ada titik baru' : ($journey['is_stopped'] ? 'Berhenti ' . sprintf('%02d:%02d:%02d', intdiv($journey['stop_seconds'], 3600), intdiv($journey['stop_seconds'] % 3600, 60), $journey['stop_seconds'] % 60) : 'Bergerak / belum 10 menit');
    $badge = match($journey['integrity_status']) { 'blocked' => 'bg-label-danger', 'review' => 'bg-label-warning', default => 'bg-label-success' };
    $label = match($journey['integrity_status']) { 'blocked' => 'Fake GPS / diblokir', 'review' => 'Perlu review', default => 'Tervalidasi' };
  @endphp
  <tr class="journey-row" data-session="{{ $journey['session_key'] }}"><td><strong>{{ $journey['employee_name'] }}</strong><br><small class="text-muted">{{ $journey['employee_code'] }} - {{ $journey['account_email'] ?: 'email belum tersedia' }}</small></td><td>{{ $journey['mode'] === 'overtime' ? 'Lembur' : 'Jam kerja' }}</td><td>{{ $durationLabel }}</td><td><strong>{{ number_format($journey['distance_km'], 2, ',', '.') }} km</strong><br><small class="text-muted">{{ $journey['point_count'] }} titik</small></td><td>{{ $journey['last_seen_at']->format('H:i:s') }}<br><small class="text-muted">{{ number_format($journey['last_latitude'], 6) }}, {{ number_format($journey['last_longitude'], 6) }}<br>terakhir {{ intdiv($journey['last_seen_age_seconds'], 60) }} mnt lalu</small></td><td><span class="badge {{ $journey['is_stale'] ? 'bg-label-warning' : ($journey['is_active'] ? 'bg-label-info' : 'bg-label-secondary') }}">{{ $journey['is_stale'] ? 'Stale / terputus' : ($journey['is_active'] ? 'Aktif' : 'Sudah out') }}</span></td><td>{{ $stopLabel }}</td><td><span class="badge {{ $badge }}">{{ $label }}</span></td></tr>
@empty
  <tr><td colspan="8" class="text-center text-muted py-5">Belum ada perjalanan terekam pada tanggal ini.</td></tr>
@endforelse
</tbody></table></div><div class="card-body border-top"><small class="text-muted">Jarak dihitung server dari titik GPS valid. Lama berhenti memakai radius 35 meter dan hanya berlaku selama sesi kerja/lembur.</small></div></div>
</div>
@endsection

@section('page-script')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(() => {
  let map = null;
  let refreshBusy = false;
  let selectedSession = null;

  const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
  const distanceMeters = (a, b) => { const r=6371000, lat=(b.lat-a.lat)*Math.PI/180, lng=(b.lng-a.lng)*Math.PI/180; const x=Math.sin(lat/2)**2+Math.cos(a.lat*Math.PI/180)*Math.cos(b.lat*Math.PI/180)*Math.sin(lng/2)**2; return r*2*Math.atan2(Math.sqrt(x),Math.sqrt(1-x)); };
  const toSeconds = (value) => { const [h,m,s]=String(value).split(':').map(Number); return (h||0)*3600+(m||0)*60+(s||0); };
  const duration = (seconds) => `${String(Math.floor(seconds/3600)).padStart(2,'0')}:${String(Math.floor(seconds%3600/60)).padStart(2,'0')}:${String(seconds%60).padStart(2,'0')}`;
  const statusLabel = (status) => status==='blocked' ? 'Fake GPS terdeteksi' : (status==='review' ? 'Perlu review HR' : 'Tervalidasi');
  const stopInfo = (route) => { const last=route.at(-1); let first=last; for(let i=route.length-1;i>=0;i--){if(distanceMeters(last,route[i])>35)break;first=route[i];} return {seconds:Math.max(0,toSeconds(last.time)-toSeconds(first.time))}; };
  const stopPoints = (route) => { const stops=[];let from=0;for(let i=1;i<=route.length;i++){const outside=i===route.length||distanceMeters(route[from],route[i])>35;if(!outside)continue;const first=route[from],last=route[i-1],seconds=Math.max(0,toSeconds(last.time)-toSeconds(first.time));if(seconds>=600)stops.push({...last,stopSeconds:seconds});from=i;}return stops; };
  const motorSvg = (state) => `<svg class="tracking-motor ${state==='reached'?'':(state==='stale'?'is-stale':(state?'is-active':'is-out'))}" style="color:${state==='reached'?'#16a34a':''}" viewBox="0 0 104 84"><circle cx="25" cy="62" r="13" fill="#0f172a"/><circle cx="80" cy="62" r="13" fill="#0f172a"/><circle cx="25" cy="62" r="6" fill="#e2e8f0"/><circle cx="80" cy="62" r="6" fill="#e2e8f0"/><path d="M25 58h20l10-23h18l10 23H48" fill="none" stroke="currentColor" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/><path d="M52 32l11-13h12" fill="none" stroke="currentColor" stroke-width="7" stroke-linecap="round"/><circle cx="58" cy="16" r="8" fill="#f2b705"/><path d="M43 39h22" stroke="#38bdf8" stroke-width="6" stroke-linecap="round"/></svg>`;

  function renderTracking() {
    const payload = document.getElementById('trackingPayload');
    const mapNode = document.getElementById('workTrackingMap');
    const detail = document.getElementById('routeDetail');
    if (!payload || !mapNode || !window.L) return;

    let points=[];
    try { points=JSON.parse(payload.textContent || '[]'); } catch (_) { points=[]; }
    if (map) { map.remove(); map=null; }
    map=L.map(mapNode).setView([-6.6127551,106.7554874],12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'OpenStreetMap'}).addTo(map);

    const sessions=points.reduce((all,point)=>{(all[point.session]??=[]).push(point);return all;},{});
    const routes=Object.values(sessions);
    routes.forEach((route)=>{const last=route.at(-1);last.isStale=Boolean(last.isActive&&last.lastSeenAgeSeconds>180);});
    const routeBySession=Object.fromEntries(routes.map((route)=>[route[0].session,route]));
    const bounds=[];

    const showDetail=(route,selectedPoint=null)=>{
      const last=route.at(-1),point=selectedPoint||last,stop=stopInfo(route),isLast=point===last,stopped=Boolean(point.stopSeconds)||(isLast&&stop.seconds>=600),stopSeconds=point.stopSeconds||stop.seconds;
      selectedSession=route[0].session;
      const sessionStatus=point.isStale?'Tracking terputus â€” menampilkan titik terakhir':(point.isActive?'Masih aktif â€” posisi terbaru':'Sudah out â€” posisi akhir sesi');
      detail.innerHTML=`<div class="d-flex align-items-center gap-2"><span class="tracking-pulse"></span><div><h5 class="mb-0">${escapeHtml(point.name||'Pegawai')}</h5><small class="text-muted">${escapeHtml(point.email||'-')}</small></div></div><div class="tracking-stat"><small class="text-muted d-block">${isLast?'Posisi terakhir':(stopped?'Titik berhenti':'Titik perjalanan')}</small><strong>${Number(point.lat).toFixed(6)}, ${Number(point.lng).toFixed(6)}</strong><small class="text-muted d-block mt-1">Terekam ${escapeHtml(point.time)} Â· ${point.mode==='overtime'?'Lembur':'Jam kerja'}</small></div><div class="tracking-stat"><small class="text-muted d-block">Status sesi</small><strong>${sessionStatus}</strong></div><div class="tracking-stat"><small class="text-muted d-block">Status pergerakan</small><strong>${stopped?'Berhenti sekitar '+duration(stopSeconds):'Bergerak / belum 10 menit'}</strong></div><div class="tracking-stat"><small class="text-muted d-block">Kegiatan</small><strong>${escapeHtml(point.activity||'Belum ada task aktif/dilaporkan')}</strong></div><div class="tracking-stat"><small class="text-muted d-block">Integritas GPS</small><strong class="${point.status==='blocked'?'text-danger':point.status==='review'?'text-warning':'text-success'}">${statusLabel(point.status)}</strong></div>`;
    };

    routes.forEach((route)=>{
      const valid=route.filter((point)=>point.status==='accepted');
      if(valid.length>1){const line=L.polyline(valid.map((p)=>[p.lat,p.lng]),{color:'#0284c7',weight:3,opacity:.72}).addTo(map);line.on('click',()=>showDetail(route));}
      if(route.length){const start=route[0];L.circleMarker([start.lat,start.lng],{radius:5,color:'#16a34a',fillColor:'#16a34a',fillOpacity:1}).addTo(map).bindTooltip('Mulai');}
      const last=route.at(-1),finalStop=stopInfo(route);
      stopPoints(route).filter((p)=>p.time!==last.time).forEach((point)=>{const marker=L.marker([point.lat,point.lng],{icon:L.divIcon({className:'tracking-motor-wrap',html:motorSvg('reached'),iconSize:[52,42],iconAnchor:[26,34]})}).addTo(map);marker.bindTooltip('Titik sudah dijangkau');marker.on('click',()=>showDetail(route,point));});
      if(last.isActive&&finalStop.seconds>=600)L.circleMarker([last.lat,last.lng],{radius:24,color:'#f59e0b',weight:3,fillColor:'#fbbf24',fillOpacity:.28}).addTo(map).bindTooltip('Berhenti â‰¥ 10 menit');
      const motor=L.marker([last.lat,last.lng],{icon:L.divIcon({className:'tracking-motor-wrap',html:motorSvg(last.isStale?'stale':last.isActive),iconSize:[52,42],iconAnchor:[26,34]})}).addTo(map);
      motor.bindTooltip(last.isStale?'Tracking terputus â€” titik terakhir':(last.isActive?'Posisi terbaru (aktif)':'Posisi akhir (out)'));motor.on('click',()=>showDetail(route));
      route.forEach((p)=>bounds.push([p.lat,p.lng]));
    });

    document.querySelectorAll('.journey-row').forEach((row)=>row.addEventListener('click',()=>{const route=routeBySession[row.dataset.session];if(route){showDetail(route);map.flyTo([route.at(-1).lat,route.at(-1).lng],16,{duration:.7});}}));
    const selected=selectedSession&&routeBySession[selectedSession];
    if(selected)showDetail(selected);else if(routes.length)showDetail(routes.at(-1));
    if(bounds.length)map.fitBounds(bounds,{padding:[32,32]});
    setTimeout(()=>map?.invalidateSize(),50);
  }

  async function refreshTracking() {
    if(refreshBusy||document.hidden)return;
    refreshBusy=true;
    const badge=document.getElementById('trackingLiveStatus');
    if(badge){badge.textContent='Memperbaruiâ€¦';badge.className='badge bg-label-warning';}
    try {
      const response=await fetch(window.location.href,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'text/html'},cache:'no-store',credentials:'same-origin'});
      if(!response.ok)throw new Error(`HTTP ${response.status}`);
      const html=await response.text();
      const doc=new DOMParser().parseFromString(html,'text/html');
      const fresh=doc.getElementById('trackingLiveRoot');
      const current=document.getElementById('trackingLiveRoot');
      if(!fresh||!current)throw new Error('Payload tracking tidak ditemukan');
      current.replaceWith(fresh);
      renderTracking();
      const time=document.getElementById('trackingLastUpdated');
      if(time)time.textContent=new Intl.DateTimeFormat('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}).format(new Date());
      const live=document.getElementById('trackingLiveStatus');
      if(live){live.textContent='Live';live.className='badge bg-label-success';}
    } catch(error) {
      const live=document.getElementById('trackingLiveStatus');
      if(live){live.textContent='Koneksi terputus';live.className='badge bg-label-danger';}
    } finally { refreshBusy=false; }
  }

  renderTracking();
  window.setInterval(refreshTracking,20000);
  document.addEventListener('visibilitychange',()=>{if(!document.hidden)refreshTracking();});
})();
</script>
@endsection