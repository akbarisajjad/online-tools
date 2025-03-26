<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

$auth = new Auth();
?>

<div class="tool-container">
    <div class="tool-header">
        <h1>JWT Decoder</h1>
        <p>Decode and validate JSON Web Tokens (JWT)</p>
    </div>
    
    <div class="tool-wrapper">
        <div class="input-section">
            <div class="form-group">
                <label for="jwt-input">JWT Token</label>
                <textarea id="jwt-input" placeholder="Enter JWT token (eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...)" rows="4"></textarea>
            </div>
            
            <div class="action-buttons">
                <button id="decode-btn" class="btn btn-primary">Decode Token</button>
                <button id="clear-btn" class="btn btn-outline">Clear</button>
                <?php if ($auth->checkAuth()): ?>
                    <button id="save-btn" class="btn btn-secondary">Save to History</button>
                <?php endif; ?>
            </div>
            
            <div class="jwt-info">
                <h4>About JWT</h4>
                <p>JWT consists of three parts separated by dots (header.payload.signature)</p>
                <ul>
                    <li><strong>Header</strong>: Algorithm and token type</li>
                    <li><strong>Payload</strong>: Contains claims (e.g., user data, expiration)</li>
                    <li><strong>Signature</strong>: Used to verify the token</li>
                </ul>
            </div>
        </div>
        
        <div class="output-section">
            <div class="output-tabs">
                <button class="tab-btn active" data-tab="header">Header</button>
                <button class="tab-btn" data-tab="payload">Payload</button>
                <button class="tab-btn" data-tab="signature">Signature</button>
                <button class="tab-btn" data-tab="validation">Validation</button>
            </div>
            
            <div class="tab-content active" id="header-tab">
                <pre id="header-output"><code>Header content will appear here...</code></pre>
            </div>
            
            <div class="tab-content" id="payload-tab">
                <pre id="payload-output"><code>Payload content will appear here...</code></pre>
            </div>
            
            <div class="tab-content" id="signature-tab">
                <div id="signature-output">
                    <p>Signature verification requires the secret key.</p>
                    <div class="secret-input">
                        <input type="text" id="secret-key" placeholder="Enter secret key for verification">
                        <button id="verify-btn" class="btn btn-secondary">Verify</button>
                    </div>
                    <div id="verification-result" class="hidden"></div>
                </div>
            </div>
            
            <div class="tab-content" id="validation-tab">
                <div id="validation-output">
                    <h4>Token Validation</h4>
                    <ul id="validation-results"></ul>
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

<link rel="stylesheet" href="/assets/css/jwt-decoder.css">
<script src="/assets/js/jwt-decoder.js"></script>
<?php require_once '../../includes/footer.php'; ?>
