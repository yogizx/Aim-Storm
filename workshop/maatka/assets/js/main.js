// ===================================
// MAATKA - LUXURY ANIMATIONS & INTERACTIONS
// ===================================

// Wait for DOM to load
document.addEventListener('DOMContentLoaded', () => {
    initLoader();
    initNavigation();
    initAnimations();
    initScrollReveal();
    initCounters();
    initFormValidation();
});

// ===================================
// LOADER
// ===================================
function initLoader() {
    const loader = document.getElementById('loader');
    
    setTimeout(() => {
        loader.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }, 2000);
}

// ===================================
// NAVIGATION
// ===================================
function initNavigation() {
    const nav = document.getElementById('nav');
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    // Scroll effect
    window.addEventListener('scroll', () => {
        if (window.scrollY > 100) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    });
    
    // Mobile menu toggle
    if (navToggle) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            
            // Animate hamburger
            const spans = navToggle.querySelectorAll('span');
            if (navMenu.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translateY(10px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translateY(-10px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });
    }
    
    // Close menu on link click
    const navLinks = navMenu.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            const spans = navToggle.querySelectorAll('span');
            spans[0].style.transform = 'none';
            spans[1].style.opacity = '1';
            spans[2].style.transform = 'none';
        });
    });
}

// ===================================
// GSAP ANIMATIONS
// ===================================
function initAnimations() {
    // Register ScrollTrigger
    gsap.registerPlugin(ScrollTrigger);
    
    // Hero animations
    if (document.querySelector('.hero-title')) {
        gsap.from('.hero-title', {
            duration: 1.2,
            y: 100,
            opacity: 0,
            ease: 'power4.out',
            delay: 2.2
        });
        
        gsap.from('.hero-subtitle', {
            duration: 1,
            y: 50,
            opacity: 0,
            ease: 'power3.out',
            delay: 2.5
        });
        
        gsap.from('.hero-description', {
            duration: 1,
            y: 30,
            opacity: 0,
            ease: 'power3.out',
            delay: 2.8
        });
        
        gsap.from('.hero-cta', {
            duration: 1,
            y: 30,
            opacity: 0,
            ease: 'power3.out',
            delay: 3.1
        });
        
        gsap.from('.hero-amount', {
            duration: 1,
            y: 30,
            opacity: 0,
            ease: 'power3.out',
            delay: 3.4
        });
        
        gsap.from('.scroll-indicator', {
            duration: 1,
            opacity: 0,
            ease: 'power3.out',
            delay: 3.7
        });
    }
    
    // Vision cards stagger
    if (document.querySelectorAll('.vision-card').length > 0) {
        gsap.from('.vision-card', {
            scrollTrigger: {
                trigger: '.vision-grid',
                start: 'top 80%'
            },
            duration: 0.8,
            y: 80,
            opacity: 0,
            stagger: 0.2,
            ease: 'power3.out'
        });
    }
    
    // Benefit cards stagger
    if (document.querySelectorAll('.benefit-card').length > 0) {
        gsap.from('.benefit-card', {
            scrollTrigger: {
                trigger: '.benefits-grid',
                start: 'top 80%'
            },
            duration: 0.8,
            y: 80,
            opacity: 0,
            stagger: 0.2,
            ease: 'power3.out'
        });
    }
    
    // Stats animation
    if (document.querySelectorAll('.stat-item').length > 0) {
        gsap.from('.stat-item', {
            scrollTrigger: {
                trigger: '.stats-grid',
                start: 'top 80%'
            },
            duration: 0.8,
            scale: 0.8,
            opacity: 0,
            stagger: 0.15,
            ease: 'back.out(1.2)'
        });
    }
    
    // CTA section
    if (document.querySelector('.cta-content')) {
        gsap.from('.cta-content h2', {
            scrollTrigger: {
                trigger: '.cta-section',
                start: 'top 80%'
            },
            duration: 1,
            y: 50,
            opacity: 0,
            ease: 'power3.out'
        });
        
        gsap.from('.cta-content p', {
            scrollTrigger: {
                trigger: '.cta-section',
                start: 'top 80%'
            },
            duration: 1,
            y: 30,
            opacity: 0,
            ease: 'power3.out',
            delay: 0.2
        });
        
        gsap.from('.cta-content .btn-primary', {
            scrollTrigger: {
                trigger: '.cta-section',
                start: 'top 80%'
            },
            duration: 1,
            y: 30,
            opacity: 0,
            ease: 'power3.out',
            delay: 0.4
        });
    }
    
    // Process steps animation
    if (document.querySelectorAll('.process-step').length > 0) {
        gsap.from('.process-step', {
            scrollTrigger: {
                trigger: '.process-steps',
                start: 'top 80%'
            },
            duration: 0.8,
            x: -80,
            opacity: 0,
            stagger: 0.2,
            ease: 'power3.out'
        });
    }
    
    // Trust features animation
    if (document.querySelectorAll('.trust-feature').length > 0) {
        gsap.from('.trust-feature', {
            scrollTrigger: {
                trigger: '.trust-features',
                start: 'top 80%'
            },
            duration: 0.8,
            y: 60,
            opacity: 0,
            stagger: 0.15,
            ease: 'power3.out'
        });
    }
}

// ===================================
// SCROLL REVEAL
// ===================================
function initScrollReveal() {
    const reveals = document.querySelectorAll('.section-reveal');
    
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
            }
        });
    }, {
        threshold: 0.15
    });
    
    reveals.forEach(reveal => {
        revealObserver.observe(reveal);
    });
}

// ===================================
// ANIMATED COUNTERS
// ===================================
function initCounters() {
    const counters = document.querySelectorAll('.stat-number[data-count]');
    
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-count'));
                animateCounter(counter, 0, target, 2000);
                counterObserver.unobserve(counter);
            }
        });
    }, {
        threshold: 0.5
    });
    
    counters.forEach(counter => {
        counterObserver.observe(counter);
    });
}

function animateCounter(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= end) {
            element.textContent = end.toLocaleString();
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current).toLocaleString();
        }
    }, 16);
}

// ===================================
// FORM VALIDATION
// ===================================
function initFormValidation() {
    const form = document.getElementById('joinForm');
    
    if (form) {
        const inputs = form.querySelectorAll('input, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                validateField(input);
            });
            
            input.addEventListener('input', () => {
                if (input.classList.contains('error')) {
                    validateField(input);
                }
            });
        });
        
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            let isValid = true;
            
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });
            
            if (isValid) {
                submitForm(form);
            }
        });
    }
}

function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const name = field.name;
    let isValid = true;
    let errorMessage = '';
    
    // Remove previous error
    field.classList.remove('error');
    const existingError = field.parentElement.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    // Required check
    if (field.hasAttribute('required') && value === '') {
        isValid = false;
        errorMessage = 'This field is required';
    }
    
    // Email validation
    if (type === 'email' && value !== '') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email';
        }
    }
    
    // Phone validation
    if (name === 'mobile' && value !== '') {
        const phoneRegex = /^[6-9]\d{9}$/;
        if (!phoneRegex.test(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid 10-digit mobile number';
        }
    }
    
    // Name validation
    if (name === 'name' && value !== '') {
        if (value.length < 2) {
            isValid = false;
            errorMessage = 'Name must be at least 2 characters';
        }
    }
    
    if (!isValid) {
        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = errorMessage;
        field.parentElement.appendChild(errorDiv);
    }
    
    return isValid;
}

function submitForm(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';
    submitBtn.style.opacity = '0.6';
    
    const formData = new FormData(form);
    
    fetch('api/payment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Initiate Razorpay payment
            initiateRazorpay(data);
        } else {
            showError(data.message || 'Something went wrong. Please try again.');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            submitBtn.style.opacity = '1';
        }
    })
    .catch(error => {
        showError('Network error. Please check your connection.');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        submitBtn.style.opacity = '1';
    });
}

function initiateRazorpay(paymentData) {
    const options = {
        key: paymentData.razorpay_key,
        amount: paymentData.amount,
        currency: 'INR',
        name: 'MAATKA',
        description: 'Digital Supporter Membership',
        order_id: paymentData.order_id,
        handler: function(response) {
            verifyPayment(response, paymentData.user_id);
        },
        prefill: {
            name: paymentData.user_name,
            email: paymentData.user_email,
            contact: paymentData.user_mobile
        },
        theme: {
            color: '#d4af37'
        },
        modal: {
            ondismiss: function() {
                const submitBtn = document.querySelector('button[type="submit"]');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Join MAATKA';
                submitBtn.style.opacity = '1';
            }
        }
    };
    
    const rzp = new Razorpay(options);
    rzp.open();
}

function verifyPayment(paymentResponse, userId) {
    fetch('api/verify.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            razorpay_payment_id: paymentResponse.razorpay_payment_id,
            razorpay_order_id: paymentResponse.razorpay_order_id,
            razorpay_signature: paymentResponse.razorpay_signature,
            user_id: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'success.html?id=' + data.legacy_id;
        } else {
            showError('Payment verification failed. Please contact support.');
        }
    })
    .catch(error => {
        showError('Verification error. Please contact support with your payment ID: ' + paymentResponse.razorpay_payment_id);
    });
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error-popup';
    errorDiv.textContent = message;
    errorDiv.style.cssText = `
        position: fixed;
        top: 100px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(255, 50, 50, 0.95);
        color: white;
        padding: 20px 40px;
        border-radius: 10px;
        z-index: 10000;
        font-size: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    `;
    
    document.body.appendChild(errorDiv);
    
    setTimeout(() => {
        errorDiv.style.opacity = '0';
        errorDiv.style.transition = 'opacity 0.5s';
        setTimeout(() => errorDiv.remove(), 500);
    }, 5000);
}

// ===================================
// SMOOTH SCROLL
// ===================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// ===================================
// COPY TO CLIPBOARD
// ===================================
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showSuccess('Copied to clipboard!');
    });
}

function showSuccess(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'success-popup';
    successDiv.textContent = message;
    successDiv.style.cssText = `
        position: fixed;
        top: 100px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(212, 175, 55, 0.95);
        color: #0a0a0a;
        padding: 20px 40px;
        border-radius: 10px;
        z-index: 10000;
        font-size: 16px;
        font-weight: 600;
        box-shadow: 0 10px 40px rgba(212, 175, 55, 0.3);
    `;
    
    document.body.appendChild(successDiv);
    
    setTimeout(() => {
        successDiv.style.opacity = '0';
        successDiv.style.transition = 'opacity 0.5s';
        setTimeout(() => successDiv.remove(), 500);
    }, 3000);
}