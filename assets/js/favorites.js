document.addEventListener('DOMContentLoaded', function() {
    const favoriteBtns = document.querySelectorAll('.favorite-btn');
    
    favoriteBtns.forEach(btn => {
        // بررسی وضعیت اولیه
        checkFavoriteStatus(btn);
        
        // افزودن رویداد کلیک
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const toolPath = this.dataset.tool;
            const isFavorite = this.classList.contains('active');
            
            toggleFavorite(toolPath, isFavorite, this);
        });
    });
    
    function toggleFavorite(toolPath, isFavorite, btn) {
        const url = isFavorite ? '/api/remove-favorite.php' : '/api/toggle-favorite.php';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                tool_path: toolPath
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.classList.toggle('active');
                const icon = btn.querySelector('i');
                
                if (isFavorite) {
                    icon.classList.replace('fas', 'far');
                } else {
                    icon.classList.replace('far', 'fas');
                }
                
                showToast(isFavorite ? 'Removed from favorites' : 'Added to favorites');
            } else {
                showToast(data.message || 'Operation failed', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }
    
    function checkFavoriteStatus(btn) {
        const toolPath = btn.dataset.tool;
        
        fetch('/api/check-favorite.php?tool=' + encodeURIComponent(toolPath))
        .then(response => response.json())
        .then(data => {
            if (data.is_favorite) {
                btn.classList.add('active');
                const icon = btn.querySelector('i');
                icon.classList.replace('far', 'fas');
            }
        });
    }
    
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
});
