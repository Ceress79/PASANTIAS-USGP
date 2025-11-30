function toggleForm() {
    const container = document.getElementById('formContainer');
    container.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cancelarForm(currentUrlParams) {
    // Si estamos editando (hay ID en URL), recargar sin ID para limpiar
    if (window.location.search.includes('edit_id')) {
        // Redirigir a la misma categoria pero sin edit_id
        // currentUrlParams debería ser algo como "cat=1"
        const params = new URLSearchParams(window.location.search);
        const cat = params.get('cat') || 1;
        window.location.href = 'Aproductos.php?cat=' + cat;
    } else {
        document.getElementById('formContainer').style.display = 'none';
    }
}

// Lógica de Stock para Ropa
function mostrarBloqueStock() {
    const tipo = document.getElementById('tipoStockSelect');
    if (!tipo) return; // Si no existe (porque estamos en Papelería/Hogar), salir.

    const val = tipo.value;

    // Ocultar todos los específicos de ropa
    ['stock_unico', 'stock_ropa', 'stock_calzado', 'stock_pantalones'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    });

    // Mostrar el seleccionado
    if (val === 'unico') {
        const el = document.getElementById('stock_unico');
        if(el) el.classList.remove('hidden');
    }
    if (val === 'ropa') {
        const el = document.getElementById('stock_ropa');
        if(el) el.classList.remove('hidden');
    }
    if (val === 'calzado') {
        const el = document.getElementById('stock_calzado');
        if(el) el.classList.remove('hidden');
    }
    if (val === 'pantalones') {
        const el = document.getElementById('stock_pantalones');
        if(el) el.classList.remove('hidden');
    }
}

// Función de inicialización que llamaremos desde el PHP
function initProductos(isRopaCategory) {
    if (isRopaCategory) {
        mostrarBloqueStock();
    }
}