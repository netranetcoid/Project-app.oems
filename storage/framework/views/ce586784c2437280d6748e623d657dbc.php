<!DOCTYPE html>
<?php
use Illuminate\Support\Str;
use App\Helpers\Helpers;

$menuFixed =
$configData['layout'] === 'vertical'
? $menuFixed ?? ''
: ($configData['layout'] === 'front'
? ''
: $configData['headerType']);
$navbarType =
$configData['layout'] === 'vertical'
? $configData['navbarType']
: ($configData['layout'] === 'front'
? 'layout-navbar-fixed'
: '');
$isFront = ($isFront ?? '') == true ? 'Front' : '';
$contentLayout = isset($container) ? ($container === 'container-xxl' ? 'layout-compact' : 'layout-wide') : '';

// Get skin name from configData - only applies to admin layouts
$isAdminLayout = !Str::contains($configData['layout'] ?? '', 'front');
$skinName = $isAdminLayout ? $configData['skinName'] ?? 'default' : 'default';

// Get semiDark value from configData - only applies to admin layouts
$semiDarkEnabled = $isAdminLayout && filter_var($configData['semiDark'] ?? false, FILTER_VALIDATE_BOOLEAN);

// Generate primary color CSS if color is set
$primaryColorCSS = '';
if (isset($configData['color']) && $configData['color']) {
$primaryColorCSS = Helpers::generatePrimaryColorCSS($configData['color']);
}

?>

<html lang="<?php echo e(session()->get('locale') ?? app()->getLocale()); ?>"
class="<?php echo e($navbarType ?? ''); ?> <?php echo e($contentLayout ?? ''); ?> <?php echo e($menuFixed ?? ''); ?> <?php echo e($menuCollapsed ?? ''); ?> <?php echo e($footerFixed ?? ''); ?> <?php echo e($customizerHidden ?? ''); ?>"
dir="<?php echo e($configData['textDirection']); ?>" data-skin="<?php echo e($skinName); ?>" data-assets-path="<?php echo e(asset('/assets') . '/'); ?>"
data-base-url="<?php echo e(url('/')); ?>" data-framework="laravel" data-template="<?php echo e($configData['layout']); ?>-menu-template"
  data-bs-theme="<?php echo e($configData['theme']); ?>" <?php if($isAdminLayout && $semiDarkEnabled): ?> data-semidark-menu="true" <?php endif; ?>>

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>
    <?php echo $__env->yieldContent('title'); ?> | <?php echo e(config('variables.templateName') ? config('variables.templateName') : 'TemplateName'); ?>

    - <?php echo e(config('variables.templateSuffix') ? config('variables.templateSuffix') : 'TemplateSuffix'); ?>

</title>
  <meta name="description"
content="<?php echo e(config('variables.templateDescription') ? config('variables.templateDescription') : ''); ?>" />
  <meta name="keywords"
content="<?php echo e(config('variables.templateKeyword') ? config('variables.templateKeyword') : ''); ?>" />
  <meta property="og:title" content="<?php echo e(config('variables.ogTitle') ? config('variables.ogTitle') : ''); ?>" />
  <meta property="og:type" content="<?php echo e(config('variables.ogType') ? config('variables.ogType') : ''); ?>" />
  <meta property="og:url" content="<?php echo e(config('variables.productPage') ? config('variables.productPage') : ''); ?>" />
  <meta property="og:image" content="<?php echo e(config('variables.ogImage') ? config('variables.ogImage') : ''); ?>" />
  <meta property="og:description"
    content="<?php echo e(config('variables.templateDescription') ? config('variables.templateDescription') : ''); ?>" />
  <meta property="og:site_name"
    content="<?php echo e(config('variables.creatorName') ? config('variables.creatorName') : ''); ?>" />
  <meta name="robots" content="noindex, nofollow" />
  <!-- laravel CRUD token -->
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />
  <!-- Canonical SEO -->
  <link rel="canonical" href="<?php echo e(config('variables.productPage') ? config('variables.productPage') : ''); ?>" />
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="<?php echo e(asset('assets/img/favicon/favicon-osm-v2.ico')); ?>" />
  <link rel="icon" type="image/png" sizes="32x32" href="<?php echo e(asset('assets/img/favicon/favicon-32x32-v2.png')); ?>" />
  <link rel="icon" type="image/png" sizes="16x16" href="<?php echo e(asset('assets/img/favicon/favicon-16x16-v2.png')); ?>" />
  <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('assets/img/favicon/apple-touch-icon-v2.png')); ?>" />

  <!-- Include Styles -->
  <!-- $isFront is used to append the front layout styles only on the front layout otherwise the variable will be blank -->
  <?php echo $__env->make('layouts/sections/styles' . $isFront, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <?php if(
      $primaryColorCSS &&
          (config('custom.custom.primaryColor') ||
              isset($_COOKIE['admin-primaryColor']) ||
              isset($_COOKIE['front-primaryColor']))): ?>
    <!-- Primary Color Style -->
    <style id="primary-color-style">
      <?php echo $primaryColorCSS; ?>

    </style>
  <?php endif; ?>

  <!-- Include Scripts for customizer, helper, analytics, config -->
  <!-- $isFront is used to append the front layout scriptsIncludes only on the front layout otherwise the variable will be blank -->
  <?php echo $__env->make('layouts/sections/scriptsIncludes' . $isFront, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>

<body>
  <!-- Layout Content -->
  <?php echo $__env->yieldContent('layoutContent'); ?>
  <!--/ Layout Content -->

  
  

  <!-- Include Scripts -->
  <!-- $isFront is used to append the front layout scripts only on the front layout otherwise the variable will be blank -->
  <?php echo $__env->make('layouts/sections/scripts' . $isFront, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>

</html>
<?php /**PATH C:\laragon\www\appoems\resources\views/layouts/commonMaster.blade.php ENDPATH**/ ?>