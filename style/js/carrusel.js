document.addEventListener('DOMContentLoaded', function() {
    const tracks = document.querySelectorAll('.carrusel-track');

    tracks.forEach(track => {
        // Obtenemos los hijos originales (las tarjetas)
        const items = Array.from(track.children);
        
        // Si no hay productos o hay muy pocos, no animamos o clonamos lo suficiente
        if(items.length === 0) return;

        // Clonamos cada elemento y lo agregamos al final
        items.forEach(item => {
            const clone = item.cloneNode(true);
            clone.setAttribute('aria-hidden', 'true'); // Accesibilidad: ignorar clon
            track.appendChild(clone);
        });
    });
});