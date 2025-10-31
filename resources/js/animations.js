/**
 * Animation Utilities
 * Modern animation helpers for smooth UI interactions
 */

// Intersection Observer for scroll animations
export function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                entry.target.classList.remove('animate-out');
            }
        });
    }, observerOptions);

    // Observe all elements with data-animate attribute
    document.querySelectorAll('[data-animate]').forEach(el => {
        observer.observe(el);
    });
}

// Fade animations
export function fadeIn(element, duration = 300) {
    element.style.opacity = '0';
    element.style.display = 'block';
    element.style.transition = `opacity ${duration}ms ease-in-out`;
    
    requestAnimationFrame(() => {
        element.style.opacity = '1';
    });
}

export function fadeOut(element, duration = 300) {
    element.style.opacity = '1';
    element.style.transition = `opacity ${duration}ms ease-in-out`;
    
    requestAnimationFrame(() => {
        element.style.opacity = '0';
    });
    
    setTimeout(() => {
        element.style.display = 'none';
    }, duration);
}

// Slide animations
export function slideDown(element, duration = 300) {
    element.style.height = '0';
    element.style.overflow = 'hidden';
    element.style.display = 'block';
    element.style.transition = `height ${duration}ms ease-in-out`;
    
    const height = element.scrollHeight;
    
    requestAnimationFrame(() => {
        element.style.height = height + 'px';
    });
    
    setTimeout(() => {
        element.style.height = 'auto';
        element.style.overflow = 'visible';
    }, duration);
}

export function slideUp(element, duration = 300) {
    element.style.height = element.scrollHeight + 'px';
    element.style.overflow = 'hidden';
    element.style.transition = `height ${duration}ms ease-in-out`;
    
    requestAnimationFrame(() => {
        element.style.height = '0';
    });
    
    setTimeout(() => {
        element.style.display = 'none';
    }, duration);
}

// Stagger animation for lists
export function staggerAnimation(elements, delay = 100) {
    elements.forEach((element, index) => {
        setTimeout(() => {
            element.classList.add('animate-in');
        }, index * delay);
    });
}

// Toast notification
export function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50 ${getToastColor(type)}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    requestAnimationFrame(() => {
        toast.style.transform = 'translateX(0)';
    });
    
    setTimeout(() => {
        toast.style.transform = 'translateX(calc(100% + 1rem))';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, duration);
}

function getToastColor(type) {
    const colors = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        warning: 'bg-yellow-500 text-white',
        info: 'bg-blue-500 text-white'
    };
    return colors[type] || colors.info;
}

// Modal animations
export function openModal(modalElement) {
    modalElement.classList.remove('hidden');
    modalElement.style.opacity = '0';
    
    requestAnimationFrame(() => {
        modalElement.style.transition = 'opacity 200ms ease-in-out';
        modalElement.style.opacity = '1';
        
        const content = modalElement.querySelector('[data-modal-content]');
        if (content) {
            content.style.transform = 'scale(0.95)';
            content.style.transition = 'transform 200ms ease-out';
            requestAnimationFrame(() => {
                content.style.transform = 'scale(1)';
            });
        }
    });
}

export function closeModal(modalElement) {
    const content = modalElement.querySelector('[data-modal-content]');
    
    if (content) {
        content.style.transform = 'scale(0.95)';
    }
    
    modalElement.style.opacity = '0';
    
    setTimeout(() => {
        modalElement.classList.add('hidden');
    }, 200);
}

// Loading spinner
export function showLoader(container) {
    const loader = document.createElement('div');
    loader.className = 'flex items-center justify-center p-8';
    loader.innerHTML = `
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    `;
    loader.setAttribute('data-loader', 'true');
    container.appendChild(loader);
}

export function hideLoader(container) {
    const loader = container.querySelector('[data-loader]');
    if (loader) {
        fadeOut(loader, 200);
        setTimeout(() => {
            if (loader.parentNode) {
                loader.parentNode.removeChild(loader);
            }
        }, 200);
    }
}

// Smooth scroll to element
export function smoothScrollTo(element, offset = 0) {
    const targetPosition = element.getBoundingClientRect().top + window.pageYOffset - offset;
    
    window.scrollTo({
        top: targetPosition,
        behavior: 'smooth'
    });
}

// Ripple effect for buttons
export function addRippleEffect(button) {
    button.addEventListener('click', function(e) {
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.className = 'ripple';
        
        button.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    });
}

// Initialize all animations on page load
export function initAnimations() {
    initScrollAnimations();
    
    // Add ripple effect to all buttons with data-ripple attribute
    document.querySelectorAll('[data-ripple]').forEach(button => {
        addRippleEffect(button);
    });
    
    // Handle modal triggers
    document.querySelectorAll('[data-modal-open]').forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modalId = trigger.getAttribute('data-modal-open');
            const modal = document.getElementById(modalId);
            if (modal) openModal(modal);
        });
    });
    
    document.querySelectorAll('[data-modal-close]').forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modal = trigger.closest('[data-modal]');
            if (modal) closeModal(modal);
        });
    });
}

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAnimations);
} else {
    initAnimations();
}
