@php
  $configData = Helper::appClasses();
  $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Pilih Company')

@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
  <div class="authentication-wrapper authentication-cover">
    <div class="authentication-inner row m-0">
      <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center p-5">
        <div class="w-100 text-center">
          <div class="mb-4">
            <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 48])</span>
          </div>
          <h3 class="mb-2">Pilih Company Aktif</h3>
          <p class="text-muted mb-0">
            Semua akses, role, permission, dan data akan mengikuti company yang dipilih.
          </p>
        </div>
      </div>

      <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg p-sm-5 p-4">
        <div class="w-px-400 mx-auto">
          <div class="app-brand mb-4">
            <a href="{{ url('/') }}" class="app-brand-link gap-2">
              <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 32])</span>
              <span class="app-brand-text demo text-heading fw-bold">OEMS</span>
            </a>
          </div>

          <h4 class="mb-1">Company Access</h4>
          <p class="mb-4 text-muted">Pilih company yang ingin digunakan sekarang.</p>

          @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
              @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
              @endforeach
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          @endif

          <form action="{{ route('company.set') }}" method="POST">
            @csrf

            <div class="list-group mb-4">
              @foreach ($companies as $company)
                <label class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                  <input class="form-check-input m-0" type="radio" name="company_id" value="{{ $company->id }}"
                    @checked($loop->first)>
                  <span class="flex-grow-1">
                    <span class="fw-medium d-block">
                      {{ $company->name ?? ($company->company_name ?? 'Company') }}
                    </span>
                    <small class="text-muted">
                      ID Company: {{ $company->id }}
                    </small>
                  </span>
                  <span class="badge bg-label-success">Active</span>
                </label>
              @endforeach
            </div>

            <button type="submit" class="btn btn-primary d-grid w-100 mb-3">
              Gunakan Company
            </button>
          </form>

          <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-label-secondary d-grid w-100">
              Logout
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
