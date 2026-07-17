@php
  $configData = Helper::appClasses();
  $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Login')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/pages-auth.js'])
@endsection

@section('content')
  <div class="authentication-wrapper authentication-cover">
    <div class="authentication-inner row m-0">
      <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center p-5">
        <div class="w-100 d-flex justify-content-center">
          <div class="text-center">
            <div class="mb-4">
              <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 48])</span>
            </div>
            <h3 class="mb-2">Selamat Datang</h3>
            <p class="text-muted mb-0">
              Masuk ke sistem operasional perusahaan dengan aman.
            </p>
          </div>
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

          <h4 class="mb-1">Login Akun</h4>
          <p class="mb-4 text-muted">Gunakan email atau username yang sudah terdaftar di sistem.</p>

          @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          @endif

          @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
              <div class="fw-medium mb-1">Login gagal</div>
              <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          @endif

          <form id="formAuthentication" class="mb-3" action="{{ route('login.proses') }}" method="POST">
            @csrf

            <div class="mb-3">
              <label for="email" class="form-label">Email / Username</label>
              <input type="text" class="form-control @error('email') is-invalid @enderror" id="email"
                name="email" value="{{ old('email') }}" placeholder="developer atau nama@email.com" autocomplete="username" autofocus>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3 form-password-toggle">
              <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Password</label>
              </div>
              <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control @error('password') is-invalid @enderror"
                  name="password" placeholder="••••••••••••" autocomplete="current-password">
                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                @error('password')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                <label class="form-check-label" for="remember">
                  Ingat saya
                </label>
              </div>
            </div>

            <button class="btn btn-primary d-grid w-100 mb-3" type="submit">
              Masuk
            </button>

            <a href="{{ route('google.login') }}" class="btn btn-label-danger d-grid w-100">
              <span class="d-flex align-items-center justify-content-center">
                <i class="ti ti-brand-google me-2"></i>
                Masuk dengan Google
              </span>
            </a>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
