@extends('layouts.contentNavbarLayout')

@section('title', 'Standar KPI Jabatan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1">Standar KPI per Jabatan</h4><p class="text-muted mb-0">Pilih aspek, isi pedoman, dan pastikan total bobot tepat 100%.</p></div><a href="{{ route('hr.kpi.index') }}" class="btn btn-label-secondary">Kembali</a></div>
    @if($aspects->isEmpty())<div class="alert alert-warning">Buat minimal satu <a href="{{ route('hr.kpi.aspects') }}">aspek KPI</a> terlebih dahulu.</div>@else
    <form method="POST" action="{{ route('hr.kpi.standards.store') }}">@csrf
        <div class="card mb-4"><div class="card-body"><div class="row g-3">
            <div class="col-md-4"><label class="form-label">Jabatan</label><select name="position_id" class="form-select" required><option value="">Pilih jabatan</option>@foreach($positions as $position)<option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>{{ $position->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Nama Standar</label><input name="name" class="form-control" value="{{ old('name', 'KPI Bulanan') }}" required></div>
            <div class="col-md-4"><label class="form-label">Bonus Maksimum</label><input type="number" min="0" name="bonus_maximum" class="form-control" value="{{ old('bonus_maximum', 0) }}" required></div>
            <div class="col-12"><label class="form-label">Catatan</label><textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea></div>
        </div></div></div>
        @error('items')<div class="alert alert-danger">{{ $message }}</div>@enderror
        <div class="card"><div class="card-header d-flex justify-content-between"><h5 class="mb-0">Aspek dan Bobot</h5><strong>Total: <span id="total-weight">0.00</span>%</strong></div><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Pilih</th><th>Aspek</th><th>Pedoman Penilaian</th><th width="140">Bobot (%)</th></tr></thead><tbody>
            @foreach($aspects as $index => $aspect)<tr data-item-row><td><input type="checkbox" class="form-check-input aspect-toggle"></td><td><small class="text-muted">{{ $aspect->category }}</small><br><strong>{{ $aspect->name }}</strong><input data-item-field type="hidden" name="items[{{ $index }}][aspect_id]" value="{{ $aspect->id }}" disabled></td><td><textarea data-item-field name="items[{{ $index }}][guideline]" class="form-control" rows="2" disabled>{{ $aspect->description }}</textarea></td><td><input data-item-field type="number" min="0.01" max="100" step="0.01" name="items[{{ $index }}][weight]" class="form-control weight-input" value="0" disabled></td></tr>@endforeach
        </tbody></table></div><div class="card-body"><button class="btn btn-primary"><i class="ti ti-device-floppy"></i> Simpan Standar KPI</button></div></div>
    </form>@endif
</div>
@endsection

@push('scripts')
<script>
const recalculate = () => { let total = 0; document.querySelectorAll('.weight-input:not(:disabled)').forEach(input => total += Number(input.value || 0)); document.querySelector('#total-weight').textContent = total.toFixed(2); };
document.querySelectorAll('[data-item-row]').forEach(row => { const toggle = row.querySelector('.aspect-toggle'); const fields = row.querySelectorAll('[data-item-field]'); toggle.addEventListener('change', () => { fields.forEach(field => field.disabled = !toggle.checked); recalculate(); }); row.querySelector('.weight-input').addEventListener('input', recalculate); });
</script>
@endpush
