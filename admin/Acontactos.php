<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: Alogin.php");
    exit();
}
require_once "db/conexion.php";

// --- LÓGICA DE EDICIÓN ---
$contacto_editar = null;
$mostrar_formulario = false; 

// Valores por defecto
$valores = [
    'tipo' => 'email', 
    'valor' => ''
];

$accion_form = 'crear_contacto';
$titulo_form = 'Añadir Nuevo Contacto';
$btn_texto = 'Guardar Contacto';

// Si recibimos un ID para editar
if (isset($_GET['edit_id'])) {
    $mostrar_formulario = true;
    $id_editar = $_GET['edit_id'];
    
    $stmt = $pdo->prepare("SELECT * FROM contactos WHERE id = ?");
    $stmt->execute([$id_editar]);
    $contacto_editar = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($contacto_editar) {
        $accion_form = 'editar_contacto';
        $titulo_form = 'Editar Contacto';
        $btn_texto = 'Actualizar Contacto';
        $valores = $contacto_editar;
    }
}

// --- CONSULTA LISTADO ---
$sql = "SELECT * FROM contactos ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$contactos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Contactos</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/Aproductos.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            
            <div class="header-actions">
                <h2 style="margin:0; color:#333;">Listado de Contactos</h2>
                <?php if (!$mostrar_formulario): ?>
                    <button class="btn-add-new" onclick="toggleForm()">
                        <i class="fas fa-plus"></i> Añadir Nuevo Contacto
                    </button>
                <?php endif; ?>
            </div>

            <?php if(isset($_GET['mensaje'])): ?>
                <p class="msg-success" style="padding:15px; background:#d4edda; color:#155724; border-radius:5px; margin-bottom:20px;">
                    <i class="fas fa-check-circle"></i> Acción realizada con éxito.
                </p>
            <?php endif; ?>

            <?php if(isset($_GET['error'])): ?>
                <p class="msg-error" style="padding:15px; background:#f8d7da; color:#721c24; border-radius:5px; margin-bottom:20px;">
                    <i class="fas fa-exclamation-circle"></i> Hubo un error al procesar la solicitud.
                </p>
            <?php endif; ?>

            <div id="formContainer" style="display: <?php echo $mostrar_formulario ? 'block' : 'none'; ?>;">
                <form action="acciones_contactos.php" method="POST" class="form-box">
                    <input type="hidden" name="accion" value="<?php echo $accion_form; ?>">
                    <?php if ($contacto_editar): ?><input type="hidden" name="id" value="<?php echo $contacto_editar['id']; ?>"><?php endif; ?>

                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid #eee; padding-bottom:15px; margin-bottom:20px;">
                        <h3 style="margin:0; color:#007bff;"><?php echo $titulo_form; ?></h3>
                        <button type="button" onclick="cancelarForm()" style="background:none; border:none; color:#dc3545; cursor:pointer; font-weight:bold;">Cancelar</button>
                    </div>

                    <div class="form-grid">
                        <div>
                            <label>Tipo de Contacto:</label>
                            <select name="tipo" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="email" <?php echo ($valores['tipo'] == 'email') ? 'selected' : ''; ?>>Email / Correo</option>
                                <option value="telefono" <?php echo ($valores['tipo'] == 'telefono') ? 'selected' : ''; ?>>Teléfono / Celular</option>
                            </select>
                        </div>
                        <div>
                            <label>Valor (Correo o Número):</label>
                            <input type="text" name="valor" required value="<?php echo htmlspecialchars($valores['valor']); ?>" placeholder="Ej: info@empresa.com o 0991234567">
                        </div>
                    </div>

                    <div style="text-align: right; margin-top: 20px;">
                        <button type="submit" class="btn-add-new" style="background:#28a745;"><?php echo $btn_texto; ?></button>
                    </div>
                </form>
            </div>

            <div class="table-container" style="background:white; padding:20px; border:1px solid #ddd; border-radius:8px;">
                <table class="admin-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; text-align: left; border-bottom:2px solid #eee;">
                            <th style="padding: 15px; width: 50px;">ID</th>
                            <th style="padding: 15px; width: 100px;">Icono</th>
                            <th style="padding: 15px; width: 150px;">Tipo</th>
                            <th style="padding: 15px;">Información de Contacto</th>
                            <th style="padding: 15px; width: 180px; text-align:right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($contactos)): ?>
                            <tr><td colspan="5" class="no-data" style="padding:40px; text-align:center; color:#999;">No hay contactos registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($contactos as $con): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 15px; color:#999;"><?php echo $con['id']; ?></td>
                                    <td style="padding: 15px; text-align: center; color: #555;">
                                        <?php if($con['tipo'] == 'email'): ?>
                                            <i class="fas fa-envelope fa-lg"></i>
                                        <?php else: ?>
                                            <i class="fas fa-phone fa-lg"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span style="text-transform: capitalize; background:#f0f0f0; padding: 4px 8px; border-radius: 4px; font-size: 0.9em;">
                                            <?php echo htmlspecialchars($con['tipo']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px; font-weight: bold; color: #333;">
                                        <?php echo htmlspecialchars($con['valor']); ?>
                                    </td>
                                    <td style="padding: 15px; text-align:right;">
                                        <a href="Acontactos.php?edit_id=<?php echo $con['id']; ?>" style="display:inline-block; padding:6px 12px; border:1px solid #007bff; color:#007bff; border-radius:4px; text-decoration:none; margin-right:5px; font-size:13px;">Editar</a>
                                        <form action="acciones_contactos.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar este contacto?');">
                                            <input type="hidden" name="accion" value="eliminar_contacto">
                                            <input type="hidden" name="id" value="<?php echo $con['id']; ?>">
                                            <button type="submit" style="background:white; border:1px solid #dc3545; color:#dc3545; padding:6px 12px; border-radius:4px; cursor:pointer; font-size:13px;">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

    <script src="js/Acontactos.js"></script>
</body>
</html>