@extends('layouts/layoutMaster')

@section('title', 'User Access')

@php
  // Kamus ini hanya untuk tampilan halaman Akses Pengguna. Kode permission
  // tetap dipakai sebagai value checkbox agar aturan backend tidak berubah.
  $roleMeta = [
    'super-admin' => ['label' => 'Super Admin', 'description' => 'Akses penuh seluruh modul perusahaan'],
    'owner' => ['label' => 'Owner', 'description' => 'Akses pemilik dan pengawasan perusahaan'],
    'director' => ['label' => 'Direktur', 'description' => 'Akses manajemen tingkat direktur'],
    'general-manager' => ['label' => 'General Manager', 'description' => 'Akses operasional lintas unit'],
    'manager' => ['label' => 'Manager', 'description' => 'Akses pengelolaan unit kerja'],
    'supervisor' => ['label' => 'Supervisor', 'description' => 'Akses supervisi tim'],
    'leader' => ['label' => 'Leader', 'description' => 'Akses pengawasan pekerjaan tim'],
    'hr' => ['label' => 'HR', 'description' => 'Akses pengelolaan sumber daya manusia'],
    'finance' => ['label' => 'Keuangan', 'description' => 'Akses payroll dan biaya pegawai'],
    'noc' => ['label' => 'NOC', 'description' => 'Akses operasional jaringan sesuai izin'],
    'technician' => ['label' => 'Teknisi', 'description' => 'Akses pekerjaan teknis sesuai izin'],
    'marketing' => ['label' => 'Marketing', 'description' => 'Akses pekerjaan marketing sesuai izin'],
    'sales' => ['label' => 'Sales', 'description' => 'Akses pekerjaan sales sesuai izin'],
    'customer-service' => ['label' => 'Customer Service', 'description' => 'Akses layanan pelanggan sesuai izin'],
    'staff' => ['label' => 'Staff', 'description' => 'Akses dasar sesuai penugasan'],
  ];

  $permissionMenuLabels = [
    'dashboard' => 'Dashboard',
    'company' => 'Perusahaan',
    'branch' => 'Branch / Site',
    'division' => 'Divisi',
    'position' => 'Jabatan',
    'employees' => 'Pegawai',
    'employee-document' => 'Dokumen Pegawai',
    'contract-type' => 'Jenis Kontrak',
    'company-document' => 'Master Dokumen',
    'bpjs-registration' => 'Pendaftaran BPJS',
    'bpjs-calculation' => 'BPJS Calculation Engine',
    'attendance' => 'Kehadiran / Absensi',
    'leave' => 'Izin & Cuti',
    'hr-request' => 'Permintaan HR',
    'overtime' => 'Lembur',
    'kpi' => 'KPI',
    'payroll' => 'Payroll',
    'employee-cost' => 'Biaya Pegawai',
    'mobile-release' => 'Rilis OvallHR',
    'business-trip' => 'Perjalanan Dinas',
    'vehicle-cost' => 'Biaya Kendaraan',
    'task' => 'Tugas',
    'project' => 'Proyek',
    'asset' => 'Aset',
    'knowledge' => 'Pengetahuan',
    'meeting' => 'Rapat',
    'report' => 'Laporan',
    'users' => 'Akses Pengguna',
    'roles' => 'Role',
    'permissions' => 'Permission',
    'menus' => 'Menu Sistem',
    'integration' => 'Integrasi',
    'audit' => 'Audit',
    'health' => 'Kesehatan Sistem',
  ];

  $permissionActionLabels = [
    'view' => 'Lihat',
    'create' => 'Tambah',
    'update' => 'Edit',
    'delete' => 'Hapus',
    'manage' => 'Kelola',
    'approve' => 'Setujui',
    'publish' => 'Publikasikan',
    'export' => 'Ekspor',
    'dispatch' => 'Kirim',
    'policy' => 'Atur Kebijakan',
    'assignment' => 'Penugasan',
  ];

  $permissionDescriptions = [
    'dashboard.view' => 'Melihat ringkasan dan statistik perusahaan',
    'users.view' => 'Melihat daftar akun pengguna AppOEMS',
    'users.update' => 'Mengubah divisi, jabatan, status, dan akses akun',
    'roles.view' => 'Melihat daftar role yang tersedia',
    'roles.update' => 'Mengubah role yang diberikan kepada pengguna',
    'permissions.view' => 'Melihat daftar permission sistem',
    'permissions.update' => 'Mengatur permission langsung pengguna',
    'attendance.view' => 'Melihat data kehadiran dan bukti presensi',
    'attendance.approve' => 'Menyetujui atau menolak presensi',
    'attendance.shift.view' => 'Melihat master shift dan jadwal kerja',
    'attendance.shift.assignment.view' => 'Melihat penugasan shift pegawai',
    'payroll.view' => 'Melihat data payroll dan komponen gaji',
    'payroll.approve' => 'Menyetujui proses payroll',
    'mobile-release.manage' => 'Mengunggah dan mempublikasikan rilis OvallHR',
    'integration.manage' => 'Mengelola koneksi integrasi AppBill',
  ];

  // Hitung grup di blok PHP agar directive @foreach tetap sederhana dan
  // kompatibel dengan compiler Blade.
  $permissionGroups = $permissions->groupBy(function ($permission) {
    return explode('.', $permission->name)[0];
  });
@endphp
@section('content')
  <div class="row g-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          <div>
            <h5 class="mb-1">User Access</h5>
            <p class="mb-0 text-muted">Kelola role, divisi, jabatan, dan permission user berdasarkan company aktif.</p>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-label-primary">{{ session('company_name') }}</span>
            <button type="button" class="btn btn-primary" id="btnReloadUsers">
              <i class="ti ti-refresh me-1"></i> Refresh
            </button>
          </div>
        </div>

        <div class="card-body">
          <div id="userAccessAlert"></div>

          <div class="alert alert-info d-flex gap-3 align-items-start mb-4">
            <i class="ti ti-login fs-4"></i>
            <div>
              <div class="fw-medium">Cara login AppOEMS</div>
              <div class="small">Buka <strong>https://oems.osm.net.id/login</strong>, lalu gunakan username atau email akun AppOEMS dan kata sandi yang diberikan Admin/Developer.</div>
              <div class="small mt-1">Akun, role, dan permission di halaman ini mengikuti company aktif. Jika lupa kata sandi, minta reset kepada Developer.</div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle" id="userAccessTable">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Email</th>
                  <th>Perusahaan</th>
                  <th>Divisi</th>
                  <th>Jabatan</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="8" class="text-center text-muted py-5">Memuat data...</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="alert alert-warning mt-4 mb-0">
            <div class="fw-medium">Catatan akses</div>
            <div>
              Role adalah sumber utama permission. Division dan position hanya dasar mapping default.
              Direct permission gunakan hanya jika benar-benar dibutuhkan.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="userAccessCanvas" aria-labelledby="userAccessCanvasLabel">
    <div class="offcanvas-header border-bottom">
      <div>
        <h5 id="userAccessCanvasLabel" class="offcanvas-title">Edit Akses User</h5>
        <small class="text-muted" id="canvasUserEmail"></small>
      </div>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">
      <input type="hidden" id="selectedUserId">

      <div class="mb-3">
        <label class="form-label">Nama lengkap</label>
        <input type="text" class="form-control" id="userName" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Username login</label>
        <input type="text" class="form-control" id="userUsername">
      </div>

      <div class="mb-3">
        <label class="form-label">Email login</label>
        <input type="email" class="form-control" id="userEmail" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Divisi</label>
        <select class="form-select" id="divisionId">
          <option value="">Pilih divisi</option>
          @foreach ($divisions as $division)
            <option value="{{ $division->id }}">{{ $division->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Jabatan</label>
        <select class="form-select" id="positionId">
          <option value="">Pilih jabatan</option>
          @foreach ($positions as $position)
            <option value="{{ $position->id }}">{{ $position->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="mb-4">
        <label class="form-label">Status</label>
        <select class="form-select" id="userStatus">
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="suspended">Suspended</option>
        </select>
      </div>

      <div class="d-grid mb-4">
        <button type="button" class="btn btn-label-primary" id="btnSaveProfile">
          Simpan Data User
        </button>
      </div>

      <div class="d-grid mb-4">
        <button type="button" class="btn btn-outline-danger" id="btnResetPassword">
          Reset Password ke 12345678 (Developer)
        </button>
        <small class="text-muted mt-2">Password sementara berlaku sekali; pengguna wajib menggantinya setelah login.</small>
      </div>
      <hr>

      <div class="mb-3">
        <label class="form-label">Role / Peran Pengguna</label>
        <small class="text-muted d-block mb-2">Role menentukan kelompok akses utama pengguna.</small>
        <div class="list-group">
          @foreach ($roles as $role)
            @php($meta = $roleMeta[$role->name] ?? ['label' => ucwords(str_replace(['-', '_'], ' ', $role->name)), 'description' => 'Kelompok hak akses pengguna'])
            <label class="list-group-item d-flex align-items-start gap-2">
              <input class="form-check-input role-checkbox mt-1" type="checkbox" value="{{ $role->name }}">
              <span>
                <span class="fw-medium d-block">{{ $meta['label'] }}</span>
                <small class="text-muted">{{ $meta['description'] }}</small>
              </span>
            </label>
          @endforeach
        </div>
      </div>

      <div class="d-grid mb-4">
        <button type="button" class="btn btn-primary" id="btnSaveRoles">
          Simpan Role
        </button>
      </div>

      <div class="accordion" id="permissionAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header" id="permissionHeading">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
              data-bs-target="#permissionCollapse">
              Permission Tambahan (Opsional)
            </button>
          </h2>
          <div id="permissionCollapse" class="accordion-collapse collapse" data-bs-parent="#permissionAccordion">
            <div class="accordion-body">
              <div class="alert alert-light border small mb-3">
                <strong>Rincian hak menu (Bahasa Indonesia)</strong><br>
                Centang hanya akses tambahan yang memang diperlukan. Nilai teknis permission tetap disimpan oleh sistem.
              </div>
              <?php foreach ($permissions as $permission): ?>
                <?php
                  $parts = explode('.', $permission->name);
                  $moduleKey = $parts[0] ?? 'other';
                  $action = $parts[1] ?? 'view';
                  $moduleLabel = $permissionMenuLabels[$moduleKey] ?? ucwords(str_replace(['-', '_'], ' ', $moduleKey));
                  $permissionLabel = ($permissionActionLabels[$action] ?? ucwords(str_replace(['-', '_'], ' ', $action))) . ' ' . $moduleLabel;
                  $permissionDescription = $permissionDescriptions[$permission->name] ?? 'Mengatur akses pengguna pada menu ' . $moduleLabel;
                ?>                <div class="border rounded p-3 mb-2">
                  <label class="form-check d-flex align-items-start gap-2 mb-0">
                    <input class="form-check-input permission-checkbox mt-1" type="checkbox" value="{{ $permission->name }}">
                    <span>
                      <span class="badge bg-label-primary mb-1">{{ $moduleLabel }}</span>
                      <span class="form-check-label d-block">{{ $permissionLabel }}</span>
                      <small class="text-muted">{{ $permissionDescription }}</small>
                    </span>
                  </label>
                </div>
              <?php endforeach; ?>
              <div class="d-grid mt-3">
                <button type="button" class="btn btn-label-warning" id="btnSavePermissions">
                  Simpan Permission Tambahan
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const tableBody = document.querySelector('#userAccessTable tbody');
      const alertBox = document.getElementById('userAccessAlert');
      const canvasEl = document.getElementById('userAccessCanvas');
      const canvas = new bootstrap.Offcanvas(canvasEl);

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

      const badgeStatus = status => {
        const map = {
          active: 'success',
          inactive: 'secondary',
          suspended: 'warning',
          locked: 'danger'
        };

        const labels = { active: 'Aktif', inactive: 'Tidak Aktif', suspended: 'Ditangguhkan', locked: 'Terkunci' };
        return `<span class="badge bg-label-${map[status] || 'secondary'}">${labels[status] || status}</span>`;
      };

      const roleLabels = {
        'super-admin': 'Super Admin', owner: 'Owner', director: 'Direktur', 'general-manager': 'General Manager',
        manager: 'Manager', supervisor: 'Supervisor', leader: 'Leader', hr: 'HR', finance: 'Keuangan',
        noc: 'NOC', technician: 'Teknisi', marketing: 'Marketing', sales: 'Sales',
        'customer-service': 'Customer Service', staff: 'Staff'
      };
      const badgeRoles = roles => {
        if (!roles || roles.length === 0) {
          return `<span class="badge bg-label-warning">Belum ada role</span>`;
        }

        return roles.map(role => `<span class="badge bg-label-primary me-1 mb-1">${roleLabels[role] || role}</span>`).join('');
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

      const loadUsers = async () => {
        tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-5">Memuat data...</td></tr>`;

        try {
          const result = await requestJson('/settings/user-access/data');
          const users = result.data || [];

          if (users.length === 0) {
            tableBody.innerHTML =
              `<tr><td colspan="8" class="text-center text-muted py-5">Belum ada data pengguna.</td></tr>`;
            return;
          }

          tableBody.innerHTML = users.map(user => `
        <tr>
          <td>
            <div class="fw-medium">${user.name || '-'}</div>
            ${user.is_super_admin ? '<small class="text-danger">Super Admin</small>' : ''}
          </td>
          <td>${user.email || '-'}</td>
          <td>${user.company || '-'}</td>
          <td>${user.division || '-'}</td>
          <td>${user.position || '-'}</td>
          <td>${badgeRoles(user.roles)}</td>
          <td>${badgeStatus(user.status)}</td>
          <td class="text-end">
            <button type="button" class="btn btn-sm btn-primary btn-edit-access" data-id="${user.id}">
              <i class="ti ti-shield-lock me-1"></i> Edit Akses
            </button>
          </td>
        </tr>
      `).join('');
        } catch (error) {
          tableBody.innerHTML =
            `<tr><td colspan="8" class="text-center text-danger py-5">${error.message}</td></tr>`;
        }
      };

      const clearChecks = () => {
        document.querySelectorAll('.role-checkbox, .permission-checkbox').forEach(el => el.checked = false);
      };

      const openEditor = async userId => {
        clearChecks();

        try {
          const result = await requestJson(`/settings/user-access/${userId}/edit`);

          document.getElementById('selectedUserId').value = result.user.id;
          document.getElementById('userName').value = result.user.name || '';
          document.getElementById('userUsername').value = result.user.username || '';
          document.getElementById('userEmail').value = result.user.email || '';
          document.getElementById('userAccessCanvasLabel').textContent = result.user.name || 'Edit Akses User';
          document.getElementById('canvasUserEmail').textContent = result.user.email || '';
          document.getElementById('divisionId').value = result.user.division_id || '';
          document.getElementById('positionId').value = result.user.position_id || '';
          document.getElementById('userStatus').value = result.user.status || 'active';

          (result.roles || []).forEach(role => {
            const checkbox = document.querySelector(`.role-checkbox[value="${role}"]`);
            if (checkbox) checkbox.checked = true;
          });

          (result.permissions || []).forEach(permission => {
            const checkbox = document.querySelector(`.permission-checkbox[value="${permission}"]`);
            if (checkbox) checkbox.checked = true;
          });

          canvas.show();
        } catch (error) {
          showAlert('danger', error.message);
        }
      };

      document.getElementById('btnReloadUsers').addEventListener('click', loadUsers);

      tableBody.addEventListener('click', function(event) {
        const button = event.target.closest('.btn-edit-access');
        if (!button) return;

        openEditor(button.dataset.id);
      });

      document.getElementById('btnSaveProfile').addEventListener('click', async function() {
        const userId = document.getElementById('selectedUserId').value;

        try {
          const result = await requestJson(`/settings/user-access/${userId}`, {
            method: 'PUT',
            body: JSON.stringify({
              name: document.getElementById('userName').value,
              username: document.getElementById('userUsername').value || null,
              email: document.getElementById('userEmail').value,
              division_id: document.getElementById('divisionId').value || null,
              position_id: document.getElementById('positionId').value || null,
              status: document.getElementById('userStatus').value,
              is_active: document.getElementById('userStatus').value === 'active'
            })
          });

          showAlert('success', result.message);
          loadUsers();
        } catch (error) {
          showAlert('danger', error.message);
        }
      });

      document.getElementById('btnResetPassword').addEventListener('click', async function() {
        const userId = document.getElementById('selectedUserId').value;
        if (!confirm('Reset password pengguna ini ke 12345678?')) return;
        try {
          const result = await requestJson(`/settings/user-access/${userId}/reset-password`, { method: 'POST' });
          showAlert('success', result.message);
        } catch (error) {
          showAlert('danger', error.message);
        }
      });
      document.getElementById('btnSaveRoles').addEventListener('click', async function() {
        const userId = document.getElementById('selectedUserId').value;
        const roles = [...document.querySelectorAll('.role-checkbox:checked')].map(el => el.value);

        try {
          const result = await requestJson(`/settings/user-access/${userId}/assign-role`, {
            method: 'POST',
            body: JSON.stringify({
              roles
            })
          });

          showAlert('success', result.message);
          loadUsers();
        } catch (error) {
          showAlert('danger', error.message);
        }
      });

      document.getElementById('btnSavePermissions').addEventListener('click', async function() {
        const userId = document.getElementById('selectedUserId').value;
        const permissions = [...document.querySelectorAll('.permission-checkbox:checked')].map(el => el.value);

        try {
          const result = await requestJson(`/settings/user-access/${userId}/assign-permission`, {
            method: 'POST',
            body: JSON.stringify({
              permissions
            })
          });

          showAlert('success', result.message);
          loadUsers();
        } catch (error) {
          showAlert('danger', error.message);
        }
      });

      loadUsers();
    });
  </script>
@endsection

