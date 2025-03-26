class JSONValidator {
    constructor() {
        this.jsonInput = document.getElementById('json-input');
        this.jsonFile = document.getElementById('json-file');
        this.jsonUrl = document.getElementById('json-url');
        this.validateBtn = document.getElementById('validate-btn');
        this.clearBtn = document.getElementById('clear-btn');
        this.saveBtn = document.getElementById('save-btn') || null;
        this.copyBtn = document.getElementById('copy-btn');
        this.fetchBtn = document.getElementById('fetch-btn');
        this.validationResult = document.getElementById('validation-result');
        this.errorDetails = document.getElementById('error-details');
        this.jsonTree = document.getElementById('json-tree');
        this.tabBtns = document.querySelectorAll('.tab-btn');
        this.tabContents = document.querySelectorAll('.tab-content');
        this.fileUploadLabel = document.querySelector('.file-upload-label');
        
        this.init();
    }
    
    init() {
        // رویدادهای تب‌ها
        this.tabBtns.forEach(btn => {
            btn.addEventListener('click', () => this.switchTab(btn.dataset.tab));
        });
        
        // رویدادهای دکمه‌ها
        this.validateBtn.addEventListener('click', () => this.validateJSON());
        this.clearBtn.addEventListener('click', () => this.clearAll());
        this.copyBtn.addEventListener('click', () => this.copyResult());
        this.fetchBtn.addEventListener('click', () => this.fetchFromUrl());
        
        if (this.saveBtn) {
            this.saveBtn.addEventListener('click', () => this.saveToProfile());
        }
        
        // رویدادهای آپلود فایل
        this.jsonFile.addEventListener('change', (e) => this.handleFileUpload(e));
        
        // Drag and drop برای فایل
        this.fileUploadLabel.addEventListener('dragover', (e) => {
            e.preventDefault();
            this.fileUploadLabel.style.borderColor = '#4361ee';
            this.fileUploadLabel.style.backgroundColor = 'rgba(67, 97, 238, 0.1)';
        });
        
        this.fileUploadLabel.addEventListener('dragleave', () => {
            this.fileUploadLabel.style.borderColor = '';
            this.fileUploadLabel.style.backgroundColor = '';
        });
        
        this.fileUploadLabel.addEventListener('drop', (e) => {
            e.preventDefault();
            this.fileUploadLabel.style.borderColor = '';
            this.fileUploadLabel.style.backgroundColor = '';
            
            if (e.dataTransfer.files.length) {
                this.jsonFile.files = e.dataTransfer.files;
                this.handleFileUpload({ target: this.jsonFile });
            }
        });
        
        // بارگذاری آخرین JSON از localStorage
        this.loadLastJSON();
    }
    
    switchTab(tabName) {
        // غیرفعال کردن همه تب‌ها
        this.tabBtns.forEach(btn => btn.classList.remove('active'));
        this.tabContents.forEach(content => content.classList.remove('active'));
        
        // فعال کردن تب انتخاب شده
        document.querySelector(`.tab-btn[data-tab="${tabName}"]`).classList.add('active');
        document.getElementById(`${tabName}-tab`).classList.add('active');
    }
    
    validateJSON() {
        let jsonString = '';
        const activeTab = document.querySelector('.tab-btn.active').dataset.tab;
        
        switch (activeTab) {
            case 'input':
                jsonString = this.jsonInput.value;
                break;
            case 'file':
                if (!this.jsonFile.files.length) {
                    this.showMessage('Please select a JSON file', 'error');
                    return;
                }
                jsonString = this.currentFileContent;
                break;
            case 'url':
                if (!this.currentUrlContent) {
                    this.showMessage('Please fetch JSON from URL first', 'error');
                    return;
                }
                jsonString = this.currentUrlContent;
                break;
        }
        
        if (!jsonString.trim()) {
            this.showMessage('Please enter some JSON to validate', 'error');
            return;
        }
        
        try {
            const jsonObj = JSON.parse(jsonString);
            this.showValidationSuccess(jsonObj);
            this.displayJSONTree(jsonObj);
            this.errorDetails.classList.add('hidden');
            
            // ذخیره در localStorage
            localStorage.setItem('lastValidatedJSON', jsonString);
        } catch (error) {
            this.showValidationError(error, jsonString);
            this.jsonTree.classList.add('hidden');
        }
    }
    
    showValidationSuccess(jsonObj) {
        this.validationResult.innerHTML = `
            <div class="validation-success">
                <i class="fas fa-check-circle"></i>
                <span>Valid JSON!</span>
            </div>
            <div class="validation-stats">
                <p>JSON contains ${this.countJSONItems(jsonObj)} items</p>
            </div>
        `;
    }
    
    countJSONItems(obj) {
        if (typeof obj !== 'object' || obj === null) return 1;
        
        let count = 0;
        if (Array.isArray(obj)) {
            count += obj.length;
            obj.forEach(item => count += this.countJSONItems(item));
        } else {
            count += Object.keys(obj).length;
            for (const key in obj) {
                count += this.countJSONItems(obj[key]);
            }
        }
        
        return count;
    }
    
    showValidationError(error, jsonString) {
        this.validationResult.innerHTML = `
            <div class="validation-error">
                <i class="fas fa-times-circle"></i>
                <span>Invalid JSON: ${error.message}</span>
            </div>
        `;
        
        this.showErrorDetails(error, jsonString);
    }
    
    showErrorDetails(error, jsonString) {
        this.errorDetails.classList.remove('hidden');
        const errorContent = this.errorDetails.querySelector('.error-content');
        
        let errorMessage = error.message;
        const positionMatch = errorMessage.match(/at position (\d+)/);
        let position = positionMatch ? parseInt(positionMatch[1]) : -1;
        
        if (position > -1) {
            const lines = jsonString.substr(0, position).split('\n');
            const lineNumber = lines.length;
            const columnNumber = lines[lines.length - 1].length + 1;
            
            errorMessage += ` (Line ${lineNumber}, Column ${columnNumber})`;
            
            // نمایش متن اطراف خطا
            const start = Math.max(0, position - 20);
            const end = Math.min(jsonString.length, position + 20);
            const context = jsonString.substring(start, end);
            
            errorContent.innerHTML = `
                <p><strong>Error:</strong> ${errorMessage}</p>
                <div class="error-context">
                    <pre>${this.highlightErrorPosition(context, position - start)}</pre>
                </div>
            `;
        } else {
            errorContent.innerHTML = `<p><strong>Error:</strong> ${errorMessage}</p>`;
        }
    }
    
    highlightErrorPosition(text, position) {
        if (position < 0 || position >= text.length) return text;
        
        const before = text.substring(0, position);
        const errorChar = text[position];
        const after = text.substring(position + 1);
        
        return `${before}<span class="error-char">${errorChar}</span>${after}`;
    }
    
    displayJSONTree(jsonObj) {
        this.jsonTree.classList.remove('hidden');
        const treeViewer = this.jsonTree.querySelector('.tree-viewer');
        treeViewer.innerHTML = '';
        
        const tree = document.createElement('ul');
        this.buildJSONTree(jsonObj, tree);
        treeViewer.appendChild(tree);
        
        // اضافه کردن رویدادهای collapse/expand
        treeViewer.querySelectorAll('.json-collapse').forEach(btn => {
            btn.addEventListener('click', function() {
                const container = this.parentElement.querySelector('ul');
                if (container) {
                    container.classList.toggle('collapsed');
                    this.classList.toggle('collapsed');
                }
            });
        });
    }
    
    buildJSONTree(data, parentEl, key = null) {
        const li = document.createElement('li');
        
        if (key !== null) {
            const keySpan = document.createElement('span');
            keySpan.className = 'json-key';
            keySpan.textContent = `${key}: `;
            li.appendChild(keySpan);
        }
        
        if (typeof data === 'object' && data !== null) {
            const type = Array.isArray(data) ? 'array' : 'object';
            const count = Array.isArray(data) ? data.length : Object.keys(data).length;
            
            const typeSpan = document.createElement('span');
            typeSpan.className = `json-${type}`;
            typeSpan.textContent = Array.isArray(data) ? '[' : '{';
            li.appendChild(typeSpan);
            
            const collapseBtn = document.createElement('span');
            collapseBtn.className = 'json-collapse';
            collapseBtn.textContent = `${count} items`;
            collapseBtn.title = 'Click to expand/collapse';
            li.appendChild(collapseBtn);
            
            const typeEndSpan = document.createElement('span');
            typeEndSpan.className = `json-${type}`;
            typeEndSpan.textContent = Array.isArray(data) ? ']' : '}';
            li.appendChild(typeEndSpan);
            
            const childUl = document.createElement('ul');
            
            if (Array.isArray(data)) {
                data.forEach((item, index) => {
                    this.buildJSONTree(item, childUl, index);
                });
            } else {
                for (const key in data) {
                    this.buildJSONTree(data[key], childUl, key);
                }
            }
            
            li.appendChild(childUl);
        } else {
            const valueSpan = document.createElement('span');
            let valueType = typeof data;
            
            if (data === null) {
                valueType = 'null';
                valueSpan.className = 'json-null';
                valueSpan.textContent = 'null';
            } else {
                valueSpan.className = `json-${valueType}`;
                
                if (valueType === 'string') {
                    valueSpan.textContent = `"${data}"`;
                } else {
                    valueSpan.textContent = String(data);
                }
            }
            
            li.appendChild(valueSpan);
        }
        
        parentEl.appendChild(li);
    }
    
    handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        if (file.size > 2 * 1024 * 1024) { // 2MB max
            this.showMessage('File is too large (max 2MB)', 'error');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            this.currentFileContent = e.target.result;
            this.fileUploadLabel.innerHTML = `
                <i class="fas fa-file-alt"></i>
                <span>${file.name}</span>
                <small>${(file.size / 1024).toFixed(1)} KB</small>
            `;
        };
        reader.readAsText(file);
    }
    
    fetchFromUrl() {
        const url = this.jsonUrl.value.trim();
        if (!url) {
            this.showMessage('Please enter a URL', 'error');
            return;
        }
        
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            this.showMessage('Invalid URL format', 'error');
            return;
        }
        
        this.fetchBtn.disabled = true;
        this.fetchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.text();
            })
            .then(text => {
                this.currentUrlContent = text;
                this.showMessage('JSON fetched successfully', 'success');
                this.fetchBtn.disabled = false;
                this.fetchBtn.textContent = 'Fetch';
            })
            .catch(error => {
                this.showMessage(`Failed to fetch: ${error.message}`, 'error');
                this.fetchBtn.disabled = false;
                this.fetchBtn.textContent = 'Fetch';
            });
    }
    
    clearAll() {
        this.jsonInput.value = '';
        this.jsonFile.value = '';
        this.jsonUrl.value = '';
        this.currentFileContent = null;
        this.currentUrlContent = null;
        this.fileUploadLabel.innerHTML = `
            <i class="fas fa-cloud-upload-alt"></i>
            <span>Choose a JSON file or drag it here</span>
        `;
        this.validationResult.innerHTML = `
            <div class="initial-message">
                <i class="fas fa-check-circle"></i>
                <p>Enter JSON data to validate</p>
            </div>
        `;
        this.errorDetails.classList.add('hidden');
        this.jsonTree.classList.add('hidden');
        localStorage.removeItem('lastValidatedJSON');
    }
    
    copyResult() {
        const activeTab = document.querySelector('.tab-btn.active').dataset.tab;
        let textToCopy = '';
        
        switch (activeTab) {
            case 'input':
                textToCopy = this.jsonInput.value;
                break;
            case 'file':
                textToCopy = this.currentFileContent || '';
                break;
            case 'url':
                textToCopy = this.currentUrlContent || '';
                break;
        }
        
        if (!textToCopy.trim()) {
            this.showMessage('Nothing to copy', 'error');
            return;
        }
        
        navigator.clipboard.writeText(textToCopy)
            .then(() => {
                const originalText = this.copyBtn.innerHTML;
                this.copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => {
                    this.copyBtn.innerHTML = originalText;
                }, 2000);
            })
            .catch(err => {
                console.error('Failed to copy: ', err);
                this.showMessage('Failed to copy text', 'error');
            });
    }
    
    saveToProfile() {
        if (!this.auth || !this.auth.checkAuth()) {
            this.showMessage('Please login to save JSON', 'error');
            return;
        }
        
        const activeTab = document.querySelector('.tab-btn.active').dataset.tab;
        let jsonString = '';
        
        switch (activeTab) {
            case 'input':
                jsonString = this.jsonInput.value;
                break;
            case 'file':
                jsonString = this.currentFileContent || '';
                break;
            case 'url':
                jsonString = this.currentUrlContent || '';
                break;
        }
        
        if (!jsonString.trim()) {
            this.showMessage('No JSON data to save', 'error');
            return;
        }
        
        try {
            JSON.parse(jsonString); // Validate again before saving
        } catch (error) {
            this.showMessage('Cannot save invalid JSON', 'error');
            return;
        }
        
        this.saveBtn.disabled = true;
        this.saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        
        fetch('/api/save-json.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                json_data: jsonString,
                title: 'JSON from Validator'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showMessage('JSON saved to your profile', 'success');
            } else {
                this.showMessage(data.message || 'Failed to save JSON', 'error');
            }
            this.saveBtn.disabled = false;
            this.saveBtn.textContent = 'Save to Profile';
        })
        .catch(error => {
            console.error('Error:', error);
            this.showMessage('Failed to save JSON', 'error');
            this.saveBtn.disabled = false;
            this.saveBtn.textContent = 'Save to Profile';
        });
    }
    
    loadLastJSON() {
        const lastJSON = localStorage.getItem('lastValidatedJSON');
        if (lastJSON) {
            this.jsonInput.value = lastJSON;
        }
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
    new JSONValidator();
});
