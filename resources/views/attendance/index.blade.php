@extends('layouts.contentNavbarLayout')

@section('title', 'Dashboard Absensi')

@section('content')
{{--
  Master dashboard ala GajiHub: angka di atas dan tabel di bawah selalu
  memakai filter yang sama. Semua keputusan approval tetap melalui controller
  agar perubahan terlacak oleh audit dan dapat disinkronkan ke AppBill.
--}}
@php
  $selectedStatus = $filters['status'] ?? 'all';
  $statusLabels = ['present' => 'Hadir', 'late' => 'Terlambat', 'incomplete' => 'Belum pulang', 'pending' => 'Review', 'rejected' => 'Ditolak'];
@endphp

<div class="card mb-4 border-0" style="background: linear-gradient(120deg, #102b50, #1d5b93); color:#fff;">
  <div class="card-body p-4">
    <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
      <div>
        <div class="text-uppercase small opacity-75 mb-2">OvallHR Control / Attendance</div>
        <h3 class="text-white mb-1"><i class="ti ti-fingerprint me-2"></i>Dashboard Absensi</h3>
        <p class="mb-0 opacity-75">Pantau kehadiran, GPS, selfie, jam kerja, keterlambatan, dan review HR dalam satu layar.</p>
      </div>
      <div class="align-self-xl-center"><span class="badge bg-white text-primary fs-6">{{ $date->translatedFormat('l, d F Y') }}</span></div>
    </div>
  </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

{{-- Filter berlaku untuk kartu statistik dan tabel, supaya angka tidak menipu HR. --}}
<div class="card mb-4"><div class="card-body">
  <form method="GET" class="row g-3 align-items-end">
    <div class="col-sm-6 col-lg-3"><label class="form-label">Tanggal</label><input type="date" class="form-control" name="date" value="{{ $date->toDateString() }}"></div>
    <div class="col-sm-6 col-lg-3"><label class="form-label">Site / Product</label><select class="form-select" name="branch_id"><option value="">Semua site</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
    <div class="col-sm-6 col-lg-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="all">Semua status</option>@foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-sm-6 col-lg-3 d-flex gap-2"><button class="btn btn-primary"><i class="ti ti-filter me-1"></i>Tampilkan</button><a href="{{ route('attendance.index') }}" class="btn btn-label-secondary">Reset</a></div>
  </form>
</div></div>

<div class="row g-4 mb-4">
  <div class="col-6 col-xl"><div class="card h-100"><div class="card-body"><div class="text-muted small">Wajib presensi</div><h3 class="mb-0">{{ $stats['expected'] }}</h3><small class="text-muted">pegawai aktif</small></div></div></div>
  <div class="col-6 col-xl"><div class="card h-100"><div class="card-body"><div class="text-muted small">Sudah masuk</div><h3 class="mb-0 text-primary">{{ $stats['checked_in'] }}</h3><small class="text-muted">clock-in tercatat</small></div></div></div>
  <div class="col-6 col-xl"><div class="card h-100"><div class="card-body"><div class="text-muted small">Sudah pulang</div><h3 class="mb-0 text-success">{{ $stats['completed'] }}</h3><small class="text-muted">clock-out lengkap</small></div></div></div>
  <div class="col-6 col-xl"><div class="card h-100"><div class="card-body"><div class="text-muted small">Terlambat</div><h3 class="mb-0 text-warning">{{ $stats['late'] }}</h3><small class="text-muted">melewati toleransi</small></div></div></div>
  <div class="col-6 col-xl"><div class="card h-100"><div class="card-body"><div class="text-muted small">Belum masuk</div><h3 class="mb-0 text-danger">{{ $stats['not_checked_in'] }}</h3><small class="text-muted">perlu ditindaklanjuti</small></div></div></div>
  <div class="col-6 col-xl"><div class="card h-100"><div class="card-body"><div class="text-muted small">Review HR</div><h3 class="mb-0 text-info">{{ $stats['pending_review'] }}</h3><small class="text-muted">pending / ditolak</small></div></div></div>
</div>

<div class="card">
  <div class="card-header d-flex flex-column flex-lg-row justify-content-between gap-3"><div><h5 class="mb-1">Rekap presensi</h5><p class="mb-0 text-muted">Bukti selfie/GPS tersimpan sesuai retention. Review hanya mengubah status approval, bukan menghapus rekam presensi.</p></div><div class="d-flex gap-2 flex-wrap">@can('attendance.shift.view')<a class="btn btn-label-primary btn-sm" href="{{ route('attendance.shifts.index') }}">Shift</a>@endcan @can('attendance.shift.assignment.view')<a class="btn btn-label-primary btn-sm" href="{{ route('attendance.shift-assignments.index') }}">Jadwal</a>@endcan @can('attendance.update')<a class="btn btn-label-primary btn-sm" href="{{ route('hr.settings.index') }}">Aturan GPS</a>@endcan</div></div>
  <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Pegawai / Site</th><th>Shift</th><th>Masuk / Pulang</th><th>Durasi / Telat</th><th>GPS & Bukti</th><th>Status</th><th class="text-end">Aksi HR</th></tr></thead><tbody>
    @forelse($records as $record)
      @php
        $approval = $record->approval_status ?: 'approved';
        $approvalColor = $approval === 'approved' ? 'success' : ($approval === 'rejected' ? 'danger' : 'warning');
        $attendanceColor = $record->status === 'late' ? 'warning' : ($record->status === 'present' ? 'primary' : 'secondary');
        $hours = sprintf('%02d:%02d', intdiv((int) $record->work_minutes, 60), ((int) $record->work_minutes % 60));
      @endphp
      <tr>
        <td><strong>{{ $record->employee?->name ?? 'Pegawai tidak ditemukan' }}</strong><div class="small text-muted">{{ $record->employee?->employee_no ?? '-' }} · {{ $record->employee?->branch?->name ?? 'Tanpa site' }}</div></td>
        <td>{{ $record->shift?->name ?? 'Tanpa shift' }}<div class="small text-muted">{{ $record->shift?->clock_in_time ? substr($record->shift->clock_in_time,0,5) : '-' }}–{{ $record->shift?->clock_out_time ? substr($record->shift->clock_out_time,0,5) : '-' }}</div></td>
        <td><strong>{{ $record->clock_in_at?->format('H:i') ?? '-' }}</strong><div class="small text-muted">Pulang: {{ $record->clock_out_at?->format('H:i') ?? 'Belum tercatat' }}</div></td>
        <td>{{ $hours }}<div class="small {{ $record->late_minutes > 0 ? 'text-warning' : 'text-muted' }}">{{ $record->late_minutes > 0 ? $record->late_minutes . ' menit terlambat' : 'Tepat waktu' }}</div></td>
        <td><div class="small">@if($record->geofence_validated)<span class="text-success"><i class="ti ti-map-pin-check"></i> Valid {{ number_format((float) $record->geofence_distance_meters,0) }} m</span>@else<span class="text-warning"><i class="ti ti-map-pin-question"></i> Belum tervalidasi</span>@endif</div><div class="d-flex gap-1 mt-1">@if($record->in_photo)<a class="btn btn-xs btn-label-primary" target="_blank" href="{{ route('attendance.records.proof', [$record, 'in']) }}">Selfie masuk</a>@endif @if($record->out_photo)<a class="btn btn-xs btn-label-primary" target="_blank" href="{{ route('attendance.records.proof', [$record, 'out']) }}">Selfie pulang</a>@endif</div></td>
        <td><span class="badge bg-label-{{ $attendanceColor }}">{{ ucfirst($record->status) }}</span><div class="mt-1"><span class="badge bg-label-{{ $approvalColor }}">{{ ucfirst($approval) }}</span></div>@if($record->rejection_reason)<div class="small text-danger mt-1">{{ $record->rejection_reason }}</div>@endif</td>
        <td class="text-end">@can('attendance.update')<div class="d-inline-flex gap-1">@if($approval !== 'approved')<form method="POST" action="{{ route('attendance.records.approve', $record) }}" onsubmit="return confirm('Setujui presensi ini?')">@csrf<button class="btn btn-sm btn-label-success">Setujui</button></form>@endif<form method="POST" action="{{ route('attendance.records.reject', $record) }}" onsubmit="const reason=window.prompt('Alasan penolakan presensi:'); if (!reason) return false; this.querySelector('[name=reason]').value=reason; return true;">@csrf<input type="hidden" name="reason"><button class="btn btn-sm btn-label-danger">Tolak</button></form></div>@endcan</td>
      </tr>
    @empty
      <tr><td colspan="7" class="text-center text-muted py-5"><i class="ti ti-calendar-off fs-2 d-block mb-2"></i>Belum ada data presensi pada filter ini.</td></tr>
    @endforelse
  </tbody></table></div>
  @if($records->hasPages())<div class="card-body">{{ $records->links() }}</div>@endif
</div>
@endsection
