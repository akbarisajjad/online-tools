<?php
/**
 * فوتر پنل مدیریت
 * نسخه: 2.0
 */
if (!defined('ADMIN_PATH')) {
    exit('Access Denied!');
}
?>
<footer class="admin-footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="footer-copyright">
                    &copy; <?= date('Y') ?> 
                    <a href="<?= site_url() ?>" target="_blank"><?= get_option('site_name') ?></a>.
                    تمام حقوق محفوظ است.
                    <?php if (defined('APP_VERSION')): ?>
                        <span class="version">نسخه <?= APP_VERSION ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="footer-links text-md-right">
                    <a href="<?= admin_url('system_info.php') ?>" data-toggle="tooltip" title="اطلاعات سیستم">
                        <i class="fas fa-server"></i>
                    </a>
                    <a href="<?= site_url('contact') ?>" target="_blank" data-toggle="tooltip" title="پشتیبانی">
                        <i class="fas fa-headset"></i>
                    </a>
                    <a href="<?= admin_url('settings.php') ?>" data-toggle="tooltip" title="تنظیمات">
                        <i class="fas fa-cog"></i>
                    </a>
                    <a href="<?= admin_url('logout.php') ?>" data-toggle="tooltip" title="خروج">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- اسکریپت‌های عمومی -->
<script src="<?= assets_url('js/vendor/jquery-3.6.0.min.js') ?>"></script>
<script src="<?= assets_url('js/vendor/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= assets_url('js/vendor/persian-date.min.js') ?>"></script>
<script src="<?= assets_url('js/vendor/persian-datepicker.min.js') ?>"></script>
<script src="<?= assets_url('js/vendor/sweetalert2.min.js') ?>"></script>
<script src="<?= assets_url('js/admin/main.js') ?>"></script>

<?php if (isset($page_scripts) && is_array($page_scripts)): ?>
    <!-- اسکریپت‌های اختصاصی صفحه -->
    <?php foreach ($page_scripts as $script): ?>
        <script src="<?= assets_url("js/admin/{$script}.js") ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- اسکریپت‌های سفارشی -->
<script>
$(document).ready(function() {
    // فعال‌سازی tooltipها
    $('[data-toggle="tooltip"]').tooltip();
    
    // نمایش زمان اجرای صفحه
    <?php if (defined('START_TIME')): ?>
        const loadTime = (<?= microtime(true) ?> - <?= START_TIME ?>) * 1000;
        console.log(`زمان اجرای صفحه: ${loadTime.toFixed(2)} میلی‌ثانیه`);
    <?php endif; ?>
    
    // نمایش پیام‌های flash
    <?php if (isset($_SESSION['flash_message'])): ?>
        Swal.fire({
            icon: '<?= $_SESSION['flash_message']['type'] ?? 'info' ?>',
            title: '<?= $_SESSION['flash_message']['title'] ?? '' ?>',
            text: '<?= addslashes($_SESSION['flash_message']['message'] ?? '') ?>',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
});
</script>
</body>
</html>
