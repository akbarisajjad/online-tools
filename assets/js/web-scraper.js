document.addEventListener('DOMContentLoaded', function() {
    // متغیرهای global
    const state = {
        currentTab: 'url-scraping',
        scrapingRules: [],
        savedProfiles: [],
        lastResults: null,
        lastResponse: null
    };
    
    // عناصر DOM
    const elements = {
        tabs: document.querySelectorAll('.tool-tabs .tab-btn'),
        tabContents: document.querySelectorAll('.tab-content'),
        userAgent: document.getElementById('user-agent'),
        customUserAgent: document.getElementById('custom-user-agent'),
        targetUrl: document.getElementById('target-url'),
        requestMethod: document.getElementById('request-method'),
        requestHeaders: document.getElementById('request-headers'),
        requestBody: document.getElementById('request-body'),
        htmlContent: document.getElementById('html-content'),
        htmlFile: document.getElementById('html-file'),
        rulesList: document.getElementById('rules-list'),
        addRuleBtn: document.getElementById('add-rule'),
        startScrapingBtn: document.getElementById('start-scraping'),
        clearAllBtn: document.getElementById('clear-all'),
        saveProfileBtn: document.getElementById('save-profile'),
        scrapingResults: document.getElementById('scraping-results'),
        rawHtmlContent: document.getElementById('raw-html-content'),
        responseInfo: document.getElementById('response-info'),
        outputTabs: document.querySelectorAll('.output-tab-btn'),
        outputTabContents: document.querySelectorAll('.output-tab-content'),
        exportJsonBtn: document.getElementById('export-json'),
        exportCsvBtn: document.getElementById('export-csv'),
        copyResultsBtn: document.getElementById('copy-results'),
        errorSection: document.getElementById('error-message'),
        errorText: document.getElementById('error-text'),
        savedProfilesList: document.getElementById('saved-profiles-list')
    };
    
    // توابع اصلی
    const init = () => {
        setupEventListeners();
        loadSavedProfiles();
    };
    
    const setupEventListeners = () => {
        // رویدادهای تب‌ها
        elements.tabs.forEach(tab => {
            tab.addEventListener('click', () => switchTab(tab.dataset.tab));
        });
        
        // رویداد User Agent
        elements.userAgent.addEventListener('change', (e) => {
            elements.customUserAgent.classList.toggle('hidden', e.target.value !== 'custom');
        });
        
        // رویدادهای دکمه‌ها
        elements.addRuleBtn.addEventListener('click', addScrapingRule);
        elements.startScrapingBtn.addEventListener('click', startScraping);
        elements.clearAllBtn.addEventListener('click', clearAll);
        if (elements.saveProfileBtn) {
            elements.saveProfileBtn.addEventListener('click', saveProfile);
        }
        elements.exportJsonBtn.addEventListener('click', exportToJson);
        elements.exportCsvBtn.addEventListener('click', exportToCsv);
        elements.copyResultsBtn.addEventListener('click', copyResults);
        
        // رویدادهای تب‌های خروجی
        elements.outputTabs.forEach(tab => {
            tab.addEventListener('click', () => switchOutputTab(tab.dataset.tab));
        });
        
        // رویداد آپلود فایل HTML
        elements.htmlFile.addEventListener('change', handleHtmlFileUpload);
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
    
    const switchOutputTab = (tabId) => {
        // غیرفعال کردن همه تب‌های خروجی
        elements.outputTabs.forEach(tab => tab.classList.remove('active'));
        elements.outputTabContents.forEach(content => content.classList.remove('active'));
        
        // فعال کردن تب انتخاب شده
        document.querySelector(`.output-tab-btn[data-tab="${tabId}"]`).classList.add('active');
        document.getElementById(`${tabId}-tab`).classList.add('active');
    };
    
    const addScrapingRule = () => {
        const ruleId = Date.now();
        const ruleItem = document.querySelector('.rule-item.template').cloneNode(true);
        ruleItem.classList.remove('template');
        ruleItem.dataset.id = ruleId;
        
        // رویداد تغییر نوع ویژگی
        const attrSelect = ruleItem.querySelector('.rule-attr');
        const customAttrInput = ruleItem.querySelector('.rule-custom-attr');
        
        attrSelect.addEventListener('change', (e) => {
            customAttrInput.classList.toggle('hidden', e.target.value !== 'custom');
        });
        
        // رویداد حذف قانون
        ruleItem.querySelector('.remove-rule').addEventListener('click', () => {
            removeScrapingRule(ruleId);
        });
        
        elements.rulesList.appendChild(ruleItem);
        
        state.scrapingRules.push({
            id: ruleId,
            type: 'css',
            selector: '',
            name: '',
            attr: 'text',
            customAttr: ''
        });
    };
    
    const removeScrapingRule = (id) => {
        state.scrapingRules = state.scrapingRules.filter(rule => rule.id !== id);
        document.querySelector(`.rule-item[data-id="${id}"]`).remove();
    };
    
    const handleHtmlFileUpload = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = (event) => {
            elements.htmlContent.value = event.target.result;
        };
        reader.readAsText(file);
    };
    
    const startScraping = async () => {
        try {
            elements.errorSection.classList.add('hidden');
            
            if (state.scrapingRules.length === 0) {
                throw new Error('لطفاً حداقل یک قانون استخراج اضافه کنید');
            }
            
            // جمع‌آوری قوانین از فرم
            updateScrapingRulesFromUI();
            
            let html, response;
            
            if (state.currentTab === 'url-scraping') {
                if (!elements.targetUrl.value) {
                    throw new Error('لطفاً یک URL معتبر وارد کنید');
                }
                
                // در حالت واقعی، اینجا درخواست به سرور ارسال می‌شود
                // این یک نمونه شبیه‌سازی شده است
                response = {
                    url: elements.targetUrl.value,
                    status: 200,
                    headers: {
                        'content-type': 'text/html'
                    },
                    html: `
                        <html>
                            <body>
                                <div class="product">
                                    <h3>محصول نمونه 1</h3>
                                    <p class="price">$19.99</p>
                                    <a href="/product/1">مشاهده جزئیات</a>
                                </div>
                                <div class="product">
                                    <h3>محصول نمونه 2</h3>
                                    <p class="price">$29.99</p>
                                    <a href="/product/2">مشاهده جزئیات</a>
                                </div>
                            </body>
                        </html>
                    `
                };
                
                html = response.html;
                state.lastResponse = response;
                
                // نمایش اطلاعات پاسخ
                displayResponseInfo(response);
                
            } else if (state.currentTab === 'html-scraping') {
                if (!elements.htmlContent.value) {
                    throw new Error('لطفاً محتوای HTML را وارد کنید');
                }
                
                html = elements.htmlContent.value;
                state.lastResponse = {
                    url: 'local-html-content',
                    status: 200,
                    headers: {
                        'content-type': 'text/html'
                    }
                };
                
                displayResponseInfo(state.lastResponse);
            }
            
            // نمایش HTML خام
            elements.rawHtmlContent.textContent = html;
            
            // استخراج داده‌ها (شبیه‌سازی شده)
            const results = simulateScraping(html);
            state.lastResults = results;
            
            // نمایش نتایج
            displayScrapingResults(results);
            
            // نمایش تب نتایج
            switchOutputTab('results');
            
        } catch (error) {
            showError(error.message);
        }
    };
    
    const updateScrapingRulesFromUI = () => {
        state.scrapingRules = [];
        
        document.querySelectorAll('.rule-item:not(.template)').forEach(ruleEl => {
            const ruleId = ruleEl.dataset.id;
            const type = ruleEl.querySelector('.rule-type').value;
            const selector = ruleEl.querySelector('.rule-selector').value;
            const name = ruleEl.querySelector('.rule-name').value;
            const attr = ruleEl.querySelector('.rule-attr').value;
            const customAttr = ruleEl.querySelector('.rule-custom-attr').value;
            
            state.scrapingRules.push({
                id: ruleId,
                type,
                selector,
                name,
                attr,
                customAttr
            });
        });
    };
    
    const simulateScraping = (html) => {
        // در حالت واقعی، اینجا HTML تجزیه و تحلیل می‌شود
        // این یک نمونه شبیه‌سازی شده است
        
        const results = [];
        
        state.scrapingRules.forEach(rule => {
            if (rule.type === 'css') {
                if (rule.selector === '.product h3') {
                    results.push({
                        name: rule.name || 'title',
                        value: ['محصول نمونه 1', 'محصول نمونه 2']
                    });
                } else if (rule.selector === '.product .price') {
                    results.push({
                        name: rule.name || 'price',
                        value: ['$19.99', '$29.99']
                    });
                } else if (rule.selector === '.product a' && rule.attr === 'href') {
                    results.push({
                        name: rule.name || 'link',
                        value: ['/product/1', '/product/2']
                    });
                } else {
                    results.push({
                        name: rule.name || 'unknown',
                        value: ['مقدار نمونه 1', 'مقدار نمونه 2']
                    });
                }
            } else if (rule.type === 'xpath') {
                results.push({
                    name: rule.name || 'xpath_data',
                    value: ['مقدار XPath 1', 'مقدار XPath 2']
                });
            } else if (rule.type === 'regex') {
                results.push({
                    name: rule.name || 'regex_data',
                    value: ['مقدار Regex 1', 'مقدار Regex 2']
                });
            }
        });
        
        // تبدیل به فرمت جدولی
        const tableData = [];
        const rowCount = Math.max(...results.map(r => r.value.length));
        
        for (let i = 0; i < rowCount; i++) {
            const row = {};
            results.forEach(r => {
                row[r.name] = r.value[i] || '';
            });
            tableData.push(row);
        }
        
        return {
            rules: state.scrapingRules,
            data: tableData
        };
    };
    
    const displayScrapingResults = (results) => {
        if (!results.data || results.data.length === 0) {
            elements.scrapingResults.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-database"></i>
                    <p>هیچ داده‌ای استخراج نشده است</p>
                </div>
            `;
            return;
        }
        
        const headers = Object.keys(results.data[0]);
        
        let html = `<table class="data-table">
            <thead>
                <tr>
                    ${headers.map(h => `<th>${h}</th>`).join('')}
                </tr>
            </thead>
            <tbody>
                ${results.data.map(row => `
                    <tr>
                        ${headers.map(h => `<td>${row[h]}</td>`).join('')}
                    </tr>
                `).join('')}
            </tbody>
        </table>`;
        
        elements.scrapingResults.innerHTML = html;
    };
    
    const displayResponseInfo = (response) => {
        let html = `
            <div class="info-item">
                <strong>URL:</strong> ${response.url}
            </div>
            <div class="info-item">
                <strong>Status Code:</strong> ${response.status}
            </div>
            <div class="info-item">
                <strong>Headers:</strong>
                <pre>${JSON.stringify(response.headers, null, 2)}</pre>
            </div>
        `;
        
        elements.responseInfo.innerHTML = html;
    };
    
    const clearAll = () => {
        if (confirm('آیا از پاک کردن همه داده‌ها اطمینان دارید؟')) {
            state.scrapingRules = [];
            state.lastResults = null;
            state.lastResponse = null;
            
            elements.rulesList.innerHTML = '';
            elements.scrapingResults.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-database"></i>
                    <p>هیچ داده‌ای استخراج نشده است</p>
                </div>
            `;
            elements.rawHtmlContent.textContent = 'محتوای HTML اینجا نمایش داده می‌شود...';
            elements.responseInfo.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-info-circle"></i>
                    <p>اطلاعات پاسخ اینجا نمایش داده می‌شود...</p>
                </div>
            `;
            
            if (state.currentTab === 'url-scraping') {
                elements.targetUrl.value = '';
                elements.requestHeaders.value = '';
                elements.requestBody.value = '';
            } else if (state.currentTab === 'html-scraping') {
                elements.htmlContent.value = '';
                elements.htmlFile.value = '';
            }
            
            elements.errorSection.classList.add('hidden');
            switchOutputTab('results');
        }
    };
    
    const saveProfile = () => {
        if (state.scrapingRules.length === 0) {
            showError('لطفاً حداقل یک قانون استخراج اضافه کنید');
            return;
        }
        
        updateScrapingRulesFromUI();
        
        const name = prompt('لطفاً یک نام برای پروفایل وارد کنید:', `پروفایل ${new Date().toLocaleString()}`);
        if (name) {
            const profile = {
                id: Date.now(),
                name,
                rules: state.scrapingRules,
                createdAt: new Date().toISOString()
            };
            
            // در حالت واقعی، اینجا درخواست به سرور ارسال می‌شود
            state.savedProfiles.push(profile);
            localStorage.setItem('saved_scraping_profiles', JSON.stringify(state.savedProfiles));
            loadSavedProfiles();
            
            alert('پروفایل با موفقیت ذخیره شد!');
        }
    };
    
    const loadSavedProfiles = () => {
        // در حالت واقعی، اینجا از API درخواست می‌دهیم
        const saved = localStorage.getItem('saved_scraping_profiles');
        if (saved) {
            state.savedProfiles = JSON.parse(saved);
            renderSavedProfiles();
        }
    };
    
    const renderSavedProfiles = () => {
        if (state.savedProfiles.length === 0) {
            elements.savedProfilesList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-bookmark"></i>
                    <p>هیچ پروفایل ذخیره شده‌ای ندارید</p>
                </div>
            `;
            return;
        }
        
        elements.savedProfilesList.innerHTML = `
            <ul class="saved-profiles">
                ${state.savedProfiles.map(profile => `
                    <li class="saved-profile-item">
                        <div class="profile-header">
                            <h4>${profile.name}</h4>
                            <div class="profile-actions">
                                <button class="btn btn-outline btn-sm load-profile" data-id="${profile.id}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline btn-sm delete-profile" data-id="${profile.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="profile-meta">
                            <small>${new Date(profile.createdAt).toLocaleString()}</small>
                            <small>${profile.rules.length} قانون</small>
                        </div>
                    </li>
                `).join('')}
            </ul>
        `;
        
        // اضافه کردن رویداد به دکمه‌ها
        document.querySelectorAll('.load-profile').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('.load-profile').dataset.id;
                loadProfile(id);
            });
        });
        
        document.querySelectorAll('.delete-profile').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('.delete-profile').dataset.id;
                deleteProfile(id);
            });
        });
    };
    
    const loadProfile = (id) => {
        const profile = state.savedProfiles.find(p => p.id == id);
        if (!profile) return;
        
        // پاک کردن قوانین موجود
        elements.rulesList.innerHTML = '';
        state.scrapingRules = [];
        
        // بارگذاری قوانین پروفایل
        profile.rules.forEach(rule => {
            const ruleId = Date.now();
            const ruleItem = document.querySelector('.rule-item.template').cloneNode(true);
            ruleItem.classList.remove('template');
            ruleItem.dataset.id = ruleId;
            
            // پر کردن فرم
            ruleItem.querySelector('.rule-type').value = rule.type;
            ruleItem.querySelector('.rule-selector').value = rule.selector;
            ruleItem.querySelector('.rule-name').value = rule.name;
            ruleItem.querySelector('.rule-attr').value = rule.attr;
            
            const customAttrInput = ruleItem.querySelector('.rule-custom-attr');
            if (rule.attr === 'custom') {
                customAttrInput.value = rule.customAttr;
                customAttrInput.classList.remove('hidden');
            }
            
            // رویداد تغییر نوع ویژگی
            const attrSelect = ruleItem.querySelector('.rule-attr');
            attrSelect.addEventListener('change', (e) => {
                customAttrInput.classList.toggle('hidden', e.target.value !== 'custom');
            });
            
            // رویداد حذف قانون
            ruleItem.querySelector('.remove-rule').addEventListener('click', () => {
                removeScrapingRule(ruleId);
            });
            
            elements.rulesList.appendChild(ruleItem);
            
            state.scrapingRules.push({
                id: ruleId,
                type: rule.type,
                selector: rule.selector,
                name: rule.name,
                attr: rule.attr,
                customAttr: rule.customAttr
            });
        });
        
        alert(`پروفایل "${profile.name}" بارگذاری شد`);
        switchTab('url-scraping');
    };
    
    const deleteProfile = (id) => {
        if (confirm('آیا از حذف این پروفایل اطمینان دارید؟')) {
            state.savedProfiles = state.savedProfiles.filter(p => p.id != id);
            localStorage.setItem('saved_scraping_profiles', JSON.stringify(state.savedProfiles));
            renderSavedProfiles();
        }
    };
    
    const exportToJson = () => {
        if (!state.lastResults) {
            showError('هیچ داده‌ای برای export وجود ندارد');
            return;
        }
        
        const data = JSON.stringify(state.lastResults.data, null, 2);
        downloadFile(data, 'scraped-data.json', 'application/json');
    };
    
    const exportToCsv = () => {
        if (!state.lastResults) {
            showError('هیچ داده‌ای برای export وجود ندارد');
            return;
        }
        
        const headers = Object.keys(state.lastResults.data[0]);
        let csv = headers.join(',') + '\n';
        
        state.lastResults.data.forEach(row => {
            csv += headers.map(h => `"${row[h]}"`).join(',') + '\n';
        });
        
        downloadFile(csv, 'scraped-data.csv', 'text/csv');
    };
    
    const copyResults = () => {
    if (!state.lastResults) {
        showError('هیچ داده‌ای برای کپی کردن وجود ندارد');
        return;
    }

    const dataToCopy = JSON.stringify(state.lastResults.data, null, 2);
    
    navigator.clipboard.writeText(dataToCopy)
        .then(() => {
            showTemporaryMessage('داده‌ها با موفقیت کپی شدند', 'success');
        })
        .catch(err => {
            showError('خطا در کپی کردن داده‌ها: ' + err.message);
        });
};

const downloadFile = (content, fileName, mimeType) => {
    const blob = new Blob([content], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = fileName;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
};

const showError = (message) => {
    elements.errorText.textContent = message;
    elements.errorSection.classList.remove('hidden');
    
    // پنهان کردن خودکار پیام خطا پس از 5 ثانیه
    setTimeout(() => {
        elements.errorSection.classList.add('hidden');
    }, 5000);
};

const showTemporaryMessage = (message, type = 'success') => {
    const messageElement = document.createElement('div');
    messageElement.className = `alert alert-${type}`;
    messageElement.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    // اضافه کردن پیام به صفحه
    const container = document.querySelector('.output-section');
    container.insertBefore(messageElement, container.firstChild);
    
    // پنهان کردن خودکار پیام پس از 3 ثانیه
    setTimeout(() => {
        messageElement.remove();
    }, 3000);
};

// شروع برنامه
init();
