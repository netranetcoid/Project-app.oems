@extends('layouts.contentNavbarLayout')

@section('title','Kontrak Pegawai')

@section('content')

<div class="container-fluid">

<div class="card">

<div class="card-header d-flex justify-content-between">

<div>

<h4>

Kontrak Pegawai

</h4>

<small>

Riwayat Kontrak Pegawai

</small>

</div>

<a
href="{{ route('hr.contracts.create') }}"
class="btn btn-primary">

<i class="ti ti-plus"></i>

Tambah Kontrak

</a>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>No</th>

<th>No Kontrak</th>

<th>Pegawai</th>

<th>Jenis</th>

<th>Mulai</th>

<th>Selesai</th>

<th>Status</th>

<th></th>

</tr>

</thead>

<tbody>

@forelse($contracts as $contract)

<tr>

<td>

{{ $loop->iteration }}

</td>

<td>

<strong>

{{ $contract->contract_no }}

</strong>

</td>

<td>

{{ $contract->employee_name }}

</td>

<td>

{{ $contract->contractType?->name }}

</td>

<td>

{{ optional($contract->start_date)->format('d M Y') }}

</td>

<td>

{{ optional($contract->end_date)->format('d M Y') }}

</td>

<td>

<span class="badge bg-{{ $contract->status_badge }}">

{{ $contract->status_label }}

</span>

</td>

<td>

<div class="btn-group">

<a
href="{{ route('hr.contracts.show',$contract) }}"
class="btn btn-info btn-sm">

<i class="ti ti-eye"></i>

</a>

<a
href="{{ route('hr.contracts.edit',$contract) }}"
class="btn btn-warning btn-sm">

<i class="ti ti-edit"></i>

</a>

<a
href="{{ route('hr.contracts.pdf',$contract) }}"
class="btn btn-danger btn-sm"
target="_blank"
title="Cetak / simpan PDF">

<i class="ti ti-file-type-pdf"></i>

</a>

<a
href="{{ route('hr.contracts.print',$contract) }}"
class="btn btn-secondary btn-sm"
target="_blank"
title="Print preview">

<i class="ti ti-printer"></i>

</a>

</div>

</td>

</tr>

@empty

<tr>

<td colspan="8"
class="text-center">

Belum ada kontrak.

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

{{ $contracts->links() }}

</div>

</div>

</div>

@endsection
