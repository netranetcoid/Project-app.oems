@extends('layouts.contentNavbarLayout')
@section('title', 'Edit Branch / Site')
@section('content')
<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><div><h4 class="mb-1">Edit Branch / Site</h4><p class="text-muted mb-0">Kode {{ $branch->code }} bersifat tetap demi konsistensi payroll, absensi, dan integrasi.</p></div><a href="{{ route('master.branches.index') }}" class="btn btn-label-secondary">Kembali</a></div>
<form method="POST" action="{{ route('master.branches.update', $branch) }}">@csrf @method('PUT') <div class="card-body">@include('master.Branches.form', ['branch' => $branch])</div><div class="card-footer text-end"><button class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan Perubahan</button></div></form></div>
@endsection
