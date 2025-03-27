document.addEventListener('DOMContentLoaded', function() {
    // متغیرهای global
    const state = {
        endpoints: [],
        currentTab: 'php-code',
        docFormat: 'html',
        options: {
            includeExamples: true,
            groupByTag: true
        }
    };
    
    // عناصر DOM
    const elements = {
        tabs: document.querySelectorAll('.tab-btn'),
        tabContents: document.querySelectorAll('.tab-content'),
        generateBtn: document.getElementById('generate-docs'),
        clearBtn: document.getElementById('clear-all'),
        saveBtn: document.getElementById('save-docs'),
        downloadBtn: document.getElementById('download-docs'),
        copyBtn: document.getElementById('copy-docs'),
        previewBtn: document.getElementById('preview-docs'),
        docsOutput: document.getElementById('docs-output'),
        docsContainer: document.getElementById('docs-output-container'),
        docsPreview: document.getElementById('docs-preview'),
        previewFrame: document.getElementById('docs-preview-frame'),
        errorSection: document.getElementById('error-message'),
        errorText: document.getElementById('error-text'),
        docFormat: document.getElementById('doc-format'),
        includeExamples: document.getElementById('include-examples'),
        groupByTag: document.getElementById('group-by-tag'),
        addEndpointBtn: document.getElementById('add-endpoint'),
        endpointsList: document.getElementById('endpoints-list')
    };
    
    // توابع اصلی
    const init = () => {
        setupEventListeners();
        setupTabs();
        setupEndpointBuilder();
    };
    
    const setupEventListeners = () => {
        // رویدادهای تب‌ها
        elements.tabs.forEach(tab => {
            tab.addEventListener('click', () => switchTab(tab.dataset.tab));
        });
        
        // رویدادهای دکمه‌ها
        elements.generateBtn.addEventListener('click', generateDocumentation);
        elements.clearBtn.addEventListener('click', clearAll);
        if (elements.saveBtn) {
            elements.saveBtn.addEventListener('click', saveDocumentation);
        }
        elements.downloadBtn.addEventListener('click', downloadDocumentation);
        elements.copyBtn.addEventListener('click', copyDocumentation);
        elements.previewBtn.addEventListener('click', togglePreview);
        
        // رویدادهای گزینه‌ها
        elements.docFormat.addEventListener('change', (e) => {
            state.docFormat = e.target.value;
        });
        
        elements.includeExamples.addEventListener('change', (e) => {
            state.options.includeExamples = e.target.checked;
        });
        
        elements.groupByTag.addEventListener('change', (e) => {
            state.options.groupByTag = e.target.checked;
        });
    };
    
    const setupTabs = () => {
        switchTab('php-code');
    };
    
    const switchTab = (tabId) => {
        state.currentTab = tabId;
        
        // غیرفعال کردن همه تب‌ها
        elements.tabs.forEach(tab => tab.classList.remove('active'));
        elements.tabContents.forEach(content => content.classList.remove('active'));
        
        // فعال کردن تب انتخاب شده
        document.querySelector(`.tab-btn[data-tab="${tabId}"]`).classList.add('active');
        document.getElementById(`${tabId}-tab`).classList.add('active');
    };
    
    const setupEndpointBuilder = () => {
        elements.addEndpointBtn.addEventListener('click', addEndpoint);
    };
    
    const addEndpoint = () => {
        const endpointId = Date.now();
        const endpointItem = document.querySelector('.endpoint-item.template').cloneNode(true);
        endpointItem.classList.remove('template');
        endpointItem.dataset.id = endpointId;
        
        endpointItem.querySelector('.remove-endpoint').addEventListener('click', () => {
            removeEndpoint(endpointId);
        });
        
        endpointItem.querySelector('.add-parameter').addEventListener('click', () => {
            addParameter(endpointId);
        });
        
        endpointItem.querySelector('.add-response').addEventListener('click', () => {
            addResponse(endpointId);
        });
        
        elements.endpointsList.appendChild(endpointItem);
        
        state.endpoints.push({
            id: endpointId,
            method: '',
            path: '',
            description: '',
            parameters: [],
            responses: []
        });
    };
    
    const removeEndpoint = (id) => {
        state.endpoints = state.endpoints.filter(e => e.id !== id);
        document.querySelector(`.endpoint-item[data-id="${id}"]`).remove();
    };
    
    const addParameter = (endpointId) => {
        const endpoint = state.endpoints.find(e => e.id == endpointId);
        if (!endpoint) return;
        
        const paramId = Date.now();
        const paramItem = document.createElement('div');
        paramItem.className = 'parameter-item';
        paramItem.dataset.id = paramId;
        paramItem.innerHTML = `
            <select class="form-control param-in" style="flex: 0.5;">
                <option value="query">Query</option>
                <option value="path">Path</option>
                <option value="header">Header</option>
                <option value="cookie">Cookie</option>
            </select>
            <input type="text" class="form-control param-name" placeholder="Parameter name">
            <input type="text" class="form-control param-type" placeholder="Type">
            <input type="text" class="form-control param-desc" placeholder="Description">
            <button class="btn btn-outline btn-sm remove-param"><i class="fas fa-times"></i></button>
        `;
        
        paramItem.querySelector('.remove-param').addEventListener('click', () => {
            removeParameter(endpointId, paramId);
        });
        
        document.querySelector(`.endpoint-item[data-id="${endpointId}"] .parameters-container`).appendChild(paramItem);
        
        endpoint.parameters.push({
            id: paramId,
            in: 'query',
            name: '',
            type: '',
            description: ''
        });
    };
    
    const removeParameter = (endpointId, paramId) => {
        const endpoint = state.endpoints.find(e => e.id == endpointId);
        if (!endpoint) return;
        
        endpoint.parameters = endpoint.parameters.filter(p => p.id != paramId);
        document.querySelector(`.parameter-item[data-id="${paramId}"]`).remove();
    };
    
    const addResponse = (endpointId) => {
        const endpoint = state.endpoints.find(e => e.id == endpointId);
        if (!endpoint) return;
        
        const respId = Date.now();
        const respItem = document.createElement('div');
        respItem.className = 'response-item';
        respItem.dataset.id = respId;
        respItem.innerHTML = `
            <input type="text" class="form-control resp-code" placeholder="200" style="flex: 0.5;">
            <input type="text" class="form-control resp-desc" placeholder="Description">
            <input type="text" class="form-control resp-type" placeholder="application/json">
            <button class="btn btn-outline btn-sm remove-resp"><i class="fas fa-times"></i></button>
        `;
        
        respItem.querySelector('.remove-resp').addEventListener('click', () => {
            removeResponse(endpointId, respId);
        });
        
        document.querySelector(`.endpoint-item[data-id="${endpointId}"] .responses-container`).appendChild(respItem);
        
        endpoint.responses.push({
            id: respId,
            code: '',
            description: '',
            contentType: ''
        });
    };
    
    const removeResponse = (endpointId, respId) => {
        const endpoint = state.endpoints.find(e => e.id == endpointId);
        if (!endpoint) return;
        
        endpoint.responses = endpoint.responses.filter(r => r.id != respId);
        document.querySelector(`.response-item[data-id="${respId}"]`).remove();
    };
    
    const generateDocumentation = () => {
        try {
            let docs;
            
            if (state.currentTab === 'php-code') {
                docs = generateFromPhpCode();
            } else if (state.currentTab === 'openapi') {
                docs = generateFromOpenApi();
            } else if (state.currentTab === 'endpoints') {
                docs = generateFromEndpoints();
            }
            
            displayDocumentation(docs);
            elements.errorSection.classList.add('hidden');
        } catch (error) {
            showError(error.message);
        }
    };
    
    const generateFromPhpCode = () => {
        const phpCode = document.getElementById('php-code').value;
        const fileInput = document.getElementById('php-file');
        
        if (!phpCode && (!fileInput || !fileInput.files[0])) {
            throw new Error('لطفاً کد PHP یا فایل را وارد کنید');
        }
        
        // در حالت واقعی، اینجا کد PHP تجزیه و تحلیل می‌شود
        // این یک نمونه ساده است
        return {
            info: {
                title: 'Generated API',
                version: '1.0.0'
            },
            paths: {
                '/users': {
                    get: {
                        summary: 'Get list of users',
                        responses: {
                            '200': {
                                description: 'List of users'
                            }
                        }
                    }
                }
            }
        };
    };
    
    const generateFromOpenApi = () => {
        const openApiCode = document.getElementById('openapi-code').value;
        const fileInput = document.getElementById('openapi-file');
        
        if (!openApiCode && (!fileInput || !fileInput.files[0])) {
            throw new Error('لطفاً کد OpenAPI/Swagger یا فایل را وارد کنید');
        }
        
        // در حالت واقعی، اینجا مشخصات OpenAPI تجزیه و تحلیل می‌شود
        // این یک نمونه ساده است
        return {
            openapi: '3.0.0',
            info: {
                title: 'Sample API',
                version: '1.0.0'
            },
            paths: {
                '/users': {
                    get: {
                        summary: 'Get list of users',
                        responses: {
                            '200': {
                                description: 'List of users'
                            }
                        }
                    }
                }
            }
        };
    };
    
    const generateFromEndpoints = () => {
        if (state.endpoints.length === 0) {
            throw new Error('لطفاً حداقل یک endpoint اضافه کنید');
        }
        
        // جمع‌آوری داده‌ها از فرم
        state.endpoints.forEach(endpoint => {
            const endpointEl = document.querySelector(`.endpoint-item[data-id="${endpoint.id}"]`);
            
            endpoint.method = endpointEl.querySelector('.endpoint-method').value;
            endpoint.path = endpointEl.querySelector('.endpoint-path').value;
            endpoint.description = endpointEl.querySelector('.endpoint-description').value;
            
            // جمع‌آوری پارامترها
            endpoint.parameters = [];
            endpointEl.querySelectorAll('.parameter-item').forEach(paramEl => {
                endpoint.parameters.push({
                    in: paramEl.querySelector('.param-in').value,
                    name: paramEl.querySelector('.param-name').value,
                    type: paramEl.querySelector('.param-type').value,
                    description: paramEl.querySelector('.param-desc').value
                });
            });
            
            // جمع‌آوری پاسخ‌ها
            endpoint.responses = [];
            endpointEl.querySelectorAll('.response-item').forEach(respEl => {
                endpoint.responses.push({
                    code: respEl.querySelector('.resp-code').value,
                    description: respEl.querySelector('.resp-desc').value,
                    contentType: respEl.querySelector('.resp-type').value
                });
            });
        });
        
        // تولید ساختار OpenAPI
        const openApiDoc = {
            openapi: '3.0.0',
            info: {
                title: 'API Documentation',
                version: '1.0.0'
            },
            paths: {}
        };
        
        state.endpoints.forEach(endpoint => {
            if (!openApiDoc.paths[endpoint.path]) {
                openApiDoc.paths[endpoint.path] = {};
            }
            
            openApiDoc.paths[endpoint.path][endpoint.method.toLowerCase()] = {
                summary: endpoint.description,
                parameters: endpoint.parameters.map(param => ({
                    name: param.name,
                    in: param.in,
                    description: param.description,
                    schema: {
                        type: param.type
                    }
                })),
                responses: endpoint.responses.reduce((acc, resp) => {
                    acc[resp.code] = {
                        description: resp.description,
                        content: {
                            [resp.contentType]: {
                                schema: {
                                    type: 'object'
                                }
                            }
                        }
                    };
                    return acc;
                }, {})
            };
        });
        
        return openApiDoc;
    };
    
    const displayDocumentation = (docs) => {
        let output;
        
        switch (state.docFormat) {
            case 'html':
                output = generateHtmlDocs(docs);
                break;
            case 'markdown':
                output = generateMarkdownDocs(docs);
                break;
            case 'openapi':
                output = JSON.stringify(docs, null, 2);
                break;
            case 'postman':
                output = generatePostmanCollection(docs);
                break;
            default:
                output = JSON.stringify(docs, null, 2);
        }
        
        elements.docsOutput.textContent = output;
    };
    
    const generateHtmlDocs = (docs) => {
        // در حالت واقعی، اینجا یک قالب HTML زیبا تولید می‌شود
        return `<!DOCTYPE html>
<html>
<head>
    <title>${docs.info.title} - API Documentation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 1200px; margin: 0 auto; padding: 20px; }
        h1 { color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .endpoint { margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        .method { font-weight: bold; color: white; padding: 3px 8px; border-radius: 3px; }
        .get { background: #61affe; }
        .post { background: #49cc90; }
        .put { background: #fca130; }
        .delete { background: #f93e3e; }
        .path { font-family: monospace; font-size: 16px; }
        .description { margin: 10px 0; }
    </style>
</head>
<body>
    <h1>${docs.info.title} <small>v${docs.info.version}</small></h1>
    
    ${Object.entries(docs.paths).map(([path, methods]) => `
        ${Object.entries(methods).map(([method, details]) => `
            <div class="endpoint">
                <div>
                    <span class="method ${method}">${method.toUpperCase()}</span>
                    <span class="path">${path}</span>
                </div>
                <div class="description">${details.summary}</div>
            </div>
        `).join('')}
    `).join('')}
</body>
</html>`;
    };
    
    const generateMarkdownDocs = (docs) => {
        return `# ${docs.info.title}
Version: ${docs.info.version}

## Endpoints

${Object.entries(docs.paths).map(([path, methods]) => `
${Object.entries(methods).map(([method, details]) => `
### \`${method.toUpperCase()} ${path}\`

${details.summary}

**Parameters:**
${details.parameters ? details.parameters.map(p => `- \`${p.name}\` (${p.in}, ${p.schema.type}): ${p.description}`).join('\n') : 'None'}

**Responses:**
${Object.entries(details.responses).map(([code, resp]) => `- ${code}: ${resp.description}`).join('\n')}
`).join('')}
`).join('')}`;
    };
    
    const generatePostmanCollection = (docs) => {
        const collection = {
            info: {
                name: docs.info.title,
                schema: 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
            },
            item: []
        };
        
        Object.entries(docs.paths).forEach(([path, methods]) => {
            Object.entries(methods).forEach(([method, details]) => {
                collection.item.push({
                    name: details.summary || `${method} ${path}`,
                    request: {
                        method: method.toUpperCase(),
                        header: [],
                        url: {
                            raw: `{{base_url}}${path}`,
                            host: ['{{base_url}}'],
                            path: path.split('/').filter(p => p)
                        }
                    },
                    response: []
                });
            });
        });
        
        return JSON.stringify(collection, null, 2);
    };
    
    const clearAll = () => {
        if (confirm('آیا از پاک کردن همه داده‌ها اطمینان دارید؟')) {
            state.endpoints = [];
            elements.endpointsList.innerHTML = '';
            elements.docsOutput.textContent = 'API documentation will appear here...';
            elements.docsPreview.classList.add('hidden');
            elements.docsContainer.classList.remove('hidden');
            elements.errorSection.classList.add('hidden');
            
            if (state.currentTab === 'php-code') {
                document.getElementById('php-code').value = '';
                document.getElementById('php-file').value = '';
            } else if (state.currentTab === 'openapi') {
                document.getElementById('openapi-code').value = '';
                document.getElementById('openapi-file').value = '';
            }
        }
    };
    
    const saveDocumentation = () => {
        const docs = elements.docsOutput.textContent;
        if (!docs || docs === 'API documentation will appear here...') {
            showError('لطفاً ابتدا مستندات را تولید کنید');
            return;
        }
        
        const name = prompt('لطفاً یک نام برای مستندات وارد کنید:', `API Documentation ${new Date().toLocaleString()}`);
        if (name) {
            // در حالت واقعی، اینجا درخواست به سرور ارسال می‌شود
            localStorage.setItem('saved_docs', docs);
            alert('مستندات با موفقیت ذخیره شد!');
        }
    };
    
    const downloadDocumentation = () => {
        const docs = elements.docsOutput.textContent;
        if (!docs || docs === 'API documentation will appear here...') {
            showError('لطفاً ابتدا مستندات را تولید کنید');
            return;
        }
        
        const blob = new Blob([docs], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `api-documentation-${new Date().toISOString().slice(0,10)}.${state.docFormat === 'openapi' ? 'json' : state.docFormat}`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };
    
    const copyDocumentation = () => {
        const docs = elements.docsOutput.textContent;
        if (!docs || docs === 'API documentation will appear here...') {
            showError('لطفاً ابتدا مستندات را تولید کنید');
            return;
        }
        
        navigator.clipboard.writeText(docs)
            .then(() => {
                alert('مستندات با موفقیت کپی شد!');
            })
            .catch(err => {
                showError('خطا در کپی کردن مستندات: ' + err.message);
            });
    };
    
    const togglePreview = () => {
        if (state.docFormat !== 'html') {
            showError('پیش‌نمایش فقط برای قالب HTML در دسترس است');
            return;
        }
        
        if (elements.docsPreview.classList.contains('hidden')) {
            const docs = elements.docsOutput.textContent;
            const blob = new Blob([docs], { type: 'text/html' });
            elements.previewFrame.src = URL.createObjectURL(blob);
            elements.docsContainer.classList.add('hidden');
            elements.docsPreview.classList.remove('hidden');
            elements.previewBtn.innerHTML = '<i class="fas fa-code"></i> Show Code';
        } else {
            elements.docsContainer.classList.remove('hidden');
            elements.docsPreview.classList.add('hidden');
            elements.previewBtn.innerHTML = '<i class="fas fa-eye"></i> Preview';
        }
    };
    
    const showError = (message) => {
        elements.errorText.textContent = message;
        elements.errorSection.classList.remove('hidden');
    };
    
    // شروع برنامه
    init();
});
