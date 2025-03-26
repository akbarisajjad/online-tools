class ThemeSwitcher {
    constructor() {
        this.themeBtn = document.querySelector('.theme-switcher');
        this.userTheme = localStorage.getItem('theme');
        this.systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        this.init();
    }
    
    init() {
        this.checkTheme();
        this.themeBtn.addEventListener('click', () => this.toggleTheme());
    }
    
    checkTheme() {
        if (this.userTheme === 'dark' || (!this.userTheme && this.systemTheme)) {
            document.documentElement.classList.add('dark');
            this.themeBtn.innerHTML = '<i class="icon-sun"></i>';
            return;
        }
        this.themeBtn.innerHTML = '<i class="icon-moon"></i>';
    }
    
    toggleTheme() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
            this.themeBtn.innerHTML = '<i class="icon-moon"></i>';
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
            this.themeBtn.innerHTML = '<i class="icon-sun"></i>';
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ThemeSwitcher();
});
