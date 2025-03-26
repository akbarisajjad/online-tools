document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Reset errors
            document.querySelectorAll('.form-group').forEach(group => {
                group.classList.remove('has-error');
            });
            
            // Validate name
            const nameInput = document.getElementById('name');
            if (!nameInput.value.trim()) {
                showError(nameInput, 'لطفا نام خود را وارد کنید');
                isValid = false;
            }
            
            // Validate email
            const emailInput = document.getElementById('email');
            if (!emailInput.value.trim()) {
                showError(emailInput, 'لطفا ایمیل خود را وارد کنید');
                isValid = false;
            } else if (!isValidEmail(emailInput.value)) {
                showError(emailInput, 'ایمیل وارد شده معتبر نیست');
                isValid = false;
            }
            
            // Validate subject
            const subjectInput = document.getElementById('subject');
            if (!subjectInput.value) {
                showError(subjectInput, 'لطفا موضوع پیام را انتخاب کنید');
                isValid = false;
            }
            
            // Validate message
            const messageInput = document.getElementById('message');
            if (!messageInput.value.trim()) {
                showError(messageInput, 'لطفا متن پیام را وارد کنید');
                isValid = false;
            } else if (messageInput.value.trim().length < 20) {
                showError(messageInput, 'پیام شما باید حداقل ۲۰ کاراکتر داشته باشد');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                
                // Scroll to first error
                const firstError = document.querySelector('.has-error');
                if (firstError) {
                    firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }
        });
    }
    
    function showError(input, message) {
        const formGroup = input.closest('.form-group');
        formGroup.classList.add('has-error');
        
        let errorElement = formGroup.querySelector('.error-message');
        if (!errorElement) {
            errorElement = document.createElement('span');
            errorElement.className = 'error-message';
            formGroup.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
    }
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
});
