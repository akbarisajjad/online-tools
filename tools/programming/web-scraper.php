<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

$auth = new Auth();
?>

<div class="tool-container">
    <div class="tool-header">
        <h1><i class="fas fa-globe"></i> Web Scraper</h1>
        <p>ابزاری پیشرفته برای استخراج داده از صفحات وب</p>
    </div>
    
    <div class="tool-wrapper">
        <div class="input-section">
            <div class="tool-tabs">
                <button class="tab-btn active" data-tab="url-scraping">URL Scraping</button>
                <button class="tab-btn" data-tab="html-scraping">HTML Scraping</button>
                <button class="tab-btn" data-tab="saved-profiles">Saved Profiles</button>
            </div>
            
            <div class="tab-content active" id="url-scraping-tab">
                <div class="form-group">
                    <label for="target-url">URL هدف:</label>
                    <input type="url" id="target-url" class="form-control" placeholder="https://example.com">
                </div>
                
                <div class="form-group">
                    <label for="user-agent">User Agent:</label>
                    <select id="user-agent" class="form-control">
                        <option value="default">پیش‌فرض</option>
                        <option value="chrome">Google Chrome</option>
                        <option value="firefox">Mozilla Firefox</option>
                        <option value="safari">Apple Safari</option>
                        <option value="mobile">Mobile Device</option>
                        <option value="custom">سفارشی</option>
                    </select>
                    <input type="text" id="custom-user-agent" class="form-control mt-2 hidden" placeholder="User Agent سفارشی">
                </div>
                
                <div class="form-group">
                    <label for="request-method">متد درخواست:</label>
                    <select id="request-method" class="form-control">
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="HEAD">HEAD</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="request-headers">هدرهای سفارشی (JSON):</label>
                    <textarea id="request-headers" class="form-control code-editor" rows="3" placeholder='{"Accept": "application/json", "Authorization": "Bearer token"}'></textarea>
                </div>
                
                <div class="form-group">
                    <label for="request-body">بدنه درخواست (برای POST):</label>
                    <textarea id="request-body" class="form-control code-editor" rows="3" placeholder='{"key": "value"}'></textarea>
                </div>
            </div>
            
            <div class="tab-content" id="html-scraping-tab">
                <div class="form-group">
                    <label for="html-content">محتوای HTML:</label>
                    <textarea id="html-content" class="form-control code-editor" rows="10" placeholder='<html>...</html>'></textarea>
                </div>
                
                <div class="form-group">
                    <label for="html-file">یا آپلود فایل HTML:</label>
                    <input type="file" id="html-file" accept=".html,.htm" class="form-control">
                </div>
            </div>
            
            <div class="tab-content" id="saved-profiles-tab">
                <div id="saved-profiles-list">
                    <div class="empty-state">
                        <i class="fas fa-bookmark"></i>
                        <p>هیچ پروفایل ذخیره شده‌ای ندارید</p>
                    </div>
                </div>
            </div>
            
            <div class="scraping-rules">
                <h3><i class="fas fa-ruler"></i> قوانین استخراج</h3>
                
                <div class="rule-item template">
                    <div class="rule-header">
                        <select class="form-control rule-type">
                            <option value="css">CSS Selector</option>
                            <option value="xpath">XPath</option>
                            <option value="regex">Regex</option>
                            <option value="json">JSON Path</option>
                        </select>
                        <input type="text" class="form-control rule-selector" placeholder="Selector">
                        <input type="text" class="form-control rule-name" placeholder="نام فیلد">
                        <select class="form-control rule-attr">
                            <option value="text">متن</option>
                            <option value="html">HTML</option>
                            <option value="href">لینک (href)</option>
                            <option value="src">منبع (src)</option>
                            <option value="custom">ویژگی سفارشی</option>
                        </select>
                        <input type="text" class="form-control rule-custom-attr hidden" placeholder="نام ویژگی">
                        <button class="btn btn-outline btn-sm remove-rule"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                
                <div id="rules-list"></div>
                
                <button id="add-rule" class="btn btn-primary"><i class="fas fa-plus"></i> افزودن قانون</button>
            </div>
            
            <div class="action-buttons">
                <button id="start-scraping" class="btn btn-primary"><i class="fas fa-play"></i> شروع استخراج</button>
                <button id="clear-all" class="btn btn-outline"><i class="fas fa-eraser"></i> پاک کردن همه</button>
                <?php if ($auth->checkAuth()): ?>
                    <button id="save-profile" class="btn btn-secondary"><i class="fas fa-save"></i> ذخیره پروفایل</button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="output-section">
            <div class="output-header">
                <h3><i class="fas fa-database"></i> نتایج استخراج</h3>
                <div class="output-actions">
                    <button id="export-json" class="btn btn-outline">
                        <i class="fas fa-file-export"></i> Export JSON
                    </button>
                    <button id="export-csv" class="btn btn-outline">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </button>
                    <button id="copy-results" class="btn btn-outline">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>
            
            <div class="output-tabs">
                <button class="output-tab-btn active" data-tab="results">Results</button>
                <button class="output-tab-btn" data-tab="raw-html">Raw HTML</button>
                <button class="output-tab-btn" data-tab="response-info">Response Info</button>
            </div>
            
            <div class="output-tab-content active" id="results-tab">
                <div id="scraping-results">
                    <div class="empty-state">
                        <i class="fas fa-database"></i>
                        <p>هیچ داده‌ای استخراج نشده است</p>
                    </div>
                </div>
            </div>
            
            <div class="output-tab-content" id="raw-html-tab">
                <pre id="raw-html-content"><code>محتوای HTML اینجا نمایش داده می‌شود...</code></pre>
            </div>
            
            <div class="output-tab-content" id="response-info-tab">
                <div id="response-info">
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <p>اطلاعات پاسخ اینجا نمایش داده می‌شود...</p>
                    </div>
                </div>
            </div>
            
            <div id="error-message" class="hidden">
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="error-text"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/assets/css/web-scraper.css">
<script src="/assets/js/web-scraper.js"></script>

<?php require_once '../../includes/footer.php'; ?>
