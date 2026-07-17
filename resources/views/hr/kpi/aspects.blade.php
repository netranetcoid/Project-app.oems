@extends('layouts.contentNavbarLayout')

@section('title', 'Master Aspek KPI')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1">Master Aspek KPI</h4><p class="text-muted mb-0">Aspek ini dipakai untuk membentuk standar KPI setiap jabatan.</p></div>
        <a href="{{ route('hr.kpi.index') }}" class="btn btn-label-secondary">Kembali</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="row">
        <div class="col-lg-4 mb-4"><div class="card"><div class="card-header"><h5 class="mb-0">Tambah Aspek</h5></div><div class="card-body">
            <form method="POST" action="{{ route('hr.kpi.aspects.store') }}">@csrf
                <div class="mb-3"><label class="form-label">Kode</label><input name="code" value="{{ old('code') }}" class="form-control @error('code') is-invalid @enderror" placeholder="contoh: QUALITY" required>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="mb-3"><label class="form-label">Nama Aspek</label><input name="name" value="{{ old('name') }}" class="form-control" placeholder="Contoh: Kualitas Penyelesaian" required></div>
                <div class="mb-3"><label class="form-label">Kategori</label><input name="category" value="{{ old('category') }}" class="form-control" placeholder="Contoh: Operasional"></div>
                <div class="mb-3"><label class="form-label">Deskripsi / acuan default</label><textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea></div>
                <div class="form-check mb-3"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" value="1" name="is_active" id="active" checked><label class="form-check-label" for="active">Aktif</label></div>
                <button class="btn btn-primary w-100">Simpan Aspek</button>
            </form>
        </div></div></div>
        <div class="col-lg-8"><div class="card"><div class="card-header"><h5 class="mb-0">Daftar Aspek</h5></div><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Kode</th><th>Aspek</th><th>Kategori</th><th>Status</th></tr></thead><tbody>
            @forelse($aspects as $aspect)<tr><td>{{ $aspect->code }}</td><td><strong>{{ $aspect->name }}</strong><br><small class="text-muted">{{ $aspect->description }}</small></td><td>{{ $aspect->category ?: '-' }}</td><td><span class="badge bg-label-{{ $aspect->is_active ? 'success' : 'secondary' }}">{{ $aspect->is_active ? 'Aktif' : 'Nonaktif' }}</span></td></tr>
            @empty<tr><td colspan="4" class="text-center py-5 text-muted">Belum ada aspek KPI.</td></tr>@endforelse
        </tbody></table></div><div class="card-body">{{ $aspects->links() }}</div></div></div>
    </div>
</div>
@endsection
