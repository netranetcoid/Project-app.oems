

<?php $__env->startSection('title', 'Detail Pegawai'); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    <div class="row">

        <div class="col-xl-4 col-lg-5">

            <div class="card">

                <div class="card-body text-center">

                    <img
                        src="<?php echo e($employee->photo_url); ?>"
                        class="rounded-circle mb-3"
                        width="130"
                        height="130">

                    <h4 class="mb-1">
                        <?php echo e($employee->name); ?>

                    </h4>

                    <p class="text-muted mb-3">
                        <?php echo e($employee->position?->name); ?>

                    </p>

                    <?php if($employee->work_status=='active'): ?>

                        <span class="badge bg-success">
                            Pegawai Aktif
                        </span>

                    <?php else: ?>

                        <span class="badge bg-danger">
                            Non Aktif
                        </span>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <div class="col-xl-8 col-lg-7">

            <div class="card">

                <div class="card-header">

                    <h5 class="mb-0">

                        Informasi Pegawai

                    </h5>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Nomor Pegawai

                            </label>

                            <div>

                                <strong>

                                    <?php echo e($employee->employee_no); ?>


                                </strong>

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Email

                            </label>

                            <div>

                                <?php echo e($employee->email); ?>


                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Site

                            </label>

                            <div>

                                <?php echo e($employee->branch?->name); ?>


                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Divisi

                            </label>

                            <div>

                                <?php echo e($employee->division?->name); ?>


                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Jabatan

                            </label>

                            <div>

                                <?php echo e($employee->position?->name); ?>


                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Tanggal Masuk

                            </label>

                            <div>

                                <?php echo e(optional($employee->join_date)->format('d M Y')); ?>


                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<div class="row mt-4">

    <div class="col-12">

        <div class="card">

            <div class="card-header border-bottom">

                <ul class="nav nav-tabs card-header-tabs" role="tablist">

                    <li class="nav-item">

                        <button class="nav-link active"
                                data-bs-toggle="tab"
                                data-bs-target="#profile"
                                type="button">

                            <i class="ti ti-user me-1"></i>

                            Profile

                        </button>

                    </li>

                    <li class="nav-item">

                        <button class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#employment"
                                type="button">

                            <i class="ti ti-briefcase me-1"></i>

                            Employment

                        </button>

                    </li>

                    <li class="nav-item">

                        <button class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#contract"
                                type="button">

                            <i class="ti ti-file-description me-1"></i>

                            Contract

                        </button>

                    </li>

                    <li class="nav-item">

                        <button class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#attendance"
                                type="button">

                            <i class="ti ti-calendar-check me-1"></i>

                            Attendance

                        </button>

                    </li>

                    <li class="nav-item">

                        <button class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#kpi"
                                type="button">

                            <i class="ti ti-chart-bar me-1"></i>

                            KPI

                        </button>

                    </li>

                    <li class="nav-item">

                        <button class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#payroll"
                                type="button">

                            <i class="ti ti-cash me-1"></i>

                            Payroll

                        </button>

                    </li>

                </ul>

            </div>

            <div class="card-body">

                <div class="tab-pane fade show active"
     id="profile">

    <div class="row">

        <div class="col-md-6">

            <table class="table table-borderless">

                <tr>

                    <th width="180">

                        Nama Lengkap

                    </th>

                    <td>

                        <?php echo e($employee->name); ?>


                    </td>

                </tr>

                <tr>

                    <th>

                        Nama Panggilan

                    </th>

                    <td>

                        <?php echo e($employee->nickname ?: '-'); ?>


                    </td>

                </tr>

                <tr>

                    <th>

                        Email

                    </th>

                    <td>

                        <?php echo e($employee->email ?: '-'); ?>


                    </td>

                </tr>

                <tr>

                    <th>

                        Nomor HP

                    </th>

                    <td>

                        <?php echo e($employee->phone ?: '-'); ?>


                    </td>

                </tr>

                <tr>

                    <th>

                        WhatsApp

                    </th>

                    <td>

                        <?php echo e($employee->whatsapp ?: '-'); ?>


                    </td>

                </tr>

                <tr>

                    <th>

                        Gender

                    </th>

                    <td>

                        <?php echo e(ucfirst($employee->gender ?? '-')); ?>


                    </td>

                </tr>

            </table>

        </div>

        <div class="col-md-6">

            <table class="table table-borderless">

                <tr>

                    <th width="180">

                        Tempat Lahir

                    </th>

                    <td>

                        <?php echo e($employee->birth_place ?: '-'); ?>


                    </td>

                </tr>

                <tr>

                    <th>

                        Tanggal Lahir

                    </th>

                    <td>

                        <?php echo e(optional($employee->birth_date)->format('d M Y')); ?>


                    </td>

                </tr>

                <tr>

                    <th>

                        Alamat

                    </th>

                    <td>

                        <?php echo e($employee->full_address ?: '-'); ?>


                    </td>

                </tr>

                <tr>

                    <th>

                        Site

                    </th>

                    <td>

                        <?php echo e($employee->branch?->name); ?>


                    </td>

                </tr>

                <tr>

                    <th>

                        Divisi

                    </th>

                    <td>

                        <?php echo e($employee->division?->name); ?>


                    </td>

                </tr>

                <tr>

                    <th>

                        Jabatan

                    </th>

                    <td>

                        <?php echo e($employee->position?->name); ?>


                    </td>

                </tr>

            </table>

        </div>

    </div>

</div>

                    <div class="tab-pane fade"
     id="employment">

    <div class="row">

        <div class="col-lg-6">

            <table class="table table-borderless">

                <tr>

                    <th width="220">

                        Status Pegawai

                    </th>

                    <td>

                        <?php switch($employee->employment_status):

                            case ('permanent'): ?>

                                <span class="badge bg-success">

                                    Pegawai Tetap

                                </span>

                                <?php break; ?>

                            <?php case ('contract'): ?>

                                <span class="badge bg-warning">

                                    Kontrak

                                </span>

                                <?php break; ?>

                            <?php case ('probation'): ?>

                                <span class="badge bg-info">

                                    Probation

                                </span>

                                <?php break; ?>

                            <?php default: ?>

                                -

                        <?php endswitch; ?>

                    </td>

                </tr>

                <tr>

                    <th>Status Kerja</th>

                    <td>

                        <?php if($employee->work_status=='active'): ?>

                            <span class="badge bg-success">

                                Aktif

                            </span>

                        <?php else: ?>

                            <span class="badge bg-danger">

                                Tidak Aktif

                            </span>

                        <?php endif; ?>

                    </td>

                </tr>

                <tr>

                    <th>Tanggal Masuk</th>

                    <td>

                        <?php echo e(optional($employee->join_date)->format('d M Y')); ?>


                    </td>

                </tr>

                <tr>

                    <th>Supervisor</th>

                    <td>

                        <?php echo e($employee->supervisor?->name ?? '-'); ?>


                    </td>

                </tr>

                <tr>

                    <th>Manager</th>

                    <td>

                        <?php echo e($employee->manager?->name ?? '-'); ?>


                    </td>

                </tr>

            </table>

        </div>

        <div class="col-lg-6">

            <table class="table table-borderless">

                <tr>

                    <th width="220">

                        Role OEMS

                    </th>

                    <td>

                        <?php echo e(optional($employee->user?->roles->first())->name ?? '-'); ?>


                    </td>

                </tr>

                <tr>

                    <th>Login OEMS</th>

                    <td>

                        <?php if($employee->user): ?>

                            <span class="badge bg-success">

                                Sudah Memiliki Login

                            </span>

                        <?php else: ?>

                            <span class="badge bg-secondary">

                                Belum Ada Login

                            </span>

                        <?php endif; ?>

                    </td>

                </tr>

                <tr>

                    <th>Email Login</th>

                    <td>

                        <?php echo e($employee->user?->email ?? '-'); ?>


                    </td>

                </tr>

                <tr>

                    <th>Terakhir Login</th>

                    <td>

                        <?php echo e(optional($employee->user?->last_login_at)->format('d M Y H:i') ?? '-'); ?>


                    </td>

                </tr>

                <tr>

                    <th>Status Sinkronisasi</th>

                    <td>

                        <span class="badge bg-primary">

                            Siap Integrasi AppBill

                        </span>

                    </td>

                </tr>

            </table>

        </div>

    </div>

</div>

                    <div class="tab-pane fade"
     id="contract">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h5 class="mb-1">

                Data Kontrak Pegawai

            </h5>

            <small class="text-muted">

                Seluruh riwayat kontrak pegawai akan tampil di halaman ini.

            </small>

        </div>

        <div>

            <button
                class="btn btn-primary"
                disabled>

                <i class="ti ti-plus me-1"></i>

                Buat Kontrak

            </button>

        </div>

    </div>

    <div class="table-responsive">

        <table class="table table-bordered align-middle">

            <thead class="table-light">

            <tr>

                <th>No Kontrak</th>

                <th>Jenis</th>

                <th>Mulai</th>

                <th>Berakhir</th>

                <th>Status</th>

                <th width="160">

                    Aksi

                </th>

            </tr>

            </thead>

            <tbody>

            <tr>

                <td colspan="6"
                    class="text-center py-5">

                    <img
                        src="<?php echo e(asset('assets/img/illustrations/page-misc-under-maintenance.png')); ?>"
                        width="180"
                        class="mb-3">

                    <h6>

                        Belum Ada Kontrak

                    </h6>

                    <p class="text-muted mb-0">

                        Modul Kontrak akan otomatis muncul di sini setelah Sprint Contract selesai.

                    </p>

                </td>

            </tr>

            </tbody>

        </table>

    </div>

</div>

                    <div class="tab-pane fade"
                         id="attendance">

                        <div class="alert alert-label-success">

                            Modul Attendance akan tampil di sini.

                        </div>

                    </div>

                    <div class="tab-pane fade"
                         id="kpi">

                        <div class="alert alert-label-danger">

                            Engine KPI akan tampil di sini.

                        </div>

                    </div>

                    <div class="tab-pane fade"
                         id="payroll">

                        <div class="alert alert-label-secondary">

                            Payroll akan tampil di sini.

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<div class="row mt-4">

    <div class="col-lg-8">

        <div class="card">

            <div class="card-header">

                <h5 class="mb-0">

                    <i class="ti ti-history me-2"></i>

                    Timeline Pegawai

                </h5>

            </div>

            <div class="card-body">

                <div class="timeline">

                    <div class="timeline-item mb-4">

                        <span class="badge bg-success">

                            Dibuat

                        </span>

                        <h6 class="mt-2 mb-1">

                            Data Pegawai Dibuat

                        </h6>

                        <small class="text-muted">

                            <?php echo e(optional($employee->created_at)->format('d M Y H:i')); ?>


                        </small>

                    </div>

                    <div class="timeline-item mb-4">

                        <span class="badge bg-info">

                            Login

                        </span>

                        <h6 class="mt-2 mb-1">

                            Login OEMS

                        </h6>

                        <small class="text-muted">

                            <?php echo e(optional($employee->user?->last_login_at)->format('d M Y H:i') ?? 'Belum Pernah Login'); ?>


                        </small>

                    </div>

                    <div class="timeline-item">

                        <span class="badge bg-warning">

                            Contract

                        </span>

                        <h6 class="mt-2 mb-1">

                            Menunggu Modul Kontrak

                        </h6>

                        <small class="text-muted">

                            Akan otomatis muncul setelah Sprint Contract.

                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="card">

            <div class="card-header">

                <h5 class="mb-0">

                    Quick Action

                </h5>

            </div>

            <div class="card-body d-grid gap-2">

                <a href="<?php echo e(route('employees.edit',$employee)); ?>"
                   class="btn btn-warning">

                    <i class="ti ti-edit me-1"></i>

                    Edit Pegawai

                </a>

                <button
                    class="btn btn-primary"
                    disabled>

                    <i class="ti ti-file-description me-1"></i>

                    Buat Kontrak

                </button>

                <button
                    class="btn btn-success"
                    disabled>

                    <i class="ti ti-chart-bar me-1"></i>

                    Input KPI

                </button>

                <button
                    class="btn btn-info"
                    disabled>

                    <i class="ti ti-calendar-check me-1"></i>

                    Lihat Absensi

                </button>

                <button
                    class="btn btn-dark"
                    disabled>

                    <i class="ti ti-cash me-1"></i>

                    Payroll

                </button>

            </div>

        </div>

    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/master/employees/show.blade.php ENDPATH**/ ?>