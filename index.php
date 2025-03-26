<?php
require_once 'includes/header.php';
?>

<main class="home-page">
    <section class="hero-section">
        <div class="container">
            <h1>مجموعه کامل ابزارهای توسعه‌دهندگان</h1>
            <p class="subtitle">بیش از ۵۰ ابزار حرفه‌ای برای پردازش داده، کدنویسی و توسعه وب</p>
            
            <div class="search-tools">
                <input type="text" placeholder="جستجوی ابزار مورد نظر..." id="search-input">
                <button id="search-btn" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </section>

    <section class="featured-tools">
        <div class="container">
            <h2>ابزارهای پرکاربرد</h2>
            <div class="tools-grid">
                <a href="/tools/data-conversion/json-formatter.php" class="tool-card">
                    <div class="tool-icon bg-purple">
                        <i class="fas fa-brackets-curly"></i>
                    </div>
                    <h3>JSON Formatter</h3>
                    <p>فرمت‌دهی و زیباسازی کدهای JSON</p>
                </a>
                
                <a href="/tools/network-security/jwt-decoder.php" class="tool-card">
                    <div class="tool-icon bg-blue">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3>JWT Decoder</h3>
                    <p>تجزیه و تحلیل توکن‌های JWT</p>
                </a>
                
                <a href="/tools/text-coding/password-generator.php" class="tool-card">
                    <div class="tool-icon bg-green">
                        <i class="fas fa-key"></i>
                    </div>
                    <h3>Password Generator</h3>
                    <p>تولید رمزهای عبور امن</p>
                </a>
                
                <a href="/tools/data-conversion/xml-to-json.php" class="tool-card">
                    <div class="tool-icon bg-orange">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3>XML to JSON</h3>
                    <p>تبدیل فایل‌های XML به JSON</p>
                </a>
            </div>
        </div>
    </section>

    <section class="all-categories">
        <div class="container">
            <h2>دسته‌بندی ابزارها</h2>
            
            <div class="categories-grid">
                <a href="/tools/data-conversion.php" class="category-card">
                    <div class="category-header">
                        <i class="fas fa-exchange-alt"></i>
                        <h3>تبدیل و پردازش داده</h3>
                    </div>
                    <ul class="tool-list">
                        <li>JSON Formatter</li>
                        <li>XML to JSON</li>
                        <li>Base64 Encoder</li>
                        <li>CSV to JSON</li>
                    </ul>
                </a>
                
                <a href="/tools/text-coding.php" class="category-card">
                    <div class="category-header">
                        <i class="fas fa-code"></i>
                        <h3>متن و کدنویسی</h3>
                    </div>
                    <ul class="tool-list">
                        <li>Password Generator</li>
                        <li>Code Beautifier</li>
                        <li>Regex Tester</li>
                        <li>SQL Formatter</li>
                    </ul>
                </a>
                
                <!-- سایر دسته‌بندی‌ها -->
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/home-search.js"></script>
<?php require_once 'includes/footer.php'; ?>
