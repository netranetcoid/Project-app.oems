@extends('layouts.contentNavbarLayout')
@section('title', 'Rekap Presensi Periode')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
  <div><a href="{{ route('attendance.index') }}" class="small">&larr; Monitoring harian</a><h4 class="mt-2 mb-1">Rekap Presensi Pegawai</h4><p class="text-muted mb-0">Ringkasan hadir, terlambat, sakit, izin, cuti, dan jam kerja per periode.</p></div>
  <a class="btn btn-success" href="{{ route('attendance.report.export', request()->query()) }}"><i class="ti ti-file-spreadsheet me-1"></i>Ekspor CSV</a>
</div>
<div class="card mb-4"><div class="card-body"><form method="GET" class="row g-3 align-items-end">
  <div class="col-md-2"><label class="form-label">Jenis periode</label><select class="form-select" name="period" id="periodType"><option value="month" @selected($period==='month')>Bulanan</option><option value="year" @selected($period==='year')>Tahunan</option><option value="custom" @selected($period==='custom')>Rentang tanggal</option></select></div>
  <div class="col-md-2 period-month"><label class="form-label">Bulan</label><input class="form-control" type="month" name="month" value="{{ $filters['month'] ?? $start->format('Y-m') }}"></div>
  <div class="col-md-2 period-year"><label class="form-label">Tahun</label><input class="form-control" type="number" min="2020" max="2100" name="year" value="{{ $filters['year'] ?? $start->year }}"></div>
  <div class="col-md-2 period-custom"><label class="form-label">Dari</label><input class="form-control" type="date" name="start_date" value="{{ $start->toDateString() }}"></div>
  <div class="col-md-2 period-custom"><label class="form-label">Sampai</label><input class="form-control" type="date" name="end_date" value="{{ $end->toDateString() }}"></div>
  <div class="col-md-3"><label class="form-label">Branch / Site</label><select class="form-select" name="branch_id"><option value="">Semua</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((string)($filters['branch_id']??'')===(string)$branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
  <div class="col-md-3"><label class="form-label">Pegawai</label><select class="form-select" name="employee_id"><option value="">Semua</option>@foreach($employeeOptions as $employee)<option value="{{ $employee->id }}" @selected((string)($filters['employee_id']??'')===(string)$employee->id)>{{ $employee->employee_no }} - {{ $employee->name }}</option>@endforeach</select></div>
  <div class="col-md-2"><button class="btn btn-primary w-100">Tampilkan</button></div>
</form></div></div>
<div class="alert alert-info">Periode aktif: <strong>{{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}</strong>. Cuti/sakit/izin hanya menghitung pengajuan yang sudah disetujui HR.</div>
<div class="row g-3 mb-4">
@foreach([['Pegawai',$summary['employees'],'primary'],['Hadir',$summary['present'],'success'],['Terlambat',$summary['late'],'warning'],['Sakit',$summary['sick'],'danger'],['Izin',$summary['permission'],'info'],['Cuti',$summary['leave'],'secondary']] as $card)
<div class="col-6 col-lg-2"><div class="card h-100"><div class="card-body"><div class="text-muted small">{{ $card[0] }}</div><h3 class="mb-0 text-{{ $card[2] }}">{{ $card[1] }}</h3></div></div></div>
@endforeach
</div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Pegawai / Site</th><th>Hadir</th><th>Terlambat</th><th>Belum pulang</th><th>Sakit</th><th>Izin</th><th>Cuti</th><th>Jam kerja</th><th></th></tr></thead><tbody>
@forelse($rows as $row)<tr><td><strong>{{ $row['employee']->name }}</strong><div class="small text-muted">{{ $row['employee']->employee_no }} &middot; {{ $row['employee']->branch?->name ?: 'Tanpa site' }}</div></td><td>{{ $row['present'] }} hari</td><td><span class="badge bg-label-{{ $row['late'] ? 'warning' : 'success' }}">{{ $row['late'] }} kali</span><div class="small text-muted">{{ $row['late_minutes'] }} menit</div></td><td>{{ $row['incomplete'] }}</td><td>{{ $row['sick_days'] }} hari</td><td>{{ $row['permission_days'] }} hari</td><td>{{ $row['leave_days'] }} hari</td><td>{{ number_format($row['work_minutes']/60,2,',','.') }} jam</td><td><a class="btn btn-sm btn-label-primary" href="{{ route('employees.show', ['employee'=>$row['employee']->id,'tab'=>'attendance']) }}">Riwayat</a></td></tr>
@empty<tr><td colspan="9" class="text-center text-muted py-5">Tidak ada pegawai pada filter ini.</td></tr>@endforelse
</tbody></table></div></div>
@push('scripts')<script>function togglePeriod(){const p=document.getElementById('periodType').value;document.querySelectorAll('.period-month').forEach(e=>e.style.display=p==='month'?'':'none');document.querySelectorAll('.period-year').forEach(e=>e.style.display=p==='year'?'':'none');document.querySelectorAll('.period-custom').forEach(e=>e.style.display=p==='custom'?'':'none')}document.getElementById('periodType').addEventListener('change',togglePeriod);togglePeriod();</script>@endpush
@endsection
