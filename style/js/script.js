// script.js - Funcionalidades para la tienda USGP

document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad del carrito
    let cartCount = 0;
    const cartCountElement = document.querySelector('.cart-count');
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    const buyNowButtons = document.querySelectorAll('.buy-now');

    // Actualizar contador del carrito
    function updateCartCount() {
        if (cartCountElement) {
            cartCountElement.textContent = cartCount;
        }
    }

    // Efecto de añadir al carrito
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (this.textContent === 'No stock') return;
            
            const productCard = this.closest('.product-card');
            const productName = productCard.querySelector('.product-name').textContent;
            
            // Efecto visual
            this.style.backgroundColor = '#28a745';
            this.style.color = 'white';
            this.textContent = '✓ Añadido';
            
            // Mostrar overlay del carrito
            productCard.classList.add('in-cart');
            
            // Incrementar contador
            cartCount++;
            updateCartCount();
            
            // Restaurar botón después de 2 segundos
            setTimeout(() => {
                this.style.backgroundColor = '';
                this.style.color = '';
                this.textContent = 'Añadir al carrito';
            }, 2000);
            
            console.log(`Producto añadido: ${productName}`);
        });
    });

    // Funcionalidad de comprar ahora
    buyNowButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (this.disabled) return;
            
            const productCard = this.closest('.product-card');
            const productName = productCard.querySelector('.product-name').textContent;
            
            // Efecto visual
            this.style.transform = 'scale(0.95)';
            
            // Redirigir a la página de compra
            setTimeout(() => {
                window.location.href = 'producto_detalle.php?product=' + encodeURIComponent(productName);
            }, 300);
        });
    });

    // Efectos hover para botones
    const buttons = document.querySelectorAll('.add-to-cart, .buy-now');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            if (!this.disabled && this.textContent !== 'No stock') {
                this.style.transform = 'translateY(-2px)';
            }
        });
        
        button.addEventListener('mouseleave', function() {
            if (!this.disabled && this.textContent !== 'No stock') {
                this.style.transform = 'translateY(0)';
            }
        });
    });

    // Navegación activa
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.main-nav a');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });
});

// Función para el efecto de zoom en imágenes
function initImageZoom() {
    const productImages = document.querySelectorAll('.product-image');
    
    productImages.forEach(img => {
        img.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
        });
        
        img.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', initImageZoom);