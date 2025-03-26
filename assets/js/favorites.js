document.addEventListener('DOMContentLoaded', function() {
    const favoriteBtns = document.querySelectorAll('.favorite-btn');
    
    favoriteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const toolPath = this.dataset.tool;
            const isFavorite = this.classList.contains('active');
            
            fetch('/api/toggle-favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    tool_path: toolPath,
                    action: isFavorite ? 'remove' : 'add'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.classList.toggle('active');
                    const icon = this.querySelector('i');
                    if (isFavorite) {
                        icon.classList.replace('fas', 'far');
                    } else {
                        icon.classList.replace('far', 'fas');
                    }
                } else {
                    alert(data.message || 'خطا در ذخیره علاقه‌مندی');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('خطا در ارتباط با سرور');
            });
        });
        
        // بررسی وضعیت علاقه‌مندی‌ها در ابتدا
        if (btn.dataset.tool) {
            fetch('/api/check-favorite.php?tool=' + encodeURIComponent(btn.dataset.tool))
            .then(response => response.json())
            .then(data => {
                if (data.is_favorite) {
                    btn.classList.add('active');
                    const icon = btn.querySelector('i');
                    icon.classList.replace('far', 'fas');
                }
            });
        }
    });
});
