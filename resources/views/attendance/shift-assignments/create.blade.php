@extends('layouts.contentNavbarLayout')

@section('title', 'Jadwal Shift Mingguan')

@section('content')
@php($selectedEmployee = old('employee_id', $assignment->employee_id ?? request('employee_id')))
<div class="row"><div class="col-12"><div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div><h4 class="mb-1">Jadwal Shift Mingguan</h4><p class="text-muted mb-0">Pilih shift setiap hari. Day Off berarti bukan hari kerja.</p></div>
    <a href="{{ route('attendance.shift-assignments.index') }}" class="btn btn-outline-secondary">Kembali</a>
  </div>
  <div class="card-body">
    @if($errors->any())<div class="alert alert-danger"><strong>Data belum disimpan.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('attendance.shift-assignments.weekly.store') }}">
      @csrf
      <div class="row g-3 mb-4">
        <div class="col-lg-6"><label class="form-label">Pegawai <span class="text-danger">*</span></label><select name="employee_id" class="form-select" required><option value="">Pilih pegawai</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" data-branch="{{ $employee->branch_id }}" @selected((string)$selectedEmployee === (string)$employee->id)>{{ $employee->employee_no ?? $employee->employee_code ?? '-' }} — {{ $employee->name ?? $employee->full_name }} | {{ $employee->division?->name ?? $employee->branch?->name ?? 'Tanpa organisasi' }}</option>@endforeach</select></div>
        <div class="col-lg-3"><label class="form-label">Mulai minggu (Senin) <span class="text-danger">*</span></label><input type="date" name="week_start" id="week_start" class="form-control" value="{{ $weekStart }}" required></div>
        <div class="col-lg-3"><label class="form-label">Branch / Site</label><select name="branch_id" id="branch_id" class="form-select"><option value="">Ikuti pegawai</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}{{ $branch->type ? ' — '.$branch->type : '' }}</option>@endforeach</select></div>
      </div>
      <div class="table-responsive"><table class="table table-bordered align-middle"><thead class="table-light"><tr><th style="width:180px">Hari / Tanggal</th><th>Shift</th><th>Keterangan</th></tr></thead><tbody id="weeklyRows"></tbody></table></div>
      <div class="row mt-3"><div class="col-12"><label class="form-label">Catatan minggu</label><textarea name="notes" class="form-control" rows="2" placeholder="Contoh: jadwal teknisi minggu berjalan">{{ old('notes') }}</textarea></div></div>
      <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('attendance.shift-assignments.index') }}" class="btn btn-outline-secondary">Batal</a><button class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan 7 Hari</button></div>
    </form>
  </div>
</div></div></div>
@endsection

@section('page-script')
<script>
(() => {
  const start = document.getElementById('week_start');
  const rows = document.getElementById('weeklyRows');
  const shifts = @json($shiftOptions);
  const days = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
  const pad = n => String(n).padStart(2,'0');
  const render = () => {
    const d = new Date(start.value + 'T00:00:00'); rows.innerHTML = '';
    for (let i=0;i<7;i++) { const x = new Date(d); x.setDate(d.getDate()+i); const iso = `${x.getFullYear()}-${pad(x.getMonth()+1)}-${pad(x.getDate())}`;
      const opts = shifts.map(s => `<option value="${s.day_off?'off':s.id}">${s.name}${s.day_off?' (bukan hari kerja)':` [${s.code}]`}</option>`).join('');
      rows.insertAdjacentHTML('beforeend', `<tr><td><strong>${days[i]}</strong><br><small class="text-muted">${x.toLocaleDateString('id-ID',{day:'2-digit',month:'long',year:'numeric'})}</small></td><td><select class="form-select" name="days[${iso}]" required>${opts}</select></td><td class="text-muted">${i < 5 ? 'Hari kerja — pilih shift sesuai kebutuhan.' : 'Bisa Day Off atau shift lembur/operasional.'}</td></tr>`);
    }
  };
  start.addEventListener('change', render); render();
  document.querySelector('[name="employee_id"]')?.addEventListener('change', function(){ const b=document.getElementById('branch_id'); if(!b.value && this.selectedOptions[0]?.dataset.branch) b.value=this.selectedOptions[0].dataset.branch; });
})();
</script>
@endsection
