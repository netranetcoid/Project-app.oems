@extends('layouts.contentNavbarLayout')

@section('title', 'Master Shift')

@section('content')

<div class="row">

    <div class="col-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <div>

                    <h4 class="card-title mb-1">

                        Master Shift

                    </h4>

                    <small class="text-muted">

                        Kelola seluruh shift kerja perusahaan

                    </small>

                </div>

                @can('attendance.shift.create')

                <a href="{{ route('attendance.shifts.create') }}"
                    class="btn btn-primary">

                    <i class="ti ti-plus me-1"></i>

                    Tambah Shift

                </a>

                @endcan

            </div>

            <div class="card-body">

                @if(session('success'))

                <div class="alert alert-success">

                    {{ session('success') }}

                </div>

                @endif

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead>

                            <tr>

                                <th width="60">No</th>

                                <th>Kode</th>

                                <th>Nama Shift</th>

                                <th>Site</th>

                                <th>Jam Kerja</th>

                                <th>Status</th>

                                <th width="170">Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                        @forelse($shifts as $shift)

                        <tr>

                            <td>

                                {{ $loop->iteration }}

                            </td>

                            <td>

                                {{ $shift->code }}

                            </td>

                            <td>

                                {{ $shift->name }}

                            </td>

                            <td>

                                {{ $shift->branch?->name ?? 'Semua Site' }}

                            </td>

                            <td>

                                {{ substr($shift->clock_in_time,0,5) }}

                                -

                                {{ substr($shift->clock_out_time,0,5) }}

                            </td>

                            <td>

                                @if($shift->status=='active')

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
                                                              @can('attendance.shift.update')

                                <a href="{{ route('attendance.shifts.edit',$shift) }}"
                                    class="btn btn-sm btn-warning">

                                    <i class="ti ti-edit"></i>

                                </a>

                                @endcan

                                @can('attendance.shift.delete')

                                <form
                                    action="{{ route('attendance.shifts.destroy',$shift) }}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus shift ini?')">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-danger">

                                        <i class="ti ti-trash"></i>

                                    </button>

                                </form>

                                @endcan

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="7"
                                class="text-center text-muted">

                                Belum ada data Shift.

                            </td>

                        </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="mt-3">

                    {{ $shifts->links() }}

                </div>

            </div>

        </div>

    </div>

</div>

@endsection