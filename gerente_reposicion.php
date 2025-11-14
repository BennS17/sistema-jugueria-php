<?php
session_start();
include 'conexion.php'; // Incluimos la conexión

// 1. VERIFICACIÓN DE SEGURIDAD (Gerente logueado)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'gerente') {
    header("Location: login.php");
    exit();
}
$nombre_usuario = $_SESSION['nombre_usuario'];
$id_usuario_gerente = $_SESSION['id_usuario'];
$mensaje = ""; // Para mostrar mensajes de éxito o error

// 2. PROCESAR FORMULARIO DE REPOSICIÓN (Si se envió por POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generar_orden'])) {
    
    $id_producto = $_POST['id_producto'];
    $cantidad = (int)$_POST['cantidad'];
    $fecha_orden = $_POST['fecha_orden'];
    
    // Validamos que la cantidad sea positiva
    if ($cantidad > 0 && !empty($id_producto) && !empty($fecha_orden)) {
        
        // Iniciamos transacción: O se hacen las dos cosas, o no se hace nada
        $conexion->begin_transaction();
        
        try {
            // 1. Insertamos la orden en el registro 'ordenes_reposicion'
            $sql_orden = "INSERT INTO ordenes_reposicion (id_usuario_gerente, id_producto, cantidad, fecha_orden) VALUES (?, ?, ?, ?)";
            $stmt_orden = $conexion->prepare($sql_orden);
            $stmt_orden->bind_param("iiss", $id_usuario_gerente, $id_producto, $cantidad, $fecha_orden);
            $stmt_orden->execute();
            $stmt_orden->close();
            
            // 2. ACTUALIZAMOS EL STOCK en la tabla 'productos'
            $sql_stock = "UPDATE productos SET stock = stock + ? WHERE id_producto = ?";
            $stmt_stock = $conexion->prepare($sql_stock);
            $stmt_stock->bind_param("ii", $cantidad, $id_producto);
            $stmt_stock->execute();
            $stmt_stock->close();
            
            // Si todo salió bien, confirmamos
            $conexion->commit();
            $mensaje = "¡Orden de reposición generada y stock actualizado con éxito!";
            
        } catch (Exception $e) {
            // Si algo falló, deshacemos todo
            $conexion->rollback();
            $mensaje = "Error al procesar la orden: " . $e->getMessage();
        }
    } else {
        $mensaje = "Error: Todos los campos son obligatorios y la cantidad debe ser mayor a 0.";
    }
}

// 3. OBTENER DATOS PARA MOSTRAR EN LA PÁGINA

// A. Lista de todos los productos para el <select>
$sql_prods = "SELECT id_producto, nombre, stock FROM productos ORDER BY nombre ASC";
$lista_productos = $conexion->query($sql_prods);

// B. Lista de todas las órdenes de reposición ya creadas (para la tabla)
$sql_ordenes = "SELECT o.fecha_orden, p.nombre AS nombre_producto, o.cantidad
                FROM ordenes_reposicion o
                JOIN productos p ON o.id_producto = p.id_producto
                ORDER BY o.fecha_orden DESC";
$lista_ordenes = $conexion->query($sql_ordenes);

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generar Reposición - Juguería Fiori</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* --- ESTILOS BÁSICOS Y DE BARRA LATERAL (Idénticos) --- */
        body { font-family: 'Poppins', sans-serif; margin: 0; background: #f4f7f6; }
        .container { display: flex; height: 100vh; }
        .sidebar { width: 250px; background: #ffffff; border-right: 1px solid #e0e0e0; display: flex; flex-direction: column; padding: 20px; }
        .sidebar-header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .sidebar-header h3 { color: #333; font-weight: 600; }
        .sidebar-header p { color: #666; font-size: 14px; }
        .sidebar-nav { flex-grow: 1; }
        .sidebar-nav a { display: block; padding: 12px 15px; text-decoration: none; color: #444; border-radius: 8px; margin-bottom: 10px; font-weight: 400; transition: background 0.3s, color 0.3s; }
        .sidebar-nav a:hover { background: #f0f0f0; color: #000; }
        .sidebar-nav a.active { background: #fda085; color: white; font-weight: 600; }
        .sidebar-footer a { display: block; padding: 12px 15px; text-decoration: none; background: #ffebee; color: #c62828; border-radius: 8px; text-align: center; font-weight: 500; transition: background 0.3s; }
        .main-content { flex-grow: 1; padding: 40px; overflow-y: auto; }
        .main-content h1 { color: #333; font-size: 32px; margin-bottom: 20px; }
        
        /* --- ESTILOS PARA EL FORMULARIO Y TABLA (Basado en Prototipo) --- */
        .reposicion-container {
            display: grid;
            grid-template-columns: 1fr 1.5fr; /* Dos columnas */
            gap: 30px;
        }
        .form-container, .table-container {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        .form-container h3, .table-container h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        /* Estilos del formulario */
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            color: #555;
            margin-bottom: 5px;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        .btn-submit {
            width: 100%;
            background: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        /* Estilos de la tabla de órdenes */
        .ordenes-table { width: 100%; border-collapse: collapse; }
        .ordenes-table th, .ordenes-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        .ordenes-table th { background: #fafafa; font-weight: 600; color: #555; font-size: 12px; }
        .ordenes-table td { color: #333; font-size: 14px; }
        
        /* Mensajes de feedback */
        .mensaje { padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 20px; }
        .mensaje.error { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }

    </style>
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <div class="sidebar-header">
                <h3>JUGUERÍA FIORI</h3>
                <p>Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?></p>
            </div>
            <div class="sidebar-nav">
                <a href="gerente_inicio.php">🏠 Inicio</a>
                <a href="gerente_reportes.php">📈 Reporte de Ventas</a>
                <a href="gerente_reposicion.php" class="active">📦 Generar Reposición</a>
            </div>
            <div class="sidebar-footer">
                <a href="logout.php">🚪 Cerrar Sesión</a>
            </div>
        </nav>

        <main class="main-content">
            <h1>Gestión de Inventario y Reposición</h1>

            <?php if (!empty($mensaje)): ?>
                <div class="mensaje <?php echo (strpos($mensaje, 'Error') !== false) ? 'error' : ''; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="reposicion-container">
                
                <div class="form-container">
                    <h3>Generar Nueva Orden</h3>
                    <form action="gerente_reposicion.php" method="POST">
                        <div class="form-group">
                            <label for="fecha_orden">Fecha de Orden:</label>
                            <input type="date" id="fecha_orden" name="fecha_orden" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="id_producto">Producto Faltante:</label>
                            <select id="id_producto" name="id_producto" class="form-control" required>
                                <option value="">-- Seleccionar producto --</option>
                                <?php 
                                if ($lista_productos->num_rows > 0) {
                                    while($prod = $lista_productos->fetch_assoc()) {
                                        echo "<option value='{$prod['id_producto']}'>{$prod['nombre']} (Stock actual: {$prod['stock']})</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="cantidad">Cantidad a reponer:</label>
                            <input type="number" id="cantidad" name="cantidad" class="form-control" min="1" placeholder="Ej: 50" required>
                        </div>
                        <button type="submit" name="generar_orden" class="btn-submit">Generar Orden y Reponer Stock</button>
                    </form>
                </div>
                
                <div class="table-container">
                    <h3>Ordenes de Reposición Registradas</h3>
                    <table class="ordenes-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($lista_ordenes->num_rows > 0): ?>
                                <?php while($orden = $lista_ordenes->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $orden['fecha_orden']; ?></td>
                                        <td><?php echo htmlspecialchars($orden['nombre_producto']); ?></td>
                                        <td>+<?php echo $orden['cantidad']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align:center;">No se han generado órdenes de reposición.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </main>
    </div>
</body>
</html>