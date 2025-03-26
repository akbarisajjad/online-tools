document.addEventListener('DOMContentLoaded', function() {
    // تغییر آواتار
    const changeAvatarBtn = document.getElementById('change-avatar-btn');
    if (changeAvatarBtn) {
        changeAvatarBtn.addEventListener('click', function() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            
            input.onchange = function(e) {
                const file = e.target.files[0];
                if (!file) return;
                
                if (file.size > 2 * 1024 * 1024) {
                    alert('حجم فایل باید کمتر از ۲ مگابایت باشد');
                    return;
                }
                
                const formData = new FormData();
                formData.append('avatar', file);
                
                fetch('/api/upload-avatar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('.profile-avatar img').src = data.avatar_url + '?t=' + Date.now();
                    } else {
                        alert(data.message || 'خطا در آپلود تصویر');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('خطا در ارتباط با سرور');
                });
            };
            
            input.click();
        });
    }
    
    // حذف از مورد علاقه‌ها
    const removeFavoriteBtns = document.querySelectorAll('.remove-favorite');
    removeFavoriteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const toolPath = this.getAttribute('data-path');
            
            fetch('/api/remove-favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ tool_path: toolPath })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.closest('li').remove();
                    
                    // اگر لیست خالی شد، پیام نمایش بده
                    if (document.querySelectorAll('.favorites-list li').length === 0) {
                        document.querySelector('.favorites-list').innerHTML = `
                            <p class="text-muted">هنوز ابزاری به مورد علاقه‌ها اضافه نکرده‌اید.</p>
                        `;
                    }
                } else {
                    alert(data.message || 'خطا در حذف از مورد علاقه‌ها');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('خطا در ارتباط با سرور');
            });
        });
    });
});
