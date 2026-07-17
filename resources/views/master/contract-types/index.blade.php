@extends('layouts.contentNavbarLayout')

@section('title', 'Master Jenis Kontrak')

@section('content')

<div class="container-fluid">

    {{-- Statistik --}}
    <div class="row mb-4">

        <div class="col-md-4">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Total Jenis Kontrak
                            </small>

                            <h3 class="mb-0">
                                {{ $stats['total'] }}
                            </h3>

                        </div>

                        <div class="avatar">

                            <span class="avatar-initial rounded bg-primary">

                                <i class="ti ti-file-description"></i>

                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Aktif
                            </small>

                            <h3 class="text-success mb-0">

                                {{ $stats['active'] }}

                            </h3>

                        </div>

                        <div class="avatar">

                            <span class="avatar-initial rounded bg-success">

                                <i class="ti ti-check"></i>

                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Non Aktif
                            </small>

                            <h3 class="text-danger mb-0">

                                {{ $stats['inactive'] }}

                            </h3>

                        </div>

                        <div class="avatar">

                            <span class="avatar-initial rounded bg-danger">

                                <i class="ti ti-x"></i>

                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Tabel --}}
    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <div>

                <h4 class="mb-0">

                    Master Jenis Kontrak

                </h4>

                <small class="text-muted">

                    Digunakan oleh Modul Kontrak Pegawai

                </small>

            </div>

            <a href="{{ route('master.contract-types.create') }}"
               class="btn btn-primary">

                <i class="ti ti-plus"></i>

                Tambah

            </a>

        </div>

        <div class="card-body">

            <form method="GET"
                  class="row mb-3">

                <div class="col-md-4">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari kode / nama..."
                        value="{{ request('search') }}">

                </div>

                <div class="col-md-auto">

                    <button
                        class="btn btn-outline-primary">

                        Cari

                    </button>

                </div>

            </form>

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>

                    <tr>

                        <th>Kode</th>

                        <th>Nama</th>

                        <th>Durasi</th>

                        <th>Status</th>

                        <th width="130">Aksi</th>

                    </tr>

                    </thead>

                    <tbody>

                    @forelse($contractTypes as $item)

                        <tr>

                            <td>

                                {{ $item->code }}

                            </td>

                            <td>

                                <strong>

                                    {{ $item->name }}

                                </strong>

                            </td>

                            <td>

                                {{ $item->duration_label }}

                            </td>

                            <td>

                                <span class="badge bg-{{ $item->status_badge }}">

                                    {{ $item->status_label }}

                                </span>

                            </td>

                            <td>

                                <a href="{{ route('master.contract-types.edit',$item) }}"
                                   class="btn btn-warning btn-sm">

                                    <i class="ti ti-edit"></i>

                                </a>

                                <form
                                    action="{{ route('master.contract-types.destroy',$item) }}"
                                    method="POST"
                                    class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus jenis kontrak?')">

                                        <i class="ti ti-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5"
                                class="text-center">

                                Belum ada data.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-3">

                {{ $contractTypes->links() }}

            </div>

        </div>

    </div>

</div>

@endsection