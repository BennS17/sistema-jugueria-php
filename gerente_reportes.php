<?php
session_start();
include 'conexion.php'; // Incluimos la conexión

// 1. VERIFICACIÓN DE SEGURIDAD (Gerente logueado)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'gerente') {
    header("Location: login.php");
    exit();
}
$nombre_usuario = $_SESSION['nombre_usuario'];

// 2. CONSULTA A LA BASE DE DATOS
// Obtenemos los comprobantes y los unimos con los pedidos y mozos
$sql = "SELECT c.id_comprobante, c.fecha_emision, c.tipo, c.total_final, p.id_pedido, u.nombre_usuario AS mozo
        FROM comprobantes c
        JOIN pedidos p ON c.id_pedido = p.id_pedido
        JOIN usuarios u ON p.id_usuario_mozo = u.id_usuario
        ORDER BY c.fecha_emision DESC";

$resultado = $conexion->query($sql);
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas - Juguería Fiori</title>
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

        /* --- Estilos de la Tabla de Reportes --- */
        .report-table { width: 100%; border-collapse: collapse; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); }
        .report-table th, .report-table td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        .report-table th { background: #fafafa; font-weight: 600; color: #555; text-transform: uppercase; font-size: 12px; }
        .report-table td { color: #333; font-size: 14px; }
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
                <a href="gerente_reportes.php" class="active">📈 Reporte de Ventas</a>
                <a href="gerente_reposicion.php">📦 Generar Reposición</a>
            </div>
            <div class="sidebar-footer">
                <a href="logout.php">🚪 Cerrar Sesión</a>
            </div>
        </nav>

        <main class="main-content">
            <h1>Reporte de Ventas (Comprobantes Emitidos)</h1>
            
            <table class="report-table">
                <thead>
                    <tr>
                        <th>N° Comprobante</th>
                        <th>Fecha de Emisión</th>
                        <th>Tipo</th>
                        <th>N° Pedido</th>
                        <th>Atendido por (Mozo)</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $fila['id_comprobante']; ?></td>
                                <td><?php echo $fila['fecha_emision']; ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($fila['tipo'])); ?></td>
                                <td><?php echo $fila['id_pedido']; ?></td>
                                <td><?php echo htmlspecialchars($fila['mozo']); ?></td>
                                <td>S/ <?php echo number_format($fila['total_final'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No se ha generado ningún comprobante de venta todavía.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>