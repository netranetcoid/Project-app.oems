@extends('layouts.contentNavbarLayout')
@section('title', 'Payroll')
@section('content')
@php
  $months = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
  $byMonth = $periods->keyBy('period_month');
  $money = fn($value) => 'Rp '.number_format((float)$value,0,',','.');
@endphp
<div class="container-fluid">
  <div class="d-flex flex-wrap justify-content-between gap-3 align-items-end mb-4">
    <div><h4 class="mb-1"><i class="ri ri-wallet-3-line me-2"></i>Payroll</h4><p class="text-muted mb-0">Histori penggajian, status pembayaran, rincian slip, dan publikasi ke OvallHR.</p></div>
    <form method="POST" action="{{ route('hr.payroll.generate') }}" class="d-flex flex-wrap gap-2 align-items-end">@csrf
      <div><label class="form-label">Tahun</label><input class="form-control" type="number" name="period_year" value="{{ $year }}" min="2020" max="2100" required></div>
      <div><label class="form-label">Bulan</label><select class="form-select" name="period_month">@foreach($months as $number=>$name)<option value="{{ $number }}" @selected($number===now()->month)>{{ $name }}</option>@endforeach</select></div>
      <button class="btn btn-primary"><i class="ri ri-calculator-line me-1"></i>Buat / Hitung Draft</button>
    </form>
  </div>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

  <div class="card mb-4"><div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h5 class="mb-0">Histori {{ $year }}</h5>
      <form method="GET" class="d-flex gap-2"><input class="form-control" type="number" name="year" value="{{ $year }}" min="2020" max="2100"><button class="btn btn-label-primary">Tampilkan</button></form>
    </div>
    <div class="row g-2">@foreach($months as $number=>$name)@php($period=$byMonth->get($number))
      <div class="col-6 col-md-3 col-xl-2"><div class="card h-100 {{ $period ? 'border-primary' : 'bg-lighter' }}">
        <div class="card-body p-3"><small>{{ $name }}</small><h6 class="mb-2">{{ $year }}</h6>
          @if($period)<div class="small mb-2"><span class="badge bg-label-{{ $period->status==='published'?'success':($period->status==='approved'?'info':'warning') }}">{{ ['draft'=>'Draft','approved'=>'Disetujui','published'=>'Terbit'][$period->status] ?? ucfirst($period->status) }}</span></div><a class="stretched-link" href="{{ route('hr.payroll.show',$period) }}">{{ $period->slips_count }} pegawai</a>
          @else<span class="small text-muted">Belum diproses</span>@endif
        </div></div></div>
    @endforeach</div>
  </div></div>

  <div class="card"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Periode</th><th>Pegawai</th><th>Siap Bayar</th><th>Sudah Bayar</th><th>Total THP</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($periods->sortByDesc('period_month') as $period)@php($percent=$period->slips_count?round($period->paid_slips_count/$period->slips_count*100):0)<tr>
      <td><strong>{{ $months[$period->period_month] }} {{ $period->period_year }}</strong><div class="small text-muted">Bayar {{ $period->salary_payment_date->format('d/m/Y') }}</div></td><td>{{ $period->slips_count }}</td>
      <td><div class="progress" style="height:8px"><div class="progress-bar" style="width:{{ $period->status==='draft'?0:100 }}%"></div></div><small>{{ $period->status==='draft'?'Belum disetujui':'100%' }}</small></td>
      <td><div class="progress" style="height:8px"><div class="progress-bar bg-success" style="width:{{ $percent }}%"></div></div><small>{{ $period->paid_slips_count }}/{{ $period->slips_count }} ({{ $percent }}%)</small></td>
      <td><strong>{{ $money($period->total_net) }}</strong></td><td><span class="badge bg-label-{{ $period->status==='published'?'success':($period->status==='approved'?'info':'warning') }}">{{ ucfirst($period->status) }}</span></td><td><a class="btn btn-sm btn-primary" href="{{ route('hr.payroll.show',$period) }}">Buka Rincian</a></td>
    </tr>@empty<tr><td colspan="7" class="text-center py-5 text-muted">Belum ada payroll pada tahun ini.</td></tr>@endforelse
  </tbody></table></div></div>
</div>
@endsection
