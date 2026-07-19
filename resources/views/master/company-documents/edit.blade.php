@extends('layouts.contentNavbarLayout')
@section('title', 'Edit Master Dokumen')
@section('content')<div class="container-fluid"><div class="card"><div class="card-header"><h4 class="mb-0">Edit Master: {{ $companyDocument->name }} <span class="badge bg-label-info">v{{ $companyDocument->template_version }}</span></h4></div><div class="card-body"><form method="POST" action="{{ route('master.company-documents.update', $companyDocument) }}">@csrf @method('PUT') @include('master.company-documents._form')</form></div></div></div>@endsection
