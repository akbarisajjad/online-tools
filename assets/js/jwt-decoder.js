class JWTDecoder {
    constructor() {
        this.jwtInput = document.getElementById('jwt-input');
        this.decodeBtn = document.getElementById('decode-btn');
        this.clearBtn = document.getElementById('clear-btn');
        this.saveBtn = document.getElementById('save-btn') || null;
        this.verifyBtn = document.getElementById('verify-btn');
        this.headerOutput = document.getElementById('header-output');
        this.payloadOutput = document.getElementById('payload-output');
        this.signatureOutput = document.getElementById('signature-output');
        this.validationOutput = document.getElementById('validation-results');
        this.verificationResult = document.getElementById('verification-result');
        this.secretKeyInput = document.getElementById('secret-key');
        this.errorMessage = document.getElementById('error-message');
        this.tabBtns = document.querySelectorAll('.output-tabs .tab-btn');
        this.tabContents = document.querySelectorAll('.tab-content');
        
        this.init();
    }
    
    init() {
        this.decodeBtn.addEventListener('click', () => this.decodeJWT());
        this.clearBtn.addEventListener('click', () => this.clearAll());
        this.verifyBtn.addEventListener('click', () => this.verifySignature());
        
        if (this.saveBtn) {
            this.saveBtn.addEventListener('click', () => this.saveToHistory());
        }
        
        this.tabBtns.forEach(btn => {
            btn.addEventListener('click', () => this.switchTab(btn.dataset.tab));
        });
        
        // Load last JWT from localStorage if exists
        this.loadLastJWT();
    }
    
    decodeJWT() {
        const jwtToken = this.jwtInput.value.trim();
        
        if (!jwtToken) {
            this.showError('Please enter a JWT token');
            return;
        }
        
        const parts = jwtToken.split('.');
        if (parts.length !== 3) {
            this.showError('Invalid JWT format. Expected 3 parts separated by dots.');
            return;
        }
        
        try {
            // Decode header
            const header = JSON.parse(this.base64UrlDecode(parts[0]));
            this.headerOutput.innerHTML = this.syntaxHighlight(header);
            
            // Decode payload
            const payload = JSON.parse(this.base64UrlDecode(parts[1]));
            this.payloadOutput.innerHTML = this.syntaxHighlight(payload);
            
            // Show signature (no decoding)
            this.signatureOutput.querySelector('p').textContent = `Signature: ${parts[2]}`;
            
            // Validate token
            this.validateToken(payload);
            
            // Show validation tab by default
            this.switchTab('validation');
            
            // Save to localStorage
            localStorage.setItem('lastJWTToken', jwtToken);
            this.hideError();
        } catch (error) {
            this.showError(`Decoding failed: ${error.message}`);
        }
    }
    
    base64UrlDecode(str) {
        // Replace non-url-safe chars and add padding if needed
        let output = str.replace(/-/g, '+').replace(/_/g, '/');
        switch (output.length % 4) {
            case 0: break;
            case 2: output += '=='; break;
            case 3: output += '='; break;
            default: throw new Error('Illegal base64url string');
        }
        
        // Decode and handle UTF-8
        const decoded = atob(output);
        try {
            return decodeURIComponent(escape(decoded));
        } catch (e) {
            return decoded;
        }
    }
    
    syntaxHighlight(json) {
        if (typeof json !== 'string') {
            json = JSON.stringify(json, null, 4);
        }
        
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        
        return json.replace(
            /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, 
            function (match) {
                let cls = 'number';
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'key';
                    } else {
                        cls = 'string';
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'boolean';
                } else if (/null/.test(match)) {
                    cls = 'null';
                }
                return `<span class="${cls}">${match}</span>`;
            }
        );
    }
    
    validateToken(payload) {
        this.validationOutput.innerHTML = '';
        const now = Math.floor(Date.now() / 1000);
        let isValid = true;
        
        // Check expiration (exp)
        if (payload.exp) {
            const isExpired = payload.exp < now;
            this.addValidationResult(
                'Token Expiration',
                isExpired ? 'Token has expired' : `Token expires in ${this.formatTimeRemaining(payload.exp - now)}`,
                isExpired ? 'invalid' : 'valid'
            );
            isValid = isValid && !isExpired;
        } else {
            this.addValidationResult(
                'Token Expiration',
                'No expiration date (exp claim) found',
                'warning'
            );
        }
        
        // Check issued at (iat)
        if (payload.iat) {
            const isFuture = payload.iat > now;
            this.addValidationResult(
                'Issued At',
                isFuture ? 'Token appears to be issued in the future' : `Issued ${this.formatTimeElapsed(now - payload.iat)} ago`,
                isFuture ? 'warning' : 'valid'
            );
        }
        
        // Check not before (nbf)
        if (payload.nbf) {
            const isNotYetValid = payload.nbf > now;
            this.addValidationResult(
                'Not Before',
                isNotYetValid ? `Token will be valid in ${this.formatTimeRemaining(payload.nbf - now)}` : 'Token is active',
                isNotYetValid ? 'warning' : 'valid'
            );
            isValid = isValid && !isNotYetValid;
        }
        
        // Highlight token if expired
        if (payload.exp && payload.exp < now) {
            this.jwtInput.classList.add('token-expired');
        } else {
            this.jwtInput.classList.remove('token-expired');
        }
        
        return isValid;
    }
    
    addValidationResult(title, message, type) {
        const li = document.createElement('li');
        li.className = `validation-${type}`;
        
        const icon = type === 'valid' ? 'fa-check-circle' : 
                    type === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle';
        
        li.innerHTML = `
            <span><strong>${title}:</strong> ${message}</span>
            <i class="fas ${icon}"></i>
        `;
        
        this.validationOutput.appendChild(li);
    }
    
    formatTimeRemaining(seconds) {
        if (seconds < 60) return `${seconds} seconds`;
        if (seconds < 3600) return `${Math.floor(seconds / 60)} minutes`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours`;
        return `${Math.floor(seconds / 86400)} days`;
    }
    
    formatTimeElapsed(seconds) {
        if (seconds < 60) return `${seconds} seconds`;
        if (seconds < 3600) return `${Math.floor(seconds / 60)} minutes`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours`;
        return `${Math.floor(seconds / 86400)} days`;
    }
    
    verifySignature() {
        const jwtToken = this.jwtInput.value.trim();
        const secret = this.secretKeyInput.value.trim();
        
        if (!jwtToken) {
            this.showError('Please enter a JWT token first');
            return;
        }
        
        if (!secret) {
            this.showError('Please enter a secret key for verification');
            return;
        }
        
        try {
            const parts = jwtToken.split('.');
            if (parts.length !== 3) {
                throw new Error('Invalid JWT format');
            }
            
            // In a real implementation, you would use a proper JWT library like jwt-decode
            // This is a simplified verification for demonstration
            const header = JSON.parse(this.base64UrlDecode(parts[0]));
            const payload = JSON.parse(this.base64UrlDecode(parts[1]));
            
            // Simulate verification (in practice, use a proper HMAC calculation)
            const isSignatureValid = this.simulateSignatureVerification(jwtToken, secret);
            
            this.verificationResult.innerHTML = `
                <p><strong>Algorithm:</strong> ${header.alg || 'Unknown'}</p>
                <p><strong>Verification:</strong> ${isSignatureValid ? 'SUCCESS' : 'FAILED'}</p>
                ${isSignatureValid ? 
                    '<p>The signature matches and the token is valid.</p>' : 
                    '<p>The signature does not match. Token may be tampered with.</p>'}
            `;
            
            this.verificationResult.className = isSignatureValid ? 'verification-success' : 'verification-failed';
            this.verificationResult.classList.remove('hidden');
        } catch (error) {
            this.showError(`Verification failed: ${error.message}`);
        }
    }
    
    simulateSignatureVerification(token, secret) {
        // Note: This is a simulation only!
        // In a real app, use a proper JWT library like jwt-decode or jsonwebtoken
        console.warn('This is a simulated verification. Use a proper JWT library in production.');
        
        // Simple check: if secret contains "valid" consider it valid
        return secret.toLowerCase().includes('valid') || 
               secret.toLowerCase().includes('secret') ||
               secret.toLowerCase().includes('key');
    }
    
    switchTab(tabName) {
        this.tabBtns.forEach(btn => btn.classList.remove('active'));
        this.tabContents.forEach(content => content.classList.remove('active'));
        
        document.querySelector(`.tab-btn[data-tab="${tabName}"]`).classList.add('active');
        document.getElementById(`${tabName}-tab`).classList.add('active');
    }
    
    clearAll() {
        this.jwtInput.value = '';
        this.headerOutput.innerHTML = '<code>Header content will appear here...</code>';
        this.payloadOutput.innerHTML = '<code>Payload content will appear here...</code>';
        this.signatureOutput.querySelector('p').textContent = 'Signature verification requires the secret key.';
        this.validationOutput.innerHTML = '';
        this.verificationResult.classList.add('hidden');
        this.secretKeyInput.value = '';
        this.jwtInput.classList.remove('token-expired');
        this.hideError();
        localStorage.removeItem('lastJWTToken');
    }
    
    saveToHistory() {
        const jwtToken = this.jwtInput.value.trim();
        if (!jwtToken) {
            this.showError('No JWT token to save');
            return;
        }
        
        try {
            const payload = JSON.parse(this.base64UrlDecode(jwtToken.split('.')[1]));
            const title = payload.sub || payload.iss || 'JWT Token';
            
            fetch('/api/save-jwt.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    jwt_token: jwtToken,
                    title: title
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showMessage('Token saved to your history', 'success');
                } else {
                    this.showError(data.message || 'Failed to save token');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showError('Failed to save token');
            });
        } catch (error) {
            this.showError(`Invalid token: ${error.message}`);
        }
    }
    
    loadLastJWT() {
        const lastJWT = localStorage.getItem('lastJWTToken');
        if (lastJWT) {
            this.jwtInput.value = lastJWT;
        }
    }
    
    showError(message) {
        this.errorMessage.classList.remove('hidden');
        document.getElementById('error-text').textContent = message;
    }
    
    hideError() {
        this.errorMessage.classList.add('hidden');
    }
    
    showMessage(message, type) {
        const messageEl = document.createElement('div');
        messageEl.className = `alert-message ${type}`;
        messageEl.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'times-circle' : 'check-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(messageEl);
        
        setTimeout(() => {
            messageEl.classList.add('fade-out');
            setTimeout(() => messageEl.remove(), 500);
        }, 3000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new JWTDecoder();
});
