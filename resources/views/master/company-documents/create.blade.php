@extends('layouts.contentNavbarLayout')
@section('title', 'Tambah Master Dokumen')
@section('content')<div class="container-fluid"><div class="card"><div class="card-header"><h4 class="mb-0">Tambah Master Dokumen</h4></div><div class="card-body"><form method="POST" action="{{ route('master.company-documents.store') }}">@csrf @include('master.company-documents._form')</form></div></div></div>@endsection
