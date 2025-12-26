/**
 * Основний JavaScript CMS
 */

document.addEventListener('DOMContentLoaded', () => {
    // Пошук
    const searchToggle = document.querySelector('.search-toggle');
    const searchWrapper = document.querySelector('.search-form-wrapper');
    
    if (searchToggle && searchWrapper) {
        searchToggle.addEventListener('click', () => {
            searchWrapper.classList.toggle('active');
            if (searchWrapper.classList.contains('active')) {
                searchWrapper.querySelector('input').focus();
            }
        });
    }
    
    // Мобільне меню
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (mobileToggle && mainNav) {
        mobileToggle.addEventListener('click', () => {
            mainNav.classList.toggle('active');
        });
    }
    
    // Форма коментарів - honeypot timing
    const commentForm = document.querySelector('.comment-form');
    if (commentForm) {
        const startTime = Date.now();
        const formStartInput = document.getElementById('formStartTime');
        
        if (formStartInput) {
            formStartInput.value = Math.floor(startTime / 1000);
        }
        
        commentForm.addEventListener('submit', (e) => {
            const elapsed = (Date.now() - startTime) / 1000;
            
            // Перевірка мінімального часу (3 секунди)
            if (elapsed < 3) {
                e.preventDefault();
                alert('Будь ласка, заповніть форму уважно.');
                return false;
            }
            
            // Перевірка honeypot
            const honeypot = commentForm.querySelector('input[name="website"]');
            if (honeypot && honeypot.value !== '') {
                e.preventDefault();
                return false;
            }
        });
    }
    
    // Smooth scroll для якорів
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.length > 1) {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Lazy loading зображень
    if ('loading' in HTMLImageElement.prototype) {
        const images = document.querySelectorAll('img[loading="lazy"]');
        images.forEach(img => {
            img.src = img.dataset.src || img.src;
        });
    }
    
    // Копіювання коду
    document.querySelectorAll('pre code').forEach(block => {
        const button = document.createElement('button');
        button.className = 'copy-code-btn';
        button.innerHTML = '<i class="fas fa-copy"></i>';
        button.title = 'Копіювати код';
        
        const pre = block.parentElement;
        pre.style.position = 'relative';
        pre.appendChild(button);
        
        button.addEventListener('click', () => {
            const text = block.textContent;
            navigator.clipboard.writeText(text).then(() => {
                button.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => {
                    button.innerHTML = '<i class="fas fa-copy"></i>';
                }, 2000);
            });
        });
    });
});