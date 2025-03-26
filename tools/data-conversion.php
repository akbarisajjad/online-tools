<?php
require_once '../includes/header.php';
require_once '../includes/auth.php';

$auth = new Auth();
$category_title = "تبدیل و پردازش داده";
$category_icon = "fas fa-exchange-alt";
$tools = [
    [
        'title' => 'JSON Formatter',
        'description' => 'فرمت‌دهی و زیباسازی کدهای JSON',
        'link' => '/tools/data-conversion/json-formatter.php',
        'icon' => 'fas fa-brackets-curly'
    ],
    [
        'title' => 'XML to JSON Converter',
        'description' => 'تبدیل فایل‌های XML به فرمت JSON',
        'link' => '/tools/data-conversion/xml-to-json.php',
        'icon' => 'fas fa-code'
    ],
    [
        'title' => 'Base64 Encoder/Decoder',
        'description' => 'رمزگذاری و رمزگشایی Base64',
        'link' => '/tools/data-conversion/base64-converter.php',
        'icon' => 'fas fa-lock'
    ],
    [
        'title' => 'CSV to JSON Converter',
        'description' => 'تبدیل داده‌های CSV به JSON',
        'link' => '/tools/data-conversion/csv-to-json.php',
        'icon' => 'fas fa-table'
    ],
    [
        'title' => 'URL Encoder/Decoder',
        'description' => 'رمزگذاری و رمزگشایی URL',
        'link' => '/tools/data-conversion/url-converter.php',
        'icon' => 'fas fa-link'
    ]
];
?>

<div class="category-page">
    <div class="category-header">
        <div class="container">
            <h1><i class="<?php echo $category_icon; ?>"></i> <?php echo $category_title; ?></h1>
            <p>ابزارهای تبدیل فرمت‌های داده و پردازش محتوا</p>
        </div>
    </div>

    <div class="container">
        <div class="tools-grid">
            <?php foreach ($tools as $tool): ?>
            <div class="tool-card">
                <a href="<?php echo $tool['link']; ?>">
                    <div class="tool-icon">
                        <i class="<?php echo $tool['icon']; ?>"></i>
                    </div>
                    <h3><?php echo $tool['title']; ?></h3>
                    <p><?php echo $tool['description']; ?></p>
                    
                    <?php if ($auth->checkAuth()): ?>
                    <button class="favorite-btn" data-tool="<?php echo $tool['link']; ?>">
                        <i class="far fa-star"></i>
                    </button>
                    <?php endif; ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="category-info">
            <h2>درباره ابزارهای تبدیل داده</h2>
            <p>این مجموعه ابزار به شما کمک می‌کند بین فرمت‌های مختلف داده تبدیل انجام دهید و محتوای خود را پردازش کنید. از JSON و XML گرفته تا فرمت‌های ساده‌تر مثل CSV و Base64.</p>
            
            <div class="features">
                <div class="feature">
                    <i class="fas fa-check-circle"></i>
                    <span>تبدیل بدون نیاز به نرم‌افزار اضافی</span>
                </div>
                <div class="feature">
                    <i class="fas fa-check-circle"></i>
                    <span>پشتیبانی از فایل‌های حجیم</span>
                </div>
                <div class="feature">
                    <i class="fas fa-check-circle"></i>
                    <span>امکان ذخیره نتایج برای کاربران</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/favorites.js"></script>
<?php require_once '../includes/footer.php'; ?>
