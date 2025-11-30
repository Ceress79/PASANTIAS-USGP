/* * Lógica para la gestión de Contactos (Acontactos.php)
 */

function toggleForm() {
    const form = document.getElementById('formContainer');
    
    // Si no existe el formulario, salimos para evitar errores
    if (!form) return;

    // Alternar visibilidad
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        // Opcional: Desplaza la vista hacia el formulario para mejor UX
        form.scrollIntoView({ behavior: 'smooth' });
    } else {
        form.style.display = 'none';
    }
}

function cancelarForm() {
    // Verificamos si hay parámetros en la URL (indicativo de que estamos editando)
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.has('edit_id')) {
        // Si estamos editando un contacto específico, recargamos la página limpia para salir del modo edición
        window.location.href = 'Acontactos.php';
    } else {
        // Si estamos creando uno nuevo, simplemente ocultamos el formulario
        toggleForm();
        
        // Limpiar inputs si el usuario escribió algo y canceló
        const formElement = document.querySelector('form.form-box');
        if (formElement) formElement.reset();
    }
}