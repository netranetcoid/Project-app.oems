@php($editing = isset($position) && $position->exists)
<div class="row g-3">
  <div class="col-md-2"><label class="form-label">Kode</label><input class="form-control" name="code" required maxlength="50" value="{{ old('code', $position->code ?? '') }}"></div>
  <div class="col-md-4"><label class="form-label">Nama Jabatan</label><input class="form-control" name="name" required value="{{ old('name', $position->name ?? '') }}"></div>
  <div class="col-md-3"><label class="form-label">Divisi</label><select class="form-select" name="division_id"><option value="">Lintas divisi</option>@foreach($divisions as $division)<option value="{{ $division->id }}" @selected((string) old('division_id', $position->division_id ?? '') === (string) $division->id)>{{ $division->name }}</option>@endforeach</select></div>
  <div class="col-md-3"><label class="form-label">Atasan Jabatan</label><select class="form-select" name="parent_id"><option value="">Tidak ada</option>@foreach($parents as $parent)@if(!$editing || $parent->id !== $position->id)<option value="{{ $parent->id }}" @selected((string) old('parent_id', $position->parent_id ?? '') === (string) $parent->id)>{{ $parent->name }}</option>@endif @endforeach</select></div>
  <div class="col-md-2"><label class="form-label">Level</label><input class="form-control" type="number" min="1" max="99" name="level" value="{{ old('level', $position->level ?? 1) }}" required></div>
  <div class="col-md-2"><label class="form-label">Grade</label><input class="form-control" name="grade" value="{{ old('grade', $position->grade ?? '') }}"></div>
  <div class="col-md-2"><label class="form-label">Tipe</label><select class="form-select" name="type"><option value="">-</option>@foreach(['staff','leader','supervisor','manager','director','owner'] as $type)<option value="{{ $type }}" @selected(old('type', $position->type ?? '') === $type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
  <div class="col-md-2"><label class="form-label">Urutan</label><input class="form-control" type="number" min="0" name="sort_order" value="{{ old('sort_order', $position->sort_order ?? 0) }}"></div>
  <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active" @selected(old('status', $position->status ?? 'active') === 'active')>Aktif</option><option value="inactive" @selected(old('status', $position->status ?? '') === 'inactive')>Nonaktif</option></select></div>
  <div class="col-md-4"><label class="form-label">Default Gaji Pokok</label><input class="form-control" type="number" min="0" name="default_basic_salary" value="{{ old('default_basic_salary', $position->default_basic_salary ?? 0) }}"></div>
  <div class="col-md-4"><label class="form-label">Default Tunjangan</label><input class="form-control" type="number" min="0" name="default_allowance" value="{{ old('default_allowance', $position->default_allowance ?? 0) }}"></div>
  <div class="col-md-4"><label class="form-label">Maks. Bonus KPI</label><input class="form-control" type="number" min="0" name="default_kpi_incentive_max" value="{{ old('default_kpi_incentive_max', $position->default_kpi_incentive_max ?? 0) }}"></div>
  <div class="col-12 d-flex flex-wrap gap-3">
    @foreach(['is_management'=>'Manajemen lintas field','is_approver'=>'Dapat menyetujui','is_field_worker'=>'Petugas lapangan','is_kpi_enabled'=>'KPI aktif','is_payroll_enabled'=>'Payroll aktif'] as $field=>$label)
      <div class="form-check"><input class="form-check-input" type="checkbox" name="{{ $field }}" value="1" id="{{ $field }}-{{ $editing ? $position->id : 'new' }}" @checked(old($field, $position->{$field} ?? in_array($field,['is_kpi_enabled','is_payroll_enabled'])))><label class="form-check-label" for="{{ $field }}-{{ $editing ? $position->id : 'new' }}">{{ $label }}</label></div>
    @endforeach
  </div>
</div>
