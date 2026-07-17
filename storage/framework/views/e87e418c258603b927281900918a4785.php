<?php if(isset($pageConfigs)): ?>
<?php echo Helper::updatePageConfig($pageConfigs); ?>

<?php endif; ?>
<?php
$configData = Helper::appClasses();
?>


<?php $__env->startPush('vendor-style'); ?>
<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<?php $__env->stopPush(); ?>

<?php
/* Display elements */
$contentNavbar = $contentNavbar ?? true;
$containerNav = $containerNav ?? 'container-xxl';
$isNavbar = $isNavbar ?? true;
$isMenu = $isMenu ?? true;
$isFlex = $isFlex ?? false;
$isFooter = $isFooter ?? true;
$customizerHidden = $customizerHidden ?? '';

/* HTML Classes */
$navbarDetached = 'navbar-detached';
$menuFixed = isset($configData['menuFixed']) ? $configData['menuFixed'] : '';
if (isset($navbarType)) {
$configData['navbarType'] = $navbarType;
}
$navbarType = isset($configData['navbarType']) ? $configData['navbarType'] : '';
$footerFixed = isset($configData['footerFixed']) ? $configData['footerFixed'] : '';
$menuCollapsed = isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : '';

/* Content classes */
$container =
isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
? 'container-xxl'
: 'container-fluid';

?>

<?php $__env->startSection('layoutContent'); ?>
<div class="layout-wrapper layout-content-navbar <?php echo e($isMenu ? '' : 'layout-without-menu'); ?>">
    <div class="layout-container">

        <?php if($isMenu): ?>
        <?php echo $__env->make('layouts/sections/menu/verticalMenu', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>


        <!-- Layout page -->
        <div class="layout-page">

            
            

            <!-- BEGIN: Navbar-->
            <?php if($isNavbar): ?>
            <?php echo $__env->make('layouts/sections/navbar/navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
            <!-- END: Navbar-->


            <!-- Content wrapper -->
            <div class="content-wrapper">

                <!-- Content -->
                <?php if($isFlex): ?>
                <div class="<?php echo e($container); ?> d-flex align-items-stretch flex-grow-1 p-0">
                    <?php else: ?>
                    <div class="<?php echo e($container); ?> flex-grow-1 container-p-y">
                        <?php endif; ?>

                        <?php echo $__env->yieldContent('content'); ?>

                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <?php if($isFooter): ?>
                    <?php echo $__env->make('layouts/sections/footer/footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                    <!-- / Footer -->
                    <div class="content-backdrop fade"></div>
                </div>
                <!--/ Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <?php if($isMenu): ?>
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
        <?php endif; ?>
        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    <?php $__env->startPush('vendor-script'); ?>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<?php $__env->stopPush(); ?>
    <?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/commonMaster', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/layouts/contentNavbarLayout.blade.php ENDPATH**/ ?>