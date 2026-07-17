<?php $__env->startSection('title', 'Role Permission'); ?>

<?php $__env->startSection('content'); ?>
  <div class="row g-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          <div>
            <h5 class="mb-1">Role Permission</h5>
            <p class="mb-0 text-muted">
              Kelola role dan permission berdasarkan company aktif.
            </p>
          </div>

          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-label-primary"><?php echo e(session('company_name')); ?></span>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('role.create')): ?>
              <button type="button" class="btn btn-primary" id="btnCreateRole">
                <i class="ti ti-plus me-1"></i> Tambah Role
              </button>
            <?php endif; ?>

            <button type="button" class="btn btn-label-secondary" id="btnReloadRoles">
              <i class="ti ti-refresh me-1"></i> Refresh
            </button>
          </div>
        </div>

        <div class="card-body">
          <div id="rolePermissionAlert"></div>

          <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle" id="rolePermissionTable">
              <thead>
                <tr>
                  <th>Role</th>
                  <th>Scope</th>
                  <th>Total Permission</th>
                  <th>Permission</th>
                  <th>Status</th>
                  <th class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="6" class="text-center text-muted py-5">Loading data...</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="alert alert-info mt-4 mb-0">
            <div class="fw-medium">Catatan</div>
            <div>
              Role adalah sumber utama hak akses. Divisi dan jabatan hanya membantu mapping role default.
              Untuk akses khusus user tertentu, gunakan direct permission di menu User Access.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="rolePermissionCanvas">
    <div class="offcanvas-header border-bottom">
      <div>
        <h5 class="offcanvas-title" id="roleCanvasTitle">Role Permission</h5>
        <small class="text-muted" id="roleCanvasSubtitle">Atur permission untuk role.</small>
      </div>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">
      <input type="hidden" id="selectedRoleId">

      <div class="mb-3">
        <label class="form-label">Nama Role</label>
        <input type="text" class="form-control" id="roleName" placeholder="contoh: area-manager">
        <div class="form-text">
          Gunakan huruf kecil, angka, titik, underscore, atau strip.
        </div>
      </div>

      <div class="d-flex align-items-center justify-content-between mb-3">
        <label class="form-label mb-0">Permission</label>
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
        <?php $__currentLoopData = $permissionGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $permissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="accordion-item">
            <h2 class="accordion-header" id="heading-<?php echo e($groupName); ?>">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapse-<?php echo e($groupName); ?>">
                <span class="text-capitalize"><?php echo e($groupName); ?></span>
                <span class="badge bg-label-primary ms-2"><?php echo e($permissions->count()); ?></span>
              </button>
            </h2>

            <div id="collapse-<?php echo e($groupName); ?>" class="accordion-collapse collapse"
              data-bs-parent="#permissionGroupAccordion">
              <div class="accordion-body">
                <div class="row g-2">
                  <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-12">
                      <label class="form-check">
                        <input class="form-check-input permission-checkbox" type="checkbox"
                          value="<?php echo e($permission->name); ?>">
                        <span class="form-check-label"><?php echo e($permission->name); ?></span>
                      </label>
                    </div>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>

      <div class="d-grid gap-2 mt-4">
        <button type="button" class="btn btn-primary" id="btnSaveRole">
          Simpan Role Permission
        </button>

        <button type="button" class="btn btn-label-danger d-none" id="btnDeleteRole">
          Hapus Role
        </button>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-script'); ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
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
      '<?php echo e(csrf_token()); ?>';

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
          return `<span class="badge bg-label-secondary me-1 mb-1">${permission}</span>`;
        }).join('');

        const more = permissions.length > 6 ?
          `<span class="badge bg-label-primary me-1 mb-1">+${permissions.length - 6} lainnya</span>` :
          '';

        return visible + more;
      };

      const scopeBadge = scope => {
        if (scope === 'global') {
          return `<span class="badge bg-label-dark">Global</span>`;
        }

        return `<span class="badge bg-label-primary">Company</span>`;
      };

      const loadRoles = async () => {
        tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-5">Loading data...</td></tr>`;

        try {
          const result = await requestJson('/settings/role-permission/data');
          const roles = result.data || [];

          if (roles.length === 0) {
            tableBody.innerHTML =
              `<tr><td colspan="6" class="text-center text-muted py-5">Belum ada role.</td></tr>`;
            return;
          }

          tableBody.innerHTML = roles.map(role => {
            const editButton = role.can_edit ?
              `<button type="button" class="btn btn-sm btn-primary btn-edit-role" data-id="${role.id}">
              <i class="ti ti-shield-lock me-1"></i> Edit
            </button>` :
              `<span class="text-muted small">Tidak bisa diedit</span>`;

            const statusBadge = role.is_protected ?
              `<span class="badge bg-label-warning">Protected</span>` :
              `<span class="badge bg-label-success">Custom</span>`;

            return `
          <tr>
            <td>
              <div class="fw-medium">${role.name}</div>
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

        roleCanvasTitle.textContent = 'Tambah Role';
        roleCanvasSubtitle.textContent = 'Buat role custom untuk company aktif.';
        btnSaveRole.textContent = 'Buat Role';

        canvas.show();
      };

      const openEditCanvas = async roleId => {
        clearForm();

        try {
          const result = await requestJson(`/settings/role-permission/${roleId}/edit`);

          selectedRoleId.value = result.role.id;
          roleName.value = result.role.name;
          roleCanvasTitle.textContent = `Edit Role: ${result.role.name}`;
          roleCanvasSubtitle.textContent = `Scope: ${result.role.scope}`;
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
          showAlert('danger', 'Nama role wajib diisi.');
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

        const confirmed = confirm('Yakin hapus role ini? Role yang masih dipakai user tidak bisa dihapus.');

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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/layoutMaster', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/setting/role_permission_index.blade.php ENDPATH**/ ?>