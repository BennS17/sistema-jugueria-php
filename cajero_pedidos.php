<?php
session_start();
include 'conexion.php'; // Incluimos la conexión

// 1. VERIFICACIÓN DE SEGURIDAD (Cajero logueado)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'cajero') {
    header("Location: login.php");
    exit();
}
$nombre_usuario = $_SESSION['nombre_usuario'];
$mensaje = ""; // Para mostrar mensajes de éxito o error
$vista = $_GET['vista'] ?? 'verificar'; // Vista por defecto: 'verificar'

// 2. PROCESAR ACCIONES (Si se envió un formulario)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ACCIÓN: VERIFICAR PEDIDO (Cambiar de 'pendiente' a 'validado')
    if (isset($_POST['verificar_pedido'])) {
        $id_pedido_a_verificar = $_POST['id_pedido'];
        
        $sql = "UPDATE pedidos SET estado = 'validado' WHERE id_pedido = ? AND estado = 'pendiente'";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_pedido_a_verificar);
        if ($stmt->execute()) {
            $mensaje = "Pedido #$id_pedido_a_verificar verificado correctamente.";
        }
        $stmt->close();
    }
    
    // ACCIÓN: COBRAR PEDIDO (Cambiar de 'validado' a 'pagado' Y CREAR COMPROBANTE)
    if (isset($_POST['cobrar_pedido'])) {
        $id_pedido_a_cobrar = $_POST['id_pedido'];
        $tipo_comprobante = $_POST['tipo_comprobante'] ?? 'boleta'; // 'boleta' o 'factura'
        $fecha_actual = date('Y-m-d H:i:s');
        
        // Usamos una transacción para asegurar que ambas operaciones funcionen
        $conexion->begin_transaction();
        try {
            // Obtenemos el total del pedido
            $sql_total = "SELECT total FROM pedidos WHERE id_pedido = ? AND estado = 'validado'";
            $stmt_total = $conexion->prepare($sql_total);
            $stmt_total->bind_param("i", $id_pedido_a_cobrar);
            $stmt_total->execute();
            $resultado = $stmt_total->get_result();
            if ($resultado->num_rows == 0) {
                throw new Exception("El pedido no existe o ya fue pagado.");
            }
            $total_pedido = $resultado->fetch_assoc()['total'];
            $stmt_total->close();

            // 1. Actualizamos el pedido a 'pagado'
            $sql_update = "UPDATE pedidos SET estado = 'pagado' WHERE id_pedido = ?";
            $stmt_update = $conexion->prepare($sql_update);
            $stmt_update->bind_param("i", $id_pedido_a_cobrar);
            $stmt_update->execute();
            $stmt_update->close();
            
            // 2. Insertamos en la tabla 'comprobantes'
            $sql_comprobante = "INSERT INTO comprobantes (id_pedido, tipo, fecha_emision, total_final) VALUES (?, ?, ?, ?)";
            $stmt_comprobante = $conexion->prepare($sql_comprobante);
            $stmt_comprobante->bind_param("issd", $id_pedido_a_cobrar, $tipo_comprobante, $fecha_actual, $total_pedido);
            $stmt_comprobante->execute();
            $stmt_comprobante->close();
            
            // Si todo fue bien, confirmamos la transacción
            $conexion->commit();
            $mensaje = "¡Pedido #$id_pedido_a_cobrar cobrado! Comprobante generado.";
            
        } catch (Exception $e) {
            $conexion->rollback(); // Deshacemos todo si algo falla
            $mensaje = "Error al procesar el pago: " . $e->getMessage();
        }
    }
}

// 3. OBTENER DATOS PARA MOSTRAR EN LA VISTA
if ($vista == 'verificar') {
    $estado_buscado = 'pendiente';
    $titulo_vista = "Verificar Pedidos Pendientes";
} else { // vista 'cobrar'
    $estado_buscado = 'validado';
    $titulo_vista = "Generar Cobro (Pedidos Verificados)";
}

// Consulta para obtener los pedidos (cabeceras)
$sql_pedidos = "SELECT p.id_pedido, p.fecha_hora, p.total, u.nombre_usuario AS mozo
                FROM pedidos p
                JOIN usuarios u ON p.id_usuario_mozo = u.id_usuario
                WHERE p.estado = ?
                ORDER BY p.fecha_hora ASC";
$stmt_pedidos = $conexion->prepare($sql_pedidos);
$stmt_pedidos->bind_param("s", $estado_buscado);
$stmt_pedidos->execute();
$lista_pedidos = $stmt_pedidos->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo_vista; ?> - Juguería Fiori</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* --- ESTILOS BÁSICOS Y DE BARRA LATERAL (Idénticos al panel del Mozo) --- */
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

        /* --- Estilos de la Tabla (Como tu prototipo) --- */
        .pedidos-table { width: 100%; border-collapse: collapse; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); }
        .pedidos-table th, .pedidos-table td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        .pedidos-table th { background: #fafafa; font-weight: 600; color: #555; text-transform: uppercase; font-size: 12px; }
        .pedidos-table td { color: #333; font-size: 14px; }
        
        /* Estilos para los botones de acción */
        .btn { padding: 8px 12px; border-radius: 6px; text-decoration: none; font-weight: 500; cursor: pointer; border: none; font-family: 'Poppins'; }
        .btn-verificar { background: #007bff; color: white; }
        .btn-cobrar { background: #28a745; color: white; }
        .mensaje { padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 20px; }
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
                <a href="cajero_inicio.php">🏠 Inicio</a>
                <a href="cajero_pedidos.php?vista=verificar" class="<?php echo ($vista == 'verificar') ? 'active' : ''; ?>">🚦 Verificar Pedidos</a>
                <a href="cajero_pedidos.php?vista=cobrar" class="<?php echo ($vista == 'cobrar') ? 'active' : ''; ?>">💰 Generar Cobro</a>
            </div>
            <div class="sidebar-footer">
                <a href="logout.php">🚪 Cerrar Sesión</a>
            </div>
        </nav>

        <main class="main-content">
            <h1><?php echo $titulo_vista; ?></h1>

            <?php if (!empty($mensaje)): ?>
                <div class="mensaje"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <table class="pedidos-table">
                <thead>
                    <tr>
                        <th>N° Pedido</th>
                        <th>Fecha/Hora</th>
                        <th>Mozo</th>
                        <th>Total</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($lista_pedidos->num_rows > 0): ?>
                        <?php while($pedido = $lista_pedidos->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $pedido['id_pedido']; ?></td>
                                <td><?php echo $pedido['fecha_hora']; ?></td>
                                <td><?php echo htmlspecialchars($pedido['mozo']); ?></td>
                                <td>S/ <?php echo number_format($pedido['total'], 2); ?></td>
                                <td>
                                    <form action="cajero_pedidos.php?vista=<?php echo $vista; ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id_pedido']; ?>">
                                        
                                        <?php if ($vista == 'verificar'): ?>
                                            <button type="submit" name="verificar_pedido" class="btn btn-verificar">Verificar</button>
                                        <?php else: // vista 'cobrar' ?>
                                            <button type="submit" name="cobrar_pedido" class="btn btn-cobrar">Generar Cobro</button>
                                        <?php endif; ?>
                                        
                                        </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">No hay pedidos en este estado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>