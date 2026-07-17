<?php
  $configData = Helper::appClasses();
  $customizerHidden = 'customizer-hide';
?>



<?php $__env->startSection('title', 'Login'); ?>

<?php $__env->startSection('vendor-style'); ?>
  <?php echo app('Illuminate\Foundation\Vite')(['resources/assets/vendor/libs/@form-validation/form-validation.scss']); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-style'); ?>
  <?php echo app('Illuminate\Foundation\Vite')(['resources/assets/vendor/scss/pages/page-auth.scss']); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('vendor-script'); ?>
  <?php echo app('Illuminate\Foundation\Vite')(['resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js']); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-script'); ?>
  <?php echo app('Illuminate\Foundation\Vite')(['resources/assets/js/pages-auth.js']); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <div class="authentication-wrapper authentication-cover">
    <div class="authentication-inner row m-0">
      <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center p-5">
        <div class="w-100 d-flex justify-content-center">
          <div class="text-center">
            <div class="mb-4">
              <span class="app-brand-logo demo"><?php echo $__env->make('_partials.macros', ['height' => 48], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
            </div>
            <h3 class="mb-2">Selamat Datang</h3>
            <p class="text-muted mb-0">
              Masuk ke sistem operasional perusahaan dengan aman.
            </p>
          </div>
        </div>
      </div>

      <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg p-sm-5 p-4">
        <div class="w-px-400 mx-auto">
          <div class="app-brand mb-4">
            <a href="<?php echo e(url('/')); ?>" class="app-brand-link gap-2">
              <span class="app-brand-logo demo"><?php echo $__env->make('_partials.macros', ['height' => 32], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
              <span class="app-brand-text demo text-heading fw-bold">OEMS</span>
            </a>
          </div>

          <h4 class="mb-1">Login Akun</h4>
          <p class="mb-4 text-muted">Gunakan email atau username yang sudah terdaftar di sistem.</p>

          <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible" role="alert">
              <?php echo e(session('success')); ?>

              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <?php if($errors->any()): ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
              <div class="fw-medium mb-1">Login gagal</div>
              <ul class="mb-0 ps-3">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <form id="formAuthentication" class="mb-3" action="<?php echo e(route('login.proses')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
              <label for="email" class="form-label">Email / Username</label>
              <input type="text" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="email"
                name="email" value="<?php echo e(old('email')); ?>" placeholder="developer atau nama@email.com" autocomplete="username" autofocus>
              <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback"><?php echo e($message); ?></div>
              <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="mb-3 form-password-toggle">
              <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Password</label>
              </div>
              <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                  name="password" placeholder="••••••••••••" autocomplete="current-password">
                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                  <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
              </div>
            </div>

            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                <label class="form-check-label" for="remember">
                  Ingat saya
                </label>
              </div>
            </div>

            <button class="btn btn-primary d-grid w-100 mb-3" type="submit">
              Masuk
            </button>

            <a href="<?php echo e(route('google.login')); ?>" class="btn btn-label-danger d-grid w-100">
              <span class="d-flex align-items-center justify-content-center">
                <i class="ti ti-brand-google me-2"></i>
                Masuk dengan Google
              </span>
            </a>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/blankLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/auth/login.blade.php ENDPATH**/ ?>