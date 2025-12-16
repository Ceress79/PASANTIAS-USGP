<!-- MODAL DE COMPRA RÁPIDA (Estilo Temu/Shein) -->
<div id="quick-modal" class="modal-overlay" onclick="cerrarModal(event)">
    <div class="modal-content">
        <button class="btn-cerrar" onclick="cerrarModalBtn()">×</button>
        
        <div class="modal-body">
            <!-- Imagen -->
            <div class="modal-img-container">
                <img id="m-img" src="" alt="Producto">
            </div>

            <!-- Info -->
            <div class="modal-info">
                <h3 id="m-titulo">Cargando...</h3>
                <p id="m-precio" class="m-precio">$0.00</p>
                
                <!-- Selector de Tallas -->
                <div id="m-variantes-container" class="m-variantes">
                    <p class="label-talla">Seleccionar talla:</p>
                    <div id="m-lista-tallas" class="tallas-grid"></div>
                </div>

                <div id="m-mensaje" class="m-msg"></div>

                <!-- Botón de Acción -->
                <button id="m-btn-agregar" class="btn-modal-agregar" onclick="agregarDesdeModal()">
                    Añadir al Carrito
                </button>
                
                <!-- Link a detalle completo -->
                <a id="m-link-detalle" href="#" class="link-detalle">Ver detalles completos ></a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Fondo oscuro */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.6); z-index: 9999;
        display: none; justify-content: center; align-items: center;
        opacity: 0; transition: opacity 0.3s;
    }
    .modal-overlay.active { display: flex; opacity: 1; }

    /* Tarjeta Blanca */
    .modal-content {
        background: white; width: 90%; max-width: 700px;
        border-radius: 12px; padding: 20px; position: relative;
        transform: translateY(20px); transition: transform 0.3s;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    .modal-overlay.active .modal-content { transform: translateY(0); }

    .btn-cerrar {
        position: absolute; top: 10px; right: 15px; font-size: 2rem;
        background: none; border: none; cursor: pointer; color: #666;
        line-height: 1; z-index: 10;
    }

    .modal-body { display: flex; gap: 20px; }
    
    .modal-img-container { flex: 1; max-width: 300px; }
    .modal-img-container img { width: 100%; height: 300px; object-fit: cover; border-radius: 8px; }

    .modal-info { flex: 1; display: flex; flex-direction: column; justify-content: center; }
    
    #m-titulo { font-size: 1.4rem; margin: 0 0 10px 0; color: #333; }
    .m-precio { font-size: 1.5rem; color: #9e1b32; font-weight: bold; margin-bottom: 20px; }

    /* Tallas */
    .label-talla { font-weight: bold; margin-bottom: 8px; color: #555; }
    .tallas-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
    
    .talla-option {
        padding: 8px 15px; border: 1px solid #ddd; border-radius: 20px;
        cursor: pointer; transition: 0.2s; user-select: none;
    }
    .talla-option:hover { border-color: #9e1b32; }
    .talla-option.selected { background-color: #9e1b32; color: white; border-color: #9e1b32; }
    .talla-option.disabled { opacity: 0.5; cursor: not-allowed; background: #eee; }

    /* Botón */
    .btn-modal-agregar {
        background-color: #333; color: white; padding: 15px;
        border: none; border-radius: 30px; font-weight: bold; font-size: 1.1rem;
        cursor: pointer; width: 100%; transition: 0.2s;
    }
    .btn-modal-agregar:hover { background-color: #000; transform: scale(1.02); }

    .link-detalle { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; font-size: 0.9rem; }
    .link-detalle:hover { text-decoration: underline; }

    .m-msg { text-align: center; margin-bottom: 10px; font-weight: bold; font-size: 0.9rem; }

    /* Móvil */
    @media (max-width: 600px) {
        .modal-body { flex-direction: column; }
        .modal-img-container { max-width: 100%; }
        .modal-img-container img { height: 200px; }
    }
</style>

<script>
    let currentProdId = null;
    let selectedVariant = '';

    // Abrir Modal y cargar datos
    function abrirModal(id) {
        const modal = document.getElementById('quick-modal');
        const content = modal.querySelector('.modal-content');
        
        currentProdId = id;
        selectedVariant = ''; // Resetear selección
        
        // Resetear UI
        document.getElementById('m-titulo').innerText = "Cargando...";
        document.getElementById('m-img').src = "";
        document.getElementById('m-lista-tallas').innerHTML = "";
        document.getElementById('m-mensaje').innerText = "";
        
        modal.classList.add('active'); // Mostrar

        // Pedir datos al servidor
        const data = new URLSearchParams();
        data.append('accion', 'obtener_info_modal');
        data.append('producto_id', id);

        fetch('acciones_carrito.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            if(res.exito) {
                const p = res.producto;
                const vars = res.variantes;

                document.getElementById('m-titulo').innerText = p.nombre;
                document.getElementById('m-precio').innerText = '$' + parseFloat(p.precio).toFixed(2);
                document.getElementById('m-img').src = p.foto ? p.foto : 'style/img/placeholder.png';
                document.getElementById('m-link-detalle').href = 'producto_detalle.php?slug=' + p.slug;

                // Generar tallas
                const container = document.getElementById('m-lista-tallas');
                container.innerHTML = "";

                if (vars.length > 0) {
                    document.getElementById('m-variantes-container').style.display = 'block';
                    vars.forEach(v => {
                        const btn = document.createElement('div');
                        btn.className = 'talla-option';
                        btn.innerText = v.talla;
                        if(v.stock <= 0) {
                            btn.classList.add('disabled');
                        } else {
                            btn.onclick = () => selectTalla(v.id, btn);
                        }
                        container.appendChild(btn);
                    });
                } else {
                    document.getElementById('m-variantes-container').style.display = 'none';
                }
            } else {
                alert("Error al cargar producto");
                cerrarModalBtn();
            }
        });
    }

    function selectTalla(vid, el) {
        document.querySelectorAll('.talla-option').forEach(b => b.classList.remove('selected'));
        el.classList.add('selected');
        selectedVariant = vid;
    }

    function cerrarModal(e) {
        if(e.target.id === 'quick-modal') cerrarModalBtn();
    }
    
    function cerrarModalBtn() {
        document.getElementById('quick-modal').classList.remove('active');
    }

    function agregarDesdeModal() {
        const btn = document.getElementById('m-btn-agregar');
        const msg = document.getElementById('m-mensaje');
        const txtOriginal = btn.innerText;

        btn.innerText = "Añadiendo...";
        btn.disabled = true;

        const data = new URLSearchParams();
        data.append('accion', 'agregar_producto');
        data.append('producto_id', currentProdId);
        data.append('variante_id', selectedVariant); // Si está vacío, se va como pendiente

        fetch('acciones_carrito.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(d => {
            if(d.exito) {
                btn.innerText = "¡Añadido!";
                btn.style.background = "#28a745";
                
                // Actualizar header
                if(document.querySelector('.cart-count')) 
                    document.querySelector('.cart-count').textContent = d.articulos;

                // Mensaje si no eligió talla
                if(!selectedVariant && document.getElementById('m-variantes-container').style.display !== 'none') {
                    msg.style.color = "#856404";
                    msg.innerText = "⚠️ Añadido sin talla. Elígela en el carrito.";
                } else {
                    msg.style.color = "green";
                    msg.innerText = "Producto añadido correctamente.";
                }

                setTimeout(() => {
                    cerrarModalBtn();
                    // Resetear botón
                    btn.innerText = "Añadir al Carrito";
                    btn.style.background = "#333";
                    btn.disabled = false;
                    msg.innerText = "";
                }, 1500);
            } else {
                msg.style.color = "red";
                msg.innerText = d.mensaje;
                btn.innerText = txtOriginal;
                btn.disabled = false;
            }
        });
    }
</script>