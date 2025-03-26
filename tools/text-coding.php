<?php
require_once '../includes/header.php';
require_once '../includes/auth.php';

$auth = new Auth();
$category_title = "متن و کدنویسی";
$category_icon = "fas fa-code";
$tools = [
    [
        'title' => 'Password Generator',
        'description' => 'تولید رمزهای عبور امن و تصادفی',
        'link' => '/tools/text-coding/password-generator.php',
        'icon' => 'fas fa-key'
    ],
    [
        'title' => 'Code Beautifier',
        'description' => 'فرمت‌دهی کدهای HTML, CSS و JavaScript',
        'link' => '/tools/text-coding/code-beautifier.php',
        'icon' => 'fas fa-indent'
    ],
    [
        'title' => 'Regex Tester',
        'description' => 'تست عبارات منظم با نمونه متن',
        'link' => '/tools/text-coding/regex-tester.php',
        'icon' => 'fas fa-asterisk'
    ],
    [
        'title' => 'SQL Formatter',
        'description' => 'زیباسازی و فرمت‌دهی کوئری‌های SQL',
        'link' => '/tools/text-coding/sql-formatter.php',
        'icon' => 'fas fa-database'
    ],
    [
        'title' => 'HTML Entity Converter',
        'description' => 'تبدیل کاراکترهای خاص به HTML Entities',
        'link' => '/tools/text-coding/html-entities.php',
        'icon' => 'fas fa-code'
    ]
];
?>

<div class="category-page">
    <div class="category-header">
        <div class="container">
            <h1><i class="<?php echo $category_icon; ?>"></i> <?php echo $category_title; ?></h1>
            <p>ابزارهای کار با متن و کدنویسی برای توسعه‌دهندگان</p>
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
            <h2>ابزارهای ضروری برای توسعه‌دهندگان</h2>
            <p>این مجموعه شامل ابزارهای کاربردی برای کار با متن، کدنویسی و پردازش رشته‌ها می‌شود. از تولید رمز عبور تا تست عبارات منظم و فرمت‌دهی کدها.</p>
            
            <div class="use-cases">
                <h3>موارد استفاده:</h3>
                <ul>
                    <li>فرمت‌دهی و مرتب‌سازی کدهای نامرتب</li>
                    <li>تست و اشکال‌زدایی عبارات منظم</li>
                    <li>تولید رمزهای عبور امن برای سیستم‌ها</li>
                    <li>بهینه‌سازی کوئری‌های SQL</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/favorites.js"></script>
<?php require_once '../includes/footer.php'; ?>
