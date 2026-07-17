@extends('layouts.contentNavbarLayout')
@section('title', 'Gaji & Tunjangan')
@section('content')
<div class="d-flex flex-wrap justify-content-between gap-3 mb-4"><div><h4 class="mb-1">Master Gaji & Tunjangan</h4><p class="text-muted mb-0">Gaji pokok, uang makan, transport, jabatan, dan batas bonus KPI per pegawai.</p></div><form class="d-flex gap-2"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari NIK/nama"><button class="btn btn-primary">Cari</button></form></div>
<div class="card"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Pegawai</th><th>Cost Center</th><th>Gaji Pokok</th><th>Uang Makan</th><th>Transport</th><th>Tunjangan Jabatan</th><th>Maks. KPI</th><th></th></tr></thead><tbody>
@forelse($employees as $employee)<tr><td><strong>{{ $employee->name }}</strong><div class="small text-muted">{{ $employee->employee_no }}</div></td><td>{{ $employee->branch?->name ?: 'PT OSM' }}<div class="small">{{ $employee->position?->name ?: '-' }}</div></td><td>Rp {{ number_format((float)$employee->basic_salary,0,',','.') }}</td><td>Rp {{ number_format((float)$employee->meal_allowance,0,',','.') }}</td><td>Rp {{ number_format((float)$employee->transport_allowance,0,',','.') }}</td><td>Rp {{ number_format((float)$employee->position_allowance,0,',','.') }}</td><td>Rp {{ number_format((float)$employee->kpi_incentive_max,0,',','.') }}</td><td>@can('employees.update')<a class="btn btn-sm btn-label-primary" href="{{ route('employees.edit',$employee) }}">Edit</a>@endcan</td></tr>
@empty<tr><td colspan="8" class="text-center py-5 text-muted">Belum ada pegawai.</td></tr>@endforelse
</tbody></table></div>@if($employees->hasPages())<div class="card-body">{{ $employees->links() }}</div>@endif</div>
@endsection
