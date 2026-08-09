<!doctype html>
<html><head><meta charset="utf-8"><title>Rekap e-Bupot {{ $record->vendor->name }}</title>
<style>body{font:13px Arial;color:#111;margin:32px}h1{margin:0}.muted{color:#666}table{width:100%;border-collapse:collapse;margin-top:20px}th,td{border:1px solid #999;padding:8px;text-align:left}.ok{color:green}.no{color:#b00}@media print{button{display:none}}</style></head>
<body><button onclick="print()">Cetak</button><h1>LEMBAR KONTROL e-BUPOT VENDOR</h1><p>PT Ovall Solusindo Mandiri · Masa {{ $record->period->translatedFormat('F Y') }}</p>
<table>
<tr><th>Vendor</th><td>{{ $record->vendor->name }}</td><th>Nomor invoice</th><td>{{ $record->invoice_number ?: '-' }}</td></tr>
<tr><th>Tanggal invoice</th><td>{{ optional($record->invoice_date)->format('d/m/Y') ?: '-' }}</td><th>Jatuh tempo</th><td>{{ optional($record->due_date)->format('d/m/Y') ?: '-' }}</td></tr>
<tr><th>NPWP / NITKU</th><td>{{ $record->vendor->npwp ?: '-' }} / {{ $record->vendor->nitku ?: '-' }}</td><th>Kode objek</th><td>{{ $record->vendor->tax_object_code ?: '-' }}</td></tr>
<tr><th>DPP PPh</th><td>Rp {{ number_format($record->tax_base,0,',','.') }}</td><th>PPh</th><td>Rp {{ number_format($record->tax_amount,0,',','.') }}</td></tr>
<tr><th>Materai</th><td>Rp {{ number_format($record->stamp_amount,0,',','.') }}</td><th>Total invoice</th><td>Rp {{ number_format($record->invoice_total,0,',','.') }}</td></tr>
<tr><th>Transfer bersih</th><td>Rp {{ number_format($record->net_transfer,0,',','.') }}</td><th>Status</th><td>{{ strtoupper($record->status) }}</td></tr>
<tr><th>Nomor BPPU Coretax</th><td>{{ $record->ebupot_number ?: '-' }}</td><th>Bukti Potong</th><td>{{ $record->ebupot_file ? 'Sudah diunggah' : 'Belum diunggah' }}</td></tr>
</table>
<h3>Checklist sebelum terbit</h3><table>@foreach($checks as $check)<tr><td>{{ ucwords(str_replace('_',' ',$check)) }}</td><td class="{{ data_get($record->checklist,$check,false)?'ok':'no' }}">{{ data_get($record->checklist,$check,false)?'YA':'BELUM' }}</td></tr>@endforeach</table>
@if($record->requires_escalation)<h2 class="no">STOP / ESKALASI</h2><p>{{ $record->escalation_reason }}</p>@endif
<p class="muted">Catatan: {{ $record->notes ?: '-' }}</p></body></html>
