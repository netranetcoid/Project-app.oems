@extends('layouts/layoutMaster')

@section('title', 'Hak Akses Peran')

@section('content')
  @php
    /* Hanya label tampilan. Nama permission asli tetap dikirim saat penyimpanan. */
    $roleLabels = [
      'viewer' => 'Peninjau',
      'marketing' => 'Pemasaran',
      'sales' => 'Penjualan',
      'technician' => 'Teknisi',
      'noc' => 'NOC / Operasional Jaringan',
      'hr' => 'HR / Sumber Daya Manusia',
      'finance' => 'Keuangan',
      'management' => 'Manajemen',
      'general-manager' => 'General Manager',
      'company-admin' => 'Admin Perusahaan',
      'company-owner' => 'Pemilik Perusahaan',
      'owner' => 'Pemilik / Owner',
      'super-admin' => 'Super Admin',
      'developer' => 'Developer',
    ];

    $moduleLabels = [
      'dashboard' => 'Dashboard', 'company' => 'Perusahaan', 'branch' => 'Branch / Site',
      'division' => 'Divisi', 'position' => 'Jabatan', 'employees' => 'Pegawai',
      'employee' => 'Pegawai', 'employee-document' => 'Dokumen Pegawai',
      'contract' => 'Kontrak', 'contract-type' => 'Jenis Kontrak',
      'company-document' => 'Dokumen Perusahaan', 'attendance' => 'Presensi',
      'attendance.shift' => 'Master Shift', 'attendance.shift.assignment' => 'Jadwal Kerja',
      'attendance.report' => 'Rekap Presensi', 'overtime' => 'Lembur',
      'leave' => 'Cuti / Izin', 'kpi' => 'KPI', 'task' => 'Tugas',
      'project' => 'Proyek', 'knowledge' => 'Pengetahuan', 'payroll' => 'Payroll',
      'payslip' => 'Slip Gaji', 'employee-cost' => 'Biaya Pegawai',
      'bpjs-calculation' => 'Perhitungan BPJS', 'bpjs-registration' => 'Pendaftaran BPJS',
      'business-trip' => 'Perjalanan Dinas', 'finance' => 'Keuangan',
      'report' => 'Laporan', 'mobile-release' => 'Rilis OvallHR',
      'ovallhr' => 'OvallHR Control', 'integration' => 'Integrasi',
      'appbill' => 'Integrasi AppBill', 'users' => 'Akun Pengguna',
      'user-access' => 'Akses Pengguna', 'roles' => 'Peran',
      'role' => 'Peran', 'permissions' => 'Hak Akses', 'permission' => 'Hak Akses',
      'menu' => 'Menu', 'settings' => 'Pengaturan', 'health' => 'Kesehatan Sistem',
      'announcement' => 'Pengumuman', 'document' => 'Dokumen',
      'ebupot-vendor' => 'e-Bupot Vendor', 'work-tracking' => 'Tracking Pegawai',
    ];

    $actionLabels = [
      'view' => 'Melihat', 'create' => 'Menambah', 'store' => 'Menyimpan',
      'update' => 'Mengubah', 'edit' => 'Mengubah', 'delete' => 'Menghapus',
      'destroy' => 'Menghapus', 'approve' => 'Menyetujui / Menolak',
      'publish' => 'Menerbitkan', 'print' => 'Mencetak', 'export' => 'Mengekspor',
      'import' => 'Mengimpor', 'download' => 'Mengunduh', 'upload' => 'Mengunggah',
      'assign' => 'Menetapkan', 'manage' => 'Mengelola', 'process' => 'Memproses',
      'dispatch' => 'Mengirim', 'reset' => 'Mereset', 'configure' => 'Mengatur',
      'toggle' => 'Mengaktifkan / Menonaktifkan', 'history' => 'Melihat Riwayat',
    ];

    $groupLabels = [
      'dashboard' => 'Dashboard', 'company' => 'Perusahaan', 'branch' => 'Branch / Site',
      'division' => 'Divisi', 'position' => 'Jabatan', 'employees' => 'Pegawai',
      'employee' => 'Pegawai', 'attendance' => 'Presensi & Jadwal Kerja',
      'overtime' => 'Lembur', 'leave' => 'Cuti & Izin', 'kpi' => 'KPI',
      'task' => 'Tugas', 'project' => 'Proyek', 'knowledge' => 'Pengetahuan',
      'payroll' => 'Payroll & Slip Gaji', 'payslip' => 'Payroll & Slip Gaji',
      'bpjs-calculation' => 'BPJS', 'bpjs-registration' => 'BPJS',
      'finance' => 'Keuangan', 'report' => 'Laporan', 'integration' => 'Integrasi',
      'mobile-release' => 'OvallHR', 'ovallhr' => 'OvallHR', 'users' => 'Pengguna & Keamanan',
      'user-access' => 'Pengguna & Keamanan', 'roles' => 'Pengguna & Keamanan',
      'permissions' => 'Pengguna & Keamanan', 'settings' => 'Pengaturan Sistem',
      'menu' => 'Pengaturan Sistem', 'ebupot-vendor' => 'e-Bupot Vendor',
      'work-tracking' => 'Tracking Pegawai',
    ];

    $permissionLabel = function (string $name) use ($moduleLabels, $actionLabels) {
      $parts = explode('.', $name);
      $action = count($parts) > 1 ? array_pop($parts) : 'manage';
      $resource = implode('.', $parts);
      $module = $moduleLabels[$resource] ?? $moduleLabels[$parts[0] ?? '']
        ?? \Illuminate\Support\Str::headline(str_replace(['.', '-', '_'], ' ', $resource ?: $name));
      $verb = $actionLabels[$action] ?? \Illuminate\Support\Str::headline(str_replace(['-', '_'], ' ', $action));
      return trim($verb . ' ' . $module);
    };

    $permissionLabels = $permissionGroups->flatten()->mapWithKeys(
      fn ($permission) => [$permission->name => $permissionLabel($permission->name)]
    );
  @endphp
  <div class="row g-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          <div>
            <h5 class="mb-1">Hak Akses Peran</h5>
            <p class="mb-0 text-muted">
              Tentukan siapa yang boleh melihat, menambah, mengubah, menghapus, atau menyetujui data pada perusahaan aktif.
            </p>
          </div>

          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-label-primary">{{ session('company_name') }}</span>

            @can('role.create')
              <button type="button" class="btn btn-primary" id="btnCreateRole">
                <i class="ti ti-plus me-1"></i> Tambah Peran
              </button>
            @endcan

            <button type="button" class="btn btn-label-secondary" id="btnReloadRoles">
              <i class="ti ti-refresh me-1"></i> Muat Ulang
            </button>
          </div>
        </div>

        <div class="card-body">
          <div id="rolePermissionAlert"></div>

          <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle" id="rolePermissionTable">
              <thead>
                <tr>
                  <th>Peran</th>
                  <th>Cakupan</th>
                  <th>Jumlah Hak Akses</th>
                  <th>Ringkasan yang Diizinkan</th>
                  <th>Status</th>
                  <th class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="6" class="text-center text-muted py-5">Memuat data...</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="alert alert-info mt-4 mb-0">
            <div class="fw-medium">Cara membaca hak akses</div>
            <div>
              Peran menentukan menu dan tindakan yang dapat dilakukan pengguna. Divisi dan jabatan hanya membantu menetapkan peran awal.
              Untuk pengecualian pada satu orang, gunakan izin langsung pada menu Akses Pengguna. Perubahan di sini tidak mengubah data pegawai.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="rolePermissionCanvas">
    <div class="offcanvas-header border-bottom">
      <div>
        <h5 class="offcanvas-title" id="roleCanvasTitle">Hak Akses Peran</h5>
        <small class="text-muted" id="roleCanvasSubtitle">Atur tindakan yang boleh dilakukan peran ini.</small>
      </div>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">
      <input type="hidden" id="selectedRoleId">

      <div class="mb-3">
        <label class="form-label">Nama Peran</label>
        <input type="text" class="form-control" id="roleName" placeholder="contoh: area-manager">
        <div class="form-text">
          Gunakan huruf kecil, angka, titik, underscore, atau strip.
        </div>
      </div>

      <div class="d-flex align-items-center justify-content-between mb-3">
        <label class="form-label mb-0">Daftar Hak Akses</label>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-label-primary" id="btnCheckAll">
            Pilih Semua
          </button>
          <button type="button" class="btn btn-sm btn-label-secondary" id="btnUncheckAll">
            Kosongkan
          </button>
        </div>
      </div>

      <div class="accordion" id="permissionGroupAccordion">
        @foreach ($permissionGroups as $groupName => $permissions)
          <div class="accordion-item">
            <h2 class="accordion-header" id="heading-{{ $groupName }}">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapse-{{ $groupName }}">
                <span>{{ $groupLabels[$groupName] ?? \Illuminate\Support\Str::headline($groupName) }}</span>
                <span class="badge bg-label-primary ms-2">{{ $permissions->count() }}</span>
              </button>
            </h2>

            <div id="collapse-{{ $groupName }}" class="accordion-collapse collapse"
              data-bs-parent="#permissionGroupAccordion">
              <div class="accordion-body">
                <div class="row g-2">
                  @foreach ($permissions as $permission)
                    <div class="col-12">
                      <label class="form-check">
                        <input class="form-check-input permission-checkbox" type="checkbox"
                          value="{{ $permission->name }}">
                        <span class="form-check-label" title="Kode sistem: {{ $permission->name }}"><strong>{{ $permissionLabel($permission->name) }}</strong><br><small class="text-muted">{{ $permission->name }}</small></span>
                      </label>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="d-grid gap-2 mt-4">
        <button type="button" class="btn btn-primary" id="btnSaveRole">
          Simpan Hak Akses
        </button>

        <button type="button" class="btn btn-label-danger d-none" id="btnDeleteRole">
          Hapus Peran
        </button>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const roleLabels = @json($roleLabels);
      const permissionLabels = @json($permissionLabels);
      const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
      })[character]);
      const roleLabel = role => roleLabels[role] || String(role || '').replace(/[-_.]+/g, ' ')
        .replace(/\b\w/g, letter => letter.toUpperCase());
      const tableBody = document.querySelector('#rolePermissionTable tbody');
      const alertBox = document.getElementById('rolePermissionAlert');

      const canvasEl = document.getElementById('rolePermissionCanvas');
      const canvas = new bootstrap.Offcanvas(canvasEl);

      const selectedRoleId = document.getElementById('selectedRoleId');
      const roleName = document.getElementById('roleName');
      const roleCanvasTitle = document.getElementById('roleCanvasTitle');
      const roleCanvasSubtitle = document.getElementById('roleCanvasSubtitle');
      const btnSaveRole = document.getElementById('btnSaveRole');
      const btnDeleteRole = document.getElementById('btnDeleteRole');

      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
      '{{ csrf_token() }}';

      const showAlert = (type, message) => {
        alertBox.innerHTML = `
      <div class="alert alert-${type} alert-dismissible" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;
      };

      const requestJson = async (url, options = {}) => {
        const response = await fetch(url, {
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            ...(options.headers || {})
          },
          ...options
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
          const message = data.message || Object.values(data.errors || {}).flat().join('<br>') ||
            'Terjadi kesalahan.';
          throw new Error(message);
        }

        return data;
      };

      const permissionBadges = permissions => {
        if (!permissions || permissions.length === 0) {
          return `<span class="badge bg-label-warning">Belum ada permission</span>`;
        }

        const visible = permissions.slice(0, 6).map(permission => {
          return `<span class="badge bg-label-secondary me-1 mb-1">${escapeHtml(permissionLabels[permission] || permission)}</span>`;
        }).join('');

        const more = permissions.length > 6 ?
          `<span class="badge bg-label-primary me-1 mb-1">+${permissions.length - 6} lainnya</span>` :
          '';

        return visible + more;
      };

      const scopeBadge = scope => {
        if (scope === 'global') {
          return `<span class="badge bg-label-dark">Semua Perusahaan</span>`;
        }

        return `<span class="badge bg-label-primary">Perusahaan Aktif</span>`;
      };

      const loadRoles = async () => {
        tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-5">Memuat data...</td></tr>`;

        try {
          const result = await requestJson('/settings/role-permission/data');
          const roles = result.data || [];

          if (roles.length === 0) {
            tableBody.innerHTML =
              `<tr><td colspan="6" class="text-center text-muted py-5">Belum ada peran.</td></tr>`;
            return;
          }

          tableBody.innerHTML = roles.map(role => {
            const editButton = role.can_edit ?
              `<button type="button" class="btn btn-sm btn-primary btn-edit-role" data-id="${role.id}">
              <i class="ti ti-shield-lock me-1"></i> Atur Akses
            </button>` :
              `<span class="text-muted small">Tidak dapat diubah</span>`;

            const statusBadge = role.is_protected ?
              `<span class="badge bg-label-warning">Dilindungi Sistem</span>` :
              `<span class="badge bg-label-success">Dapat Disesuaikan</span>`;

            return `
          <tr>
            <td>
              <div class="fw-medium">${escapeHtml(roleLabel(role.name))}</div><small class="text-muted">Kode: ${escapeHtml(role.name)}</small>
              <small class="text-muted">ID: ${role.id}</small>
            </td>
            <td>${scopeBadge(role.scope)}</td>
            <td>
              <span class="badge bg-label-info">${role.permissions_count}</span>
            </td>
            <td>${permissionBadges(role.permissions)}</td>
            <td>${statusBadge}</td>
            <td class="text-end">${editButton}</td>
          </tr>
        `;
          }).join('');
        } catch (error) {
          tableBody.innerHTML =
            `<tr><td colspan="6" class="text-center text-danger py-5">${error.message}</td></tr>`;
        }
      };

      const clearForm = () => {
        selectedRoleId.value = '';
        roleName.value = '';
        roleName.disabled = false;
        btnDeleteRole.classList.add('d-none');

        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
          checkbox.checked = false;
        });
      };

      const openCreateCanvas = () => {
        clearForm();

        roleCanvasTitle.textContent = 'Tambah Peran';
        roleCanvasSubtitle.textContent = 'Buat peran khusus untuk perusahaan aktif.';
        btnSaveRole.textContent = 'Buat Peran';

        canvas.show();
      };

      const openEditCanvas = async roleId => {
        clearForm();

        try {
          const result = await requestJson(`/settings/role-permission/${roleId}/edit`);

          selectedRoleId.value = result.role.id;
          roleName.value = result.role.name;
          roleCanvasTitle.textContent = `Atur Akses: ${roleLabel(result.role.name)}`;
          roleCanvasSubtitle.textContent = result.role.scope === 'global' ? 'Cakupan: Semua perusahaan' : 'Cakupan: Perusahaan aktif';
          btnSaveRole.textContent = 'Simpan Perubahan';

          if (!result.role.is_protected) {
            btnDeleteRole.classList.remove('d-none');
          }

          if (result.role.is_protected) {
            roleName.disabled = true;
          }

          (result.permissions || []).forEach(permission => {
            const checkbox = document.querySelector(`.permission-checkbox[value="${permission}"]`);
            if (checkbox) checkbox.checked = true;
          });

          canvas.show();
        } catch (error) {
          showAlert('danger', error.message);
        }
      };

      const saveRole = async () => {
        const roleId = selectedRoleId.value;
        const permissions = [...document.querySelectorAll('.permission-checkbox:checked')].map(el => el.value);

        const payload = {
          name: roleName.value.trim(),
          permissions
        };

        if (!payload.name) {
          showAlert('danger', 'Nama peran wajib diisi.');
          return;
        }

        try {
          const result = roleId ?
            await requestJson(`/settings/role-permission/${roleId}`, {
              method: 'PUT',
              body: JSON.stringify(payload)
            }) :
            await requestJson('/settings/role-permission', {
              method: 'POST',
              body: JSON.stringify(payload)
            });

          showAlert('success', result.message);
          canvas.hide();
          loadRoles();
        } catch (error) {
          showAlert('danger', error.message);
        }
      };

      const deleteRole = async () => {
        const roleId = selectedRoleId.value;

        if (!roleId) {
          return;
        }

        const confirmed = confirm('Yakin menghapus peran ini? Peran yang masih dipakai pengguna tidak dapat dihapus.');

        if (!confirmed) {
          return;
        }

        try {
          const result = await requestJson(`/settings/role-permission/${roleId}`, {
            method: 'DELETE'
          });

          showAlert('success', result.message);
          canvas.hide();
          loadRoles();
        } catch (error) {
          showAlert('danger', error.message);
        }
      };

      document.getElementById('btnReloadRoles').addEventListener('click', loadRoles);

      const btnCreateRole = document.getElementById('btnCreateRole');
      if (btnCreateRole) {
        btnCreateRole.addEventListener('click', openCreateCanvas);
      }

      tableBody.addEventListener('click', function(event) {
        const button = event.target.closest('.btn-edit-role');

        if (!button) {
          return;
        }

        openEditCanvas(button.dataset.id);
      });

      document.getElementById('btnCheckAll').addEventListener('click', function() {
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
          checkbox.checked = true;
        });
      });

      document.getElementById('btnUncheckAll').addEventListener('click', function() {
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
          checkbox.checked = false;
        });
      });

      btnSaveRole.addEventListener('click', saveRole);
      btnDeleteRole.addEventListener('click', deleteRole);

      loadRoles();
    });
  </script>
@endsection
