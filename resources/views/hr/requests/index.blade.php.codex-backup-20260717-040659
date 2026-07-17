@extends('layouts.contentNavbarLayout')
@section('title', 'Pengajuan HR')
@section('content')
<div class="container-fluid">
  <div class="d-flex flex-wrap justify-content-between gap-2 mb-3"><div><h4 class="mb-1">Pusat Pengajuan & Approval</h4><p class="text-muted mb-0">Cuti, sakit, izin, kasbon, piutang, lembur, dan klaim biaya dalam satu halaman.</p></div>@if(!empty($activeTypes))<div><span class="badge bg-label-primary">Filter: {{ implode(', ', $activeTypes) }}</span></div>@endif</div>

  {{-- Filter dipindahkan ke dalam halaman agar sidebar tidak berisi banyak
       menu yang sebenarnya membuka fungsi approval yang sama. --}}
  <div class="nav-align-top mb-4">
    <div class="nav nav-pills flex-wrap gap-2 osm-request-filter" role="navigation" aria-label="Filter pengajuan HR">
      <a class="nav-link {{ empty($activeTypes) ? 'active' : '' }}" href="{{ route('hr.requests.index') }}">Semua</a>
      <a class="nav-link {{ request()->routeIs('hr.requests.leave') ? 'active' : '' }}" href="{{ route('hr.requests.leave') }}">Cuti</a>
      <a class="nav-link {{ request()->routeIs('hr.requests.permission-sick') ? 'active' : '' }}" href="{{ route('hr.requests.permission-sick') }}">Izin & Sakit</a>
      <a class="nav-link {{ request()->routeIs('hr.requests.overtime') ? 'active' : '' }}" href="{{ route('hr.requests.overtime') }}">Lembur</a>
      <a class="nav-link {{ request()->routeIs('hr.requests.finance') ? 'active' : '' }}" href="{{ route('hr.requests.finance') }}">Kasbon, Piutang & Klaim</a>
    </div>
  </div>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

  <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Master Kebijakan (dapat diedit HR)</h5></div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Jenis</th><th>Maks. nominal</th><th>Maks. hari</th><th>Maks. cicilan</th><th>Dokumen</th><th>Aktif</th><th></th></tr></thead><tbody>
    @foreach($policies as $policy)<tr><form method="POST" action="{{ route('hr.requests.policies.update', $policy) }}">@csrf @method('PUT')
      <td><strong>{{ $policy->name }}</strong><div class="small text-muted">{{ $policy->type }}</div></td>
      <td><input class="form-control form-control-sm" type="number" min="0" name="max_amount" value="{{ $policy->max_amount }}" placeholder="Tanpa batas"></td>
      <td><input class="form-control form-control-sm" type="number" min="1" name="max_days" value="{{ $policy->max_days }}" placeholder="-"></td>
      <td><input class="form-control form-control-sm" type="number" min="1" name="max_installments" value="{{ $policy->max_installments }}" placeholder="-"></td>
      <td><input type="hidden" name="requires_document" value="0"><input class="form-check-input" type="checkbox" name="requires_document" value="1" @checked($policy->requires_document)></td>
      <td><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($policy->is_active)></td>
      <td><button class="btn btn-sm btn-label-primary">Simpan</button></td>
    </form></tr>@endforeach
    </tbody></table></div>
  </div>

  <div class="card"><div class="card-header"><h5 class="mb-0">Antrean Approval</h5></div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>No / tanggal</th><th>Karyawan</th><th>Jenis</th><th>Rincian</th><th>Status</th><th>Keputusan HR</th></tr></thead><tbody>
    @forelse($requests as $item)<tr>
      <td><strong>{{ $item->request_no }}</strong><div class="small text-muted">{{ $item->submitted_at?->format('d/m/Y H:i') }}</div></td>
      <td>{{ $item->employee?->name }}<div class="small text-muted">{{ $item->branch?->name }}</div></td>
      <td>{{ ucwords(str_replace('_',' ', $item->type)) }}</td>
      <td>@if($item->requested_amount)Rp {{ number_format((float)$item->requested_amount,0,',','.') }} / {{ $item->installment_count ?: 1 }}x<br>@endif{{ $item->start_date?->format('d/m/Y') }} @if($item->end_date)–{{ $item->end_date->format('d/m/Y') }}@endif<div class="small text-muted text-wrap" style="max-width:280px">{{ $item->reason }}</div></td>
      <td><span class="badge bg-label-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($item->status) }}</span></td>
      <td>@if($item->status === 'submitted')
        <form method="POST" action="{{ route('hr.requests.approve',$item) }}" class="d-flex flex-column gap-1 mb-2">@csrf
          @if(in_array($item->type,['cash_advance','receivable','reimbursement']))<input class="form-control form-control-sm" name="approved_amount" type="number" min="1" value="{{ $item->requested_amount }}" placeholder="Nominal disetujui">@endif
          @if(in_array($item->type,['cash_advance','receivable']))<input class="form-control form-control-sm" name="installment_count" type="number" min="1" value="{{ $item->installment_count ?: 1 }}" placeholder="Cicilan">@endif
          <input class="form-control form-control-sm" name="hr_note" placeholder="Catatan HR"><button class="btn btn-sm btn-success">Setujui</button>
        </form>
        <form method="POST" action="{{ route('hr.requests.reject',$item) }}" class="d-flex gap-1">@csrf<input class="form-control form-control-sm" required minlength="5" name="hr_note" placeholder="Alasan penolakan"><button class="btn btn-sm btn-outline-danger">Tolak</button></form>
      @else<div class="small text-muted">{{ $item->hr_note ?: '-' }}</div>@endif</td>
    </tr>@empty<tr><td colspan="6" class="text-center py-5 text-muted">Belum ada pengajuan.</td></tr>@endforelse
    </tbody></table></div><div class="card-body">{{ $requests->links() }}</div>
  </div>
</div>
@endsection
