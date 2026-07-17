@extends('layouts.contentNavbarLayout')

@section('title', 'Tambah Division')

@section('content')

<form action="{{ route('master.divisions.store') }}" method="POST">

    @csrf

    <div class="row">

        <div class="col-12">

            <div class="card">

                <div class="card-header">

                    <h4 class="mb-0">
                        Tambah Division
                    </h4>

                    <small class="text-muted">
                        Tambah data Division baru
                    </small>

                </div>

                <div class="card-body">

                    <div class="row g-3">

                        {{-- Company --}}

                        <div class="col-md-6">

                            <label class="form-label">
                                Company
                            </label>

                            <select
                                name="company_id"
                                class="form-select @error('company_id') is-invalid @enderror">

                                <option value="">
                                    -- Pilih Company --
                                </option>

                                @foreach($companies as $company)

                                <option
                                    value="{{ $company->id }}"
                                    @selected(old('company_id')==$company->id)>

                                    {{ $company->name }}

                                </option>

                                @endforeach

                            </select>

                            @error('company_id')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                            @enderror

                        </div>

                        {{-- Parent Division --}}

                        <div class="col-md-6">

                            <label class="form-label">

                                Parent Division

                            </label>

                            <select
                                name="parent_id"
                                class="form-select">

                                <option value="">

                                    -- Root Division --

                                </option>

                                @foreach($parents as $parent)

                                <option
                                    value="{{ $parent->id }}"
                                    @selected(old('parent_id')==$parent->id)>

                                    {{ $parent->name }}

                                </option>

                                @endforeach

                            </select>

                        </div>

                        {{-- Code --}}

                        <div class="col-md-4">

                            <label class="form-label">

                                Kode Division

                            </label>

                            <input
                                type="text"
                                name="code"
                                value="{{ old('code') }}"
                                class="form-control @error('code') is-invalid @enderror">

                            @error('code')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                            @enderror

                        </div>

                        {{-- Name --}}

                        <div class="col-md-8">

                            <label class="form-label">

                                Nama Division

                            </label>

                            <input
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                class="form-control @error('name') is-invalid @enderror">

                            @error('name')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                            @enderror

                        </div>

                        {{-- Type --}}

                        <div class="col-md-6">

                            <label class="form-label">

                                Tipe Division

                            </label>

                            <input
                                type="text"
                                name="type"
                                value="{{ old('type') }}"
                                class="form-control">

                        </div>

                        {{-- Head Division --}}

                        <div class="col-md-6">

                            <label class="form-label">

                                Kepala Division

                            </label>

                            <input
                                type="text"
                                name="head_name"
                                value="{{ old('head_name') }}"
                                class="form-control">

                        </div>
                                                {{-- Description --}}

                        <div class="col-12">

                            <label class="form-label">

                                Deskripsi

                            </label>

                            <textarea
                                name="description"
                                rows="4"
                                class="form-control">{{ old('description') }}</textarea>

                        </div>

                        {{-- Status --}}

                        <div class="col-md-4">

                            <label class="form-label">

                                Status

                            </label>

                            <select
                                name="status"
                                class="form-select @error('status') is-invalid @enderror">

                                <option value="active"
                                    @selected(old('status','active')=='active')>

                                    Active

                                </option>

                                <option value="inactive"
                                    @selected(old('status')=='inactive')>

                                    Inactive

                                </option>

                            </select>

                            @error('status')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                            @enderror

                        </div>

                    </div>

                </div>

                <div class="card-footer d-flex justify-content-between">

                    <a href="{{ route('master.divisions.index') }}"
                        class="btn btn-label-secondary">

                        <i class="ti ti-arrow-left me-1"></i>

                        Kembali

                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="ti ti-device-floppy me-1"></i>

                        Simpan Division

                    </button>

                </div>

            </div>

        </div>

    </div>

</form>

@endsection