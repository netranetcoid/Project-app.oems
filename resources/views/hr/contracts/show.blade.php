@extends('layouts.contentNavbarLayout')

@section('title','Detail Kontrak')

@section('content')

<div class="container-fluid">

    {{-- Aksi dokumen disediakan di detail kontrak agar HR/management dapat
         langsung membuka PDF resmi atau print preview tanpa kembali ke tabel. --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="mb-1">Detail Kontrak</h4>
            <span class="text-muted">{{ $contract->contract_no }}</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('hr.contracts.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i>Kembali
            </a>
            <a href="{{ route('hr.contracts.print', $contract) }}" target="_blank" class="btn btn-outline-primary">
                <i class="ti ti-printer me-1"></i>Preview Cetak
            </a>
            <a href="{{ route('hr.contracts.pdf', $contract) }}" target="_blank" class="btn btn-danger">
                <i class="ti ti-file-type-pdf me-1"></i>Cetak PDF
            </a>
        </div>
    </div>

    <div class="row">

        {{-- PROFILE --}}
        <div class="col-xl-4">

            <div class="card">

                <div class="card-body text-center">

                   @if($contract->employee && $contract->employee->photo)

<img
    src="{{ asset('storage/'.$contract->employee->photo) }}"
    class="rounded-circle mb-3"
    width="120"
    height="120"
    style="object-fit:cover;">

@else

<img
    src="{{ asset('assets/img/avatars/1.png') }}"
    class="rounded-circle mb-3"
    width="120"
    height="120"
    style="object-fit:cover;">

@endif

                    <h4>

                        {{ $contract->employee_name }}

                    </h4>

                    <p class="text-muted">

                        {{ $contract->position_name }}

                    </p>

                    <span class="badge bg-{{ $contract->status_badge }}">

                        {{ $contract->status_label }}

                    </span>

                    <hr>

                    <div class="text-start">

                        <p>

                            <strong>No Kontrak</strong><br>

                            {{ $contract->contract_no }}

                        </p>

                        <p>

                            <strong>Jenis</strong><br>

                            {{ $contract->contractType?->name }}

                        </p>

                        <p>

                            <strong>Mulai</strong><br>

                            {{ optional($contract->start_date)->format('d M Y') }}

                        </p>

                        <p>

                            <strong>Berakhir</strong><br>

                            @if($contract->contractType?->is_probation)
                                PKWTT - tidak berakhir otomatis
                            @else
                                {{ optional($contract->end_date)->format('d M Y') }}
                            @endif

                        </p>

                        @if($contract->contractType?->is_probation)
                        <p>
                            <strong>Akhir Evaluasi Probation</strong><br>
                            {{ optional($contract->probation_end_date)->format('d M Y') }}
                        </p>
                        @endif

                        <p>

                            <strong>Sisa Hari</strong><br>

                            {{ $contract->remaining_days }}

                        </p>

                    </div>

                </div>

            </div>

        </div>

        {{-- DETAIL --}}
        <div class="col-xl-8">

            <div class="card">

                <div class="card-header">

                    <h5>

                        Informasi Kontrak

                    </h5>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Nomor Surat

                            </label>

                            <div>

                                {{ $contract->letter_no ?? '-' }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Lama Kontrak

                            </label>

                            <div>

                                {{ $contract->duration_month }} Bulan

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Site

                            </label>

                            <div>

                                {{ $contract->branch_name }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Divisi

                            </label>

                            <div>

                                {{ $contract->division_name }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Jabatan

                            </label>

                            <div>

                                {{ $contract->position_name }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Gaji Pokok

                            </label>

                            <div>

                                Rp {{ number_format($contract->basic_salary,0,',','.') }}

                            </div>

                        </div>

                    </div>

                    <hr>

                    <h6>

                        Catatan

                    </h6>

                    {{ $contract->notes ?: '-' }}

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
