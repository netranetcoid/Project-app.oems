@extends('layouts.contentNavbarLayout')

@section('title', 'Master Dokumen Perusahaan')

@section('content')
<div class="container-fluid">
  <div class="card mb-4 border-primary">
    <div class="card-body d-flex flex-column flex-lg-row gap-3 justify-content-between align-items-lg-center">
      <div>
        <h4 class="mb-1"><i class="ri ri-folder-paper-line me-2"></i>Master Dokumen Perusahaan</h4>
        <p class="mb-0 text-muted">Satu pusat template PT OSM: SOP, MOU, surat, nota, surat jalan, dan checklist kepatuhan.</p>
      </div>
      <a class="btn btn-primary" href="{{ route('master.company-documents.create') }}"><i class="ri ri-add-line me-1"></i>Tambah Dokumen</a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="GET" class="row g-2 mb-4">
        <div class="col-md-5"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, atau kategori..."></div>
        <div class="col-auto"><button class="btn btn-outline-primary">Cari</button></div>
      </form>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead><tr><th>Dokumen</th><th>Kategori</th><th>Versi</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
          <tbody>
            @forelse($documents as $document)
              <tr>
                <td><div class="fw-semibold">{{ $document->name }}</div><small class="text-muted">{{ $document->code }} · {{ $document->subject ?: 'Tanpa subjek' }}</small></td>
                <td><span class="badge bg-label-info">{{ $categories[$document->category] ?? $document->category }}</span></td>
                <td>v{{ $document->template_version }}</td>
                <td><span class="badge bg-label-{{ $document->is_active ? 'success' : 'secondary' }}">{{ $document->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td class="text-end text-nowrap">
                  <a class="btn btn-sm btn-outline-primary" title="Preview / cetak" href="{{ route('master.company-documents.show', $document) }}"><i class="ri ri-printer-line"></i></a>
                  <a class="btn btn-sm btn-outline-warning" title="Edit master" href="{{ route('master.company-documents.edit', $document) }}"><i class="ri ri-edit-line"></i></a>
                  @if(!$document->is_system)
                    <form method="POST" action="{{ route('master.company-documents.destroy', $document) }}" class="d-inline" onsubmit="return confirm('Hapus master dokumen ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="ri ri-delete-bin-line"></i></button></form>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-5">Belum ada master dokumen. Jalankan seeder untuk template bawaan PT OSM.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $documents->links() }}</div>
    </div>
  </div>
</div>
@endsection
