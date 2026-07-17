<?php $__env->startSection('title', 'User Access'); ?>

<?php $__env->startSection('content'); ?>
  <div class="row g-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          <div>
            <h5 class="mb-1">User Access</h5>
            <p class="mb-0 text-muted">Kelola role, divisi, jabatan, dan permission user berdasarkan company aktif.</p>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-label-primary"><?php echo e(session('company_name')); ?></span>
            <button type="button" class="btn btn-primary" id="btnReloadUsers">
              <i class="ti ti-refresh me-1"></i> Refresh
            </button>
          </div>
        </div>

        <div class="card-body">
          <div id="userAccessAlert"></div>

          <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle" id="userAccessTable">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Email</th>
                  <th>Company</th>
                  <th>Divisi</th>
                  <th>Jabatan</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="8" class="text-center text-muted py-5">Loading data...</td>
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
        <label class="form-label">Divisi</label>
        <select class="form-select" id="divisionId">
          <option value="">Pilih divisi</option>
          <?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($division->id); ?>"><?php echo e($division->name); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Jabatan</label>
        <select class="form-select" id="positionId">
          <option value="">Pilih jabatan</option>
          <?php $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($position->id); ?>"><?php echo e($position->name); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

      <hr>

      <div class="mb-3">
        <label class="form-label">Role</label>
        <div class="list-group">
          <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <label class="list-group-item d-flex align-items-center gap-2">
              <input class="form-check-input role-checkbox" type="checkbox" value="<?php echo e($role->name); ?>">
              <span class="fw-medium"><?php echo e($role->name); ?></span>
            </label>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
              Direct Permission Optional
            </button>
          </h2>
          <div id="permissionCollapse" class="accordion-collapse collapse" data-bs-parent="#permissionAccordion">
            <div class="accordion-body">
              <div class="row g-2">
                <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <div class="col-12">
                    <label class="form-check">
                      <input class="form-check-input permission-checkbox" type="checkbox" value="<?php echo e($permission->name); ?>">
                      <span class="form-check-label"><?php echo e($permission->name); ?></span>
                    </label>
                  </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </div>

              <div class="d-grid mt-3">
                <button type="button" class="btn btn-label-warning" id="btnSavePermissions">
                  Simpan Direct Permission
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-script'); ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const tableBody = document.querySelector('#userAccessTable tbody');
      const alertBox = document.getElementById('userAccessAlert');
      const canvasEl = document.getElementById('userAccessCanvas');
      const canvas = new bootstrap.Offcanvas(canvasEl);

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

      const badgeStatus = status => {
        const map = {
          active: 'success',
          inactive: 'secondary',
          suspended: 'warning',
          locked: 'danger'
        };

        return `<span class="badge bg-label-${map[status] || 'secondary'}">${status}</span>`;
      };

      const badgeRoles = roles => {
        if (!roles || roles.length === 0) {
          return `<span class="badge bg-label-warning">Belum ada role</span>`;
        }

        return roles.map(role => `<span class="badge bg-label-primary me-1 mb-1">${role}</span>`).join('');
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
        tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-5">Loading data...</td></tr>`;

        try {
          const result = await requestJson('/settings/user-access/data');
          const users = result.data || [];

          if (users.length === 0) {
            tableBody.innerHTML =
              `<tr><td colspan="8" class="text-center text-muted py-5">Belum ada data user.</td></tr>`;
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/layoutMaster', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/auth/user_access_index.blade.php ENDPATH**/ ?>