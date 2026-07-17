<?php
  use Illuminate\Support\Facades\Vite;

  $menuCollapsed = $configData['menuCollapsed'] === 'layout-menu-collapsed' ? json_encode(true) : false;

  // Get skin value directly from the config, keeping it as numeric if applicable
  $skin = $configData['skins'] ?? 0;

  // If we have a skin name from cookie or other source, use that instead
  $skinName = $configData['skinName'] ?? '';

  // Use either the skin name or numeric ID, prioritizing the name if available
  $defaultSkin = $skinName ?: $skin;

  // Define layout type and cookie naming
  $isAdminLayout = !str_contains($configData['layout'] ?? '', 'front');
  $primaryColorCookieName = $isAdminLayout ? 'admin-primaryColor' : 'front-primaryColor';

  // Get primary color - first from cookie, then from config
  $primaryColor = isset($_COOKIE[$primaryColorCookieName])
      ? $_COOKIE[$primaryColorCookieName]
      : $configData['color'] ?? null;
?>
<!-- laravel style -->
<?php echo app('Illuminate\Foundation\Vite')(['resources/assets/vendor/js/helpers.js']); ?>
<!-- beautify ignore:start -->
<?php if($configData['hasCustomizer']): ?>
<!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
  <?php echo app('Illuminate\Foundation\Vite')(['resources/assets/vendor/js/template-customizer.js']); ?>
<?php endif; ?>

  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <?php echo app('Illuminate\Foundation\Vite')(['resources/assets/js/config.js']); ?>

<?php if($configData['hasCustomizer']): ?>
<script type="module">
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize template customizer after DOM is loaded
    if (window.TemplateCustomizer) {
      try {
        // Get the skin currently applied to the document
        const appliedSkin = document.documentElement.getAttribute('data-skin') || "<?php echo e($defaultSkin); ?>";

        window.templateCustomizer = new TemplateCustomizer({
          defaultTextDir: "<?php echo e($configData['textDirection']); ?>",
          <?php if($primaryColor): ?>
            defaultPrimaryColor: "<?php echo e($primaryColor); ?>",
          <?php endif; ?>
          defaultTheme: "<?php echo e($configData['themeOpt']); ?>",
          defaultSkin: appliedSkin,
          defaultSemiDark: <?php echo e($configData['semiDark'] ? 'true' : 'false'); ?>,
          defaultShowDropdownOnHover: "<?php echo e($configData['showDropdownOnHover']); ?>",
          displayCustomizer: "<?php echo e($configData['displayCustomizer']); ?>",
          lang: '<?php echo e(app()->getLocale()); ?>',
          'controls': <?php echo json_encode($configData['customizerControls']); ?>,
        });

        // Ensure color is applied on page load
        <?php if($primaryColor): ?>
          if (window.Helpers && typeof window.Helpers.setColor === 'function') {
            window.Helpers.setColor("<?php echo e($primaryColor); ?>", true);
          }
        <?php endif; ?>
      } catch (error) {
        console.warn('Template customizer initialization error:', error);
      }
    }
  });
</script>
<?php endif; ?>
<?php /**PATH C:\laragon\www\appoems\resources\views/layouts/sections/scriptsIncludes.blade.php ENDPATH**/ ?>