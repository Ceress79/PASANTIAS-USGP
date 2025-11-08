<?php
// (Aquí puedes incluir la conexión a la BD en el futuro)
include('bases/header.php');
?>

<link rel="stylesheet" href="style/css/ropa_accesorio.css">

<main>
    <div class="product-page-container">
        <h2>Ropa y Accesorios</h2>
        
        <section class="product-grid-ra">

            <div class="product-card-ra">
                <i class="fas fa-shopping-cart product-cart-icon-ra"></i>
                
                <div class="product-image-box-ra">
                    <img src="style/img/vestido.png" alt="Producto de ejemplo 1">
                </div>
                
                <div class="product-info-ra">
                    <div>
                        <h3 class="product-name-ra">Nombre de producto</h3>
                        <p class="product-price-ra">$45.00</p>
                    </div>
                    
                    <div class="product-actions-ra">
                        <button class="btn-ra add-cart">Añadir al carrito</button>
                        <button class="btn-ra buy-now">Comprar</button>
                    </div>
                </div>
            </div>
            <div class="product-card-ra">
                <div class="product-image-box-ra">
                    <img src="style/img/polo.png" alt="Producto de ejemplo 2">
                </div>
                
                <div class="product-info-ra">
                    <div>
                        <h3 class="product-name-ra">Nombre de producto</h3>
                        <p class="product-price-ra">$30.00</p>
                    </div>
                    
                    <div class="product-actions-ra">
                        <button class="btn-ra add-cart">Añadir al carrito</button>
                        <button class="btn-ra no-stock" disabled>No stock</button>
                    </div>
                </div>
            </div>
            <div class="product-card-ra">
                <i class="fas fa-shopping-cart product-cart-icon-ra"></i>
                <div class="product-image-box-ra">
                    <img src="style/img/gorra.png" alt="Producto de ejemplo 3">
                </div>
                <div class="product-info-ra">
                    <div>
                        <h3 class="product-name-ra">Gorra USGP</h3>
                        <p class="product-price-ra">$25.00</p>
                    </div>
                    <div class="product-actions-ra">
                        <button class="btn-ra add-cart">Añadir al carrito</button>
                        <button class="btn-ra buy-now">Comprar</button>
                    </div>
                </div>
            </div>
            <div class="product-card-ra">
                <div class="product-image-box-ra">
                    <img src="style/img/img_ropa/mokap vestido 5 1.png" alt="Producto de ejemplo 4">
                </div>
                <div class="product-info-ra">
                    <div>
                        <h3 class="product-name-ra">Falda Midi</h3>
                        <p class="product-price-ra">$40.00</p>
                    </div>
                    <div class="product-actions-ra">
                        <button class="btn-ra add-cart no-stock" disabled>Añadir al carrito</button>
                        <button class="btn-ra no-stock" disabled>No stock</button>
                    </div>
                </div>
            </div>
            </section> </div> </main>

<?php include('bases/footer.php'); ?>