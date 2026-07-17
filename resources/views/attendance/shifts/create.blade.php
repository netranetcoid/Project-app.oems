@extends('layouts.contentNavbarLayout')

@section('title', 'Tambah Shift')

@section('content')

<form action="{{ route('attendance.shifts.store') }}" method="POST">

    @csrf

    <div class="row">

        <div class="col-12">

            <div class="card">

                <div class="card-header border-bottom">

    <nav aria-label="breadcrumb">

        <ol class="breadcrumb breadcrumb-style1 mb-2">

            <li class="breadcrumb-item">

                <a href="{{ route('attendance.index') }}">
                    Absensi
                </a>

            </li>

            <li class="breadcrumb-item">

                <a href="{{ route('attendance.shifts.index') }}">
                    Master Shift
                </a>

            </li>

            <li class="breadcrumb-item active">
                Tambah Shift
            </li>

        </ol>

    </nav>

    <h3 class="mb-1 fw-bold">

        <i class="ti ti-clock-plus text-primary me-2"></i>

        Tambah Master Shift

    </h3>

    <p class="text-muted mb-0">

        Buat jadwal kerja yang akan digunakan pada perusahaan.

    </p>

</div>

                <div class="card-body">

    <div class="card border shadow-sm mb-4">

        <div class="card-header bg-light">

            <h5 class="mb-0">

                Informasi Dasar

            </h5>

        </div>

        <div class="card-body">

            <div class="row g-4">

                        {{-- Site --}}

                        <div class="col-lg-4">

    <label class="form-label fw-semibold">

        Site

    </label>

    <select
        name="branch_id"
        class="form-select form-select-lg">

        <option value="">
            Semua Site
        </option>

        @foreach($branches as $branch)

        <option value="{{ $branch->id }}">
            {{ $branch->name }}
        </option>

        @endforeach

    </select>

    <small class="text-muted">

        Berlaku untuk seluruh site jika dikosongkan.

    </small>

</div>

                        {{-- Code --}}

                        <div class="col-lg-4">

    <label class="form-label fw-semibold">

        Kode Shift

    </label>

    <input
        type="text"
        name="code"
        class="form-control form-control-lg"
        placeholder="OFF001"
        required>

    <small class="text-muted">

        Contoh : OFF001

    </small>

</div>

                        {{-- Name --}}

                        <div class="col-lg-4">

    <label class="form-label fw-semibold">

        Nama Shift

    </label>

    <input
        type="text"
        name="name"
        class="form-control form-control-lg"
        placeholder="Office Reguler"
        required>

    <small class="text-muted">

        Nama yang tampil pada jadwal kerja.

    </small>

</div>
<div class="card border shadow-sm mb-4">

    <div class="card-header bg-light">

        <h5 class="mb-0">
            Detail Waktu
        </h5>

    </div>

    <div class="card-body">

        <div class="row g-4">

                        {{-- Work Type --}}

                        <div class="col-md-4">

                            <label class="form-label">

                                Jenis Shift

                            </label>

                            <select
                                name="work_type"
                                class="form-select">

                                <option value="office">

                                    Office

                                </option>

                                <option value="shift">

                                    Shift

                                </option>

                                <option value="flexible">

                                    Flexible

                                </option>

                            </select>

                        </div>

                        {{-- Clock In --}}

                        <div class="col-md-4">

                            <label class="form-label">

                                Jam Masuk

                            </label>

                          <input
    type="text"
    name="clock_in_time"
    class="form-control form-control-lg timepicker"
    placeholder="08:00"
    autocomplete="off"
    required>

                        </div>

                        {{-- Clock Out --}}

                        <div class="col-md-4">

                            <label class="form-label">

                                Jam Pulang

                            </label>

                           <input
    type="text"
    name="clock_out_time"
    class="form-control form-control-lg timepicker"
    placeholder="17:00"
    autocomplete="off"
    required>

                        </div>

                        {{-- Break Start --}}

                        <div class="col-md-3">

                            <label class="form-label">

                                Mulai Istirahat

                            </label>

                           <input
    type="text"
    name="break_start_time"
    class="form-control form-control-lg timepicker"
    placeholder="12:00"
    autocomplete="off"
    value="{{ old('break_start_time') }}">

                        </div>

                        {{-- Break End --}}

                        <div class="col-md-3">

                            <label class="form-label">

                                Selesai Istirahat

                            </label>

                            <input
    type="text"
    name="break_end_time"
    class="form-control form-control-lg timepicker"
    placeholder="13:00"
    autocomplete="off"
    value="{{ old('break_end_time') }}">
                        </div>

                        {{-- Work Hours --}}

                        <div class="col-md-3">

                            <label class="form-label">

                                Jam Kerja

                            </label>

                            <input
                                type="number"
                                name="work_hours"
                                value="8"
                                class="form-control">

                        </div>

                        {{-- Grace In --}}

                        <div class="col-md-3">

                            <label class="form-label">

                                Grace Masuk

                            </label>

                            <input
                                type="number"
                                name="grace_in_minutes"
                                value="15"
                                class="form-control">

                        </div>
                                                {{-- Grace Out --}}
                        <div class="col-md-3">

                            <label class="form-label">

                                Grace Pulang

                            </label>

                            <input
                                type="number"
                                name="grace_out_minutes"
                                value="0"
                                class="form-control">

                        </div>

                        {{-- Late Tolerance --}}
                        <div class="col-md-3">

                            <label class="form-label">

                                Toleransi Telat

                            </label>

                            <input
                                type="number"
                                name="late_tolerance_minutes"
                                value="0"
                                class="form-control">

                        </div>

                        {{-- Overtime --}}
                        <div class="col-md-3">

                            <label class="form-label">

                                Lembur Setelah (Menit)

                            </label>

                            <input
                                type="number"
                                name="overtime_after_minutes"
                                value="30"
                                class="form-control">

                        </div>

                        {{-- Status --}}
                        <div class="col-md-3">

                            <label class="form-label">

                                Status

                            </label>

                            <select
                                name="status"
                                class="form-select">

                                <option value="active">

                                    Active

                                </option>

                                <option value="inactive">

                                    Inactive

                                </option>

                            </select>

                        </div>

                        {{-- GPS --}}
                        <div class="col-md-4">

                            <div class="form-check mt-4">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="gps_required"
                                    value="1"
                                    checked>

                                <label class="form-check-label">

                                    Wajib GPS

                                </label>

                            </div>

                        </div>

                        {{-- Selfie --}}
                        <div class="col-md-4">

                            <div class="form-check mt-4">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="selfie_required"
                                    value="1"
                                    checked>

                                <label class="form-check-label">

                                    Wajib Selfie

                                </label>

                            </div>

                        </div>

                        {{-- Photo --}}
                        <div class="col-md-4">

                            <div class="form-check mt-4">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="photo_required"
                                    value="1">

                                <label class="form-check-label">

                                    Wajib Foto Lokasi

                                </label>

                            </div>

                        </div>

                        {{-- Allow Overtime --}}
                        <div class="col-md-4">

                            <div class="form-check mt-2">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="allow_overtime"
                                    value="1"
                                    checked>

                                <label class="form-check-label">

                                    Izinkan Lembur

                                </label>

                            </div>

                        </div>

                        {{-- Notes --}}
                        <div class="col-12">

                            <label class="form-label">

                                Catatan

                            </label>

                            <textarea
                                name="notes"
                                rows="4"
                                class="form-control"></textarea>

                        </div>

                    </div>

                </div>

                <div class="card-footer d-flex justify-content-between">

                    <a href="{{ route('attendance.shifts.index') }}"
                        class="btn btn-label-secondary">

                        <i class="ti ti-arrow-left me-1"></i>

                        Kembali

                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="ti ti-device-floppy me-1"></i>

                        Simpan Shift

                    </button>

                </div>

            </div>

        </div>

    </div>

</form>

@push('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {

    flatpickr(".timepicker", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minuteIncrement: 5
    });

});
</script>
@endpush

@endsection