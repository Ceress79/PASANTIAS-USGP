<?php
include('bases/header.php');
?>

<main>
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Ropa y Accesorios</h1>
            </div>
        </div>
    </section>

    <div class="page-content">
        <section class="products-section">
            <h2>Ropa y Accesorios</h2>
            
            <div class="product-grid">
                <!-- Gorra -->
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="style/img/gorra.png" alt="Gorra USGP" class="product-image">
                        <div class="cart-overlay">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Gorra USGP</h3>
                        <p class="product-price">$25.00</p>
                        <div class="product-actions">
                            <button class="add-to-cart">Añadir al carrito</button>
                            <button class="buy-now">Comprar</button>
                        </div>
                    </div>
                </div>
                
                <!-- Vestido -->
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="style/img/vestido.png" alt="Vestido con patrones precolombinos" class="product-image">
                        <div class="cart-overlay">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Vestido Cultural</h3>
                        <p class="product-price">$45.00</p>
                        <div class="product-actions">
                            <button class="add-to-cart">Añadir al carrito</button>
                            <button class="buy-now">Comprar</button>
                        </div>
                    </div>
                </div>
                
                <!-- Cartera -->
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="style/img/cartera.png" alt="Cartera USGP" class="product-image">
                        <div class="cart-overlay">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Cartera Elegante</h3>
                        <p class="product-price">$35.00</p>
                        <div class="product-actions">
                            <button class="add-to-cart">Añadir al carrito</button>
                            <button class="buy-now">Comprar</button>
                        </div>
                    </div>
                </div>
                
                <!-- Camiseta Polo -->
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="style/img/polo.png" alt="Camiseta Polo USGP" class="product-image">
                        <div class="cart-overlay">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Camiseta Polo</h3>
                        <p class="product-price">$30.00</p>
                        <div class="product-actions">
                            <button class="add-to-cart">Añadir al carrito</button>
                            <button class="buy-now">Comprar</button>
                        </div>
                    </div>
                </div>
                
                <!-- Zapatos -->
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="style/img/zapatos.png" alt="Zapatos USGP" class="product-image">
                        <div class="cart-overlay">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Zapatos Casuales</h3>
                        <p class="product-price">$55.00</p>
                        <div class="product-actions">
                            <button class="add-to-cart">Añadir al carrito</button>
                            <button class="buy-now">Comprar</button>
                        </div>
                    </div>
                </div>
                
                <!-- Falda -->
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="style/img/falda.png" alt="Falda USGP" class="product-image">
                        <div class="cart-overlay">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Falda Midi</h3>
                        <p class="product-price">$40.00</p>
                        <div class="product-actions">
                            <button class="add-to-cart">No stock</button>
                            <button class="buy-now" disabled>Comprar</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php include('bases/footer.php'); ?>