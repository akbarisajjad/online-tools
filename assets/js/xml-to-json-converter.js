class XmlToJsonConverter {
    constructor() {
        this.xmlInput = document.getElementById('xml-input');
        this.xmlFile = document.getElementById('xml-file');
        this.xmlUrl = document.getElementById('xml-url');
        this.convertBtn = document.getElementById('convert-btn');
        this.clearBtn = document.getElementById('clear-btn');
        this.copyBtn = document.getElementById('copy-btn');
        this.saveBtn = document.getElementById('save-btn') || null;
        this.downloadBtn = document.getElementById('download-btn');
        this.fetchBtn = document.getElementById('fetch-btn');
        this.jsonOutput = document.getElementById('json-output');
        this.conversionStats = document.getElementById('conversion-stats');
        this.errorMessage = document.getElementById('error-message');
        this.tabBtns = document.querySelectorAll('.tab-btn');
        this.tabContents = document.querySelectorAll('.tab-content');
        this.fileUploadLabel = document.querySelector('.file-upload-label');
        
        // Options
        this.prettyPrint = document.getElementById('pretty-print');
        this.preserveAttributes = document.getElementById('preserve-attributes');
        this.forceArray = document.getElementById('force-array');
        
        // Stats elements
        this.xmlSizeEl = document.getElementById('xml-size');
        this.jsonSizeEl = document.getElementById('json-size');
        this.conversionTimeEl = document.getElementById('conversion-time');
        this.elementsCountEl = document.getElementById('elements-count');
        
        this.currentFileContent = null;
        this.currentUrlContent = null;
        this.currentJsonOutput = null;
        
        this.init();
    }
    
    init() {
        // Initialize tabs
        this.tabBtns.forEach(btn => {
            btn.addEventListener('click', () => this.switchTab(btn.dataset.tab));
        });
        
        // Initialize buttons
        this.convertBtn.addEventListener('click', () => this.convertXmlToJson());
        this.clearBtn.addEventListener('click', () => this.clearAll());
        this.copyBtn.addEventListener('click', () => this.copyJson());
        this.downloadBtn.addEventListener('click', () => this.downloadJson());
        this.fetchBtn.addEventListener('click', () => this.fetchXmlFromUrl());
        
        if (this.saveBtn) {
            this.saveBtn.addEventListener('click', () => this.saveToProfile());
        }
        
        // Initialize file upload
        this.xmlFile.addEventListener('change', (e) => this.handleFileUpload(e));
        
        // Initialize drag and drop
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
                this.xmlFile.files = e.dataTransfer.files;
                this.handleFileUpload({ target: this.xmlFile });
            }
        });
        
        // Load last XML from localStorage if exists
        this.loadLastXml();
    }
    
    switchTab(tabName) {
        this.tabBtns.forEach(btn => btn.classList.remove('active'));
        this.tabContents.forEach(content => content.classList.remove('active'));
        
        document.querySelector(`.tab-btn[data-tab="${tabName}"]`).classList.add('active');
        document.getElementById(`${tabName}-tab`).classList.add('active');
    }
    
    convertXmlToJson() {
        let xmlString = '';
        const activeTab = document.querySelector('.tab-btn.active').dataset.tab;
        
        switch (activeTab) {
            case 'input':
                xmlString = this.xmlInput.value;
                break;
            case 'file':
                if (!this.xmlFile.files.length) {
                    this.showError('Please select an XML file');
                    return;
                }
                xmlString = this.currentFileContent;
                break;
            case 'url':
                if (!this.currentUrlContent) {
                    this.showError('Please fetch XML from URL first');
                    return;
                }
                xmlString = this.currentUrlContent;
                break;
        }
        
        if (!xmlString.trim()) {
            this.showError('Please enter some XML to convert');
            return;
        }
        
        // Start conversion timer
        const startTime = performance.now();
        
        try {
            // Parse XML string
            const xmlDoc = this.parseXml(xmlString);
            
            // Convert to JavaScript object
            const options = {
                preserveAttributes: this.preserveAttributes.checked,
                forceArray: this.forceArray.checked
            };
            const jsonObj = this.xmlToJson(xmlDoc, options);
            
            // Convert to JSON string
            const jsonStr = this.prettyPrint.checked 
                ? JSON.stringify(jsonObj, null, 4) 
                : JSON.stringify(jsonObj);
            
            // Display results
            this.displayJsonResult(jsonStr);
            this.showConversionStats(startTime, xmlString, jsonStr);
            this.hideError();
            
            // Save to localStorage
            localStorage.setItem('lastConvertedXml', xmlString);
            this.currentJsonOutput = jsonStr;
        } catch (error) {
            this.showError(error.message);
            this.conversionStats.classList.add('hidden');
        }
    }
    
    parseXml(xmlString) {
        try {
            // Try standard DOMParser first
            const parser = new DOMParser();
            const xmlDoc = parser.parseFromString(xmlString, "application/xml");
            
            // Check for parser errors
            const parserError = xmlDoc.getElementsByTagName("parsererror");
            if (parserError.length > 0) {
                throw new Error(this.extractParserError(parserError[0]));
            }
            
            return xmlDoc;
        } catch (error) {
            throw new Error(`XML parsing error: ${error.message}`);
        }
    }
    
    extractParserError(parserError) {
        // Try to extract meaningful error message
        if (parserError.textContent) {
            const matches = parserError.textContent.match(/error:\s*(.*?)\n/i);
            if (matches && matches[1]) {
                return matches[1].trim();
            }
            return parserError.textContent.trim().split('\n')[0];
        }
        return "Invalid XML format";
    }
    
    xmlToJson(xml, options = {}) {
        const result = {};
        
        if (xml.nodeType === Node.DOCUMENT_NODE) {
            result[xml.documentElement.nodeName] = this.processNode(xml.documentElement, options);
            return result;
        }
        
        if (xml.nodeType === Node.ELEMENT_NODE) {
            return this.processNode(xml, options);
        }
        
        throw new Error("Unsupported XML node type");
    }
    
    processNode(node, options) {
        const obj = {};
        
        // Process attributes
        if (options.preserveAttributes && node.attributes && node.attributes.length > 0) {
            obj["@attributes"] = {};
            for (let i = 0; i < node.attributes.length; i++) {
                const attr = node.attributes[i];
                obj["@attributes"][attr.nodeName] = attr.nodeValue;
            }
        }
        
        // Process child nodes
        const childNodes = node.childNodes;
        let hasElements = false;
        
        for (let i = 0; i < childNodes.length; i++) {
            const child = childNodes[i];
            
            if (child.nodeType === Node.ELEMENT_NODE) {
                hasElements = true;
                const childName = child.nodeName;
                const childValue = this.processNode(child, options);
                
                if (obj[childName]) {
                    if (!Array.isArray(obj[childName])) {
                        obj[childName] = [obj[childName]];
                    }
                    obj[childName].push(childValue);
                } else {
                    if (options.forceArray) {
                        obj[childName] = [childValue];
                    } else {
                        obj[childName] = childValue;
                    }
                }
            } else if (child.nodeType === Node.TEXT_NODE && child.nodeValue.trim() !== '') {
                if (!hasElements) {
                    return child.nodeValue.trim();
                }
                obj["#text"] = child.nodeValue.trim();
            }
        }
        
        // Handle empty objects
        if (!hasElements && Object.keys(obj).length === 0) {
            return "";
        }
        
        return obj;
    }
    
    displayJsonResult(jsonStr) {
        // Syntax highlighting for JSON
        this.jsonOutput.innerHTML = this.syntaxHighlight(jsonStr);
        this.conversionStats.classList.remove('hidden');
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
    
    showConversionStats(startTime, xmlString, jsonStr) {
        const endTime = performance.now();
        const conversionTime = (endTime - startTime).toFixed(2);
        
        // Count XML elements
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(xmlString, "application/xml");
        const elementsCount = xmlDoc.getElementsByTagName('*').length;
        
        // Update stats
        this.xmlSizeEl.textContent = this.formatFileSize(xmlString.length);
        this.jsonSizeEl.textContent = this.formatFileSize(jsonStr.length);
        this.conversionTimeEl.textContent = `${conversionTime} ms`;
        this.elementsCountEl.textContent = elementsCount;
    }
    
    formatFileSize(bytes) {
        if (bytes < 1024) return `${bytes} bytes`;
        if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(2)} KB`;
        return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
    }
    
    handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        if (file.size > 5 * 1024 * 1024) { // 5MB max
            this.showError('File is too large (max 5MB)');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            this.currentFileContent = e.target.result;
            this.fileUploadLabel.innerHTML = `
                <i class="fas fa-file-code"></i>
                <span>${file.name}</span>
                <small>${(file.size / 1024).toFixed(1)} KB</small>
            `;
        };
        reader.onerror = () => {
            this.showError('Error reading file');
        };
        reader.readAsText(file);
    }
    
    fetchXmlFromUrl() {
        const url = this.xmlUrl.value.trim();
        if (!url) {
            this.showError('Please enter a URL');
            return;
        }
        
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            this.showError('Invalid URL format');
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
                this.showMessage('XML fetched successfully', 'success');
                this.fetchBtn.disabled = false;
                this.fetchBtn.textContent = 'Fetch';
            })
            .catch(error => {
                this.showError(`Failed to fetch: ${error.message}`);
                this.fetchBtn.disabled = false;
                this.fetchBtn.textContent = 'Fetch';
            });
    }
    
    clearAll() {
        this.xmlInput.value = '';
        this.xmlFile.value = '';
        this.xmlUrl.value = '';
        this.currentFileContent = null;
        this.currentUrlContent = null;
        this.currentJsonOutput = null;
        this.fileUploadLabel.innerHTML = `
            <i class="fas fa-cloud-upload-alt"></i>
            <span>Choose an XML file or drag it here</span>
        `;
        this.jsonOutput.textContent = 'JSON output will appear here...';
        this.conversionStats.classList.add('hidden');
        this.hideError();
        localStorage.removeItem('lastConvertedXml');
    }
    
    copyJson() {
        if (!this.currentJsonOutput) {
            this.showError('No JSON to copy');
            return;
        }
        
        navigator.clipboard.writeText(this.currentJsonOutput)
            .then(() => {
                const originalText = this.copyBtn.innerHTML;
                this.copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => {
                    this.copyBtn.innerHTML = originalText;
                }, 2000);
            })
            .catch(err => {
                console.error('Failed to copy: ', err);
                this.showError('Failed to copy JSON');
            });
    }
    
    downloadJson() {
        if (!this.currentJsonOutput) {
            this.showError('No JSON to download');
            return;
        }
        
        const blob = new Blob([this.currentJsonOutput], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'converted.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
    
    saveToProfile() {
        if (!this.auth || !this.auth.checkAuth()) {
            this.showError('Please login to save JSON');
            return;
        }
        
        if (!this.currentJsonOutput) {
            this.showError('No JSON to save');
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
                json_data: this.currentJsonOutput,
                title: 'Converted from XML'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showMessage('JSON saved to your profile', 'success');
            } else {
                this.showError(data.message || 'Failed to save JSON');
            }
            this.saveBtn.disabled = false;
            this.saveBtn.textContent = 'Save to Profile';
        })
        .catch(error => {
            console.error('Error:', error);
            this.showError('Failed to save JSON');
            this.saveBtn.disabled = false;
            this.saveBtn.textContent = 'Save to Profile';
        });
    }
    
    loadLastXml() {
        const lastXml = localStorage.getItem('lastConvertedXml');
        if (lastXml) {
            this.xmlInput.value = lastXml;
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
    new XmlToJsonConverter();
});
