@extends('layouts.contentNavbarLayout')

@section('title', 'Edit Pegawai')

@section('content')

<div class="container-fluid">

    <div class="row">

        <div class="col-12">

            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <div>

                        <h4 class="mb-1">

                            <i class="ti ti-edit me-2"></i>

                            Edit Pegawai

                        </h4>

                        <small class="text-muted">

                            Perbarui informasi pegawai pada sistem OEMS.

                        </small>

                    </div>

                    <a
                        href="{{ route('employees.index') }}"
                        class="btn btn-outline-secondary">

                        <i class="ti ti-arrow-left"></i>

                        Kembali

                    </a>

                </div>

                <form
                    action="{{ route('employees.update', $employee) }}"
                    method="POST"
                    enctype="multipart/form-data">

                    @csrf

                    @method('PUT')

                    <div class="card-body">

                        @include('master.employees._form')

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection