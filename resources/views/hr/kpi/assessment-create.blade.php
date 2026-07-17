@extends('layouts.contentNavbarLayout')

@section('title', 'Penilaian KPI')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1">Penilaian KPI Pegawai</h4><p class="text-muted mb-0">Nilai semua aspek berdasarkan standar jabatan pegawai.</p></div><a href="{{ route('hr.kpi.index') }}" class="btn btn-label-secondary">Kembali</a></div>
    @if($standards->isEmpty())<div class="alert alert-warning">Belum ada standar KPI aktif. <a href="{{ route('hr.kpi.standards.create') }}">Buat standar KPI</a> terlebih dahulu.</div>@else
    <form method="POST" action="{{ route('hr.kpi.assessments.store') }}">@csrf
        <div class="card mb-4"><div class="card-body"><div class="row g-3">
            <div class="col-md-5"><label class="form-label">Pegawai</label><select id="employee" name="employee_id" class="form-select" required><option value="">Pilih pegawai</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" data-position="{{ $employee->position_id }}">{{ $employee->employee_no }} — {{ $employee->name }} ({{ $employee->position?->name ?? 'Tanpa jabatan' }})</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Bulan</label><select name="period_month" class="form-select" required>@foreach(range(1,12) as $month)<option value="{{ $month }}" @selected((int) old('period_month', now()->month) === $month)>{{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Tahun</label><input type="number" name="period_year" class="form-control" value="{{ old('period_year', now()->year) }}" min="2020" max="2100" required></div>
            <div class="col-md-2"><label class="form-label">Total Nilai</label><input id="total-score" class="form-control fw-bold" value="0.00" readonly></div>
        </div></div></div>
        <div class="card"><div class="card-header"><h5 class="mb-0">Pilih Standar KPI</h5></div><div class="card-body"><select id="standard" name="kpi_standard_id" class="form-select mb-4" required disabled><option value="">Pilih pegawai terlebih dahulu</option>@foreach($standards as $standard)<option value="{{ $standard->id }}" data-position="{{ $standard->position_id }}">{{ $standard->position?->name }} — {{ $standard->name }} (Maks. bonus Rp {{ number_format((float) $standard->bonus_maximum, 0, ',', '.') }})</option>@endforeach</select>
            @foreach($standards as $standard)<div class="standard-items d-none" data-standard="{{ $standard->id }}"><div class="table-responsive"><table class="table table-bordered align-middle"><thead><tr><th>Aspek</th><th>Pedoman</th><th>Bobot</th><th width="150">Nilai (0–100)</th><th width="120">Hasil</th></tr></thead><tbody>@foreach($standard->items as $item)<tr><td><strong>{{ $item->aspect_name }}</strong></td><td>{{ $item->guideline ?: '-' }}</td><td><span class="weight-value">{{ $item->weight }}</span>%</td><td><input data-score data-weight="{{ $item->weight }}" name="scores[{{ $item->id }}]" type="number" min="0" max="100" step="0.01" class="form-control" disabled required></td><td><span data-result>0.00</span></td></tr>@endforeach</tbody></table></div></div>@endforeach
            <div class="mt-3"><label class="form-label">Catatan Penilai</label><textarea name="notes" class="form-control" rows="3" placeholder="Catatan atau bukti kerja KPI"></textarea></div>
            <button class="btn btn-primary mt-4"><i class="ti ti-send"></i> Ajukan KPI untuk Approval</button>
        </div></div>
    </form>@endif
</div>
@endsection

@push('scripts')
<script>
const employee = document.querySelector('#employee'), standard = document.querySelector('#standard');
const updateScores = () => { let total = 0; document.querySelectorAll('.standard-items:not(.d-none) [data-score]').forEach(input => { const result = Number(input.value || 0) * Number(input.dataset.weight) / 100; input.closest('tr').querySelector('[data-result]').textContent = result.toFixed(2); total += result; }); document.querySelector('#total-score').value = total.toFixed(2); };
const showStandard = () => { document.querySelectorAll('.standard-items').forEach(box => { const active = box.dataset.standard === standard.value; box.classList.toggle('d-none', !active); box.querySelectorAll('[data-score]').forEach(input => input.disabled = !active); }); updateScores(); };
employee.addEventListener('change', () => { const position = employee.options[employee.selectedIndex].dataset.position; standard.disabled = !position; [...standard.options].forEach(option => { if (!option.value) return; option.hidden = option.dataset.position !== position; }); standard.value = ''; showStandard(); });
standard.addEventListener('change', showStandard); document.querySelectorAll('[data-score]').forEach(input => input.addEventListener('input', updateScores));
</script>
@endpush
