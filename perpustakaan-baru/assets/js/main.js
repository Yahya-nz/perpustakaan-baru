// Main JavaScript - Perpustakaan Digital

document.addEventListener('DOMContentLoaded', function() {
    initNavbar();
    initSearch();
    initModals();
    initAnimations();
    initForms();
});

// Navbar scroll effect
function initNavbar() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
}

// Search functionality
function initSearch() {
    const searchInput = document.getElementById('searchInput');
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function(e) {
            const searchTerm = e.target.value.toLowerCase();
            filterBooks(searchTerm);
        }, 300));
    }
    
    if (filterButtons.length > 0) {
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                filterButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const category = this.dataset.category;
                filterByCategory(category);
            });
        });
    }
}

// Filter books by search term
function filterBooks(searchTerm) {
    const bookCards = document.querySelectorAll('.book-card');
    
    bookCards.forEach(card => {
        const title = card.querySelector('.book-title').textContent.toLowerCase();
        const author = card.querySelector('.book-author').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || author.includes(searchTerm)) {
            card.style.display = 'block';
            card.style.animation = 'fadeInUp 0.5s ease';
        } else {
            card.style.display = 'none';
        }
    });
}

// Filter books by category
function filterByCategory(category) {
    const bookCards = document.querySelectorAll('.book-card');
    
    bookCards.forEach(card => {
        const bookCategory = card.dataset.category;
        
        if (category === 'all' || bookCategory === category) {
            card.style.display = 'block';
            card.style.animation = 'fadeInUp 0.5s ease';
        } else {
            card.style.display = 'none';
        }
    });
}

// Modal functionality
function initModals() {
    const modalTriggers = document.querySelectorAll('[data-modal]');
    const modalCloses = document.querySelectorAll('.modal-close, [data-dismiss="modal"]');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.dataset.modal;
            openModal(modalId);
        });
    });
    
    modalCloses.forEach(close => {
        close.addEventListener('click', function() {
            closeModal(this.closest('.modal'));
        });
    });
    
    // Close modal on outside click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this);
            }
        });
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modal) {
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Scroll animations
function initAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.book-card, .stat-card').forEach(el => {
        observer.observe(el);
    });
}

// Form validation and handling
function initForms() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading"></span> Memproses...';
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            showError(input, 'Field ini wajib diisi');
        } else {
            clearError(input);
        }
        
        // Email validation
        if (input.type === 'email' && input.value) {
            if (!isValidEmail(input.value)) {
                isValid = false;
                showError(input, 'Format email tidak valid');
            }
        }
        
        // Password confirmation
        if (input.name === 'confirm_password') {
            const password = form.querySelector('[name="password"]');
            if (input.value !== password.value) {
                isValid = false;
                showError(input, 'Password tidak cocok');
            }
        }
    });
    
    return isValid;
}

function showError(input, message) {
    clearError(input);
    
    input.style.borderColor = '#f44336';
    const error = document.createElement('div');
    error.className = 'form-error';
    error.style.color = '#f44336';
    error.style.fontSize = '0.85rem';
    error.style.marginTop = '0.25rem';
    error.textContent = message;
    
    input.parentNode.appendChild(error);
}

function clearError(input) {
    input.style.borderColor = '';
    const error = input.parentNode.querySelector('.form-error');
    if (error) {
        error.remove();
    }
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Debounce utility
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Confirm action
function confirmAction(message) {
    return confirm(message);
}

// Show notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.style.position = 'fixed';
    notification.style.top = '100px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.animation = 'fadeInUp 0.3s ease';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// AJAX helper
function ajax(url, method, data, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            callback(JSON.parse(xhr.responseText));
        }
    };
    
    if (method === 'POST' && data) {
        xhr.send(new URLSearchParams(data).toString());
    } else {
        xhr.send();
    }
}

// Format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}
