@extends('layouts.contentNavbarLayout')
@section('title', 'Tambah Branch / Site')
@section('content')
<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><div><h4 class="mb-1">Tambah Branch / Site</h4><p class="text-muted mb-0">PT OSM adalah induk. Site wajib ditempatkan di bawah Branch atau Head Office.</p></div><a href="{{ route('master.branches.index') }}" class="btn btn-label-secondary">Kembali</a></div>
<form method="POST" action="{{ route('master.branches.store') }}">@csrf <div class="card-body">@include('master.Branches.form')</div><div class="card-footer text-end"><button class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan Branch / Site</button></div></form></div>
@endsection
