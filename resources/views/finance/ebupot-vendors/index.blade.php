@extends('layouts.contentNavbarLayout')
@section('title', 'e-Bupot Vendor')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
  <div><h4 class="mb-1">e-Bupot Vendor</h4><p class="text-muted mb-0">Invoice → Coretax → Bukti Potong → pembayaran → kirim ke vendor → pelaporan.</p></div>
  <div class="d-flex gap-2"><button class="btn btn-label-primary" data-bs-toggle="modal" data-bs-target="#settingsModal">Template & deadline</button><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#vendorModal">+ Vendor</button></div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Data belum disimpan:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="alert alert-warning"><strong>Kontrol wajib:</strong> jika SKB/fasilitas, kode objek, NITKU, DPP, atau dokumen tidak jelas, tandai <b>STOP / Eskalasi</b>.</div>
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between"><div><h5 class="mb-1">Panduan SOP Admin</h5><small>Ringkasan pekerjaan bulanan.</small></div><button class="btn btn-sm btn-label-primary" data-bs-toggle="collapse" data-bs-target="#sopGuide">Buka panduan</button></div>
  <div class="collapse" id="sopGuide"><div class="card-body"><ol class="mb-3">
    <li>Tanggal 1–10: kumpulkan invoice, Faktur Pajak, kontrak/PO, dan jadwal bayar.</li>
    <li>Catat nomor invoice, tanggal invoice, serta tanggal jatuh tempo masing-masing tagihan.</li>
    <li>Konfirmasi kode objek Coretax, NITKU 22 digit, DPP sebelum PPN, tarif, dan SKB/fasilitas.</li>
    <li>Setelah BPPU terbit dari Coretax, admin wajib mengunggah PDF Bukti Potong ke AppOEMS.</li>
    <li>Bayar vendor sebesar total invoice dikurangi PPh, lalu kirim PDF Bukti Potong kepada vendor.</li>
    <li>Akhir bulan lakukan rekonsiliasi; bayar PPh maksimal tanggal {{ $settings->payment_deadline_day }} dan lapor SPT maksimal tanggal {{ $settings->report_deadline_day }} bulan berikutnya.</li>
  </ol><div class="alert alert-info mb-0"><b>Rumus:</b> PPh = tarif × DPP jasa sebelum PPN. Transfer bersih = total invoice − PPh. Materai bukan DPP PPh.</div></div></div>
</div>

<form method="GET" class="card card-body mb-4"><div class="row align-items-end g-3"><div class="col-md-3"><label class="form-label">Masa pajak</label><input class="form-control" type="month" name="period" value="{{ $period->format('Y-m') }}"></div><div class="col-md-2"><button class="btn btn-primary w-100">Tampilkan</button></div></div></form>

<div class="row g-3 mb-4">
@foreach([['Vendor',$summary['total'],'primary'],['Perlu dilengkapi',$summary['waiting'],'warning'],['BPPU terbit',$summary['issued'],'info'],['Sudah dikirim',$summary['sent'],'success'],['Total PPh','Rp '.number_format($summary['tax'],0,',','.'),'danger']] as $item)
<div class="col-6 col-lg"><div class="card h-100"><div class="card-body"><small class="text-muted">{{ $item[0] }}</small><h4 class="mb-0 text-{{ $item[2] }}">{{ $item[1] }}</h4></div></div></div>
@endforeach
</div>

<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead><tr><th>Vendor</th><th>Invoice & jatuh tempo</th><th>Perhitungan</th><th>Dokumen Coretax</th><th>Status</th><th>Aksi</th></tr></thead>
<tbody>
@forelse($records as $record)
<tr class="{{ $record->requires_escalation ? 'table-danger' : '' }}">
  <td><strong>{{ $record->vendor->name }}</strong><div class="small text-muted">{{ $record->vendor->code }} · {{ $record->vendor->service_name ?: 'Layanan belum diisi' }}</div></td>
  <td><strong>{{ $record->invoice_number ?: 'Nomor belum diisi' }}</strong><div class="small">Invoice: {{ optional($record->invoice_date)->format('d/m/Y') ?: '-' }}</div><div class="small {{ $record->due_date && $record->due_date->isPast() && !in_array($record->status,['paid','sent','reported']) ? 'text-danger fw-bold' : 'text-muted' }}">Jatuh tempo: {{ optional($record->due_date)->format('d/m/Y') ?: 'belum diisi' }}</div></td>
  <td>DPP Rp {{ number_format($record->tax_base,0,',','.') }}<div class="small">Materai Rp {{ number_format($record->stamp_amount,0,',','.') }}</div><div class="small text-success">Transfer bersih Rp {{ number_format($record->net_transfer,0,',','.') }}</div></td>
  <td>@if($record->ebupot_file)<a class="btn btn-sm btn-success" href="{{ route('finance.ebupot-vendors.download',['record'=>$record,'type'=>'ebupot_file']) }}">Unduh Bukti Potong</a>@else<span class="badge bg-label-warning">Belum diunggah</span>@endif</td>
  <td><span class="badge bg-label-{{ in_array($record->status,['sent','reported'])?'success':($record->status==='issued'?'info':'warning') }}">{{ str_replace('_',' ',strtoupper($record->status)) }}</span></td>
  <td><div class="d-flex flex-wrap gap-1"><button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#edit{{ $record->id }}">Buka</button><a class="btn btn-sm btn-label-secondary" target="_blank" href="{{ route('finance.ebupot-vendors.print',$record) }}">Cetak</a>@if($record->vendor->whatsapp)<a class="btn btn-sm btn-success" target="_blank" href="{{ route('finance.ebupot-vendors.whatsapp',[$record,'kind'=>'initial']) }}">WhatsApp</a>@endif</div></td>
</tr>
<tr class="collapse" id="edit{{ $record->id }}"><td colspan="6"><form method="POST" enctype="multipart/form-data" action="{{ route('finance.ebupot-vendors.records.update',$record) }}" class="p-3">@csrf @method('PUT')
  <div class="row g-3">
    <div class="col-md-3"><label class="form-label">Nomor invoice</label><input class="form-control" name="invoice_number" value="{{ $record->invoice_number }}"></div>
    <div class="col-md-3"><label class="form-label">Tanggal invoice</label><input type="date" class="form-control" name="invoice_date" value="{{ optional($record->invoice_date)->format('Y-m-d') }}"></div>
    <div class="col-md-3"><label class="form-label fw-bold">Tanggal jatuh tempo</label><input type="date" class="form-control" name="due_date" value="{{ optional($record->due_date)->format('Y-m-d') }}"></div>
    <div class="col-md-3"><label class="form-label">Total invoice</label><input class="form-control rupiah" name="invoice_total" value="{{ (int)$record->invoice_total }}" required></div>
    <div class="col-md-3"><label class="form-label">DPP PPh</label><input class="form-control rupiah" name="tax_base" value="{{ (int)$record->tax_base }}" required></div>
    <div class="col-md-2"><label class="form-label">Tarif %</label><input type="number" step="0.0001" min="0" max="100" class="form-control" name="tax_rate" value="{{ $record->tax_rate }}" required></div>
    <div class="col-md-2"><label class="form-label">PPN</label><input class="form-control rupiah" name="vat_amount" value="{{ (int)$record->vat_amount }}"></div>
    <div class="col-md-2"><label class="form-label">Materai</label><input class="form-control rupiah" name="stamp_amount" value="{{ (int)$record->stamp_amount }}"></div>
    <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status">@foreach(['waiting_data'=>'Menunggu data','draft'=>'Konsep','issued'=>'BPPU terbit','paid'=>'Vendor dibayar','sent'=>'Bukti dikirim','reported'=>'SPT dilaporkan','cancelled'=>'Dibatalkan'] as $key=>$label)<option value="{{ $key }}" @selected($record->status===$key)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label">Nomor BPPU Coretax</label><input class="form-control" name="ebupot_number" value="{{ $record->ebupot_number }}"></div>
    <div class="col-md-3"><label class="form-label">Tanggal BPPU</label><input type="date" class="form-control" name="ebupot_date" value="{{ optional($record->ebupot_date)->format('Y-m-d') }}"></div>
    <div class="col-md-6"><label class="form-label fw-bold">Upload Bukti Potong Coretax (PDF)</label><input type="file" class="form-control" name="ebupot_file" accept="application/pdf"><small class="text-danger">Wajib diunggah mulai status BPPU terbit.</small>@if($record->ebupot_file)<div class="mt-1"><a href="{{ route('finance.ebupot-vendors.download',['record'=>$record,'type'=>'ebupot_file']) }}">Lihat file yang tersimpan</a></div>@endif</div>
    <div class="col-md-3"><label class="form-label">Invoice/Faktur Pajak</label><input type="file" class="form-control" name="invoice_file"></div>
    <div class="col-md-3"><label class="form-label">Bukti transfer</label><input type="file" class="form-control" name="transfer_file"></div>
    <div class="col-12"><label class="form-label">Catatan</label><textarea class="form-control" name="notes">{{ $record->notes }}</textarea></div>
    <div class="col-12"><div class="row g-2">@foreach($checks as $check)<div class="col-md-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="checklist[{{ $check }}]" value="1" @checked(data_get($record->checklist,$check,false))><span class="form-check-label">{{ ucwords(str_replace('_',' ',$check)) }}</span></label></div>@endforeach</div></div>
    <div class="col-md-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="requires_escalation" value="1" @checked($record->requires_escalation)><span class="form-check-label text-danger">STOP / Eskalasi</span></label></div>
    <div class="col-md-8"><input class="form-control" name="escalation_reason" value="{{ $record->escalation_reason }}" placeholder="Alasan eskalasi"></div>
    <div class="col-12 text-end"><button class="btn btn-primary">Simpan Pekerjaan</button></div>
  </div>
</form></td></tr>
@empty
<tr><td colspan="6" class="text-center text-muted py-5">Belum ada pekerjaan vendor pada masa ini.</td></tr>
@endforelse
</tbody></table></div></div>

<div class="card mt-4"><div class="card-header"><h5 class="mb-1">Master Vendor</h5><small>Identitas dan pengaturan pajak vendor.</small></div><div class="card-body"><div class="accordion" id="vendorList">
@foreach($vendors as $vendor)
<div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#vendor{{ $vendor->id }}">{{ $vendor->code }} · {{ $vendor->name }}</button></h2><div id="vendor{{ $vendor->id }}" class="accordion-collapse collapse" data-bs-parent="#vendorList"><div class="accordion-body">
<form method="POST" action="{{ route('finance.ebupot-vendors.vendors.update',$vendor) }}">@csrf @method('PUT')<div class="row g-3">
  <div class="col-md-6"><label class="form-label">Nama</label><input class="form-control" name="name" value="{{ $vendor->name }}" required></div><div class="col-md-6"><label class="form-label">Layanan</label><input class="form-control" name="service_name" value="{{ $vendor->service_name }}"></div>
  <div class="col-md-3"><label class="form-label">NPWP</label><input class="form-control" name="npwp" value="{{ $vendor->npwp }}"></div><div class="col-md-3"><label class="form-label">NITKU</label><input class="form-control" name="nitku" value="{{ $vendor->nitku }}"></div><div class="col-md-3"><label class="form-label">Kode objek</label><input class="form-control" name="tax_object_code" value="{{ $vendor->tax_object_code }}"></div><div class="col-md-3"><label class="form-label">Tarif %</label><input type="number" step="0.0001" class="form-control" name="default_tax_rate" value="{{ $vendor->default_tax_rate }}"></div>
  <div class="col-md-3"><label class="form-label">PIC</label><input class="form-control" name="pic_name" value="{{ $vendor->pic_name }}"></div><div class="col-md-3"><label class="form-label">WhatsApp</label><input class="form-control" name="whatsapp" value="{{ $vendor->whatsapp }}"></div><div class="col-md-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ $vendor->email }}"></div><div class="col-md-3"><label class="form-label">Jenis pajak</label><input class="form-control" name="tax_article" value="{{ $vendor->tax_article }}"></div>
  <div class="col-md-4"><label class="form-check"><input type="checkbox" class="form-check-input" name="has_tax_facility" value="1" @checked($vendor->has_tax_facility)> Ada SKB/fasilitas</label></div><div class="col-md-4"><label class="form-check"><input type="checkbox" class="form-check-input" name="is_active" value="1" @checked($vendor->is_active)> Vendor aktif</label></div>
  <div class="col-12"><label class="form-label">Catatan fasilitas</label><input class="form-control" name="tax_facility_notes" value="{{ $vendor->tax_facility_notes }}"></div><div class="col-12"><label class="form-label">Catatan umum</label><textarea class="form-control" name="notes">{{ $vendor->notes }}</textarea></div><div class="col-12 text-end"><button class="btn btn-primary">Simpan Vendor</button></div>
</div></form>
<form method="POST" action="{{ route('finance.ebupot-vendors.vendors.destroy',$vendor) }}" class="mt-2 text-end">@csrf @method('DELETE')<button class="btn btn-outline-danger" onclick="return confirm('Hapus vendor dan seluruh pekerjaan bulanannya?')">Hapus Vendor</button></form>
</div></div></div>
@endforeach
</div></div></div>

<div class="modal fade" id="vendorModal" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" enctype="multipart/form-data" action="{{ route('finance.ebupot-vendors.vendors.store') }}">@csrf
<div class="modal-header"><h5>Tambah Vendor dan Invoice Awal</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row g-3">
  <div class="col-md-6"><label class="form-label">Nama vendor *</label><input class="form-control" name="name" required></div><div class="col-md-6"><label class="form-label">Layanan</label><input class="form-control" name="service_name"></div>
  <div class="col-md-4"><label class="form-label">NPWP</label><input class="form-control" name="npwp"></div><div class="col-md-4"><label class="form-label">NITKU 22 digit</label><input class="form-control" name="nitku"></div><div class="col-md-4"><label class="form-label">Kode objek</label><input class="form-control" name="tax_object_code" placeholder="24-104-26"></div>
  <div class="col-md-4"><label class="form-label">PIC</label><input class="form-control" name="pic_name"></div><div class="col-md-4"><label class="form-label">WhatsApp</label><input class="form-control" name="whatsapp"></div><div class="col-md-4"><label class="form-label">Email</label><input type="email" class="form-control" name="email"></div>
  <div class="col-md-4"><label class="form-label">Jenis pajak</label><input class="form-control" name="tax_article" value="PPh Pasal 23"></div><div class="col-md-4"><label class="form-label">Tarif default %</label><input type="number" step="0.0001" min="0" max="100" class="form-control" name="default_tax_rate" value="2"></div><div class="col-md-4"><label class="form-check mt-4"><input class="form-check-input" type="checkbox" name="has_tax_facility" value="1"> Ada SKB/fasilitas (STOP)</label></div>
  <div class="col-12"><hr><h6>Invoice awal</h6></div>
  <div class="col-md-3"><label class="form-label">Masa pajak *</label><input type="month" class="form-control" name="initial_period" value="{{ $period->format('Y-m') }}" required></div><div class="col-md-3"><label class="form-label">Nomor invoice</label><input class="form-control" name="invoice_number"></div><div class="col-md-3"><label class="form-label">Tanggal invoice</label><input type="date" class="form-control" name="invoice_date"></div><div class="col-md-3"><label class="form-label fw-bold">Tanggal jatuh tempo</label><input type="date" class="form-control" name="due_date"></div>
  <div class="col-md-4"><label class="form-label">File invoice/faktur</label><input type="file" class="form-control" name="invoice_file"></div><div class="col-md-4"><label class="form-label">DPP PPh</label><input class="form-control rupiah" name="tax_base"></div><div class="col-md-4"><label class="form-label">PPN</label><input class="form-control rupiah" name="vat_amount"></div><div class="col-md-4"><label class="form-label">Total invoice</label><input class="form-control rupiah" name="invoice_total"></div>
  <div class="col-md-4"><label class="form-check mt-4"><input class="form-check-input" type="checkbox" name="has_stamp" value="1"> <strong>Pakai materai Rp10.000</strong></label></div><div class="col-12"><label class="form-label">Catatan</label><textarea class="form-control" name="notes"></textarea></div>
</div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Vendor</button></div></form></div></div>

<div class="modal fade" id="settingsModal" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="{{ route('finance.ebupot-vendors.settings.update') }}">@csrf @method('PUT')<div class="modal-header"><h5>Template WhatsApp & Deadline</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-3"><div class="col-md-6"><label class="form-label">Batas bayar PPh</label><input type="number" class="form-control" name="payment_deadline_day" value="{{ $settings->payment_deadline_day }}"></div><div class="col-md-6"><label class="form-label">Batas lapor SPT</label><input type="number" class="form-control" name="report_deadline_day" value="{{ $settings->report_deadline_day }}"></div>@foreach(['wa_initial_template'=>'WA konfirmasi awal','wa_amount_template'=>'WA konfirmasi nominal','wa_sent_template'=>'WA kirim bukti','email_template'=>'Template email'] as $field=>$label)<div class="col-12"><label class="form-label">{{ $label }}</label><textarea class="form-control" rows="3" name="{{ $field }}">{{ $settings->{$field} }}</textarea></div>@endforeach</div><div class="modal-footer"><button class="btn btn-primary">Simpan Pengaturan</button></div></form></div></div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.rupiah').forEach(function(input){
  function format(){var number=input.value.replace(/\D/g,'');input.value=number?new Intl.NumberFormat('id-ID').format(number):'';}
  format(); input.addEventListener('input',format);
});
document.querySelectorAll('form').forEach(function(form){form.addEventListener('submit',function(){form.querySelectorAll('.rupiah').forEach(function(input){input.value=input.value.replace(/\D/g,'');});});});
</script>
@endpush
