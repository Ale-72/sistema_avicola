// Main JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Animación de elementos al hacer scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observar elementos con clase 'animate-on-scroll'
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
});

// Funciones útiles globales
window.utils = {
    // Formatear precio
    formatPrice: function (price) {
        return 'S/ ' + parseFloat(price).toFixed(2);
    },

    // Mostrar notificación
    showNotification: function (message, type = 'info') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} slide-in`;
        alert.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`;

        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(alert, container.firstChild);

            setTimeout(() => {
                alert.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        }
    },

    // Validar formulario
    validateForm: function (formId) {
        const form = document.getElementById(formId);
        if (!form) return false;

        let isValid = true;
        const inputs = form.querySelectorAll('[required]');

        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });

        return isValid;
    },

    // AJAX request helper
    ajax: function (url, method = 'GET', data = null) {
        return fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: data ? JSON.stringify(data) : null
        }).then(response => response.json());
    }
};
