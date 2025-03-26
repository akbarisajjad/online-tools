document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const categorySections = document.querySelectorAll('.category-section');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // حذف کلاس active از همه دکمه‌ها
            filterBtns.forEach(b => b.classList.remove('active'));
            // اضافه کردن کلاس active به دکمه فعلی
            this.classList.add('active');
            
            const category = this.dataset.category;
            
            if (category === 'all') {
                // نمایش همه دسته‌بندی‌ها
                categorySections.forEach(section => {
                    section.style.display = 'block';
                });
            } else {
                // نمایش فقط دسته‌بندی انتخاب شده
                categorySections.forEach(section => {
                    if (section.dataset.category === category) {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
            }
        });
    });
});
