@extends('layouts.contentNavbarLayout')

@section('title', 'Edit Shift')

@section('content')

<form action="{{ route('attendance.shifts.update', $shift) }}" method="POST">

    @csrf
    @method('PUT')

    <div class="row">

        <div class="col-12">

            <div class="card">

                <div class="card-header">

                    <h4 class="mb-0">

                        Edit Shift

                    </h4>

                    <small class="text-muted">

                        Perbarui Master Shift

                    </small>

                </div>

                <div class="card-body">

                    <div class="row g-3">

                        {{-- Site --}}
                        <div class="col-md-6">

                            <label class="form-label">

                                Site

                            </label>

                            <select
                                name="branch_id"
                                class="form-select">

                                <option value="">
                                    Semua Site
                                </option>

                                @foreach($branches as $branch)

                                <option
                                    value="{{ $branch->id }}"
                                    @selected(old('branch_id', $shift->branch_id) == $branch->id)>

                                    {{ $branch->name }}

                                </option>

                                @endforeach

                            </select>

                        </div>

                        {{-- Code --}}
                        <div class="col-md-3">

                            <label class="form-label">

                                Kode Shift

                            </label>

                            <input
                                type="text"
                                name="code"
                                value="{{ old('code', $shift->code) }}"
                                class="form-control"
                                readonly>

                        </div>

                        {{-- Name --}}
                        <div class="col-md-3">

                            <label class="form-label">

                                Nama Shift

                            </label>

                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $shift->name) }}"
                                class="form-control"
                                required>

                        </div>

                        {{-- Work Type --}}
                        <div class="col-md-4">

                            <label class="form-label">

                                Jenis Shift

                            </label>

                            <select
                                name="work_type"
                                class="form-select">

                                <option value="office"
                                    @selected($shift->work_type=='office')>

                                    Office

                                </option>

                                <option value="shift"
                                    @selected($shift->work_type=='shift')>

                                    Shift

                                </option>

                                <option value="flexible"
                                    @selected($shift->work_type=='flexible')>

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
                                type="time"
                                name="clock_in_time"
                                value="{{ old('clock_in_time', $shift->clock_in_time) }}"
                                class="form-control">

                        </div>

                        {{-- Clock Out --}}
                        <div class="col-md-4">

                            <label class="form-label">

                                Jam Pulang

                            </label>

                            <input
                                type="time"
                                name="clock_out_time"
                                value="{{ old('clock_out_time', $shift->clock_out_time) }}"
                                class="form-control">

                        </div>

                        {{-- Break Start --}}
                        <div class="col-md-3">

                            <label class="form-label">

                                Mulai Istirahat

                            </label>

                            <input
                                type="time"
                                name="break_start"
                                value="{{ old('break_start', $shift->break_start) }}"
                                class="form-control">

                        </div>

                        {{-- Break End --}}
                        <div class="col-md-3">

                            <label class="form-label">

                                Selesai Istirahat

                            </label>

                            <input
                                type="time"
                                name="break_end"
                                value="{{ old('break_end', $shift->break_end) }}"
                                class="form-control">

                        </div>

                        {{-- Work Hours --}}
                        <div class="col-md-3">

                            <label class="form-label">

                                Jam Kerja

                            </label>

                            <input
                                type="number"
                                name="work_hours"
                                value="{{ old('work_hours', $shift->work_hours) }}"
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
                                value="{{ old('grace_in_minutes', $shift->grace_in_minutes) }}"
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
                                value="{{ old('grace_out_minutes', $shift->grace_out_minutes) }}"
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
                                value="{{ old('late_tolerance_minutes', $shift->late_tolerance_minutes) }}"
                                class="form-control">

                        </div>

                        {{-- Overtime After --}}
                        <div class="col-md-3">

                            <label class="form-label">

                                Lembur Setelah (Menit)

                            </label>

                            <input
                                type="number"
                                name="overtime_after_minutes"
                                value="{{ old('overtime_after_minutes', $shift->overtime_after_minutes) }}"
                                class="form-control">

                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Maksimum Lembur / Hari (Menit)</label>
                            <input type="number" name="overtime_max_minutes" value="{{ old('overtime_max_minutes', $shift->overtime_max_minutes ?? 180) }}" min="1" max="720" class="form-control">
                            <small class="text-muted">Default 180 menit / 3 jam.</small>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-3">

                            <label class="form-label">

                                Status

                            </label>

                            <select
                                name="status"
                                class="form-select">

                                <option value="active"
                                    @selected(old('status', $shift->status) == 'active')>

                                    Active

                                </option>

                                <option value="inactive"
                                    @selected(old('status', $shift->status) == 'inactive')>

                                    Inactive

                                </option>

                            </select>

                        </div>

                        {{-- GPS --}}
                        <div class="col-md-3">

                            <div class="form-check mt-4">

                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    name="gps_required"
                                    value="1"
                                    @checked(old('gps_required', $shift->gps_required))>

                                <label class="form-check-label">

                                    Wajib GPS

                                </label>

                            </div>

                        </div>

                        {{-- Selfie --}}
                        <div class="col-md-3">

                            <div class="form-check mt-4">

                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    name="selfie_required"
                                    value="1"
                                    @checked(old('selfie_required', $shift->selfie_required))>

                                <label class="form-check-label">

                                    Wajib Selfie

                                </label>

                            </div>

                        </div>

                        {{-- Photo --}}
                        <div class="col-md-3">

                            <div class="form-check mt-4">

                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    name="photo_required"
                                    value="1"
                                    @checked(old('photo_required', $shift->photo_required))>

                                <label class="form-check-label">

                                    Wajib Foto Lokasi

                                </label>

                            </div>

                        </div>

                        {{-- Allow Overtime --}}
                        <div class="col-md-3">

                            <div class="form-check mt-4">

                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    name="allow_overtime"
                                    value="1"
                                    @checked(old('allow_overtime', $shift->allow_overtime))>

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
                                class="form-control">{{ old('notes', $shift->notes) }}</textarea>

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

                        Update Shift

                    </button>

                </div>

            </div>

        </div>

    </div>

</form>

@endsection
