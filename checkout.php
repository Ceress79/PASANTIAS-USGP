<?php
session_start();
require_once "admin/db/conexion.php";

// 1. Seguridad
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión para comprar.";
    header("Location: usuario/login.php");
    exit();
}

// 2. Verificar Carrito
$stmt = $pdo->prepare("SELECT COUNT(*) FROM carrito_compras WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
if ($stmt->fetchColumn() == 0) {
    header("Location: carrito.php");
    exit();
}

// 3. RECUPERAR ÚLTIMA DIRECCIÓN USADA (NUEVO)
$datos_guardados = []; // Array vacío por defecto

// Buscamos la última orden de este usuario
$stmtDireccion = $pdo->prepare("SELECT direccion_envio FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmtDireccion->execute([$_SESSION['user_id']]);
$ultima_orden = $stmtDireccion->fetch(PDO::FETCH_ASSOC);

if ($ultima_orden) {
    // Si existe, decodificamos el JSON para llenar el formulario
    $datos_guardados = json_decode($ultima_orden['direccion_envio'], true);
}

include('bases/header.php');
?>

<link rel="stylesheet" href="style/css/checkout.css">

<div class="checkout-container">
    <div class="checkout-box">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0;">Datos de envío</h2>
            <?php if(!empty($datos_guardados)): ?>
                <span style="font-size:12px; color:green; background:#e0ffe0; padding:5px 10px; border-radius:15px;">
                    <i class="fas fa-history"></i> Datos de tu última compra cargados
                </span>
            <?php endif; ?>
        </div>
        
        <form action="acciones_checkout.php" method="POST" id="formCheckout">
            <input type="hidden" name="accion" value="procesar_envio">

            <div class="form-group">
                <label>Ingresa una dirección</label>
                <input type="text" name="calle_principal" 
                       value="<?php echo htmlspecialchars($datos_guardados['calle_principal'] ?? ''); ?>" 
                       placeholder="Ej: Avenida 103" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Provincia</label>
                    <select name="provincia" required>
                        <option value="">Selecciona una provincia</option>
                        <?php 
                            $provincias = ['Manabí', 'Guayas', 'Pichincha', 'Azuay', 'El Oro']; // Agrega más si quieres
                            $prov_guardada = $datos_guardados['provincia'] ?? '';
                            
                            foreach($provincias as $prov) {
                                $selected = ($prov_guardada == $prov) ? 'selected' : '';
                                echo "<option value='$prov' $selected>$prov</option>";
                            }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cantón</label>
                    <input type="text" name="canton" 
                           value="<?php echo htmlspecialchars($datos_guardados['canton'] ?? ''); ?>" 
                           placeholder="Ej: Manta" required>
                </div>
            </div>

            <div class="form-group">
                <label>Parroquia</label>
                <input type="text" name="parroquia" 
                       value="<?php echo htmlspecialchars($datos_guardados['parroquia'] ?? ''); ?>" 
                       placeholder="Selecciona una parroquia" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Calle secundaria</label>
                    <input type="text" name="calle_secundaria" 
                           value="<?php echo htmlspecialchars($datos_guardados['calle_secundaria'] ?? ''); ?>" 
                           placeholder="Ingresa una calle" required>
                </div>
                <div class="form-group">
                    <label>Departamento (opcional)</label>
                    <input type="text" name="departamento" 
                           value="<?php echo htmlspecialchars($datos_guardados['departamento'] ?? ''); ?>" 
                           placeholder="Ej: 10B">
                </div>
            </div>

            <div class="form-group">
                <label>Referencia</label>
                <textarea name="referencia" rows="2" placeholder="Ej: Entre calles, color del edificio..." required><?php echo htmlspecialchars($datos_guardados['referencia'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>Tipo de dirección</label>
                <div class="radio-group">
                    <?php $tipo = $datos_guardados['tipo'] ?? 'Residencial'; ?>
                    <label>
                        <input type="radio" name="tipo_direccion" value="Residencial" <?php echo ($tipo == 'Residencial') ? 'checked' : ''; ?>> 
                        Residencial
                    </label>
                    <label>
                        <input type="radio" name="tipo_direccion" value="Laboral" <?php echo ($tipo == 'Laboral') ? 'checked' : ''; ?>> 
                        Laboral
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Nombre y Apellido (Quien recibe)</label>
                <input type="text" name="recibe_nombre" 
                       value="<?php echo htmlspecialchars($datos_guardados['recibe_nombre'] ?? ''); ?>" 
                       required>
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" name="recibe_telefono" 
                       value="<?php echo htmlspecialchars($datos_guardados['recibe_telefono'] ?? ''); ?>" 
                       required>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn-continuar">Continuar</button>
            </div>
        </form>
    </div>
</div>

<?php include('bases/footer.php'); ?>