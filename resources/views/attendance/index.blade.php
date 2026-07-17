@extends('layouts.contentNavbarLayout')

@section('title', 'Absensi')

@section('content')

<div class="row">

    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">

                <h3 class="mb-1">
                    <i class="ti ti-calendar-time text-primary me-2"></i>
                    Dashboard Absensi
                </h3>

                <p class="text-muted mb-0">
                    Kelola seluruh aktivitas absensi perusahaan.
                </p>

            </div>
        </div>
    </div>

</div>

<div class="row">

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">

                <i class="ti ti-users fs-1 text-primary"></i>

                <h5 class="mt-3">Master Shift</h5>

                <small class="text-muted">
                    Kelola jam kerja perusahaan
                </small>

                <div class="mt-3">
                    <a href="{{ route('attendance.shifts.index') }}"
                        class="btn btn-primary btn-sm w-100">
                        Buka
                    </a>
                </div>

            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">

                <i class="ti ti-calendar-event fs-1 text-success"></i>

                <h5 class="mt-3">Jadwal Kerja</h5>

                <small class="text-muted">
                    Segera tersedia
                </small>

                <div class="mt-3">
                    <button class="btn btn-outline-secondary btn-sm w-100" disabled>
                        Coming Soon
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">

                <i class="ti ti-beach fs-1 text-warning"></i>

                <h5 class="mt-3">Hari Libur</h5>

                <small class="text-muted">
                    Segera tersedia
                </small>

                <div class="mt-3">
                    <button class="btn btn-outline-secondary btn-sm w-100" disabled>
                        Coming Soon
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">

                <i class="ti ti-clipboard-check fs-1 text-danger"></i>

                <h5 class="mt-3">Approval</h5>

                <small class="text-muted">
                    Segera tersedia
                </small>

                <div class="mt-3">
                    <button class="btn btn-outline-secondary btn-sm w-100" disabled>
                        Coming Soon
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>

@endsection