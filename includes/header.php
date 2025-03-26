<?php
require_once __DIR__ . '/auth.php';
$auth = new Auth();
$user = $auth->getUser();
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ابزارک آنلاین - مجموعه کامل ابزارهای توسعه‌دهندگان</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/theme.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
    <link rel="stylesheet" href="/assets/css/navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="/index.php">
                    <img src="/assets/images/logo.svg" alt="ابزارک آنلاین" class="logo-img">
                    <span class="logo-text">ابزارک</span>
                </a>
            </div>
            
            <nav class="main-nav">
                <ul class="nav-list">
                    <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                        <a href="/index.php" class="nav-link">خانه</a>
                    </li>
                    <li class="has-dropdown">
                        <a href="#" class="nav-link">ابزارها <i class="fas fa-chevron-down dropdown-icon"></i></a>
                        <ul class="dropdown-menu tools-dropdown">
                            <li class="dropdown-header">دسته‌بندی ابزارها</li>
                            <li>
                                <a href="/tools/data-conversion.php">
                                    <i class="fas fa-exchange-alt"></i> تبدیل داده‌ها
                                </a>
                            </li>
                            <li>
                                <a href="/tools/text-coding.php">
                                    <i class="fas fa-code"></i> متن و کدنویسی
                                </a>
                            </li>
                            <li>
                                <a href="/tools/calculators.php">
                                    <i class="fas fa-calculator"></i> ماشین‌حساب‌ها
                                </a>
                            </li>
                            <li>
                                <a href="/tools/date-time.php">
                                    <i class="far fa-calendar-alt"></i> تاریخ و زمان
                                </a>
                            </li>
                            <li>
                                <a href="/tools/graphics-colors.php">
                                    <i class="fas fa-palette"></i> گرافیک و رنگ
                                </a>
                            </li>
                            <li class="dropdown-divider"></li>
                            <li class="dropdown-header">ابزارهای پرکاربرد</li>
                            <li>
                                <a href="/tools/data-conversion/json-formatter.php">
                                    <i class="fas fa-brackets-curly"></i> JSON Formatter
                                </a>
                            </li>
                            <li>
                                <a href="/tools/text-coding/password-generator.php">
                                    <i class="fas fa-key"></i> Password Generator
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="/about.php" class="nav-link">درباره ما</a>
                    </li>
                    <li>
                        <a href="/contact.php" class="nav-link">تماس</a>
                    </li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <button class="theme-switcher" aria-label="تغییر تم">
                    <i class="fas fa-moon"></i>
                </button>
                
                <div class="search-box">
                    <input type="text" placeholder="جستجوی ابزار..." class="search-input">
                    <button class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <?php if ($user): ?>
                    <div class="user-dropdown">
                        <button class="user-btn">
                            <img src="<?php echo htmlspecialchars($user['avatar'] ?: '/assets/images/default-avatar.png'); ?>" 
                                 alt="آواتار کاربر" class="user-avatar">
                            <span class="username"><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></span>
                            <i class="fas fa-chevron-down dropdown-icon"></i>
                        </button>
                        <ul class="dropdown-menu user-dropdown-menu">
                            <li>
                                <a href="/profile.php">
                                    <i class="far fa-user"></i> پروفایل
                                </a>
                            </li>
                            <li>
                                <a href="/profile.php#favorites">
                                    <i class="far fa-star"></i> مورد علاقه‌ها
                                </a>
                            </li>
                            <li class="dropdown-divider"></li>
                            <li>
                                <a href="/auth/logout.php">
                                    <i class="fas fa-sign-out-alt"></i> خروج
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="/auth/login.php" class="btn btn-outline">ورود</a>
                        <a href="/auth/register.php" class="btn btn-primary">ثبت‌نام</a>
                    </div>
                <?php endif; ?>
                
                <button class="mobile-menu-btn">
                    <span class="menu-line"></span>
                    <span class="menu-line"></span>
                    <span class="menu-line"></span>
                </button>
            </div>
        </div>
        
        <!-- منوی موبایل -->
        <div class="mobile-menu">
            <div class="mobile-search">
                <input type="text" placeholder="جستجوی ابزار..." class="mobile-search-input">
                <button class="mobile-search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <ul class="mobile-nav-list">
                <li>
                    <a href="/index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> خانه
                    </a>
                </li>
                <li class="mobile-dropdown">
                    <button class="mobile-dropdown-btn">
                        <i class="fas fa-tools"></i> ابزارها
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <ul class="mobile-dropdown-menu">
                        <li><a href="/tools/data-conversion.php">تبدیل داده‌ها</a></li>
                        <li><a href="/tools/text-coding.php">متن و کدنویسی</a></li>
                        <li><a href="/tools/calculators.php">ماشین‌حساب‌ها</a></li>
                        <li><a href="/tools/date-time.php">تاریخ و زمان</a></li>
                        <li><a href="/tools/graphics-colors.php">گرافیک و رنگ</a></li>
                    </ul>
                </li>
                <li>
                    <a href="/about.php">
                        <i class="fas fa-info-circle"></i> درباره ما
                    </a>
                </li>
                <li>
                    <a href="/contact.php">
                        <i class="far fa-envelope"></i> تماس
                    </a>
                </li>
                <?php if ($user): ?>
                    <li>
                        <a href="/profile.php">
                            <i class="far fa-user"></i> پروفایل
                        </a>
                    </li>
                    <li>
                        <a href="/auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i> خروج
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="/auth/login.php">
                            <i class="fas fa-sign-in-alt"></i> ورود
                        </a>
                    </li>
                    <li>
                        <a href="/auth/register.php">
                            <i class="fas fa-user-plus"></i> ثبت‌نام
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <script src="/assets/js/navigation.js"></script>
    <script src="/assets/js/search.js"></script>
    <script src="/assets/js/theme-switcher.js"></script>
