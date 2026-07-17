@extends('layouts.contentNavbarLayout')

@section('title','Tambah Jadwal Shift')

@section('content')

<div class="row">

<div class="col-lg-8 mx-auto">

<div class="card">

<div class="card-header">

<h4 class="mb-0">

Tambah Jadwal Shift

</h4>

</div>

<div class="card-body">

<form method="POST"
action="{{ route('attendance.shift-assignments.store') }}">

@csrf

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Site

</label>

<select
name="branch_id"
class="form-select">

<option value="">

Semua Site

</option>

@foreach($branches as $branch)

<option value="{{ $branch->id }}">

{{ $branch->name }}

</option>

@endforeach

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Pegawai

</label>

<select
name="employee_id"
class="form-select"
required>

<option value="">

Pilih Pegawai

</option>

@foreach($employees as $employee)

<option value="{{ $employee->id }}">

{{ $employee->full_name }}

</option>

@endforeach

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Master Shift

</label>

<select
name="attendance_shift_id"
class="form-select"
required>

<option value="">

Pilih Shift

</option>

@foreach($shifts as $shift)

<option value="{{ $shift->id }}">

{{ $shift->name }}

</option>

@endforeach

</select>

</div>

<div class="col-md-3 mb-3">

<label class="form-label">

Tanggal Mulai

</label>

<input
type="date"
name="start_date"
class="form-control"
required>

</div>

<div class="col-md-3 mb-3">

<label class="form-label">

Tanggal Selesai

</label>

<input
type="date"
name="end_date"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Status

</label>

<select
name="status"
class="form-select">

<option value="active">

Active

</option>

<option value="inactive">

Inactive

</option>

</select>

</div>

<div class="col-12 mb-3">

<label class="form-label">

Catatan

</label>

<textarea
name="notes"
rows="3"
class="form-control"></textarea>

</div>

</div>

<div class="text-end">

<a href="{{ route('attendance.shift-assignments.index') }}"
class="btn btn-secondary">

Kembali

</a>

<button
type="submit"
class="btn btn-primary">

Simpan

</button>

</div>

</form>

</div>

</div>

</div>

</div>

@endsection