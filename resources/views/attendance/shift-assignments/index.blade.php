@extends('layouts.contentNavbarLayout')

@section('title', 'Jadwal Shift')

@section('content')

<div class="row">

    <div class="col-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <div>

                    <h4 class="mb-1">
                        Jadwal Shift Pegawai
                    </h4>

                    <small class="text-muted">
                        Kelola jadwal shift seluruh pegawai
                    </small>

                </div>

                @can('attendance.shift.assignment.create')

                <a href="{{ route('attendance.shift-assignments.create') }}"
                    class="btn btn-primary">

                    <i class="ti ti-plus me-1"></i>

                    Tambah Jadwal

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

                    <table class="table table-bordered align-middle">

                        <thead>

                        <tr>

                            <th>No</th>

                            <th>Pegawai</th>

                            <th>Site</th>

                            <th>Shift</th>

                            <th>Periode</th>

                            <th>Status</th>

                            <th width="150">Aksi</th>

                        </tr>

                        </thead>

                        <tbody>

                        @forelse($assignments as $item)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $item->employee->full_name }}</td>

                            <td>{{ $item->branch?->name ?? '-' }}</td>

                            <td>{{ $item->shift->name }}</td>

                            <td>

                                {{ $item->start_date->format('d M Y') }}

                                -

                                {{ optional($item->end_date)->format('d M Y') ?? '-' }}

                            </td>

                            <td>

                                @if($item->status=='active')

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

                                <a href="#"

                                    class="btn btn-warning btn-sm">

                                    <i class="ti ti-edit"></i>

                                </a>

                                <button

                                    class="btn btn-danger btn-sm">

                                    <i class="ti ti-trash"></i>

                                </button>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="7"

                                class="text-center text-muted">

                                Belum ada Jadwal Shift.

                            </td>

                        </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="mt-3">

                    {{ $assignments->links() }}

                </div>

            </div>

        </div>

    </div>

</div>

@endsection