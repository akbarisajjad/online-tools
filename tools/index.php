<?php
require_once '../includes/header.php';
require_once '../includes/auth.php';

$auth = new Auth();
$categories = [
    'data-conversion' => [
        'title' => 'تبدیل و پردازش داده',
        'icon' => 'fas fa-exchange-alt',
        'tools' => [
            'json-formatter' => 'JSON Formatter',
            'xml-to-json' => 'XML to JSON',
            'csv-to-json' => 'CSV to JSON'
        ]
    ],
    'text-coding' => [
        'title' => 'ابزارهای متن و کدنویسی',
        'icon' => 'fas fa-code',
        'tools' => [
            'code-beautifier' => 'Code Beautifier',
            'password-generator' => 'Password Generator'
        ]
    ]
    // سایر دسته‌بندی‌ها...
];
?>

<div class="tools-container">
    <div class="tools-header">
        <h1>همه ابزارهای آنلاین</h1>
        <p>بیش از ۲۰۰ ابزار کاربردی در دسته‌بندی‌های مختلف</p>
        
        <div class="category-filter">
            <button class="filter-btn active" data-category="all">همه ابزارها</button>
            <?php foreach ($categories as $slug => $category): ?>
                <button class="filter-btn" data-category="<?php echo $slug; ?>">
                    <i class="<?php echo $category['icon']; ?>"></i>
                    <?php echo $category['title']; ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="tools-grid">
        <?php foreach ($categories as $cat_slug => $category): ?>
            <div class="category-section" data-category="<?php echo $cat_slug; ?>">
                <h2 class="category-title">
                    <i class="<?php echo $category['icon']; ?>"></i>
                    <?php echo $category['title']; ?>
                </h2>
                
                <div class="tools-list">
                    <?php foreach ($category['tools'] as $tool_slug => $tool_name): ?>
                        <a href="/tools/<?php echo $cat_slug; ?>/<?php echo $tool_slug; ?>.php" class="tool-card">
                            <div class="tool-icon">
                                <i class="<?php echo $category['icon']; ?>"></i>
                            </div>
                            <h3><?php echo $tool_name; ?></h3>
                            <p>توضیحات کوتاه درباره ابزار</p>
                            
                            <?php if ($auth->checkAuth()): ?>
                                <button class="favorite-btn" data-tool="<?php echo $cat_slug . '/' . $tool_slug; ?>">
                                    <i class="far fa-star"></i>
                                </button>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="/assets/js/tools-filter.js"></script>
<script src="/assets/js/favorites.js"></script>
<?php require_once '../includes/footer.php'; ?>
