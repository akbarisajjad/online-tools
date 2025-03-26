<?php 
http_response_code(404);
require_once 'includes/header.php'; 
?>

<div class="error-container">
    <div class="error-content">
        <h1>404</h1>
        <h2>صفحه مورد نظر یافت نشد</h2>
        <p>آدرس وارد شده معتبر نیست یا صفحه حذف شده است.</p>
        <a href="/" class="btn btn-primary">بازگشت به صفحه اصلی</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
