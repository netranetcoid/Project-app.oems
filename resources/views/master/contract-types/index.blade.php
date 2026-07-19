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

                    Kelola seluruh dokumen template perusahaan. Kontrak pegawai yang sudah terbit tetap memakai snapshot versinya sendiri.

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

                        <th>Nama / Kategori</th>

                        <th>Durasi</th>

                        <th>Versi</th>

                        <th>Status</th>

                        <th>Diperbarui</th>

                        <th width="210">Aksi</th>

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

                                <div class="small text-muted mt-1">
                                    {{ $templateReferences[$item->template_key]['label'] ?? ($item->template_key ? str($item->template_key)->replace('_', ' ')->title() : 'Kategori custom') }}
                                </div>

                                @if(blank($item->template_body))
                                    <span class="badge bg-label-warning mt-1">Draf belum diisi</span>
                                @else
                                    <span class="badge bg-label-success mt-1">Template siap diedit</span>
                                @endif

                            </td>

                            <td>

                                {{ $item->duration_label }}

                            </td>

                            <td>

                                <span class="badge bg-label-info">v{{ $item->template_version ?: 1 }}</span>

                            </td>

                            <td>

                                <span class="badge bg-{{ $item->status_badge }}">

                                    {{ $item->status_label }}

                                </span>

                            </td>

                            <td>

                                <span class="small text-muted">
                                    {{ optional($item->updated_at)->format('d M Y H:i') ?? '-' }}
                                </span>

                            </td>

                            <td>

                                <a href="{{ route('master.contract-types.edit',$item) }}"
                                   class="btn btn-primary btn-sm">

                                    <i class="ti ti-file-pencil me-1"></i> Edit Template

                                </a>

                                <form
                                    action="{{ route('master.contract-types.destroy',$item) }}"
                                    method="POST"
                                    class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        class="btn btn-label-danger btn-sm"
                                        onclick="return confirm('Hapus jenis kontrak?')">

                                        <i class="ti ti-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7"
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
