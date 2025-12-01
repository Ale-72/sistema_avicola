// Navbar toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggler = document.getElementById('navbarToggler');
    const menu = document.getElementById('navbarMenu');
    
    if (toggler && menu) {
        toggler.addEventListener('click', function() {
            menu.classList.toggle('active');
            toggler.classList.toggle('active');
        });
        
        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!toggler.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('active');
                toggler.classList.remove('active');
            }
        });
    }
    
    // Actualizar contador del carrito
    updateCartCount();
});

// Función para actualizar contador del carrito
function updateCartCount() {
    const cartBadge = document.getElementById('cartCount');
    if (cartBadge) {
        // Obtener cantidad del carrito desde localStorage o API
        const cartItems = JSON.parse(localStorage.getItem('cart') || '[]');
        const totalItems = cartItems.reduce((sum, item) => sum + item.cantidad, 0);
        cartBadge.textContent = totalItems;
        
        if (totalItems > 0) {
            cartBadge.style.display = 'block';
        } else {
            cartBadge.style.display = 'none';
        }
    }
}

//Cerrar alertas automáticamente
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => alert.remove(), 300);
    });
}, 5000);

// Animación de fade out
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeOut {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(-20px); }
    }
`;
document.head.appendChild(style);
