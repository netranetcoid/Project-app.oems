@extends('layouts.blankLayout')

@section('title', 'Preview OvallHR')

@section('content')
{{-- Preview hanya simulasi desain dan permission browser. Tidak pernah mengirim
     selfie/GPS ke server; bukti nyata selalu melalui APK dan API presensi. --}}
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1">Preview OvallHR</h4><p class="text-muted mb-0">Model layar mobile sebelum rilis APK.</p></div><a href="{{ route('ovallhr.control-center.index') }}#mobile-branding" class="btn btn-label-secondary">Kembali</a></div>
  <div class="mx-auto shadow rounded-4 overflow-hidden" style="max-width:390px;min-height:780px;border:8px solid #172033;background:#000">
    <div class="p-4 text-white" style="background:linear-gradient(135deg, {{ $branding['secondary_color'] }}, {{ $branding['primary_color'] }});min-height:200px"><div class="d-flex align-items-center gap-2">@if($branding['logo_url'])<img src="{{ $branding['logo_url'] }}" alt="Logo" style="width:48px;height:48px;object-fit:contain;background:#fff;border-radius:50%;padding:4px">@else<div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;color:{{ $branding['primary_color'] }};font-weight:800">{{ strtoupper(substr($branding['app_name'],0,1)) }}</div>@endif<div><strong>{{ $branding['app_name'] }}</strong><div class="small opacity-75">{{ $branding['company_label'] }}</div></div></div><div class="mt-4 small opacity-75">{{ $branding['welcome_text'] }}</div><h4 class="mb-0 text-white">Halo, Karyawan</h4></div>
    <div class="p-3 text-white" id="homePreview">
      <div class="rounded-4 p-3 mb-3" style="background:#364359"><div class="row text-center g-2"><div class="col-3">⏱<br><small>Kehadiran</small></div><div class="col-3">✈<br><small>Izin & Cuti</small></div><div class="col-3">💳<br><small>Gaji</small></div><div class="col-3">▣<br><small>Kalender</small></div><div class="col-3 mt-3">✓<br><small>Approval</small></div><div class="col-3 mt-3">▤<br><small>Kasbon</small></div><div class="col-3 mt-3">✎<br><small>Pengajuan</small></div></div></div>
      <button type="button" id="openAttendancePreview" class="btn w-100 text-white" style="background:#13b6d2">Buka menu Kehadiran</button>
    </div>
    <div class="p-3 text-white d-none" id="attendanceMenuPreview">
      <button type="button" id="backHomePreview" class="btn btn-sm text-white p-0 mb-3">← Kembali</button><h5>Kehadiran</h5>
      @foreach ([['↪','Presensi Masuk','#18b56a'],['↩','Presensi Keluar','#f05a63'],['◷','Mulai Lembur','#22b679'],['↶','Selesai Lembur','#f2a33a'],['▣','Jadwal Shift','#3b82f6'],['▤','Ringkasan Presensi','#ffa94d'],['☷','Histori Presensi','#67d7a2']] as [$icon,$label,$color])
        <button type="button" class="attendance-option border-0 d-flex align-items-center w-100 text-start text-white mb-2 px-3 py-3 rounded-4" data-label="{{ $label }}" style="background:#364359"><span class="rounded-circle d-inline-flex justify-content-center align-items-center me-3" style="width:42px;height:42px;background:{{ $color }}22;color:{{ $color }};font-size:22px">{{ $icon }}</span><span class="flex-grow-1">{{ $label }}</span><span class="opacity-50">›</span></button>
      @endforeach
    </div>
    <div id="cameraPreviewPanel" class="d-none position-relative text-white" style="min-height:690px;background:#202b36"><video id="cameraPreview" autoplay playsinline muted class="position-absolute w-100 h-100" style="object-fit:cover;opacity:.66"></video><div class="position-relative p-3"><button type="button" id="backAttendancePreview" class="btn btn-sm text-white p-0 mb-3">← Presensi Masuk</button><div class="rounded-4 p-3" style="background:#162237df"><div>🕘 <b>Waktu Kehadiran</b><br><span id="timestampPreview" class="ms-4">Memuat waktu...</span></div><hr><div>📍 <b>Radius 150 meter dari kantor</b><br><span id="gpsPreview" class="ms-4">GPS belum diperiksa</span></div><hr><div>▦ <b>Kantor</b><br><span class="ms-4">PT Ovall Solusindo Mandiri</span></div></div></div><div class="position-absolute bottom-0 start-0 end-0 p-4 text-center"><button type="button" id="captureAttendancePreview" class="btn rounded-circle text-white" style="width:70px;height:70px;border:4px solid #fff;background:#13b6d2">●</button><canvas id="cameraSnapshot" class="d-none"></canvas><div class="small mt-2">Simulasi kamera: APK menambahkan watermark OvallHR + GPS.</div></div></div>
    <div class="text-center py-2" style="background:#1d2b40;color:#b9c1ce;font-size:11px">Home &nbsp;&nbsp;&nbsp; Pengajuan &nbsp;&nbsp;&nbsp; <b style="color:{{ $branding['primary_color'] }}">◉ Kehadiran</b> &nbsp;&nbsp;&nbsp; Profil</div>
  </div>
</div>
<script>
(() => {
  let stream; const home=document.getElementById('homePreview'), menu=document.getElementById('attendanceMenuPreview'), camera=document.getElementById('cameraPreviewPanel'), video=document.getElementById('cameraPreview'), gps=document.getElementById('gpsPreview');
  function stop(){stream?.getTracks().forEach(track=>track.stop());stream=null;}
  async function openCamera(){menu.classList.add('d-none');camera.classList.remove('d-none');document.getElementById('timestampPreview').textContent=new Date().toLocaleString('id-ID');try{stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:'user'},audio:false});video.srcObject=stream;}catch(_){gps.textContent='Kamera tidak diizinkan browser';}if(navigator.geolocation)navigator.geolocation.getCurrentPosition(p=>gps.textContent=`GPS siap ±${Math.round(p.coords.accuracy)} m`,()=>gps.textContent='GPS belum tersedia');}
  document.getElementById('openAttendancePreview').onclick=()=>{home.classList.add('d-none');menu.classList.remove('d-none')};document.getElementById('backHomePreview').onclick=()=>{menu.classList.add('d-none');home.classList.remove('d-none')};document.querySelectorAll('.attendance-option').forEach(button=>button.onclick=()=>button.dataset.label.includes('Presensi')?openCamera():alert(`${button.dataset.label} akan membuka halaman terkait di APK.`));document.getElementById('backAttendancePreview').onclick=()=>{stop();camera.classList.add('d-none');menu.classList.remove('d-none')};document.getElementById('captureAttendancePreview').onclick=()=>alert('Selfie simulasi diambil. APK akan menambahkan watermark dan mengirim bukti ke AppOEMS.');window.addEventListener('beforeunload',stop);
})();
</script>
@endsection
