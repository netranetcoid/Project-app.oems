@extends('layouts.contentNavbarLayout')

@section('title', isset($assignment) ? 'Edit Jadwal Shift' : 'Tambah Jadwal Shift')

@section('content')
@php
  $isEdit = isset($assignment);
  $selectedEmployee = old('employee_id', $assignment->employee_id ?? request('employee_id'));
  $selectedBranch = old('branch_id', $assignment->branch_id ?? '');
@endphp
<div class="row"><div class="col-xl-9 col-lg-10 mx-auto"><div class="card">
  <div class="card-header d-flex justify-content-between align-items-center"><div><h4 class="mb-1">{{ $isEdit ? 'Edit Jadwal Shift' : 'Tambah Jadwal Shift' }}</h4><p class="text-muted mb-0">Pilih pegawai, shift, dan periode kerja. Pilihan pegawai berasal dari company aktif.</p></div><a href="{{ route('attendance.shift-assignments.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a></div>
  <div class="card-body">
    @if($errors->any())<div class="alert alert-danger"><strong>Data belum tersimpan.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if($employees->isEmpty())<div class="alert alert-warning mb-0">Belum ada pegawai pada company aktif. Tambahkan pegawai dulu melalui Master Data.</div>
    @else
    <form method="POST" action="{{ $isEdit ? route('attendance.shift-assignments.update', $assignment) : route('attendance.shift-assignments.store') }}">
      @csrf @if($isEdit) @method('PUT') @endif
      <div class="row g-3">
        <div class="col-12"><label class="form-label" for="employee_id">Pegawai <span class="text-danger">*</span></label><select id="employee_id" name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required><option value="">Pilih pegawai</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" data-branch="{{ $employee->branch_id }}" @selected((string) $selectedEmployee === (string) $employee->id)>{{ $employee->employee_no ?? $employee->employee_code ?? '-' }} â€” {{ $employee->name }} | {{ $employee->division?->name ?? $employee->branch?->name ?? 'Tanpa organisasi' }} | {{ $employee->position?->name ?? 'Tanpa jabatan' }}</option>@endforeach</select><div class="form-text">Seluruh pegawai company aktif ditampilkan, termasuk data pegawai lama yang statusnya belum seragam.</div></div>
        <div class="col-md-6"><label class="form-label" for="branch_id">Branch / Site</label><select id="branch_id" name="branch_id" class="form-select"><option value="">Ikuti branch/site pegawai</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((string) $selectedBranch === (string) $branch->id)>{{ $branch->name }}{{ $branch->type ? ' â€” '.$branch->type : '' }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label" for="attendance_shift_id">Master Shift <span class="text-danger">*</span></label><select id="attendance_shift_id" name="attendance_shift_id" class="form-select @error('attendance_shift_id') is-invalid @enderror" required><option value="">Pilih shift</option>@foreach($shifts as $shift)<option value="{{ $shift->id }}" @selected((string) old('attendance_shift_id', $assignment->attendance_shift_id ?? '') === (string) $shift->id)>{{ $shift->name }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="start_date">Tanggal Mulai <span class="text-danger">*</span></label><input id="start_date" type="date" name="start_date" value="{{ old('start_date', isset($assignment) ? optional($assignment->start_date)->format('Y-m-d') : now()->format('Y-m-d')) }}" class="form-control @error('start_date') is-invalid @enderror" required></div>
        <div class="col-md-4"><label class="form-label" for="end_date">Tanggal Selesai</label><input id="end_date" type="date" name="end_date" value="{{ old('end_date', isset($assignment) ? optional($assignment->end_date)->format('Y-m-d') : '') }}" class="form-control"><div class="form-text">Kosongkan untuk jadwal berjalan.</div></div>
        <div class="col-md-4"><label class="form-label" for="status">Status <span class="text-danger">*</span></label><select id="status" name="status" class="form-select"><option value="active" @selected(old('status', $assignment->status ?? 'active') === 'active')>Aktif</option><option value="inactive" @selected(old('status', $assignment->status ?? '') === 'inactive')>Nonaktif</option></select></div>
        <div class="col-12"><label class="form-label" for="notes">Catatan</label><textarea id="notes" name="notes" rows="3" class="form-control" placeholder="Contoh: jadwal teknisi kantor / rotasi lapangan">{{ old('notes', $assignment->notes ?? '') }}</textarea></div>
      </div>
      <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('attendance.shift-assignments.index') }}" class="btn btn-outline-secondary">Batal</a><button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Jadwal' }}</button></div>
    </form>
    @endif
  </div>
</div></div></div>
@endsection

@section('page-script')
<script>
document.getElementById('employee_id')?.addEventListener('change', function () {
  const employeeBranch = this.options[this.selectedIndex]?.dataset.branch;
  const branch = document.getElementById('branch_id');
  if (employeeBranch && branch && !branch.value) branch.value = employeeBranch;
});
</script>
@endsection