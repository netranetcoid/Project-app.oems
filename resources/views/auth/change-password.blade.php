@extends('layouts.blankLayout')

@section('title', 'Ubah Kata Sandi')

@section('content')
<div class="container-xxl d-flex align-items-center justify-content-center min-vh-100 py-5">
  <div class="card shadow-sm" style="width:100%;max-width:520px">
    <div class="card-body p-4 p-md-5">
      <h3 class="mb-2">{{ $isRequired ? 'Ganti kata sandi pertama' : 'Ubah kata sandi' }}</h3>
      <p class="text-body-secondary mb-4">
        {{ $isRequired ? 'Kata sandi sementara wajib diganti sebelum Anda dapat membuka menu AppOEMS.' : 'Masukkan kata sandi lama dan kata sandi baru Anda.' }}
      </p>

      @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
      @endif

      <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
          <label class="form-label">Kata sandi lama</label>
          <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password">
          @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label class="form-label">Kata sandi baru</label>
          <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="8" autocomplete="new-password">
          @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
          <div class="form-text">Minimal 8 karakter dan harus berbeda dari kata sandi lama.</div>
        </div>
        <div class="mb-4">
          <label class="form-label">Konfirmasi kata sandi baru</label>
          <input type="password" name="password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
        </div>
        <button class="btn btn-primary w-100" type="submit">Simpan kata sandi baru</button>
      </form>

      @unless($isRequired)
        <a class="btn btn-label-secondary w-100 mt-3" href="{{ route('dashboard') }}">Kembali ke dashboard</a>
      @endunless
    </div>
  </div>
</div>
@endsection