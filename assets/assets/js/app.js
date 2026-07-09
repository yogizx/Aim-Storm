// assets/js/app.js - Adapted for Red/Black Theme

(function() {
    'use strict';

    // Utility Functions
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

    const isReducedMotion = () => {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    };

    // Mobile Navigation
    const initMobileNav = () => {
        const toggle = document.querySelector('.header__toggle');
        const menu = document.querySelector('.header__menu');
        
        if (!toggle || !menu) return;
        
        toggle.addEventListener('click', () => {
            const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', !isExpanded);
            menu.classList.toggle('active');
            
            // Animate hamburger
            const spans = toggle.querySelectorAll('span');
            if (menu.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translateY(6px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translateY(-6px)';
            } else {
                spans[0].style.transform = '';
                spans[1].style.opacity = '';
                spans[2].style.transform = '';
            }
        });
        
        // Close menu on link click
        const links = menu.querySelectorAll('.header__link');
        links.forEach(link => {
            link.addEventListener('click', () => {
                menu.classList.remove('active');
                toggle.setAttribute('aria-expanded', 'false');
            });
        });
    };

    // Dropdown Menus
    const initDropdowns = () => {
        const dropdowns = document.querySelectorAll('.header__dropdown');
        
        dropdowns.forEach(dropdown => {
            const button = dropdown.querySelector('.header__link--dropdown');
            
            if (!button) return;
            
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const isExpanded = button.getAttribute('aria-expanded') === 'true';
                button.setAttribute('aria-expanded', !isExpanded);
            });
        });
    };

    // Smooth Scroll
    const initSmoothScroll = () => {
        if (isReducedMotion()) return;
        
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const target = document.querySelector(targetId);
                if (!target) return
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });
    };

    // Scroll Animations (IntersectionObserver)
    const initScrollAnimations = () => {
        if (isReducedMotion()) {
            document.querySelectorAll('.reveal').forEach(el => {
                el.classList.add('revealed');
            });
            return;
        }
        
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.reveal').forEach(el => {
            observer.observe(el);
        });
    };

    // Number Counter Animation
    const initCounters = () => {
        const counters = document.querySelectorAll('.counter');
        
        if (isReducedMotion()) {
            counters.forEach(counter => {
                const target = parseFloat(counter.dataset.target);
                const suffix = counter.dataset.suffix || '';
                const decimals = parseInt(counter.dataset.decimals) || 0;
                counter.textContent = target.toFixed(decimals) + suffix;
            });
            return;
        }
        
        const animateCounter = (counter) => {
            const target = parseFloat(counter.dataset.target);
            const suffix = counter.dataset.suffix || '';
            const decimals = parseInt(counter.dataset.decimals) || 0;
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;
            
            const updateCounter = () => {
                current += step;
                if (current < target) {
                    counter.textContent = current.toFixed(decimals) + suffix;
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target.toFixed(decimals) + suffix;
                }
            };
            
            updateCounter();
        };
        
        const observerOptions = {
            threshold: 0.5
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        counters.forEach(counter => {
            observer.observe(counter);
        });
    };

    // Testimonial Slider
    const initTestimonialSlider = () => {
        const slider = document.querySelector('.testimonials-slider');
        if (!slider) return;
        
        const testimonials = slider.querySelectorAll('.testimonial');
        const prevBtn = slider.querySelector('.slider-arrow--prev');
        const nextBtn = slider.querySelector('.slider-arrow--next');
        
        if (!testimonials.length) return;
        
        let currentIndex = 0;
        const totalSlides = testimonials.length;
        
        const showSlide = (index) => {
            testimonials.forEach((testimonial, i) => {
                testimonial.classList.toggle('testimonial--active', i === index);
            });
        };
        
        const nextSlide = () => {
            currentIndex = (currentIndex + 1) % totalSlides;
            showSlide(currentIndex);
        };
        
        const prevSlide = () => {
            currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
            showSlide(currentIndex);
        };
        
        if (nextBtn) nextBtn.addEventListener('click', nextSlide);
        if (prevBtn) prevBtn.addEventListener('click', prevSlide);
        
        // Auto-play
        if (!isReducedMotion() && totalSlides > 1) {
            setInterval(nextSlide, 5000);
        }
    };

    // Case Snippet Carousel
    const initCaseCarousel = () => {
        const carousel = document.querySelector('.case-carousel');
        if (!carousel) return;
        
        const snippets = carousel.querySelectorAll('.case-snippet');
        const dots = carousel.querySelectorAll('.dot');
        
        if (!snippets.length || !dots.length) return;
        
        let currentIndex = 0;
        
        const showSnippet = (index) => {
            snippets.forEach((snippet, i) => {
                snippet.classList.toggle('case-snippet--active', i === index);
            });
            dots.forEach((dot, i) => {
                dot.classList.toggle('dot--active', i === index);
            });
        };
        
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentIndex = index;
                showSnippet(currentIndex);
            });
        });
        
        // Auto-play
        if (!isReducedMotion() && snippets.length > 1) {
            setInterval(() => {
                currentIndex = (currentIndex + 1) % snippets.length;
                showSnippet(currentIndex);
            }, 4000);
        }
    };

    // Form Validation
    const initFormValidation = () => {
        const forms = document.querySelectorAll('form[data-form]');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                
                const inputs = form.querySelectorAll('[required]');
                let isValid = true;
                
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('error');
                        input.style.borderColor = 'var(--color-primary)';
                    } else {
                        input.classList.remove('error');
                        input.style.borderColor = '';
                    }
                });
                
                if (isValid) {
                    const button = form.querySelector('button[type="submit"]');
                    const originalText = button.textContent;
                    button.textContent = 'Thank you! We\'ll be in touch.';
                    button.disabled = true;
                    button.style.background = 'var(--color-success)';
                    
                    setTimeout(() => {
                        button.textContent = originalText;
                        button.disabled = false;
                        button.style.background = '';
                        form.reset();
                    }, 3000);
                }
            });
            
            form.querySelectorAll('input, select, textarea').forEach(input => {
                input.addEventListener('input', () => {
                    input.classList.remove('error');
                    input.style.borderColor = '';
                });
            });
        });
    };

    // Particles Animation
    const initParticles = () => {
        if (isReducedMotion()) return;
        
        const particlesContainer = document.querySelector('.hero__particles');
        if (!particlesContainer) return;
        
        const particleCount = 20;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.cssText = `
                position: absolute;
                width: ${Math.random() * 4 + 1}px;
                height: ${Math.random() * 4 + 1}px;
                background: rgba(212, 20, 20, ${Math.random() * 0.5 + 0.2});
                border-radius: 50%;
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
                animation: particleFloat ${Math.random() * 20 + 10}s infinite linear;
                animation-delay: ${Math.random() * 5}s;
            `;
            particlesContainer.appendChild(particle);
        }
        
        if (!document.querySelector('#particle-animation')) {
            const style = document.createElement('style');
            style.id = 'particle-animation';
            style.textContent = `
                @keyframes particleFloat {
                    0% {
                        transform: translate(0, 0);
                        opacity: 0;
                    }
                    10% {
                        opacity: 1;
                    }
                    90% {
                        opacity: 1;
                    }
                    100% {
                        transform: translate(100px, -100vh);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    };

    // Sticky Header
    const initStickyHeader = () => {
        const header = document.querySelector('.header');
        if (!header) return;
        
        let lastScroll = 0;
        
        const handleScroll = debounce(() => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                header.classList.add('header--scrolled');
                header.style.background = 'rgba(0, 0, 0, 0.95)';
                
                if (currentScroll > lastScroll) {
                    header.style.transform = 'translateY(-100%)';
                } else {
                    header.style.transform = 'translateY(0)';
                }
            } else {
                header.classList.remove('header--scrolled');
                header.style.background = '';
                header.style.transform = '';
            }
            
            lastScroll = currentScroll;
        }, 10);
        
        window.addEventListener('scroll', handleScroll);
    };

    // Initialize Everything
    const init = () => {
        initMobileNav();
        initDropdowns();
        initSmoothScroll();
        initScrollAnimations();
        initCounters();
        initTestimonialSlider();
        initCaseCarousel();
        initFormValidation();
        initParticles();
        initStickyHeader();
    };

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
