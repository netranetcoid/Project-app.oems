@extends('layouts.contentNavbarLayout')

@section('title', 'Roadmap Perjalanan Karyawan')

@section('content')
<div class="container-fluid">
    {{-- Halaman kebijakan ini sengaja read-only agar status pegawai hanya berubah
         melalui proses HR/kontrak yang memiliki approval dan audit trail. --}}
    <div class="card mb-4 overflow-hidden" style="background: linear-gradient(135deg, #173f77 0%, #176baf 55%, #25a9c8 100%);">
        <div class="card-body p-4 p-lg-5 text-white">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="avatar avatar-lg"><span class="avatar-initial rounded-circle bg-white text-primary"><i class="ti ti-route-2 fs-3"></i></span></span>
                        <span class="badge bg-white text-primary">PT OSM</span>
                    </div>
                    <h2 class="text-white mb-2">Roadmap Perjalanan Karyawan</h2>
                    <p class="mb-0 opacity-75">Peta yang transparan dari pelamar sampai jenjang kepemimpinan. Acuan ini membantu karyawan dan HR memahami proses, evaluasi, serta peluang pengembangan.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-inline-flex align-items-center gap-2 rounded px-3 py-2" style="background: rgba(255,255,255,.16);">
                        <i class="ti ti-shield-check fs-4"></i>
                        <span class="small text-start">Karier berdasarkan<br><strong>Kinerja & Kompetensi</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-primary d-flex align-items-start gap-3 mb-4" role="alert">
        <i class="ti ti-info-circle fs-4"></i>
        <div><strong>Status hubungan kerja dan jenjang karier adalah dua hal berbeda.</strong><br><span class="small">PKWT/PKWTT ditetapkan sesuai kebutuhan perusahaan dan ketentuan hukum. Promosi ditentukan oleh KPI, kompetensi, integritas, kepemimpinan, dan kontribusi; bukan hanya masa kerja.</span></div>
    </div>

    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="avatar avatar-sm"><span class="avatar-initial rounded bg-label-primary"><i class="ti ti-map-2"></i></span></span>
        <div><h4 class="mb-0">Lima Tahap Perjalanan</h4><small class="text-muted">Dari proses seleksi hingga pengembangan karier.</small></div>
    </div>

    <div class="row g-4 mb-5">
        @foreach($stages as $stage)
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-top border-4 border-{{ $stage['color'] }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="avatar"><span class="avatar-initial rounded bg-label-{{ $stage['color'] }}"><i class="{{ $stage['icon'] }}"></i></span></span>
                            <span class="badge bg-label-{{ $stage['color'] }}">{{ $stage['number'] }}</span>
                        </div>
                        <h5 class="mb-1">{{ $stage['title'] }}</h5>
                        <p class="small text-{{ $stage['color'] }} mb-3"><i class="ti ti-clock me-1"></i>{{ $stage['duration'] }}</p>
                        <div class="small text-muted mb-2">{{ $stage['status'] }}</div>
                        <ul class="ps-3 small mb-3">
                            @foreach($stage['items'] as $item)<li class="mb-1">{{ $item }}</li>@endforeach
                        </ul>
                        <div class="rounded bg-label-{{ $stage['color'] }} p-2 small"><strong>Output:</strong> {{ $stage['output'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header"><h4 class="mb-1"><i class="ti ti-stairs-up me-2 text-primary"></i>Jenjang Karier</h4><small class="text-muted">Kenaikan jenjang bergantung pada hasil evaluasi dan kebutuhan organisasi.</small></div>
                <div class="card-body">
                    <div class="row g-2 align-items-stretch">
                        @foreach($careerLevels as $index => $level)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-3 border rounded p-3 h-100 {{ $index >= 5 ? 'bg-label-primary' : '' }}">
                                    <span class="badge bg-primary rounded-pill">{{ $index + 1 }}</span>
                                    <strong>{{ $level }}</strong>
                                    @if(!$loop->last)<i class="ti ti-arrow-down ms-auto text-muted d-md-none"></i>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><h4 class="mb-1"><i class="ti ti-git-branch me-2 text-success"></i>Alur Keputusan</h4><small class="text-muted">Ringkasan proses status kerja dan evaluasi.</small></div>
                <div class="card-body">
                    <div class="d-flex gap-3 mb-3"><span class="avatar avatar-sm"><span class="avatar-initial rounded-circle bg-label-primary">1</span></span><div><strong>Pelamar → Seleksi → Onboarding</strong><div class="small text-muted">Onboarding berlangsung sampai 90 hari.</div></div></div>
                    <div class="d-flex gap-3 mb-3"><span class="avatar avatar-sm"><span class="avatar-initial rounded-circle bg-label-info">2</span></span><div><strong>Evaluasi onboarding</strong><div class="small text-muted">Kehadiran, attitude, integritas, skill, dan teamwork.</div></div></div>
                    <div class="d-flex gap-3 mb-3"><span class="avatar avatar-sm"><span class="avatar-initial rounded-circle bg-label-warning">3</span></span><div><strong>Memenuhi standar</strong><div class="small text-muted">Posisi PKWT → PKWT; kebutuhan tetap jangka panjang → PKWTT.</div></div></div>
                    <div class="d-flex gap-3"><span class="avatar avatar-sm"><span class="avatar-initial rounded-circle bg-label-success">4</span></span><div><strong>Evaluasi KPI setiap 6 bulan</strong><div class="small text-muted">Dasar pengembangan, promosi, dan talent pool.</div></div></div>
                    <div class="alert alert-warning small mt-4 mb-0"><i class="ti ti-alert-triangle me-1"></i> Bila tidak memenuhi standar, hubungan kerja ditindaklanjuti sesuai ketentuan yang berlaku dan proses HR.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="avatar avatar-sm"><span class="avatar-initial rounded bg-label-success"><i class="ti ti-gift"></i></span></span>
        <div><h4 class="mb-0">Benefit Berdasarkan Masa Kerja</h4><small class="text-muted">Benefit direalisasikan sesuai kebijakan perusahaan dan aturan yang berlaku.</small></div>
    </div>
    <div class="row g-3 mb-4">
        @foreach($benefitTiers as $tier)
            <div class="col-xl-4 col-md-6">
                <div class="card h-100"><div class="card-body"><span class="badge bg-{{ $tier['color'] }} mb-3">{{ $tier['period'] }}</span><ul class="ps-3 mb-0 small">@foreach($tier['items'] as $item)<li class="mb-1">{{ $item }}</li>@endforeach</ul></div></div>
            </div>
        @endforeach
    </div>

    <div class="card bg-label-primary border-0">
        <div class="card-body p-4">
            <div class="row align-items-center g-3"><div class="col-md-1 text-center"><i class="ti ti-heart-handshake fs-1 text-primary"></i></div><div class="col-md-11"><h4 class="mb-1">Prinsip OSM</h4><p class="mb-0">Perusahaan membangun karier secara adil, terukur, dan bertahap. Masa kerja membuka kesempatan, namun promosi selalu ditentukan oleh kontribusi nyata, kualitas kerja, integritas, kompetensi, dan kesiapan memimpin.</p></div></div>
        </div>
    </div>
</div>
@endsection
