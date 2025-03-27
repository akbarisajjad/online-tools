<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

$auth = new Auth();
?>

<div class="tool-container">
    <div class="tool-header">
        <h1><i class="fas fa-file-code"></i> API Documentation Generator</h1>
        <p>ابزاری حرفه‌ای برای تولید مستندات API به صورت خودکار</p>
    </div>
    
    <div class="tool-wrapper">
        <div class="input-section">
            <div class="tool-tabs">
                <button class="tab-btn active" data-tab="php-code">PHP Code</button>
                <button class="tab-btn" data-tab="openapi">OpenAPI/Swagger</button>
                <button class="tab-btn" data-tab="endpoints">Endpoints</button>
            </div>
            
            <div class="tab-content active" id="php-code-tab">
                <div class="form-group">
                    <label for="php-file">آپلود فایل PHP:</label>
                    <input type="file" id="php-file" accept=".php" class="form-control">
                </div>
                <div class="form-group">
                    <label for="php-code">یا کد PHP را مستقیماً وارد کنید:</label>
                    <textarea id="php-code" class="code-editor" placeholder='کد PHP حاوی API endpoints را اینجا وارد کنید...'></textarea>
                </div>
            </div>
            
            <div class="tab-content" id="openapi-tab">
                <div class="form-group">
                    <label for="openapi-file">آپلود فایل OpenAPI/Swagger:</label>
                    <input type="file" id="openapi-file" accept=".json,.yaml,.yml" class="form-control">
                </div>
                <div class="form-group">
                    <label for="openapi-code">یا کد OpenAPI/Swagger را مستقیماً وارد کنید:</label>
                    <textarea id="openapi-code" class="code-editor" placeholder='مشخصات OpenAPI/Swagger را اینجا وارد کنید...'></textarea>
                </div>
            </div>
            
            <div class="tab-content" id="endpoints-tab">
                <div id="endpoints-builder">
                    <div class="endpoint-item template">
                        <div class="endpoint-header">
                            <input type="text" class="form-control endpoint-method" placeholder="GET" list="http-methods">
                            <input type="text" class="form-control endpoint-path" placeholder="/api/v1/users">
                            <button class="btn btn-outline btn-sm remove-endpoint"><i class="fas fa-trash"></i></button>
                        </div>
                        <div class="endpoint-body">
                            <div class="form-group">
                                <label>Description:</label>
                                <input type="text" class="form-control endpoint-description" placeholder="Endpoint description...">
                            </div>
                            <div class="form-group">
                                <label>Parameters:</label>
                                <div class="parameters-container"></div>
                                <button class="btn btn-secondary btn-sm add-parameter"><i class="fas fa-plus"></i> Add Parameter</button>
                            </div>
                            <div class="form-group">
                                <label>Responses:</label>
                                <div class="responses-container"></div>
                                <button class="btn btn-secondary btn-sm add-response"><i class="fas fa-plus"></i> Add Response</button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="endpoints-list"></div>
                    
                    <button id="add-endpoint" class="btn btn-primary"><i class="fas fa-plus"></i> Add Endpoint</button>
                </div>
                
                <datalist id="http-methods">
                    <option>GET</option>
                    <option>POST</option>
                    <option>PUT</option>
                    <option>PATCH</option>
                    <option>DELETE</option>
                    <option>HEAD</option>
                    <option>OPTIONS</option>
                </datalist>
            </div>
            
            <div class="action-buttons">
                <button id="generate-docs" class="btn btn-primary"><i class="fas fa-magic"></i> Generate Documentation</button>
                <button id="clear-all" class="btn btn-outline"><i class="fas fa-eraser"></i> Clear</button>
                <?php if ($auth->checkAuth()): ?>
                    <button id="save-docs" class="btn btn-secondary"><i class="fas fa-save"></i> Save Documentation</button>
                <?php endif; ?>
            </div>
            
            <div class="generation-options">
                <h4>Documentation Options</h4>
                <div class="options-grid">
                    <div class="form-group">
                        <label for="doc-format">Format:</label>
                        <select id="doc-format" class="form-control">
                            <option value="html">HTML</option>
                            <option value="markdown">Markdown</option>
                            <option value="openapi">OpenAPI (JSON)</option>
                            <option value="postman">Postman Collection</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="include-examples" checked>
                            Include Examples
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="group-by-tag" checked>
                            Group by Tags
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="output-section">
            <div class="output-header">
                <h3><i class="fas fa-file-alt"></i> API Documentation</h3>
                <div class="output-actions">
                    <button id="download-docs" class="btn btn-outline">
                        <i class="fas fa-download"></i> Download
                    </button>
                    <button id="copy-docs" class="btn btn-outline">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                    <button id="preview-docs" class="btn btn-outline">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                </div>
            </div>
            
            <div id="docs-output-container">
                <pre id="docs-output"><code>API documentation will appear here...</code></pre>
            </div>
            
            <div id="docs-preview" class="hidden">
                <iframe id="docs-preview-frame" sandbox="allow-same-origin"></iframe>
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

<link rel="stylesheet" href="/assets/css/api-docs-generator.css">
<script src="/assets/js/api-docs-generator.js"></script>

<?php require_once '../../includes/footer.php'; ?>
