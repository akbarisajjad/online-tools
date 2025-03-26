<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

$auth = new Auth();
?>

<div class="tool-container">
    <div class="tool-header">
        <h1>JSON Validator</h1>
        <p>Validate your JSON data and find syntax errors</p>
    </div>
    
    <div class="tool-wrapper">
        <div class="input-section">
            <div class="tool-tabs">
                <button class="tab-btn active" data-tab="input">Input</button>
                <button class="tab-btn" data-tab="file">Upload File</button>
                <button class="tab-btn" data-tab="url">Fetch from URL</button>
            </div>
            
            <div class="tab-content active" id="input-tab">
                <textarea id="json-input" placeholder='Paste your JSON here...'></textarea>
            </div>
            
            <div class="tab-content" id="file-tab">
                <div class="file-upload">
                    <input type="file" id="json-file" accept=".json,application/json">
                    <label for="json-file" class="file-upload-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Choose a JSON file or drag it here</span>
                    </label>
                </div>
            </div>
            
            <div class="tab-content" id="url-tab">
                <div class="url-input">
                    <input type="url" id="json-url" placeholder="https://example.com/data.json">
                    <button id="fetch-btn" class="btn btn-primary">Fetch</button>
                </div>
            </div>
            
            <div class="action-buttons">
                <button id="validate-btn" class="btn btn-primary">Validate JSON</button>
                <button id="clear-btn" class="btn btn-outline">Clear</button>
                <?php if ($auth->checkAuth()): ?>
                    <button id="save-btn" class="btn btn-secondary">Save to Profile</button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="output-section">
            <div class="output-header">
                <h3>Validation Result</h3>
                <div class="output-actions">
                    <button id="copy-btn" class="btn btn-outline">
                        <i class="far fa-copy"></i> Copy
                    </button>
                </div>
            </div>
            
            <div id="validation-result">
                <div class="initial-message">
                    <i class="fas fa-check-circle"></i>
                    <p>Enter JSON data to validate</p>
                </div>
            </div>
            
            <div id="error-details" class="hidden">
                <h4>Error Details</h4>
                <div class="error-content"></div>
            </div>
            
            <div id="json-tree" class="hidden">
                <h4>JSON Structure</h4>
                <div class="tree-viewer"></div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/assets/css/json-validator.css">
<script src="/assets/js/json-validator.js"></script>
<?php require_once '../../includes/footer.php'; ?>
