<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="site-header">
    <div class="container">
        <div class="logo">
            <a href="index.php">
                <img src="assets/images/logo.svg" alt="ابزارک آنلاین">
                <span>ابزارک</span>
            </a>
        </div>
        
        <nav class="main-nav">
            <ul>
                <li <?php echo ($current_page == 'index.php') ? 'class="active"' : ''; ?>>
                    <a href="index.php">خانه</a>
                </li>
                <li>
                    <a href="#">ابزارها</a>
                    <ul class="dropdown">
                        <!-- منوی آبشاری دسته‌بندی‌ها -->
                    </ul>
                </li>
                <li><a href="#">درباره ما</a></li>
                <li><a href="#">تماس</a></li>
            </ul>
        </nav>
        
        <div class="header-actions">
            <button class="theme-switcher" aria-label="تغییر تم">
                <i class="icon-moon"></i>
            </button>
            <button class="mobile-menu-btn">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>
