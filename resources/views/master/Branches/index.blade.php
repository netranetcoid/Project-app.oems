@extends('layouts.contentNavbarLayout')

@section('title', 'Master Site')

@section('content')

<div class="row">

    <div class="col-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <div>

                    <h4 class="card-title mb-1">
                        Master Site
                    </h4>

                    <small class="text-muted">
                        Kelola seluruh Site perusahaan
                    </small>

                </div>

                @can('branch.create')

                <a href="{{ route('master.branches.create') }}"
                    class="btn btn-primary">

                    <i class="ti ti-plus me-1"></i>

                    Tambah Site

                </a>

                @endcan

            </div>

            <div class="card-body">

                @if(session('success'))

                <div class="alert alert-success alert-dismissible fade show">

                    {{ session('success') }}

                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>

                </div>

                @endif

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead>

                            <tr>

                                <th width="60">No</th>

                                <th>Kode</th>

                                <th>Nama Site</th>

                                <th>Company</th>

                                <th>Status</th>

                                <th width="150">Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($branches as $branch)

                            <tr>

                                <td>
                                    {{ $loop->iteration + ($branches->firstItem() - 1) }}
                                </td>

                                <td>
                                    {{ $branch->code }}
                                </td>

                                <td>
                                    {{ $branch->name }}
                                </td>

                                <td>
                                    {{ $branch->company?->name }}
                                </td>

                                <td>

                                    @switch($branch->status)

                                    @case('active')

                                    <span class="badge bg-label-success">
                                        Active
                                    </span>

                                    @break

                                    @case('inactive')

                                    <span class="badge bg-label-warning">
                                        Inactive
                                    </span>

                                    @break

                                    @default

                                    <span class="badge bg-label-danger">
                                        Closed
                                    </span>

                                    @endswitch

                                </td>

                                <td>

                                    <div class="d-flex gap-1">

                                        @can('branch.update')

                                        <a href="{{ route('master.branches.edit',$branch) }}"
                                            class="btn btn-sm btn-icon btn-label-warning">

                                            <i class="ti ti-edit"></i>

                                        </a>

                                        @endcan

                                        @can('branch.delete')

                                        <form action="{{ route('master.branches.destroy',$branch) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus Site ini?')">

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-icon btn-label-danger">

                                                <i class="ti ti-trash"></i>

                                            </button>

                                        </form>

                                        @endcan

                                    </div>

                                </td>

                            </tr>

                            @empty

                            <tr>

                                <td colspan="6"
                                    class="text-center py-4 text-muted">

                                    Belum ada data Site.

                                </td>

                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                @if($branches->hasPages())

                <div class="mt-3">

                    {{ $branches->links() }}

                </div>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection