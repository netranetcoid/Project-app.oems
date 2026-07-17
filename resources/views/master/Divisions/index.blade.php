@extends('layouts.contentNavbarLayout')

@section('title', 'Master Division')

@section('content')

<div class="row">

    <div class="col-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <div>

                    <h4 class="card-title mb-1">
                        Master Division
                    </h4>

                    <small class="text-muted">
                        Kelola seluruh Division perusahaan
                    </small>

                </div>

                @can('division.create')

                <a href="{{ route('master.divisions.create') }}"
                    class="btn btn-primary">

                    <i class="ti ti-plus me-1"></i>

                    Tambah Division

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
                                <th>Division</th>
                                <th>Parent</th>
                                <th>Company</th>
                                <th>Status</th>
                                <th width="150">Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($divisions as $division)

                            <tr>

                                <td>
                                    {{ $loop->iteration + ($divisions->firstItem() - 1) }}
                                </td>

                                <td>{{ $division->code }}</td>

                                <td>{{ $division->name }}</td>

                                <td>{{ $division->parent?->name ?? '-' }}</td>

                                <td>{{ $division->company?->name }}</td>

                                <td>

                                    @if($division->status=='active')

                                    <span class="badge bg-label-success">
                                        Active
                                    </span>

                                    @else

                                    <span class="badge bg-label-danger">
                                        Inactive
                                    </span>

                                    @endif

                                </td>

                                <td>

                                    <div class="d-flex gap-1">

                                        @can('division.update')

                                        <a href="{{ route('master.divisions.edit',$division) }}"
                                            class="btn btn-sm btn-icon btn-label-warning">

                                            <i class="ti ti-edit"></i>

                                        </a>

                                        @endcan

                                        @can('division.delete')

                                        <form
                                            action="{{ route('master.divisions.destroy',$division) }}"
                                            method="POST"
                                            onsubmit="return confirm('Hapus Division ini?')">

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

                                <td colspan="7"
                                    class="text-center py-4">

                                    Belum ada data Division.

                                </td>

                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                @if($divisions->hasPages())

                <div class="mt-3">

                    {{ $divisions->links() }}

                </div>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection