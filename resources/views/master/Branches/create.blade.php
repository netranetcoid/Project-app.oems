@extends('layouts.contentNavbarLayout')

@section('title', 'Tambah Site')

@section('content')

<div class="row">

    <div class="col-xl-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <div>
                    <h4 class="card-title mb-1">
                        Tambah Site
                    </h4>

                    <small class="text-muted">
                        Tambahkan Site baru perusahaan
                    </small>
                </div>

                <a href="{{ route('master.branches.index') }}"
                   class="btn btn-label-secondary">

                    <i class="ti ti-arrow-left me-1"></i>

                    Kembali

                </a>

            </div>

            <form action="{{ route('master.branches.store') }}"
                  method="POST">

                @csrf

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Company <span class="text-danger">*</span>
                            </label>

                            <select
                                name="company_id"
                                class="form-select @error('company_id') is-invalid @enderror">

                                <option value="">Pilih Company</option>

                                @foreach($companies as $company)

                                    <option value="{{ $company->id }}"
                                        {{ old('company_id')==$company->id?'selected':'' }}>

                                        {{ $company->name }}

                                    </option>

                                @endforeach

                            </select>

                            @error('company_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Kode Site <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="code"
                                value="{{ old('code') }}"
                                class="form-control @error('code') is-invalid @enderror">

                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Nama Site <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                class="form-control @error('name') is-invalid @enderror">

                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Tipe Site
                            </label>

                            <select
                                name="type"
                                class="form-select">

                                <option value="head_office">Head Office</option>
                                <option value="branch">Branch</option>
                                <option value="site">Site</option>
                                <option value="warehouse">Warehouse</option>
                                <option value="project">Project</option>

                            </select>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="form-control">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Telepon
                            </label>

                            <input
                                type="text"
                                name="phone"
                                value="{{ old('phone') }}"
                                class="form-control">

                        </div>

                        <div class="col-md-12 mb-3">

                            <label class="form-label">
                                Alamat
                            </label>

                            <textarea
                                name="address"
                                rows="3"
                                class="form-control">{{ old('address') }}</textarea>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Status
                            </label>

                            <select
                                name="status"
                                class="form-select">

                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="closed">Closed</option>

                            </select>

                        </div>

                    </div>

                </div>

                <div class="card-footer text-end">

                    <button
                        type="reset"
                        class="btn btn-label-secondary">

                        Reset

                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="ti ti-device-floppy me-1"></i>

                        Simpan Site

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection