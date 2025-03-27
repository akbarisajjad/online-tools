document.addEventListener('DOMContentLoaded', function() {
    // متغیرهای global
    const state = {
        selectedTables: [],
        selectedFields: [],
        conditions: [],
        sortFields: [],
        savedQueries: []
    };
    
    // عناصر DOM
    const elements = {
        queryType: document.getElementById('queryType'),
        tablesList: document.getElementById('tablesList'),
        selectedTables: document.getElementById('selectedTables'),
        selectedFields: document.getElementById('selectedFields'),
        conditionsContainer: document.getElementById('conditionsContainer'),
        sortContainer: document.getElementById('sortContainer'),
        sqlOutput: document.getElementById('sql-output'),
        generateBtn: document.getElementById('generate-btn'),
        clearBtn: document.getElementById('clear-btn'),
        copyBtn: document.getElementById('copy-btn'),
        saveBtn: document.getElementById('save-btn'),
        executeBtn: document.getElementById('execute-btn'),
        queryResults: document.getElementById('query-results'),
        resultsSection: document.getElementById('results-section'),
        errorSection: document.getElementById('error-section'),
        errorText: document.getElementById('error-text'),
        rawSqlInput: document.getElementById('raw-sql-input'),
        savedQueriesList: document.getElementById('saved-queries-list')
    };
    
    // داده‌های نمونه
    const sampleData = {
        tables: [
            {name: 'users', fields: ['id', 'username', 'email', 'created_at', 'status']},
            {name: 'products', fields: ['id', 'name', 'price', 'category_id', 'stock']},
            {name: 'orders', fields: ['id', 'user_id', 'product_id', 'quantity', 'order_date']},
            {name: 'categories', fields: ['id', 'name', 'description']}
        ]
    };
    
    // توابع اصلی
    const init = () => {
        setupEventListeners();
        loadTables();
        loadSavedQueries();
        setupTabs();
    };
    
    const setupEventListeners = () => {
        // رویدادهای نوع کوئری
        elements.queryType.addEventListener('change', toggleQuerySections);
        
        // رویدادهای دکمه‌ها
        elements.generateBtn.addEventListener('click', generateQuery);
        elements.clearBtn.addEventListener('click', clearAll);
        elements.copyBtn.addEventListener('click', copyQuery);
        if (elements.saveBtn) {
            elements.saveBtn.addEventListener('click', saveQuery);
        }
        elements.executeBtn.addEventListener('click', executeQuery);
        
        // رویدادهای اضافه کردن شرایط و مرتب‌سازی
        document.getElementById('addCondition').addEventListener('click', addCondition);
        document.getElementById('addSort').addEventListener('click', addSortField);
    };
    
    const setupTabs = () => {
        const tabs = document.querySelectorAll('.tab-btn');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // غیرفعال کردن همه تب‌ها
                document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // فعال کردن تب انتخاب شده
                tab.classList.add('active');
                const tabId = tab.getAttribute('data-tab');
                document.getElementById(`${tabId}-tab`).classList.add('active');
            });
        });
    };
    
    const loadTables = () => {
        elements.tablesList.innerHTML = '';
        sampleData.tables.forEach(table => {
            const li = document.createElement('li');
            li.textContent = table.name;
            li.dataset.tableName = table.name;
            li.addEventListener('click', () => selectTable(table));
            elements.tablesList.appendChild(li);
        });
    };
    
    const loadSavedQueries = () => {
        // در حالت واقعی، اینجا از API درخواست می‌دهیم
        const saved = localStorage.getItem('saved_queries');
        if (saved) {
            state.savedQueries = JSON.parse(saved);
            renderSavedQueries();
        }
    };
    
    const renderSavedQueries = () => {
        if (state.savedQueries.length === 0) {
            elements.savedQueriesList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-bookmark"></i>
                    <p>هیچ کوئری ذخیره شده‌ای ندارید</p>
                </div>
            `;
            return;
        }
        
        elements.savedQueriesList.innerHTML = `
            <ul class="saved-queries">
                ${state.savedQueries.map((query, index) => `
                    <li class="saved-query-item">
                        <div class="query-header">
                            <h4>${query.name || 'کوئری بدون نام'}</h4>
                            <div class="query-actions">
                                <button class="btn btn-outline btn-sm load-query" data-index="${index}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline btn-sm delete-query" data-index="${index}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <pre class="query-preview">${query.sql.substring(0, 100)}...</pre>
                    </li>
                `).join('')}
            </ul>
        `;
        
        // اضافه کردن رویداد به دکمه‌ها
        document.querySelectorAll('.load-query').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = e.target.closest('.load-query').dataset.index;
                loadQuery(index);
            });
        });
        
        document.querySelectorAll('.delete-query').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = e.target.closest('.delete-query').dataset.index;
                deleteQuery(index);
            });
        });
    };
    
    const selectTable = (table) => {
        if (!state.selectedTables.includes(table.name)) {
            state.selectedTables.push(table.name);
            
            // اضافه کردن به لیست جداول انتخاب شده
            const li = document.createElement('li');
            li.textContent = table.name;
            li.dataset.tableName = table.name;
            li.classList.add('selected');
            li.addEventListener('click', () => deselectTable(table.name));
            elements.selectedTables.appendChild(li);
            
            // اضافه کردن فیلدهای جدول
            table.fields.forEach(field => {
                addField(table.name, field);
            });
        }
        
        generateQuery();
    };
    
    const deselectTable = (tableName) => {
        state.selectedTables = state.selectedTables.filter(t => t !== tableName);
        
        // حذف از لیست نمایش
        const items = elements.selectedTables.querySelectorAll('li');
        items.forEach(item => {
            if (item.dataset.tableName === tableName) {
                item.remove();
            }
        });
        
        // حذف فیلدهای مربوطه
        state.selectedFields = state.selectedFields.filter(f => f.table !== tableName);
        renderSelectedFields();
        
        generateQuery();
    };
    
    const addField = (tableName, fieldName) => {
        if (!state.selectedFields.some(f => f.table === tableName && f.field === fieldName)) {
            state.selectedFields.push({table: tableName, field: fieldName});
            renderSelectedFields();
        }
    };
    
    const removeField = (tableName, fieldName) => {
        state.selectedFields = state.selectedFields.filter(f => !(f.table === tableName && f.field === fieldName));
        renderSelectedFields();
        generateQuery();
    };
    
    const renderSelectedFields = () => {
        elements.selectedFields.innerHTML = '';
        
        state.selectedFields.forEach(f => {
            const fieldItem = document.createElement('div');
            fieldItem.className = 'field-item';
            fieldItem.innerHTML = `
                <span>${f.table}.${f.field}</span>
                <i class="fas fa-times remove-field" data-table="${f.table}" data-field="${f.field}"></i>
            `;
            fieldItem.querySelector('.remove-field').addEventListener('click', (e) => {
                e.stopPropagation();
                removeField(f.table, f.field);
            });
            elements.selectedFields.appendChild(fieldItem);
        });
    };
    
    const toggleQuerySections = () => {
        const queryType = elements.queryType.value;
        
        // نمایش/مخفی کردن بخش‌های مختلف بر اساس نوع کوئری
        document.getElementById('fields-section').style.display = queryType === 'SELECT' ? 'block' : 'none';
        document.getElementById('conditions-section').style.display = 
            (queryType === 'SELECT' || queryType === 'UPDATE' || queryType === 'DELETE') ? 'block' : 'none';
        document.getElementById('sort-section').style.display = queryType === 'SELECT' ? 'block' : 'none';
    };
    
const addCondition = () => {
    const conditionId = Date.now();
    
    const conditionItem = document.createElement('div');
    conditionItem.className = 'condition-item';
    conditionItem.dataset.id = conditionId;
    conditionItem.innerHTML = `
        <div class="condition-row">
            <select class="condition-field form-control">
                <option value="">-- انتخاب فیلد --</option>
                ${state.selectedFields.map(f => 
                    `<option value="${f.table}.${f.field}">${f.table}.${f.field}</option>`
                ).join('')}
            </select>
            <select class="condition-operator form-control">
                <option value="=">=</option>
                <option value="!=">!=</option>
                <option value=">">></option>
                <option value="<"><</option>
                <option value=">=">>=</option>
                <option value="<="><=</option>
                <option value="LIKE">LIKE</option>
                <option value="NOT LIKE">NOT LIKE</option>
                <option value="IN">IN</option>
                <option value="NOT IN">NOT IN</option>
                <option value="IS NULL">IS NULL</option>
                <option value="IS NOT NULL">IS NOT NULL</option>
            </select>
            <input type="text" class="condition-value form-control" placeholder="مقدار">
        </div>
        <div class="condition-actions">
            <button class="btn btn-outline btn-sm remove-condition">
                <i class="fas fa-trash"></i> حذف
            </button>
        </div>
    `;
    
    conditionItem.querySelector('.remove-condition').addEventListener('click', () => {
        removeCondition(conditionId);
    });
    
    elements.conditionsContainer.appendChild(conditionItem);
    state.conditions.push({
        id: conditionId,
        field: '',
        operator: '=',
        value: ''
    });
};
  const executeQuery = () => {
    let query;
    const activeTab = document.querySelector('.tab-content.active').id;
    
    if (activeTab === 'raw-sql-tab') {
        query = elements.rawSqlInput.value.trim();
    } else {
        query = elements.sqlOutput.textContent.trim();
    }
    
    if (!query || query.startsWith('--')) {
        showError('لطفاً ابتدا یک کوئری معتبر تولید کنید');
        return;
    }
    
    // در حالت واقعی، اینجا درخواست AJAX به سرور ارسال می‌شود
    // این یک نمونه شبیه‌سازی شده است
    const startTime = performance.now();
    
    // شبیه‌سازی اجرای کوئری
    setTimeout(() => {
        const endTime = performance.now();
        const executionTime = Math.round(endTime - startTime);
        
        // نتایج نمونه
        let sampleResults = [];
        const queryType = elements.queryType.value;
        
        if (queryType === 'SELECT') {
            sampleResults = [
                {id: 1, username: 'user1', email: 'user1@example.com', status: 'active'},
                {id: 2, username: 'user2', email: 'user2@example.com', status: 'inactive'},
                {id: 3, username: 'user3', email: 'user3@example.com', status: 'active'}
            ];
        } else if (queryType === 'INSERT') {
            sampleResults = {affected_rows: 1, message: 'Record inserted successfully'};
        } else if (queryType === 'UPDATE') {
            sampleResults = {affected_rows: 1, message: 'Record updated successfully'};
        } else if (queryType === 'DELETE') {
            sampleResults = {affected_rows: 1, message: 'Record deleted successfully'};
        }
        
        // نمایش نتایج
        displayResults(sampleResults, executionTime);
    }, 500);
};

const displayResults = (results, executionTime) => {
    elements.errorSection.classList.add('hidden');
    elements.resultsSection.classList.remove('hidden');
    
    if (Array.isArray(results)) {
        // نتایج SELECT
        let html = '<table><thead><tr>';
        
        // ایجاد هدرهای جدول
        Object.keys(results[0]).forEach(key => {
            html += `<th>${key}</th>`;
        });
        html += '</tr></thead><tbody>';
        
        // ایجاد ردیف‌های جدول
        results.forEach(row => {
            html += '<tr>';
            Object.values(row).forEach(value => {
                html += `<td>${value}</td>`;
            });
            html += '</tr>';
        });
        
        html += '</tbody></table>';
        elements.queryResults.innerHTML = html;
        
        // آمار اجرا
        document.getElementById('rows-count').textContent = results.length;
        document.getElementById('execution-time').textContent = `${executionTime} ms`;
    } else {
        // نتایج INSERT/UPDATE/DELETE
        elements.queryResults.innerHTML = `
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                ${results.message || 'Operation completed successfully'}
            </div>
            <div class="stat-item">
                <span class="stat-label">تعداد رکوردهای تأثیرگرفته:</span>
                <span class="stat-value">${results.affected_rows || 0}</span>
            </div>
        `;
        document.getElementById('execution-time').textContent = `${executionTime} ms`;
    }
};

const showError = (message) => {
    elements.errorText.textContent = message;
    elements.errorSection.classList.remove('hidden');
    elements.resultsSection.classList.add('hidden');
};
