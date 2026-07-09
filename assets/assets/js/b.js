 document.addEventListener('DOMContentLoaded', function() {
    // Mobile Nav Logic
    const toggle = document.querySelector('.header__toggle');
    const menu = document.querySelector('.header__menu');
    if(toggle && menu) {
        toggle.addEventListener('click', () => {
            menu.classList.toggle('active');
        });
    }

    // Enhanced Scroll Animation Observer with FORCE reveal
    const observerOptions = { 
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                // Force visible state
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe ALL reveal elements
    document.querySelectorAll('.reveal').forEach(el => {
        observer.observe(el);
        // Force visible if already in viewport
        if (el.getBoundingClientRect().top < window.innerHeight * 0.9) {
            el.classList.add('revealed');
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }
    });
    
    // Force reveal staggered elements
    document.querySelectorAll('.reveal--stagger').forEach(el => {
        observer.observe(el);
        if (el.getBoundingClientRect().top < window.innerHeight * 0.9) {
            el.classList.add('revealed');
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
            // Reveal child elements
            el.querySelectorAll('*').forEach(child => {
                child.style.opacity = '1';
                child.style.transform = 'translateY(0)';
            });
        }
    });

    // Force reveal all sections immediately
    setTimeout(() => {
        document.querySelectorAll('section:not(.hero)').forEach(section => {
            section.style.opacity = '1';
            section.style.visibility = 'visible';
            section.style.display = 'block';
        });
    }, 100);

    // Counters
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const suffix = counter.getAttribute('data-suffix') || '';
        let count = 0;
        const updateCount = () => {
            const inc = target / 200;
            if(count < target) {
                count += inc;
                counter.innerText = Math.ceil(count) + suffix;
                setTimeout(updateCount, 10);
            } else {
                counter.innerText = target + suffix;
            }
        };
        updateCount();
    });
    
    // Initialize particles
    initFastParticles();
});

// The rest of your existing JavaScript functions remain the same
// Keep all your existing particle animation code

            // Scroll Animation Observer
            const observerOptions = { threshold: 0.1 };
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
            
            // Stagger animations
            document.querySelectorAll('.reveal--stagger').forEach(el => observer.observe(el));

            
        // Technology Stack Interactivity
document.addEventListener('DOMContentLoaded', function() {
    // Initialize reveal animations
    const reveals = document.querySelectorAll('.reveal');
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
            }
        });
    }, { threshold: 0.1 });
    
    reveals.forEach(reveal => revealObserver.observe(reveal));
    
    // Category card interactions
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            const icon = this.querySelector('.category-icon i');
            if (icon) {
                icon.style.transform = 'rotate(360deg)';
                icon.style.transition = 'transform 0.6s ease';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            const icon = this.querySelector('.category-icon i');
            if (icon) {
                icon.style.transform = 'rotate(0deg)';
            }
        });
    });
    
    // Tech tag interactions
    const techTags = document.querySelectorAll('.tech-tag');
    techTags.forEach(tag => {
        tag.addEventListener('click', function(e) {
            e.preventDefault();
            const tech = this.textContent.trim();
            
            // Add visual feedback
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 200);
            
            // You could filter content by selected technology
            console.log('Selected technology:', tech);
            
            // Show notification
            showTechNotification(tech);
        });
    });
    
    // Proficiency meter animation on scroll
    const proficiencyMeter = document.querySelector('.proficiency-meter');
    if (proficiencyMeter) {
        const meterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const meterFills = entry.target.querySelectorAll('.meter-fill');
                    meterFills.forEach((fill, index) => {
                        const width = fill.style.width;
                        fill.style.width = '0%';
                        setTimeout(() => {
                            fill.style.width = width;
                        }, index * 200);
                    });
                }
            });
        }, { threshold: 0.5 });
        
        meterObserver.observe(proficiencyMeter);
    }
    
    // Quick stat animations
    const quickStats = document.querySelectorAll('.quick-stat');
    quickStats.forEach(stat => {
        stat.addEventListener('mouseenter', function() {
            const icon = this.querySelector('.stat-icon');
            const value = this.querySelector('.stat-value');
            
            if (icon) {
                icon.style.transform = 'scale(1.1) rotate(5deg)';
            }
            
            if (value) {
                const originalValue = value.textContent;
                if (originalValue.includes('%')) {
                    value.style.transform = 'scale(1.1)';
                }
            }
        });
        
        stat.addEventListener('mouseleave', function() {
            const icon = this.querySelector('.stat-icon');
            const value = this.querySelector('.stat-value');
            
            if (icon) {
                icon.style.transform = '';
            }
            
            if (value) {
                value.style.transform = '';
            }
        });
    });
    
    // Architecture layer interactions
    const archLayers = document.querySelectorAll('.arch-layer');
    archLayers.forEach(layer => {
        layer.addEventListener('mouseenter', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.transform = 'scale(1.2)';
            }
        });
        
        layer.addEventListener('mouseleave', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.transform = 'scale(1)';
            }
        });
    });
    
    // Button hover effects
    const buttons = document.querySelectorAll('.btn-primary, .btn-secondary, .btn-outline');
    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            const icon = this.querySelector('i:last-child');
            if (icon && this.classList.contains('btn-primary')) {
                icon.style.transform = 'translateX(5px)';
            }
        });
        
        btn.addEventListener('mouseleave', function() {
            const icon = this.querySelector('i:last-child');
            if (icon && this.classList.contains('btn-primary')) {
                icon.style.transform = 'translateX(0)';
            }
        });
    });
    
    // Benefit item animations
    const benefitItems = document.querySelectorAll('.benefit-item');
    benefitItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.transform = 'scale(1.2)';
                icon.style.color = '#ffd600';
            }
        });
        
        item.addEventListener('mouseleave', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.transform = 'scale(1)';
                icon.style.color = '#f01717';
            }
        });
    });
    
    // Consultation card animation
    const consultCard = document.querySelector('.consultation-card');
    if (consultCard) {
        consultCard.addEventListener('mouseenter', function() {
            const icon = this.querySelector('.consult-icon');
            if (icon) {
                icon.style.transform = 'scale(1.1) rotate(10deg)';
            }
        });
        
        consultCard.addEventListener('mouseleave', function() {
            const icon = this.querySelector('.consult-icon');
            if (icon) {
                icon.style.transform = 'scale(1) rotate(0deg)';
            }
        });
    }
    
    // Helper function to show tech notification
    function showTechNotification(tech) {
        // Create notification element
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(240, 23, 23, 0.9);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            z-index: 1000;
            font-weight: 600;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        `;
        notification.innerHTML = `
            <i class="fas fa-code" style="margin-right: 8px;"></i>
            Selected: ${tech}
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
        }, 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            notification.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
});

// hero section animation
// Fast Particle Animation - Particles Come Earlier
function initFastParticles() {
    const particlesContainer = document.querySelector('.particles-container');
    if (!particlesContainer) return;
    
    // Clear existing particles
    particlesContainer.innerHTML = '';
    
    // Create 300 particles (more density)
    const particleCount = 300;
    
    // Color palette
    const colors = [
        'rgba(212, 20, 20, 0.8)',     // Bright Red
        'rgba(255, 214, 0, 0.7)',      // Bright Yellow
        'rgba(255, 107, 107, 0.6)',    // Bright Pink
        'rgba(255, 255, 255, 0.5)',    // White
        'rgba(97, 218, 251, 0.6)',     // Cyan
        'rgba(76, 175, 80, 0.5)',      // Green
        'rgba(147, 51, 234, 0.6)',     // Purple
        'rgba(251, 191, 36, 0.7)'      // Amber
    ];
    
    // Animation types - ALL FAST
    const animations = [
        {name: 'particleFast', min: 3, max: 6},    // 3-6 seconds (FASTEST)
        {name: 'particleMedium', min: 4, max: 7},  // 4-7 seconds
        {name: 'particleSlow', min: 5, max: 8},    // 5-8 seconds
        {name: 'particleDiagonal', min: 4, max: 6} // 4-6 seconds
    ];
    
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        
        // Random properties for variety
        const size = Math.random() * 4 + 1; // 1-5px
        
        // Start position - particles start lower (100-120vh) so they appear earlier
        const startX = Math.random() * 100;
        const startY = Math.random() * 20 + 100; // Start at 100-120vh (just below viewport)
        
        // Random animation
        const animIndex = Math.floor(Math.random() * animations.length);
        const anim = animations[animIndex];
        const duration = Math.random() * (anim.max - anim.min) + anim.min;
        
        // VERY SHORT delay - particles start almost immediately
        const delay = Math.random() * 1.5; // 0-1.5 second delay
        
        // Random color
        const color = colors[Math.floor(Math.random() * colors.length)];
        
        // Random shape (some squares, most circles)
        const isSquare = Math.random() > 0.85;
        const borderRadius = isSquare ? '2px' : '50%';
        
        // Apply styles
        particle.style.cssText = `
            width: ${size}px;
            height: ${size}px;
            left: ${startX}%;
            top: ${startY}%;
            background: ${color};
            box-shadow: 0 0 ${size * 3}px ${color};
            border-radius: ${borderRadius};
            animation: ${anim.name} ${duration}s infinite linear;
            animation-delay: ${delay}s;
            opacity: ${Math.random() * 0.7 + 0.3}; // Start more visible
        `;
        
        // Add glow effect to some particles
        if (Math.random() > 0.5) {
            particle.style.animation += `, particleGlow ${Math.random() * 2 + 1}s infinite alternate`;
        }
        
        // Add trail effect to some particles
        if (Math.random() > 0.7) {
            const trail = document.createElement('div');
            trail.className = 'particle-trail';
            trail.style.cssText = `
                position: absolute;
                width: ${size * 2}px;
                height: ${size * 2}px;
                background: ${color};
                border-radius: 50%;
                filter: blur(2px);
                opacity: 0;
                animation: particleTrail ${duration * 0.8}s infinite linear;
                animation-delay: ${delay}s;
            `;
            particle.appendChild(trail);
        }
        
        // Add rotation to some particles
        if (Math.random() > 0.6) {
            particle.style.transform += ` rotate(${Math.random() * 360}deg)`;
        }
        
        particlesContainer.appendChild(particle);
    }
    
    // Add interactive mouse effect
    const heroSection = document.querySelector('.hero');
    if (heroSection) {
        heroSection.addEventListener('mousemove', (e) => {
            if (Math.random() > 0.8) { // Less frequent for performance
                const mouseParticle = document.createElement('div');
                mouseParticle.className = 'particle';
                
                const color = colors[Math.floor(Math.random() * colors.length)];
                const size = Math.random() * 3 + 1;
                
                mouseParticle.style.cssText = `
                    position: absolute;
                    left: ${e.clientX}px;
                    top: ${e.clientY}px;
                    width: ${size}px;
                    height: ${size}px;
                    background: ${color};
                    border-radius: 50%;
                    pointer-events: none;
                    z-index: 2;
                    box-shadow: 0 0 ${size * 3}px ${color};
                    animation: mouseParticle 1.5s ease-out forwards;
                `;
                
                particlesContainer.appendChild(mouseParticle);
                
                // Remove after animation
                setTimeout(() => {
                    if (mouseParticle.parentNode) {
                        mouseParticle.parentNode.removeChild(mouseParticle);
                    }
                }, 1500);
            }
        });
        
        // Add mouse particle animation
        if (!document.querySelector('#mouse-particle-animation')) {
            const style = document.createElement('style');
            style.id = 'mouse-particle-animation';
            style.textContent = `
                @keyframes mouseParticle {
                    0% {
                        transform: translate(0, 0) scale(1);
                        opacity: 1;
                    }
                    100% {
                        transform: translate(
                            ${Math.random() * 60 - 30}px, 
                            ${Math.random() * 60 - 30}px
                        ) scale(0);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
}

// Initialize immediately - no delay
document.addEventListener('DOMContentLoaded', function() {
    // Initialize particles ASAP
    initFastParticles();
    
    // Add a slight refresh every 10 seconds to keep particles dense
    setInterval(() => {
        // Only refresh a few particles at a time for performance
        const particles = document.querySelectorAll('.particle');
        if (particles.length < 200) { // If we lost particles, refresh some
            for (let i = 0; i < 50; i++) {
                if (particles[i]) {
                    particles[i].style.animationDuration = `${Math.random() * 4 + 3}s`;
                    particles[i].style.opacity = Math.random() * 0.7 + 0.3;
                }
            }
        }
    }, 10000);
    
    // Regenerate on resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(initFastParticles, 100);
    });
    
    // Performance optimization: Throttle animation updates
    let lastUpdate = 0;
    function updateParticles(currentTime) {
        if (currentTime - lastUpdate > 100) { // Update every 100ms
            // Adjust particle speeds randomly for variety
            const particles = document.querySelectorAll('.particle');
            const randomParticle = particles[Math.floor(Math.random() * particles.length)];
            if (randomParticle) {
                randomParticle.style.animationDuration = `${Math.random() * 3 + 3}s`;
            }
            lastUpdate = currentTime;
        }
        requestAnimationFrame(updateParticles);
    }
    requestAnimationFrame(updateParticles);
});
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

(function () {
  // If user prefers reduced motion, reveal everything and exit.
  const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const reveals = Array.from(document.querySelectorAll('.reveal, .reveal--stagger, .reveal--delay-1, .reveal--delay-2'));

  function revealNow(el) {
    if (!el) return;
    if (el.classList.contains('reveal--stagger')) {
      // reveal children with small stagger
      const children = Array.from(el.children);
      children.forEach((child, idx) => {
        const delay = 100 * idx; // 100ms step
        setTimeout(() => child.classList.add('revealed'), delay);
      });
      el.classList.add('revealed');
      return;
    }
    // For plain reveal items, just add class
    el.classList.add('revealed');
  }

  if (reduceMotion) {
    // Immediately reveal all
    reveals.forEach(r => {
      if (r.classList.contains('reveal--stagger')) {
        Array.from(r.children).forEach(c => c.classList.add('revealed'));
      }
      r.classList.add('revealed');
    });
    return;
  }

  // IntersectionObserver options - tune rootMargin to reveal slightly before visible
  const io = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        revealNow(entry.target);
        observer.unobserve(entry.target);
      }
    });
  }, {
    root: null,
    rootMargin: '0px 0px -8% 0px', // reveal slightly earlier
    threshold: 0.08
  });

  // Observe each reveal node
  reveals.forEach(node => {
    // If node already visible in viewport, reveal immediately
    const rect = node.getBoundingClientRect();
    if (rect.top < (window.innerHeight || document.documentElement.clientHeight) && rect.bottom > 0) {
      // small timeout to let layout settle
      setTimeout(() => revealNow(node), 60);
    } else {
      io.observe(node);
    }
  });

  // Also reveal on DOMContentLoaded for any elements inserted late
  window.addEventListener('DOMContentLoaded', () => {
    reveals.forEach(node => {
      if (node.classList.contains('revealed')) return;
      const r = node.getBoundingClientRect();
      if (r.top < (window.innerHeight || document.documentElement.clientHeight) && r.bottom > 0) {
        revealNow(node);
      }
    });
  });

})();

// about page

// Function to handle image loading errors
        function handleImageError() {
            const container = document.querySelector('.team-diagram-container');
            const image = document.getElementById('teamStructureImage');
            
            // Remove the broken image
            if (image) {
                image.style.display = 'none';
            }
            
            // Create error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'team-diagram-error';
            errorDiv.innerHTML = `
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                <h3>Team Structure Image Not Available</h3>
                <p>We're unable to display our team structure diagram at the moment.</p>
                <p style="font-size: 0.9rem; margin-top: 10px;">Please check the console for more details.</p>
            `;
            
            container.appendChild(errorDiv);
            
            // Log detailed error information
            console.error('Team structure image failed to load. Please check:');
            console.error('1. File path: assets/images/img/team structure.png');
            console.error('2. File exists in the correct location');
            console.error('3. File name spelling (including spaces)');
            console.error('4. File permissions');
        }
        
        // Alternative image paths to try if the primary fails
        function tryAlternativePaths() {
            const alternativePaths = [
                'assets/Images/img/team structure.png',
                'assets/images/team structure.png',
                'assets/img/team structure.png',
                'assets/assets/Images/img/team structure.png'
            ];
            
            const image = document.getElementById('teamStructureImage');
            let currentIndex = 0;
            
            image.onerror = function() {
                if (currentIndex < alternativePaths.length) {
                    console.log(`Trying alternative path: ${alternativePaths[currentIndex]}`);
                    image.src = alternativePaths[currentIndex];
                    currentIndex++;
                } else {
                    handleImageError();
                }
            };
        }
        
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            tryAlternativePaths();
        });

// contact page

document.addEventListener('DOMContentLoaded', function() {
        const countrySelect = document.getElementById('country-select');
        
        // Comprehensive list of countries, flags, and dial codes
        const countries = [
            { code: "+93", flag: "🇦🇫", name: "Afghanistan" },
            { code: "+355", flag: "🇦🇱", name: "Albania" },
            { code: "+213", flag: "🇩🇿", name: "Algeria" },
            { code: "+1", flag: "🇦🇸", name: "American Samoa" },
            { code: "+376", flag: "🇦🇩", name: "Andorra" },
            { code: "+244", flag: "🇦🇴", name: "Angola" },
            { code: "+1", flag: "🇦🇮", name: "Anguilla" },
            { code: "+1", flag: "🇦🇬", name: "Antigua" },
            { code: "+54", flag: "🇦🇷", name: "Argentina" },
            { code: "+374", flag: "🇦🇲", name: "Armenia" },
            { code: "+297", flag: "🇦🇼", name: "Aruba" },
            { code: "+61", flag: "🇦🇺", name: "Australia" },
            { code: "+43", flag: "🇦🇹", name: "Austria" },
            { code: "+994", flag: "🇦🇿", name: "Azerbaijan" },
            { code: "+1", flag: "🇧🇸", name: "Bahamas" },
            { code: "+973", flag: "🇧🇭", name: "Bahrain" },
            { code: "+880", flag: "🇧🇩", name: "Bangladesh" },
            { code: "+1", flag: "🇧🇧", name: "Barbados" },
            { code: "+375", flag: "🇧🇾", name: "Belarus" },
            { code: "+32", flag: "🇧🇪", name: "Belgium" },
            { code: "+501", flag: "🇧🇿", name: "Belize" },
            { code: "+229", flag: "🇧🇯", name: "Benin" },
            { code: "+1", flag: "🇧🇲", name: "Bermuda" },
            { code: "+975", flag: "🇧🇹", name: "Bhutan" },
            { code: "+591", flag: "🇧🇴", name: "Bolivia" },
            { code: "+387", flag: "🇧🇦", name: "Bosnia" },
            { code: "+267", flag: "🇧🇼", name: "Botswana" },
            { code: "+55", flag: "🇧🇷", name: "Brazil" },
            { code: "+1", flag: "🇻🇬", name: "British Virgin Is." },
            { code: "+673", flag: "🇧🇳", name: "Brunei" },
            { code: "+359", flag: "🇧🇬", name: "Bulgaria" },
            { code: "+226", flag: "🇧🇫", name: "Burkina Faso" },
            { code: "+257", flag: "🇧🇮", name: "Burundi" },
            { code: "+855", flag: "🇰🇭", name: "Cambodia" },
            { code: "+237", flag: "🇨🇲", name: "Cameroon" },
            { code: "+1", flag: "🇨🇦", name: "Canada" },
            { code: "+238", flag: "🇨🇻", name: "Cape Verde" },
            { code: "+1", flag: "🇰🇾", name: "Cayman Is." },
            { code: "+236", flag: "🇨🇫", name: "Central African Rep." },
            { code: "+235", flag: "🇹🇩", name: "Chad" },
            { code: "+56", flag: "🇨🇱", name: "Chile" },
            { code: "+86", flag: "🇨🇳", name: "China" },
            { code: "+57", flag: "🇨🇴", name: "Colombia" },
            { code: "+269", flag: "🇰🇲", name: "Comoros" },
            { code: "+242", flag: "🇨🇬", name: "Congo" },
            { code: "+243", flag: "🇨🇩", name: "DR Congo" },
            { code: "+682", flag: "🇨🇰", name: "Cook Is." },
            { code: "+506", flag: "🇨🇷", name: "Costa Rica" },
            { code: "+385", flag: "🇭🇷", name: "Croatia" },
            { code: "+53", flag: "🇨🇺", name: "Cuba" },
            { code: "+599", flag: "🇨🇼", name: "Curaçao" },
            { code: "+357", flag: "🇨🇾", name: "Cyprus" },
            { code: "+420", flag: "🇨🇿", name: "Czech Republic" },
            { code: "+45", flag: "🇩🇰", name: "Denmark" },
            { code: "+253", flag: "🇩🇯", name: "Djibouti" },
            { code: "+1", flag: "🇩🇲", name: "Dominica" },
            { code: "+1", flag: "🇩🇴", name: "Dominican Rep." },
            { code: "+593", flag: "🇪🇨", name: "Ecuador" },
            { code: "+20", flag: "🇪🇬", name: "Egypt" },
            { code: "+503", flag: "🇸🇻", name: "El Salvador" },
            { code: "+240", flag: "🇬🇶", name: "Equatorial Guinea" },
            { code: "+291", flag: "🇪🇷", name: "Eritrea" },
            { code: "+372", flag: "🇪🇪", name: "Estonia" },
            { code: "+251", flag: "🇪🇹", name: "Ethiopia" },
            { code: "+500", flag: "🇫🇰", name: "Falkland Is." },
            { code: "+298", flag: "🇫🇴", name: "Faroe Is." },
            { code: "+679", flag: "🇫🇯", name: "Fiji" },
            { code: "+358", flag: "🇫🇮", name: "Finland" },
            { code: "+33", flag: "🇫🇷", name: "France" },
            { code: "+594", flag: "🇬🇫", name: "French Guiana" },
            { code: "+689", flag: "🇵🇫", name: "French Polynesia" },
            { code: "+241", flag: "🇬🇦", name: "Gabon" },
            { code: "+220", flag: "🇬🇲", name: "Gambia" },
            { code: "+995", flag: "🇬🇪", name: "Georgia" },
            { code: "+49", flag: "🇩🇪", name: "Germany" },
            { code: "+233", flag: "🇬🇭", name: "Ghana" },
            { code: "+350", flag: "🇬🇮", name: "Gibraltar" },
            { code: "+30", flag: "🇬🇷", name: "Greece" },
            { code: "+299", flag: "🇬🇱", name: "Greenland" },
            { code: "+1", flag: "🇬🇩", name: "Grenada" },
            { code: "+590", flag: "🇬🇵", name: "Guadeloupe" },
            { code: "+1", flag: "🇬🇺", name: "Guam" },
            { code: "+502", flag: "🇬🇹", name: "Guatemala" },
            { code: "+44", flag: "🇬🇬", name: "Guernsey" },
            { code: "+224", flag: "🇬🇳", name: "Guinea" },
            { code: "+245", flag: "🇬🇼", name: "Guinea-Bissau" },
            { code: "+592", flag: "🇬🇾", name: "Guyana" },
            { code: "+509", flag: "🇭🇹", name: "Haiti" },
            { code: "+504", flag: "🇭🇳", name: "Honduras" },
            { code: "+852", flag: "🇭🇰", name: "Hong Kong" },
            { code: "+36", flag: "🇭🇺", name: "Hungary" },
            { code: "+354", flag: "🇮🇸", name: "Iceland" },
            { code: "+91", flag: "🇮🇳", name: "India" },
            { code: "+62", flag: "🇮🇩", name: "Indonesia" },
            { code: "+98", flag: "🇮🇷", name: "Iran" },
            { code: "+964", flag: "🇮🇶", name: "Iraq" },
            { code: "+353", flag: "🇮🇪", name: "Ireland" },
            { code: "+44", flag: "🇮🇲", name: "Isle of Man" },
            { code: "+972", flag: "🇮🇱", name: "Israel" },
            { code: "+39", flag: "🇮🇹", name: "Italy" },
            { code: "+225", flag: "🇨🇮", name: "Ivory Coast" },
            { code: "+1", flag: "🇯🇲", name: "Jamaica" },
            { code: "+81", flag: "🇯🇵", name: "Japan" },
            { code: "+44", flag: "🇯🇪", name: "Jersey" },
            { code: "+962", flag: "🇯🇴", name: "Jordan" },
            { code: "+7", flag: "🇰🇿", name: "Kazakhstan" },
            { code: "+254", flag: "🇰🇪", name: "Kenya" },
            { code: "+686", flag: "🇰🇮", name: "Kiribati" },
            { code: "+965", flag: "🇰🇼", name: "Kuwait" },
            { code: "+996", flag: "🇰🇬", name: "Kyrgyzstan" },
            { code: "+856", flag: "🇱🇦", name: "Laos" },
            { code: "+371", flag: "🇱🇻", name: "Latvia" },
            { code: "+961", flag: "🇱🇧", name: "Lebanon" },
            { code: "+266", flag: "🇱🇸", name: "Lesotho" },
            { code: "+231", flag: "🇱🇷", name: "Liberia" },
            { code: "+218", flag: "🇱🇾", name: "Libya" },
            { code: "+423", flag: "🇱🇮", name: "Liechtenstein" },
            { code: "+370", flag: "🇱🇹", name: "Lithuania" },
            { code: "+352", flag: "🇱🇺", name: "Luxembourg" },
            { code: "+853", flag: "🇲🇴", name: "Macau" },
            { code: "+389", flag: "🇲🇰", name: "Macedonia" },
            { code: "+261", flag: "🇲🇬", name: "Madagascar" },
            { code: "+265", flag: "🇲🇼", name: "Malawi" },
            { code: "+60", flag: "🇲🇾", name: "Malaysia" },
            { code: "+960", flag: "🇲🇻", name: "Maldives" },
            { code: "+223", flag: "🇲🇱", name: "Mali" },
            { code: "+356", flag: "🇲🇹", name: "Malta" },
            { code: "+692", flag: "🇲🇭", name: "Marshall Is." },
            { code: "+596", flag: "🇲🇶", name: "Martinique" },
            { code: "+222", flag: "🇲🇷", name: "Mauritania" },
            { code: "+230", flag: "🇲🇺", name: "Mauritius" },
            { code: "+262", flag: "🇾🇹", name: "Mayotte" },
            { code: "+52", flag: "🇲🇽", name: "Mexico" },
            { code: "+691", flag: "🇫🇲", name: "Micronesia" },
            { code: "+373", flag: "🇲🇩", name: "Moldova" },
            { code: "+377", flag: "🇲🇨", name: "Monaco" },
            { code: "+976", flag: "🇲🇳", name: "Mongolia" },
            { code: "+382", flag: "🇲🇪", name: "Montenegro" },
            { code: "+1", flag: "🇲🇸", name: "Montserrat" },
            { code: "+212", flag: "🇲🇦", name: "Morocco" },
            { code: "+258", flag: "🇲🇿", name: "Mozambique" },
            { code: "+95", flag: "🇲🇲", name: "Myanmar" },
            { code: "+264", flag: "🇳🇦", name: "Namibia" },
            { code: "+674", flag: "🇳🇷", name: "Nauru" },
            { code: "+977", flag: "🇳🇵", name: "Nepal" },
            { code: "+31", flag: "🇳🇱", name: "Netherlands" },
            { code: "+687", flag: "🇳🇨", name: "New Caledonia" },
            { code: "+64", flag: "🇳🇿", name: "New Zealand" },
            { code: "+505", flag: "🇳🇮", name: "Nicaragua" },
            { code: "+227", flag: "🇳🇪", name: "Niger" },
            { code: "+234", flag: "🇳🇬", name: "Nigeria" },
            { code: "+683", flag: "🇳🇺", name: "Niue" },
            { code: "+672", flag: "🇳🇫", name: "Norfolk Island" },
            { code: "+1", flag: "🇲🇵", name: "Northern Mariana Is." },
            { code: "+47", flag: "🇳🇴", name: "Norway" },
            { code: "+968", flag: "🇴🇲", name: "Oman" },
            { code: "+92", flag: "🇵🇰", name: "Pakistan" },
            { code: "+680", flag: "🇵🇼", name: "Palau" },
            { code: "+970", flag: "🇵🇸", name: "Palestine" },
            { code: "+507", flag: "🇵🇦", name: "Panama" },
            { code: "+675", flag: "🇵🇬", name: "Papua New Guinea" },
            { code: "+595", flag: "🇵🇾", name: "Paraguay" },
            { code: "+51", flag: "🇵🇪", name: "Peru" },
            { code: "+63", flag: "🇵🇭", name: "Philippines" },
            { code: "+48", flag: "🇵🇱", name: "Poland" },
            { code: "+351", flag: "🇵🇹", name: "Portugal" },
            { code: "+1", flag: "🇵🇷", name: "Puerto Rico" },
            { code: "+974", flag: "🇶🇦", name: "Qatar" },
            { code: "+262", flag: "🇷🇪", name: "Réunion" },
            { code: "+40", flag: "🇷🇴", name: "Romania" },
            { code: "+7", flag: "🇷🇺", name: "Russia" },
            { code: "+250", flag: "🇷🇼", name: "Rwanda" },
            { code: "+1", flag: "🇰🇳", name: "St. Kitts & Nevis" },
            { code: "+1", flag: "🇱🇨", name: "St. Lucia" },
            { code: "+590", flag: "🇲🇫", name: "St. Martin" },
            { code: "+508", flag: "🇵🇲", name: "St. Pierre & Miquelon" },
            { code: "+1", flag: "🇻🇨", name: "St. Vincent" },
            { code: "+685", flag: "🇼🇸", name: "Samoa" },
            { code: "+378", flag: "🇸🇲", name: "San Marino" },
            { code: "+239", flag: "🇸🇹", name: "Sao Tome" },
            { code: "+966", flag: "🇸🇦", name: "Saudi Arabia" },
            { code: "+221", flag: "🇸🇳", name: "Senegal" },
            { code: "+381", flag: "🇷🇸", name: "Serbia" },
            { code: "+248", flag: "🇸🇨", name: "Seychelles" },
            { code: "+232", flag: "🇸🇱", name: "Sierra Leone" },
            { code: "+65", flag: "🇸🇬", name: "Singapore" },
            { code: "+1", flag: "🇸🇽", name: "Sint Maarten" },
            { code: "+421", flag: "🇸🇰", name: "Slovakia" },
            { code: "+386", flag: "🇸🇮", name: "Slovenia" },
            { code: "+677", flag: "🇸🇧", name: "Solomon Is." },
            { code: "+252", flag: "🇸🇴", name: "Somalia" },
            { code: "+27", flag: "🇿🇦", name: "South Africa" },
            { code: "+82", flag: "🇰🇷", name: "South Korea" },
            { code: "+211", flag: "🇸🇸", name: "South Sudan" },
            { code: "+34", flag: "🇪🇸", name: "Spain" },
            { code: "+94", flag: "🇱🇰", name: "Sri Lanka" },
            { code: "+249", flag: "🇸🇩", name: "Sudan" },
            { code: "+597", flag: "🇸🇷", name: "Suriname" },
            { code: "+268", flag: "🇸🇿", name: "Swaziland" },
            { code: "+46", flag: "🇸🇪", name: "Sweden" },
            { code: "+41", flag: "🇨🇭", name: "Switzerland" },
            { code: "+963", flag: "🇸🇾", name: "Syria" },
            { code: "+886", flag: "🇹🇼", name: "Taiwan" },
            { code: "+992", flag: "🇹🇯", name: "Tajikistan" },
            { code: "+255", flag: "🇹🇿", name: "Tanzania" },
            { code: "+66", flag: "🇹🇭", name: "Thailand" },
            { code: "+670", flag: "🇹🇱", name: "Timor-Leste" },
            { code: "+228", flag: "🇹🇬", name: "Togo" },
            { code: "+676", flag: "🇹🇴", name: "Tonga" },
            { code: "+1", flag: "🇹🇹", name: "Trinidad & Tobago" },
            { code: "+216", flag: "🇹🇳", name: "Tunisia" },
            { code: "+90", flag: "🇹🇷", name: "Turkey" },
            { code: "+993", flag: "🇹🇲", name: "Turkmenistan" },
            { code: "+1", flag: "🇹🇨", name: "Turks & Caicos Is." },
            { code: "+688", flag: "🇹🇻", name: "Tuvalu" },
            { code: "+256", flag: "🇺🇬", name: "Uganda" },
            { code: "+380", flag: "🇺🇦", name: "Ukraine" },
            { code: "+971", flag: "🇦🇪", name: "UAE" },
            { code: "+44", flag: "🇬🇧", name: "United Kingdom" },
            { code: "+1", flag: "🇺🇸", name: "United States" },
            { code: "+598", flag: "🇺🇾", name: "Uruguay" },
            { code: "+998", flag: "🇺🇿", name: "Uzbekistan" },
            { code: "+678", flag: "🇻🇺", name: "Vanuatu" },
            { code: "+39", flag: "🇻🇦", name: "Vatican City" },
            { code: "+58", flag: "🇻🇪", name: "Venezuela" },
            { code: "+84", flag: "🇻🇳", name: "Vietnam" },
            { code: "+967", flag: "🇾🇪", name: "Yemen" },
            { code: "+260", flag: "🇿🇲", name: "Zambia" },
            { code: "+263", flag: "🇿🇼", name: "Zimbabwe" }
        ];

        // Sort countries alphabetically
        countries.sort((a, b) => a.name.localeCompare(b.name));

        // Clear existing options
        countrySelect.innerHTML = '';

        // Add options to select
        countries.forEach(country => {
            const option = document.createElement('option');
            option.value = country.code;
            // Format: 🇮🇳 +91 (India)
            option.text = `${country.flag} ${country.code}`;
            option.setAttribute('title', country.name); // Tooltip for full name
            
            // Default to India if needed
            if(country.code === "+91" && country.name === "India") {
                option.selected = true;
            }
            
            countrySelect.appendChild(option);
        });
    });
// Init intl-tel-input and tweak dropdown width to match input
document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.querySelector('#phone');
            
            if (phoneInput) {
                // Initialize intl-tel-input
                // We don't need custom click handlers because we fixed the CSS
                const iti = window.intlTelInput(phoneInput, {
                    initialCountry: 'in', // India
                    separateDialCode: true,
                    autoHideDialCode: false,
                    utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js',
                });
            }
        });