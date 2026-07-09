// Reveal on Scroll
document.addEventListener('DOMContentLoaded', () => {
    // Theme Toggle Logic
    const themeToggleBtns = document.querySelectorAll('.theme-toggle-btn, .mobile-theme-btn');
    const body = document.body;

    // Check for saved theme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        body.setAttribute('data-theme', savedTheme);
        updateThemeIcons(savedTheme);
    }

    themeToggleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcons(newTheme);
        });
    });

    function updateThemeIcons(theme) {
        themeToggleBtns.forEach(btn => {
            const icon = btn.querySelector('i');
            if (icon) {
                if (theme === 'dark') {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                } else {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
            }
        });
    }
    const revealElements = document.querySelectorAll('[data-reveal]');

    const revealOnScroll = () => {
        revealElements.forEach(el => {
            const elementTop = el.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            if (elementTop < windowHeight - 50) {
                el.classList.add('active');
            }
        });
    };

    window.addEventListener('scroll', revealOnScroll);
    revealOnScroll(); // Initial check

    // Smooth Scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Counter Animation
    const stats = document.querySelectorAll('.stat-num');
    const animateCounter = (el) => {
        const target = parseInt(el.textContent);
        const suffix = el.textContent.replace(/[0-9]/g, '');
        let count = 0;
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60fps

        const updateCount = () => {
            count += increment;
            if (count < target) {
                el.textContent = Math.floor(count) + suffix;
                requestAnimationFrame(updateCount);
            } else {
                el.textContent = target + suffix;
            }
        };
        updateCount();
    };

    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                statsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    stats.forEach(stat => statsObserver.observe(stat));

    // Mobile Menu Toggle
    const mobileMenu = document.getElementById('mobile-menu');
    const nav = document.querySelector('nav');

    if (mobileMenu) {
        mobileMenu.addEventListener('click', () => {
            nav.classList.toggle('active');
            const icon = mobileMenu.querySelector('i');
            if (nav.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }

    // Close menu when a link is clicked
    document.querySelectorAll('nav a').forEach(link => {
        link.addEventListener('click', () => {
            nav.classList.remove('active');
            const icon = mobileMenu.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    });
});


// career page js code

document.addEventListener('DOMContentLoaded', () => {
    // Mobile Menu Toggle
    const menuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');

    if (menuBtn) {
        menuBtn.addEventListener('click', () => {
            // Simple toggle for demonstration purposes
            // In a real framework, we might toggle a class like 'open'
            if (navLinks.style.display === 'flex') {
                navLinks.style.display = 'none';
                navLinks.style.flexDirection = 'row';
                navLinks.style.position = 'static';
                navLinks.style.backgroundColor = 'transparent';
            } else {
                navLinks.style.display = 'flex';
                navLinks.style.flexDirection = 'column';
                navLinks.style.position = 'absolute';
                navLinks.style.top = '70px';
                navLinks.style.left = '0';
                navLinks.style.width = '100%';
                navLinks.style.backgroundColor = 'white';
                navLinks.style.padding = '20px';
                navLinks.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
                navLinks.style.zIndex = '1000';
            }
        });
    }

    // Apply Button Interaction
    const applyButtons = document.querySelectorAll('.btn-primary');
    applyButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            // Placeholder interaction
            alert('Application form would open here.');
        });
    });
});