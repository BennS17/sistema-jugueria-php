<?php
// 1. Iniciamos la sesión y verificamos (igual que en mozo_inicio.php)
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'mozo') {
    header("Location: login.php");
    exit();
}
$nombre_usuario = $_SESSION['nombre_usuario'];

// 2. Incluimos la conexión a la base de datos
include 'conexion.php';

// 3. Preparamos la consulta SQL para obtener los productos
$sql = "SELECT nombre, precio, stock FROM productos ORDER BY nombre ASC";
$resultado = $conexion->query($sql);

// Es buena práctica cerrar la conexión cuando ya no se usa
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consultar Productos - Juguería Fiori</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilos_panel.css"> <style>
        /* Estilos base */
        body { font-family: 'Poppins', sans-serif; margin: 0; background: #f4f7f6; }
        .container { display: flex; height: 100vh; }

        /* --- Barra Lateral (Sidebar) --- */
        .sidebar { width: 250px; background: #ffffff; border-right: 1px solid #e0e0e0; display: flex; flex-direction: column; padding: 20px; }
        .sidebar-header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .sidebar-header h3 { color: #333; font-weight: 600; }
        .sidebar-header p { color: #666; font-size: 14px; }
        .sidebar-nav { flex-grow: 1; }
        .sidebar-nav a { display: block; padding: 12px 15px; text-decoration: none; color: #444; border-radius: 8px; margin-bottom: 10px; font-weight: 400; transition: background 0.3s, color 0.3s; }
        .sidebar-nav a:hover { background: #f0f0f0; color: #000; }
        .sidebar-nav a.active { background: #fda085; color: white; font-weight: 600; }
        .sidebar-footer a { display: block; padding: 12px 15px; text-decoration: none; background: #ffebee; color: #c62828; border-radius: 8px; text-align: center; font-weight: 500; transition: background 0.3s; }
        .sidebar-footer a:hover { background: #ef9a9a; }

        /* --- Contenido Principal --- */
        .main-content { flex-grow: 1; padding: 40px; overflow-y: auto; /* Permite scroll si la tabla es larga */ }
        .main-content h1 { color: #333; font-size: 32px; margin-bottom: 20px; }

        /* --- Estilos de la Tabla (Como tu prototipo) --- */
        .product-table {
            width: 100%;
            border-collapse: collapse; /* Bordes unidos */
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden; /* Para que el radius funcione en las esquinas */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        .product-table th, .product-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        .product-table th {
            background: #fafafa;
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            font-size: 12px;
        }
        .product-table td {
            color: #333;
            font-size: 14px;
        }
        /* Estilos para el Stock */
        .stock-disponible { color: #2e7d32; font-weight: 600; }
        .stock-bajo { color: #f57c00; font-weight: 600; }
        .stock-agotado { color: #d32f2f; font-weight: 600; }
        
        .stock-note {
            margin-top: 15px;
            font-size: 12px;
            color: #777;
        }

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
                <a href="mozo_inicio.php">🏠 Inicio</a>
                <a href="mozo_consultar.php" class="active">🥤 Consultar Productos</a>
                <a href="mozo_registrar_pedido.php">📝 Realizar Pedido</a>
            </div>
            <div class="sidebar-footer">
                <a href="logout.php">🚪 Cerrar Sesión</a>
            </div>
        </nav>

        <main class="main-content">
            <h1>Lista de Productos Disponibles</h1>
            
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 4. Verificamos si hay productos
                    if ($resultado->num_rows > 0) {
                        // 5. Recorremos los resultados y los mostramos en la tabla
                        while($fila = $resultado->fetch_assoc()) {
                            
                            // Lógica de Stock (basada en la nota de tu prototipo)
                            $stock_texto = '';
                            $stock_clase = ''; // Clase CSS para el color
                            
                            if ($fila['stock'] == 0) {
                                $stock_texto = "Agotado";
                                $stock_clase = "stock-agotado";
                            } elseif ($fila['stock'] <= 10) { // Asumimos que "Bajo stock" es 10 o menos
                                $stock_texto = "Bajo stock (" . $fila['stock'] . ")";
                                $stock_clase = "stock-bajo";
                            } else { // Más de 10
                                $stock_texto = "Disponible";
                                $stock_clase = "stock-disponible";
                            }

                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($fila['nombre']) . "</td>";
                            echo "<td>S/ " . number_format($fila['precio'], 2) . "</td>";
                            echo "<td class='" . $stock_clase . "'>" . $stock_texto . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No hay productos registrados</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            
            <p class="stock-note">Nota: "Bajo stock" se muestra para 10 unidades o menos.</p>

        </main>
    </div>
</body>
</html>