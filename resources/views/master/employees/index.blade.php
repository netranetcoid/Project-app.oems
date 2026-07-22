@extends('layouts.contentNavbarLayout')

@section('title', 'Data Pegawai')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <div>
                <h4 class="mb-0">
                    <i class="ti ti-users me-2"></i>
                    Data Pegawai
                </h4>

                <small class="text-muted">
                    Master Data Pegawai OEMS
                </small>
            </div>

            <a href="{{ route('employees.create') }}"
               class="btn btn-primary">

                <i class="ti ti-plus"></i>

                Tambah Pegawai

            </a>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                    <tr>

                        <th width="70">Foto</th>

                        <th>Kode</th>

                        <th>Nama</th>

                        <th>Company / Branch / Site</th>

                        <th>Divisi</th>

                        <th>Jabatan</th>

                        <th>Status</th>

                        <th width="140">Aksi</th>

                    </tr>

                    </thead>

                    <tbody>

                    @forelse($employees as $employee)

                        <tr>

                            <td>

                                <img src="{{ $employee->photo_url }}"
                                     class="rounded-circle"
                                     width="45">

                            </td>

                            <td>

                                {{ $employee->employee_no }}

                            </td>

                            <td>

                                <strong>

                                    {{ $employee->name }}

                                </strong>

                                <br>

                                <small class="text-muted">

                                    {{ $employee->email }}

                                </small>

                            </td>

                            <td>

                                <strong>PT OSM</strong><br>
                                <small class="text-muted">{{ $employee->branch?->parent?->name ? $employee->branch->parent->name.' → ' : '' }}{{ $employee->branch?->name ?: 'Belum ditetapkan' }}</small>

                            </td>

                            <td>

                                {{ $employee->division?->name }}

                            </td>

                            <td>

                                {{ $employee->position?->name }}

                            </td>

                            <td>

                                @if($employee->work_status=='active')

                                    <span class="badge bg-success">

                                        Aktif

                                    </span>

                                @else

                                    <span class="badge bg-danger">

                                        Non Aktif

                                    </span>

                                @endif

                            </td>

                            <td>

    <div class="btn-group">

        <a href="{{ route('employees.show',$employee) }}"
           class="btn btn-sm btn-info"
           title="Detail Pegawai">

            <i class="ti ti-eye"></i>

        </a>

        <a href="{{ route('employees.edit',$employee) }}"
           class="btn btn-sm btn-warning"
           title="Edit">

            <i class="ti ti-edit"></i>

        </a>

        <form method="POST"
              action="{{ route('employees.destroy',$employee) }}"
              class="d-inline">

            @csrf
            @method('DELETE')

            <button
                type="submit"
                class="btn btn-sm btn-danger"
                onclick="return confirm('Hapus pegawai?')">

                <i class="ti ti-trash"></i>

            </button>

        </form>

    </div>

</td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8"
                                class="text-center">

                                Belum ada data pegawai.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-3">

                {{ $employees->links() }}

            </div>

        </div>

    </div>

</div>

@endsection
