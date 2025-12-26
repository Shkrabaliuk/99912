/**
 * JavaScript для адмін-панелі
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // Автозбереження чернеток постів
    const contentField = document.querySelector('textarea[name="content"]');
    const titleField = document.querySelector('input[name="title"]');
    
    if (contentField && titleField) {
        let autoSaveTimer;
        
        const autoSave = () => {
            const draft = {
                title: titleField.value,
                content: contentField.value,
                excerpt: document.querySelector('textarea[name="excerpt"]')?.value || '',
                tags: document.querySelector('input[name="tags"]')?.value || '',
                timestamp: Date.now()
            };
            
            localStorage.setItem('draft_autosave', JSON.stringify(draft));
            
            // Показуємо індикатор збереження
            showAutoSaveIndicator();
        };
        
        // Автозбереження кожні 30 секунд
        const startAutoSave = () => {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(autoSave, 30000);
        };
        
        contentField.addEventListener('input', startAutoSave);
        titleField.addEventListener('input', startAutoSave);
        
        // Відновлення чернетки
        const restoreDraft = () => {
            const saved = localStorage.getItem('draft_autosave');
            if (saved) {
                const draft = JSON.parse(saved);
                const age = Date.now() - draft.timestamp;
                
                // Якщо чернетка свіжа (менше 24 годин)
                if (age < 86400000 && !titleField.value && !contentField.value) {
                    if (confirm('Знайдено збережену чернетку. Відновити?')) {
                        titleField.value = draft.title;
                        contentField.value = draft.content;
                        
                        const excerptField = document.querySelector('textarea[name="excerpt"]');
                        if (excerptField) excerptField.value = draft.excerpt;
                        
                        const tagsField = document.querySelector('input[name="tags"]');
                        if (tagsField) tagsField.value = draft.tags;
                    }
                }
            }
        };
        
        restoreDraft();
    }
    
    // Індикатор автозбереження
    function showAutoSaveIndicator() {
        let indicator = document.getElementById('autosave-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'autosave-indicator';
            indicator.className = 'autosave-indicator';
            indicator.innerHTML = '<i class="fas fa-check"></i> Чернетку збережено';
            document.body.appendChild(indicator);
        }
        
        indicator.classList.add('show');
        setTimeout(() => {
            indicator.classList.remove('show');
        }, 2000);
    }
    
    // Попередження про незбережені зміни
    let formChanged = false;
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                formChanged = true;
            });
            
            input.addEventListener('input', () => {
                formChanged = true;
            });
        });
        
        form.addEventListener('submit', () => {
            formChanged = false;
        });
    });
    
    window.addEventListener('beforeunload', (e) => {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = 'У вас є незбережені зміни. Ви впевнені, що хочете вийти?';
            return e.returnValue;
        }
    });
    
    // Швидкі клавіші
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + S для збереження
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const form = document.querySelector('form');
            if (form) {
                form.submit();
            }
        }
        
        // Ctrl/Cmd + B для жирного (в textarea)
        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
            const textarea = document.activeElement;
            if (textarea.tagName === 'TEXTAREA') {
                e.preventDefault();
                wrapSelection(textarea, '**', '**');
            }
        }
        
        // Ctrl/Cmd + I для курсиву
        if ((e.ctrlKey || e.metaKey) && e.key === 'i') {
            const textarea = document.activeElement;
            if (textarea.tagName === 'TEXTAREA') {
                e.preventDefault();
                wrapSelection(textarea, '*', '*');
            }
        }
    });
    
    // Обгортання виділеного тексту
    function wrapSelection(textarea, before, after) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        const selection = text.substring(start, end);
        
        const newText = text.substring(0, start) + before + selection + after + text.substring(end);
        textarea.value = newText;
        
        textarea.focus();
        textarea.setSelectionRange(start + before.length, end + before.length);
    }
    
    // Підрахунок символів
    const excerptField = document.querySelector('textarea[name="excerpt"]');
    if (excerptField) {
        const counter = document.createElement('small');
        counter.className = 'char-counter';
        excerptField.parentNode.appendChild(counter);
        
        const updateCounter = () => {
            const length = excerptField.value.length;
            counter.textContent = `${length} символів`;
            
            if (length > 200) {
                counter.style.color = '#e74c3c';
            } else if (length > 150) {
                counter.style.color = '#f39c12';
            } else {
                counter.style.color = '#7f8c8d';
            }
        };
        
        excerptField.addEventListener('input', updateCounter);
        updateCounter();
    }
    
    // Підрахунок слів у контенті
    if (contentField) {
        const wordCounter = document.createElement('div');
        wordCounter.className = 'word-counter';
        contentField.parentNode.appendChild(wordCounter);
        
        const updateWordCounter = () => {
            const text = contentField.value.trim();
            const words = text ? text.split(/\s+/).length : 0;
            const chars = text.length;
            const readingTime = Math.ceil(words / 200); // 200 words per minute
            
            wordCounter.innerHTML = `
                <span><i class="fas fa-pen"></i> ${words} слів</span>
                <span><i class="fas fa-keyboard"></i> ${chars} символів</span>
                <span><i class="fas fa-clock"></i> ~${readingTime} хв читання</span>
            `;
        };
        
        contentField.addEventListener('input', updateWordCounter);
        updateWordCounter();
    }
    
    // Вставка зображень через drag & drop
    if (contentField) {
        contentField.addEventListener('drop', (e) => {
            e.preventDefault();
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                
                if (file.type.startsWith('image/')) {
                    // Тут можна додати завантаження на сервер
                    // Поки що просто вставляємо Markdown синтаксис
                    const text = `![${file.name}](url-to-image)`;
                    insertAtCursor(contentField, text);
                }
            }
        });
        
        contentField.addEventListener('dragover', (e) => {
            e.preventDefault();
        });
    }
    
    // Вставка тексту в позицію курсора
    function insertAtCursor(textarea, text) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const value = textarea.value;
        
        textarea.value = value.substring(0, start) + text + value.substring(end);
        textarea.focus();
        textarea.setSelectionRange(start + text.length, start + text.length);
    }
    
    // Підказки для тегів
    const tagsInput = document.querySelector('input[name="tags"]');
    if (tagsInput) {
        tagsInput.addEventListener('input', () => {
            const value = tagsInput.value;
            const lastChar = value[value.length - 1];
            
            // Додаємо пробіл після коми
            if (lastChar === ',') {
                tagsInput.value = value + ' ';
            }
        });
    }
    
    // Підтвердження видалення
    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', (e) => {
            const message = element.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Анімація для статистики
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const target = parseInt(stat.textContent);
        let current = 0;
        const increment = target / 20;
        
        const updateNumber = () => {
            current += increment;
            if (current < target) {
                stat.textContent = Math.floor(current);
                requestAnimationFrame(updateNumber);
            } else {
                stat.textContent = target;
            }
        };
        
        // Запускаємо анімацію при завантаженні
        setTimeout(updateNumber, 100);
    });
    
    // Пошук у таблицях
    const searchInput = document.querySelector('.table-search');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.admin-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
    
    // Масові дії (якщо потрібно в майбутньому)
    const selectAll = document.querySelector('#select-all');
    if (selectAll) {
        selectAll.addEventListener('change', (e) => {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = e.target.checked;
            });
        });
    }
    
});

// Очищення кешу
function clearCache() {
    if (!confirm('Очистити весь кеш? Це може тимчасово уповільнити сайт.')) {
        return;
    }
    
    fetch(window.CMS_CONFIG.siteUrl + '/admin/clear-cache.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Кеш успішно очищено!');
            location.reload();
        } else {
            alert('Помилка: ' + (data.message || 'Не вдалося очистити кеш'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при очищенні кешу');
    });
}