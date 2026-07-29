@extends('layouts.contentNavbarLayout')

@section('title', 'Jadwal Kerja')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h4 class="mb-1">Jadwal Kerja</h4>
    <p class="text-muted mb-0">Pantau jadwal seluruh pegawai dan kelola penugasan shift per perusahaan.</p>
  </div>
  @can('attendance.shift.assignment.create')
    <a href="{{ route('attendance.shift-assignments.create') }}" class="btn btn-primary">
      <i class="ti ti-plus me-1"></i> Tambah Jadwal Shift
    </a>
  @endcan
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<div class="card">
  <div class="card-header border-bottom">
    <ul class="nav nav-tabs card-header-tabs" role="tablist">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#fixed-schedule" type="button" role="tab">Jadwal Tetap</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#shift-schedule" type="button" role="tab">Jadwal Shift</button></li>
    </ul>
  </div>
  <div class="card-body">
    <div class="tab-content p-0">
      <div class="tab-pane fade show active" id="fixed-schedule" role="tabpanel">
        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
          <div><h5 class="mb-1">Daftar Jadwal Pegawai</h5><small class="text-muted">Jadwal aktif hari ini ditampilkan untuk setiap pegawai.</small></div>
          <div class="input-group" style="max-width: 320px;"><span class="input-group-text"><i class="ti ti-search"></i></span><input id="employeeScheduleSearch" class="form-control" placeholder="Cari nama, organisasi, atau jabatan"></div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="employeeScheduleTable">
            <thead class="table-light"><tr><th>No.</th><th>Nama</th><th>Organisasi</th><th>Jabatan</th><th>Status Kerja</th><th>Jadwal</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($employees as $employee)
              @php($current = $currentAssignments->get($employee->id))
              <tr data-search="{{ strtolower($employee->name.' '.($employee->employee_no ?? '').' '.($employee->branch?->name ?? '').' '.($employee->division?->name ?? '').' '.($employee->position?->name ?? '')) }}">
                <td>{{ $loop->iteration }}</td>
                <td><strong>{{ $employee->name }}</strong><br><small class="text-muted">{{ $employee->employee_no ?? $employee->employee_code ?? '-' }}</small></td>
                <td>{{ $employee->division?->name ?? $employee->branch?->name ?? 'Belum diatur' }}<br><small class="text-muted">{{ $employee->branch?->name ?? 'Company-wide' }}</small></td>
                <td>{{ $employee->position?->name ?? 'Belum diatur' }}</td>
                <td><span class="badge bg-label-{{ ($employee->work_status ?? 'active') === 'active' ? 'success' : 'warning' }}">{{ ucfirst($employee->work_status ?? 'aktif') }}</span></td>
                <td>
                  @if($current)
                    <span class="badge bg-label-primary">{{ $current->shift?->name ?? 'Shift tidak ditemukan' }}</span>
                  @else
                    <span class="badge bg-label-secondary">Belum ditetapkan</span>
                  @endif
                </td>
                <td class="text-end">
                  @can('attendance.shift.assignment.create')
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('attendance.shift-assignments.create', ['employee_id' => $employee->id]) }}"><i class="ti ti-calendar-plus me-1"></i>Atur</a>
                  @endcan
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center text-muted py-5">Belum ada pegawai pada perusahaan aktif.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="tab-pane fade" id="shift-schedule" role="tabpanel">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light"><tr><th>No.</th><th>Pegawai / Site</th><th>Shift</th><th>Periode</th><th>Status</th><th>Catatan</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($assignments as $item)
              <tr>
                <td>{{ $assignments->firstItem() + $loop->index }}</td>
                <td><strong>{{ $item->employee?->name ?? 'Pegawai dihapus' }}</strong><br><small class="text-muted">{{ $item->branch?->name ?? $item->employee?->branch?->name ?? 'Company-wide' }}</small></td>
                <td>{{ $item->shift?->name ?? '-' }}</td>
                <td>{{ optional($item->start_date)->format('d M Y') }}<br><small class="text-muted">s.d. {{ optional($item->end_date)->format('d M Y') ?? 'berjalan' }}</small></td>
                <td><span class="badge bg-label-{{ $item->status === 'active' ? 'success' : 'secondary' }}">{{ $item->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td>{{ $item->notes ?: '-' }}</td>
                <td class="text-end text-nowrap">
                  @can('attendance.shift.assignment.update')<a href="{{ route('attendance.shift-assignments.edit', $item) }}" class="btn btn-sm btn-outline-warning"><i class="ti ti-edit"></i></a>@endcan
                  @can('attendance.shift.assignment.delete')
                    <form action="{{ route('attendance.shift-assignments.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus penugasan shift ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button></form>
                  @endcan
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center text-muted py-5">Belum ada penugasan shift.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
        <div class="mt-3">{{ $assignments->links() }}</div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
document.getElementById('employeeScheduleSearch')?.addEventListener('input', function () {
  const needle = this.value.toLowerCase().trim();
  document.querySelectorAll('#employeeScheduleTable tbody tr[data-search]').forEach(function (row) {
    row.classList.toggle('d-none', needle !== '' && !row.dataset.search.includes(needle));
  });
});
</script>
@endsection