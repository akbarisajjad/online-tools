<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

$auth = new Auth();
?>

<div class="tool-container">
    <div class="tool-header">
        <h1><i class="fas fa-database"></i> SQL Query Builder</h1>
        <p>ابزاری حرفه‌ای برای ساخت کوئری‌های SQL بدون نیاز به کدنویسی</p>
    </div>
    
    <div class="tool-wrapper">
        <div class="input-section">
            <div class="tool-tabs">
                <button class="tab-btn active" data-tab="query-builder">Query Builder</button>
                <button class="tab-btn" data-tab="raw-sql">Raw SQL</button>
                <button class="tab-btn" data-tab="saved-queries">Saved Queries</button>
            </div>
            
            <div class="tab-content active" id="query-builder-tab">
                <div class="query-type-section">
                    <label for="queryType">نوع کوئری:</label>
                    <select id="queryType" class="form-control">
                        <option value="SELECT">SELECT (خواندن)</option>
                        <option value="INSERT">INSERT (درج)</option>
                        <option value="UPDATE">UPDATE (به‌روزرسانی)</option>
                        <option value="DELETE">DELETE (حذف)</option>
                    </select>
                </div>
                
                <div id="tables-section" class="query-section">
                    <h3><i class="fas fa-table"></i> جداول</h3>
                    <div class="tables-container">
                        <div class="available-tables">
                            <h4>جداول موجود</h4>
                            <ul id="tablesList" class="table-list"></ul>
                        </div>
                        <div class="selected-tables">
                            <h4>جداول انتخاب شده</h4>
                            <ul id="selectedTables" class="table-list"></ul>
                        </div>
                    </div>
                </div>
                
                <div id="fields-section" class="query-section">
                    <h3><i class="fas fa-columns"></i> فیلدها</h3>
                    <div id="selectedFields" class="fields-container"></div>
                </div>
                
                <div id="conditions-section" class="query-section">
                    <h3><i class="fas fa-filter"></i> شرایط</h3>
                    <div id="conditionsContainer" class="conditions-container"></div>
                    <button id="addCondition" class="btn btn-secondary">
                        <i class="fas fa-plus"></i> افزودن شرط
                    </button>
                </div>
                
                <div id="sort-section" class="query-section">
                    <h3><i class="fas fa-sort"></i> مرتب‌سازی</h3>
                    <div id="sortContainer" class="sort-container"></div>
                    <button id="addSort" class="btn btn-secondary">
                        <i class="fas fa-plus"></i> افزودن فیلد مرتب‌سازی
                    </button>
                </div>
            </div>
            
            <div class="tab-content" id="raw-sql-tab">
                <textarea id="raw-sql-input" class="sql-editor" placeholder='کوئری SQL خود را اینجا وارد کنید...'></textarea>
            </div>
            
            <div class="tab-content" id="saved-queries-tab">
                <div id="saved-queries-list" class="saved-queries-container">
                    <div class="empty-state">
                        <i class="fas fa-bookmark"></i>
                        <p>هیچ کوئری ذخیره شده‌ای ندارید</p>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <button id="generate-btn" class="btn btn-primary">
                    <i class="fas fa-magic"></i> تولید کوئری
                </button>
                <button id="clear-btn" class="btn btn-outline">
                    <i class="fas fa-eraser"></i> پاک کردن
                </button>
                <button id="copy-btn" class="btn btn-secondary">
                    <i class="fas fa-copy"></i> کپی کوئری
                </button>
                <?php if ($auth->checkAuth()): ?>
                    <button id="save-btn" class="btn btn-secondary">
                        <i class="fas fa-save"></i> ذخیره کوئری
                    </button>
                <?php endif; ?>
                <button id="execute-btn" class="btn btn-success">
                    <i class="fas fa-play"></i> اجرای کوئری
                </button>
            </div>
        </div>
        
        <div class="output-section">
            <div class="output-header">
                <h3><i class="fas fa-code"></i> کوئری تولید شده</h3>
                <div class="output-actions">
                    <button id="download-btn" class="btn btn-outline">
                        <i class="fas fa-download"></i> دانلود
                    </button>
                </div>
            </div>
            
            <div id="sql-output-container">
                <pre id="sql-output"><code>-- کوئری شما اینجا نمایش داده می‌شود</code></pre>
            </div>
            
            <div id="results-section" class="results-container hidden">
                <h3><i class="fas fa-table"></i> نتایج</h3>
                <div id="query-results" class="query-results-table"></div>
                <div id="query-stats" class="query-stats">
                    <div class="stat-item">
                        <span class="stat-label">تعداد رکوردها:</span>
                        <span class="stat-value" id="rows-count">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">زمان اجرا:</span>
                        <span class="stat-value" id="execution-time">0 ms</span>
                    </div>
                </div>
            </div>
            
            <div id="error-section" class="hidden">
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="error-text"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/assets/css/sql-query-builder.css">
<script src="/assets/js/sql-query-builder.js"></script>

<?php require_once '../../includes/footer.php'; ?>
