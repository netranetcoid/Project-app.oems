@extends('layouts.contentNavbarLayout')
@section('title', 'Payroll')
@section('content')
<div class="container-fluid"><div class="d-flex flex-wrap justify-content-between gap-3 align-items-end mb-4">
  <div><h4 class="mb-1">Payroll & Slip Gaji</h4><p class="text-muted mb-0">Gaji dibayar akhir bulan; bonus KPI tanggal 15 bulan berikutnya.</p></div>
  <form method="POST" action="{{ route('hr.payroll.generate') }}" class="d-flex gap-2 align-items-end">@csrf
    <div><label class="form-label">Tahun</label><input class="form-control" type="number" name="period_year" value="{{ now()->year }}" min="2020" max="2100"></div>
    <div><label class="form-label">Bulan</label><input class="form-control" type="number" name="period_month" value="{{ now()->month }}" min="1" max="12"></div>
    <button class="btn btn-primary">Buat / hitung draft</button>
  </form>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="card"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Periode</th><th>Jadwal Bayar</th><th>Slip</th><th>Total Neto</th><th>Bonus KPI</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($periods as $period)<tr><td><strong>{{ sprintf('%02d/%04d',$period->period_month,$period->period_year) }}</strong></td><td>Gaji: {{ $period->salary_payment_date->format('d/m/Y') }}<br><span class="small text-muted">KPI: {{ $period->kpi_payment_date->format('d/m/Y') }}</span></td><td>{{ $period->slips_count }}</td><td>Rp {{ number_format((float)$period->total_net,0,',','.') }}</td><td>Rp {{ number_format((float)$period->total_kpi_bonus,0,',','.') }}</td><td><span class="badge bg-label-{{ $period->status === 'published' ? 'success' : ($period->status === 'approved' ? 'info' : 'warning') }}">{{ ucfirst($period->status) }}</span></td><td><a class="btn btn-sm btn-label-primary" href="{{ route('hr.payroll.show',$period) }}">Detail</a></td></tr>
@empty<tr><td colspan="7" class="text-center py-5 text-muted">Belum ada periode payroll.</td></tr>@endforelse
</tbody></table></div><div class="card-body">{{ $periods->links() }}</div></div></div>
@endsection
