<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

$auth = new Auth();
?>

<div class="tool-container">
    <div class="tool-header">
        <h1>XML to JSON Converter</h1>
        <p>Convert your XML data to JSON format</p>
    </div>
    
    <div class="tool-wrapper">
        <div class="input-section">
            <div class="tool-tabs">
                <button class="tab-btn active" data-tab="input">Input</button>
                <button class="tab-btn" data-tab="file">Upload File</button>
                <button class="tab-btn" data-tab="url">Fetch from URL</button>
            </div>
            
            <div class="tab-content active" id="input-tab">
                <textarea id="xml-input" placeholder='Paste your XML here...'></textarea>
            </div>
            
            <div class="tab-content" id="file-tab">
                <div class="file-upload">
                    <input type="file" id="xml-file" accept=".xml,application/xml">
                    <label for="xml-file" class="file-upload-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Choose an XML file or drag it here</span>
                    </label>
                </div>
            </div>
            
            <div class="tab-content" id="url-tab">
                <div class="url-input">
                    <input type="url" id="xml-url" placeholder="https://example.com/data.xml">
                    <button id="fetch-btn" class="btn btn-primary">Fetch</button>
                </div>
            </div>
            
            <div class="action-buttons">
                <button id="convert-btn" class="btn btn-primary">Convert to JSON</button>
                <button id="clear-btn" class="btn btn-outline">Clear</button>
                <button id="copy-btn" class="btn btn-secondary">Copy JSON</button>
                <?php if ($auth->checkAuth()): ?>
                    <button id="save-btn" class="btn btn-secondary">Save to Profile</button>
                <?php endif; ?>
            </div>
            
            <div class="conversion-options">
                <h4>Conversion Options</h4>
                <div class="options-grid">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="pretty-print" checked>
                            Pretty Print
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="preserve-attributes">
                            Preserve Attributes
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="force-array" checked>
                            Force Array for Single Elements
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="output-section">
            <div class="output-header">
                <h3>JSON Output</h3>
                <div class="output-actions">
                    <button id="download-btn" class="btn btn-outline">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
            </div>
            
            <div id="json-output-container">
                <pre id="json-output"><code>JSON output will appear here...</code></pre>
            </div>
            
            <div id="conversion-stats" class="hidden">
                <h4>Conversion Statistics</h4>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-label">XML Size:</span>
                        <span class="stat-value" id="xml-size">0 bytes</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">JSON Size:</span>
                        <span class="stat-value" id="json-size">0 bytes</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Conversion Time:</span>
                        <span class="stat-value" id="conversion-time">0 ms</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Elements Converted:</span>
                        <span class="stat-value" id="elements-count">0</span>
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

<link rel="stylesheet" href="/assets/css/xml-to-json-converter.css">
<script src="/assets/js/xml-to-json-converter.js"></script>
<?php require_once '../../includes/footer.php'; ?>
