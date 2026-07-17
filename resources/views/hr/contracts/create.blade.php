@extends('layouts.contentNavbarLayout')

@section('title','Tambah Kontrak Pegawai')

@section('content')

<div class="container-fluid">

    <form
        method="POST"
        action="{{ route('hr.contracts.store') }}">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <div>

                    <h4 class="mb-0">

                        Tambah Kontrak Pegawai

                    </h4>

                    <small class="text-muted">

                        Data kontrak pegawai OEMS

                    </small>

                </div>

            </div>

            <div class="card-body">

                @include('hr.contracts._form')

            </div>

        </div>

    </form>

</div>

@endsection