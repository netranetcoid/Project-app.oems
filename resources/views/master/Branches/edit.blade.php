@extends('layouts.contentNavbarLayout')

@section('title', 'Edit Site')

@section('content')

<div class="row">
    <div class="col-xl-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <div>
                    <h4 class="card-title mb-1">Edit Site</h4>
                    <small class="text-muted">
                        Perbarui informasi Site
                    </small>
                </div>

                <a href="{{ route('master.branches.index') }}"
                   class="btn btn-label-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Kembali
                </a>

            </div>

            <form action="{{ route('master.branches.update',$branch->id) }}"
                  method="POST">

                @csrf
                @method('PUT')

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company</label>

                            <select name="company_id"
                                    class="form-select @error('company_id') is-invalid @enderror">

                                @foreach($companies as $company)

                                    <option value="{{ $company->id }}"
                                        {{ old('company_id',$branch->company_id)==$company->id ? 'selected':'' }}>

                                        {{ $company->name }}

                                    </option>

                                @endforeach

                            </select>

                            @error('company_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Kode Site</label>

                            <input type="text"
                                   name="code"
                                   class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code',$branch->code) }}">

                            @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Nama Site</label>

                            <input type="text"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name',$branch->name) }}">

                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Tipe Site</label>

                            <select name="type" class="form-select">

                                <option value="head_office" {{ old('type',$branch->type)=='head_office'?'selected':'' }}>Head Office</option>

                                <option value="branch" {{ old('type',$branch->type)=='branch'?'selected':'' }}>Branch</option>

                                <option value="site" {{ old('type',$branch->type)=='site'?'selected':'' }}>Site</option>

                                <option value="warehouse" {{ old('type',$branch->type)=='warehouse'?'selected':'' }}>Warehouse</option>

                                <option value="project" {{ old('type',$branch->type)=='project'?'selected':'' }}>Project</option>

                            </select>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Email</label>

                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="{{ old('email',$branch->email) }}">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Telepon</label>

                            <input type="text"
                                   name="phone"
                                   class="form-control"
                                   value="{{ old('phone',$branch->phone) }}">

                        </div>

                        <div class="col-md-12 mb-3">

                            <label class="form-label">Alamat</label>

                            <textarea name="address"
                                      rows="3"
                                      class="form-control">{{ old('address',$branch->address) }}</textarea>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Status</label>

                            <select name="status"
                                    class="form-select">

                                <option value="active" {{ old('status',$branch->status)=='active'?'selected':'' }}>Active</option>

                                <option value="inactive" {{ old('status',$branch->status)=='inactive'?'selected':'' }}>Inactive</option>

                                <option value="closed" {{ old('status',$branch->status)=='closed'?'selected':'' }}>Closed</option>

                            </select>

                        </div>

                    </div>

                </div>

                <div class="card-footer text-end">

                    <button class="btn btn-label-secondary"
                            type="reset">
                        Reset
                    </button>

                    <button class="btn btn-primary"
                            type="submit">

                        <i class="ti ti-device-floppy me-1"></i>

                        Update Site

                    </button>

                </div>

            </form>

        </div>

    </div>
</div>

@endsection