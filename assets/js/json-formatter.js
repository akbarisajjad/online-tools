class JSONFormatter {
    constructor() {
        this.jsonInput = document.getElementById('json-input');
        this.jsonOutput = document.getElementById('json-output');
        this.formatBtn = document.getElementById('format-btn');
        this.minifyBtn = document.getElementById('minify-btn');
        this.clearBtn = document.getElementById('clear-btn');
        this.copyBtn = document.getElementById('copy-btn');
        this.downloadBtn = document.getElementById('download-btn');
        
        this.init();
    }
    
    init() {
        this.formatBtn.addEventListener('click', () => this.formatJSON());
        this.minifyBtn.addEventListener('click', () => this.minifyJSON());
        this.clearBtn.addEventListener('click', () => this.clearAll());
        this.copyBtn.addEventListener('click', () => this.copyToClipboard());
        this.downloadBtn.addEventListener('click', () => this.downloadJSON());
        
        // Load last used JSON from localStorage
        const lastJSON = localStorage.getItem('lastJSON');
        if (lastJSON) {
            this.jsonInput.value = lastJSON;
            this.formatJSON();
        }
    }
    
    formatJSON() {
        try {
            const jsonObj = JSON.parse(this.jsonInput.value);
            const formattedJSON = JSON.stringify(jsonObj, null, 4);
            this.jsonOutput.innerHTML = this.syntaxHighlight(formattedJSON);
            localStorage.setItem('lastJSON', this.jsonInput.value);
        } catch (error) {
            this.jsonOutput.innerHTML = `<span class="error">خطا: ${error.message}</span>`;
        }
    }
    
    minifyJSON() {
        try {
            const jsonObj = JSON.parse(this.jsonInput.value);
            const minifiedJSON = JSON.stringify(jsonObj);
            this.jsonOutput.innerHTML = `<code>${minifiedJSON}</code>`;
            localStorage.setItem('lastJSON', minifiedJSON);
        } catch (error) {
            this.jsonOutput.innerHTML = `<span class="error">خطا: ${error.message}</span>`;
        }
    }
    
    syntaxHighlight(json) {
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
    
    clearAll() {
        this.jsonInput.value = '';
        this.jsonOutput.innerHTML = '<code>نتیجه اینجا نمایش داده می‌شود...</code>';
        localStorage.removeItem('lastJSON');
    }
    
    copyToClipboard() {
        const outputText = this.jsonOutput.textContent;
        navigator.clipboard.writeText(outputText)
            .then(() => {
                this.copyBtn.textContent = 'کپی شد!';
                setTimeout(() => {
                    this.copyBtn.textContent = 'کپی';
                }, 2000);
            })
            .catch(err => {
                console.error('خطا در کپی کردن: ', err);
            });
    }
    
    downloadJSON() {
        const blob = new Blob([this.jsonOutput.textContent], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'formatted.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new JSONFormatter();
});
