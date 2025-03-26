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
        
