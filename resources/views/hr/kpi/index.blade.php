@extends('layouts.contentNavbarLayout')

@section('title', 'KPI Pegawai')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="mb-1">KPI Pegawai</h4>
            <p class="text-muted mb-0">Nilai kinerja, approval, dan bonus KPI per periode.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('hr.kpi.aspects') }}" class="btn btn-label-primary"><i class="ti ti-list-details"></i> Master Aspek</a>
            <a href="{{ route('hr.kpi.standards.create') }}" class="btn btn-label-primary"><i class="ti ti-target-arrow"></i> Standar Jabatan</a>
            <a href="{{ route('hr.kpi.assessments.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Nilai KPI</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="row mb-4">
        @foreach(['aspects' => ['Aspek Aktif', 'primary', 'ti-list-check'], 'standards' => ['Standar Aktif', 'info', 'ti-target-arrow'], 'submitted' => ['Menunggu Approval', 'warning', 'ti-clock'], 'approved' => ['KPI Disetujui', 'success', 'ti-circle-check']] as $key => [$label, $color, $icon])
            <div class="col-sm-6 col-xl-3 mb-3">
                <div class="card h-100"><div class="card-body d-flex justify-content-between align-items-center">
                    <div><small class="text-muted">{{ $label }}</small><h3 class="mb-0">{{ $stats[$key] }}</h3></div>
                    <span class="avatar"><span class="avatar-initial rounded bg-label-{{ $color }}"><i class="ti {{ $icon }}"></i></span></span>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Riwayat Penilaian</h5></div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead><tr><th>Periode</th><th>Pegawai</th><th>Jabatan</th><th>Nilai</th><th>Grade</th><th>Bonus KPI</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($assessments as $assessment)
                        <tr>
                            <td>{{ sprintf('%02d', $assessment->period_month) }}/{{ $assessment->period_year }}</td>
                            <td><strong>{{ $assessment->employee?->name }}</strong></td>
                            <td>{{ $assessment->position?->name ?? '-' }}</td>
                            <td>{{ number_format((float) $assessment->total_score, 2) }}</td>
                            <td><span class="badge bg-label-{{ $assessment->grade === 'A' ? 'success' : ($assessment->grade === 'D' ? 'danger' : 'warning') }}">{{ $assessment->grade }}</span></td>
                            <td>Rp {{ number_format((float) $assessment->bonus_amount, 0, ',', '.') }}</td>
                            <td><span class="badge bg-label-{{ $assessment->status === 'approved' ? 'success' : ($assessment->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($assessment->status) }}</span></td>
                            <td><a href="{{ route('hr.kpi.assessments.show', $assessment) }}" class="btn btn-sm btn-label-primary">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-5 text-muted">Belum ada penilaian KPI.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $assessments->links() }}</div>
    </div>
</div>
@endsection
