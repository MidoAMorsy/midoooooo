/**
 * Ahmed Ashraf Portfolio - Main JavaScript
 */

(function() {
    'use strict';

    // =====================================================
    // Utility Functions
    // =====================================================
    
    const $ = (selector) => document.querySelector(selector);
    const $$ = (selector) => document.querySelectorAll(selector);
    
    const debounce = (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };
    
    const throttle = (func, limit) => {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    };

    // =====================================================
    // Navigation
    // =====================================================
    
    const initNavigation = () => {
        const navbar = $('.navbar');
        const navbarToggle = $('.navbar-toggle');
        const navbarMenu = $('.navbar-menu');
        const navLinks = $$('.nav-link');
        
        // Scroll effect
        const handleScroll = () => {
            if (window.scrollY > 100) {
                navbar?.classList.add('scrolled');
            } else {
                navbar?.classList.remove('scrolled');
            }
        };
        
        window.addEventListener('scroll', throttle(handleScroll, 100));
        handleScroll(); // Initial check
        
        // Mobile menu toggle
        navbarToggle?.addEventListener('click', () => {
            navbarMenu?.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });
        
        // Close menu on link click
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navbarMenu?.classList.remove('active');
                document.body.classList.remove('menu-open');
            });
        });
        
        // Close menu on outside click
        document.addEventListener('click', (e) => {
            if (!navbarMenu?.contains(e.target) && !navbarToggle?.contains(e.target)) {
                navbarMenu?.classList.remove('active');
                document.body.classList.remove('menu-open');
            }
        });
        
        // Active link on scroll
        const sections = $$('section[id]');
        const setActiveLink = () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                if (window.scrollY >= sectionTop - 200) {
                    current = section.getAttribute('id');
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href')?.includes(current)) {
                    link.classList.add('active');
                }
            });
        };
        
        window.addEventListener('scroll', throttle(setActiveLink, 100));
    };

    // =====================================================
    // Smooth Scroll
    // =====================================================
    
    const initSmoothScroll = () => {
        $$('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const target = $(targetId);
                if (target) {
                    e.preventDefault();
                    const offsetTop = target.offsetTop - 80;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });
    };

    // =====================================================
    // Back to Top Button
    // =====================================================
    
    const initBackToTop = () => {
        const btn = $('.back-to-top');
        if (!btn) return;
        
        const toggleVisibility = () => {
            if (window.scrollY > 500) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        };
        
        window.addEventListener('scroll', throttle(toggleVisibility, 100));
        
        btn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    };

    // =====================================================
    // Animated Counters
    // =====================================================
    
    const initCounters = () => {
        const counters = $$('[data-count]');
        if (!counters.length) return;
        
        const animateCounter = (counter) => {
            const target = parseInt(counter.dataset.count);
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;
            
            const update = () => {
                current += step;
                if (current < target) {
                    counter.textContent = Math.floor(current);
                    requestAnimationFrame(update);
                } else {
                    counter.textContent = target;
                }
            };
            
            update();
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        counters.forEach(counter => observer.observe(counter));
    };

    // =====================================================
    // Scroll Animations
    // =====================================================
    
    const initScrollAnimations = () => {
        const elements = $$('[data-aos]');
        if (!elements.length) return;
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('aos-animate');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        elements.forEach(el => observer.observe(el));
    };

    // =====================================================
    // Form Validation
    // =====================================================
    
    const initFormValidation = () => {
        const forms = $$('form[data-validate]');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Clear previous errors
                this.querySelectorAll('.form-error').forEach(el => el.remove());
                this.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));
                
                // Validate required fields
                this.querySelectorAll('[required]').forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        showFieldError(field, field.dataset.errorRequired || 'This field is required');
                    }
                });
                
                // Validate email fields
                this.querySelectorAll('[type="email"]').forEach(field => {
                    if (field.value && !isValidEmail(field.value)) {
                        isValid = false;
                        showFieldError(field, field.dataset.errorEmail || 'Please enter a valid email');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    };
    
    const showFieldError = (field, message) => {
        field.classList.add('error');
        const error = document.createElement('div');
        error.className = 'form-error';
        error.textContent = message;
        field.parentNode.appendChild(error);
    };
    
    const isValidEmail = (email) => {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    };

    // =====================================================
    // Contact Form AJAX
    // =====================================================
    
    const initContactForm = () => {
        const form = $('#contact-form');
        if (!form) return;
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('[type="submit"]');
            const originalText = submitBtn.textContent;
            
            // Disable button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Sending...';
            
            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message || 'Message sent successfully!', 'success');
                    this.reset();
                } else {
                    showToast(result.error || 'Failed to send message', 'error');
                }
            } catch (error) {
                showToast('An error occurred. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    };

    // =====================================================
    // Toast Notifications
    // =====================================================
    
    const showToast = (message, type = 'info', duration = 5000) => {
        let container = $('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.style.cssText = `
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            margin-bottom: 10px;
            animation: slideIn 0.3s ease;
            max-width: 400px;
        `;
        
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        
        const colors = {
            success: '#22c55e',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        
        toast.innerHTML = `
            <span style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;
                         background:${colors[type]};color:#fff;border-radius:50%;font-size:14px;">
                ${icons[type]}
            </span>
            <span style="flex:1;color:#333;">${message}</span>
            <button onclick="this.parentElement.remove()" style="background:none;border:none;
                    color:#999;cursor:pointer;font-size:18px;">&times;</button>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };
    
    // Add toast animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    
    // Make showToast globally available
    window.showToast = showToast;

    // =====================================================
    // Lightbox
    // =====================================================
    
    const initLightbox = () => {
        const images = $$('[data-lightbox]');
        if (!images.length) return;
        
        images.forEach(img => {
            img.style.cursor = 'pointer';
            img.addEventListener('click', function() {
                openLightbox(this.src, this.alt);
            });
        });
    };
    
    const openLightbox = (src, alt) => {
        const overlay = document.createElement('div');
        overlay.className = 'lightbox-overlay';
        overlay.style.cssText = `
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        `;
        
        overlay.innerHTML = `
            <button class="lightbox-close" style="position:absolute;top:20px;right:20px;
                    background:none;border:none;color:#fff;font-size:40px;cursor:pointer;">&times;</button>
            <img src="${src}" alt="${alt}" style="max-width:100%;max-height:90vh;border-radius:8px;">
        `;
        
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay || e.target.classList.contains('lightbox-close')) {
                overlay.remove();
            }
        });
        
        document.addEventListener('keydown', function closeOnEsc(e) {
            if (e.key === 'Escape') {
                overlay.remove();
                document.removeEventListener('keydown', closeOnEsc);
            }
        });
        
        document.body.appendChild(overlay);
    };

    // =====================================================
    // Lazy Loading Images
    // =====================================================
    
    const initLazyLoad = () => {
        const images = $$('img[data-src]');
        if (!images.length) return;
        
        const loadImage = (img) => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
            img.classList.add('loaded');
        };
        
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        loadImage(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '50px' });
            
            images.forEach(img => observer.observe(img));
        } else {
            images.forEach(loadImage);
        }
    };

    // =====================================================
    // Typewriter Effect
    // =====================================================
    
    const initTypewriter = () => {
        const elements = $$('[data-typewriter]');
        
        elements.forEach(el => {
            const texts = el.dataset.typewriter.split('|');
            let textIndex = 0;
            let charIndex = 0;
            let isDeleting = false;
            let typeSpeed = 100;
            
            const type = () => {
                const currentText = texts[textIndex];
                
                if (isDeleting) {
                    el.textContent = currentText.substring(0, charIndex - 1);
                    charIndex--;
                    typeSpeed = 50;
                } else {
                    el.textContent = currentText.substring(0, charIndex + 1);
                    charIndex++;
                    typeSpeed = 100;
                }
                
                if (!isDeleting && charIndex === currentText.length) {
                    typeSpeed = 2000;
                    isDeleting = true;
                } else if (isDeleting && charIndex === 0) {
                    isDeleting = false;
                    textIndex = (textIndex + 1) % texts.length;
                    typeSpeed = 500;
                }
                
                setTimeout(type, typeSpeed);
            };
            
            type();
        });
    };

    // =====================================================
    // Tab Component
    // =====================================================
    
    const initTabs = () => {
        const tabGroups = $$('[data-tabs]');
        
        tabGroups.forEach(group => {
            const tabs = group.querySelectorAll('[data-tab]');
            const panels = group.querySelectorAll('[data-tab-panel]');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const targetId = tab.dataset.tab;
                    
                    // Update tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    
                    // Update panels
                    panels.forEach(panel => {
                        panel.classList.remove('active');
                        if (panel.dataset.tabPanel === targetId) {
                            panel.classList.add('active');
                        }
                    });
                });
            });
        });
    };

    // =====================================================
    // Modal Component
    // =====================================================
    
    const initModals = () => {
        // Open modal
        $$('[data-modal-open]').forEach(trigger => {
            trigger.addEventListener('click', () => {
                const modalId = trigger.dataset.modalOpen;
                const modal = $(`#${modalId}`);
                if (modal) {
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            });
        });
        
        // Close modal
        $$('[data-modal-close]').forEach(trigger => {
            trigger.addEventListener('click', () => {
                const modal = trigger.closest('.modal');
                if (modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
        
        // Close on backdrop click
        $$('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
        
        // Close on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                $$('.modal.active').forEach(modal => {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }
        });
    };

    // =====================================================
    // Language Switcher
    // =====================================================
    
    const initLanguageSwitcher = () => {
        $$('[data-lang]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const lang = this.dataset.lang;
                
                // Set cookie
                document.cookie = `language=${lang};path=/;max-age=${365 * 24 * 60 * 60}`;
                
                // Reload page with language parameter
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            });
        });
    };

    // =====================================================
    // Dark Mode Toggle
    // =====================================================
    
    const initDarkMode = () => {
        const toggle = $('.dark-mode-toggle');
        if (!toggle) return;
        
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const savedTheme = localStorage.getItem('theme');
        
        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
        
        toggle.addEventListener('click', () => {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            const newTheme = isDark ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    };

    // =====================================================
    // Parallax Effect
    // =====================================================
    
    const initParallax = () => {
        const elements = $$('[data-parallax]');
        if (!elements.length) return;
        
        const handleParallax = () => {
            elements.forEach(el => {
                const speed = parseFloat(el.dataset.parallax) || 0.5;
                const rect = el.getBoundingClientRect();
                const scrolled = window.pageYOffset;
                const yPos = -(scrolled * speed);
                
                el.style.transform = `translateY(${yPos}px)`;
            });
        };
        
        window.addEventListener('scroll', throttle(handleParallax, 10));
    };

    // =====================================================
    // Initialize Everything
    // =====================================================
    
    const init = () => {
        initNavigation();
        initSmoothScroll();
        initBackToTop();
        initCounters();
        initScrollAnimations();
        initFormValidation();
        initContactForm();
        initLightbox();
        initLazyLoad();
        initTypewriter();
        initTabs();
        initModals();
        initLanguageSwitcher();
        initDarkMode();
        initParallax();
    };
    
    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
