document.addEventListener('DOMContentLoaded', function() {
    // منوی موبایل
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            mobileMenu.classList.toggle('active');
            document.body.classList.toggle('no-scroll');
        });
    }
    
    // منوی آبشاری موبایل
    const mobileDropdownBtns = document.querySelectorAll('.mobile-dropdown-btn');
    
    mobileDropdownBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.toggle('active');
            const dropdown = this.nextElementSibling;
            dropdown.classList.toggle('active');
        });
    });
    
    // بستن منو هنگام کلیک خارج از آن
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.mobile-menu') && !e.target.closest('.mobile-menu-btn')) {
            if (mobileMenu.classList.contains('active')) {
                mobileMenu.classList.remove('active');
                mobileMenuBtn.classList.remove('active');
                document.body.classList.remove('no-scroll');
            }
        }
    });
    
    // تغییر آیکون منوی همبرگر هنگام باز/بسته شدن
    mobileMenuBtn.addEventListener('click', function() {
        const lines = this.querySelectorAll('.menu-line');
        lines[0].style.transform = this.classList.contains('active') ? 'rotate(45deg) translate(5px, 5px)' : '';
        lines[1].style.opacity = this.classList.contains('active') ? '0' : '1';
        lines[2].style.transform = this.classList.contains('active') ? 'rotate(-45deg) translate(5px, -5px)' : '';
    });
});
