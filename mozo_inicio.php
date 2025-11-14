<?php
// 1. Iniciamos la sesión
session_start();

// 2. Verificamos si el usuario es un mozo que ha iniciado sesión
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'mozo') {
    header("Location: login.php"); // Redirigir al login si no cumple
    exit();
}

// Si todo está bien, le damos la bienvenida
$nombre_usuario = $_SESSION['nombre_usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Mozo - Juguería Fiori</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Estilos base */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: #f4f7f6;
        }
        .container {
            display: flex;
            height: 100vh;
        }

        /* --- Barra Lateral (Sidebar) --- */
        .sidebar {
            width: 250px;
            background: #ffffff;
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }
        .sidebar-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .sidebar-header h3 {
            color: #333;
            font-weight: 600;
        }
        .sidebar-header p {
            color: #666;
            font-size: 14px;
        }
        .sidebar-nav {
            flex-grow: 1; /* Empuja el "Cerrar Sesión" hacia abajo */
        }
        .sidebar-nav a {
            display: block;
            padding: 12px 15px;
            text-decoration: none;
            color: #444;
            border-radius: 8px;
            margin-bottom: 10px;
            font-weight: 400;
            transition: background 0.3s, color 0.3s;
        }
        .sidebar-nav a:hover {
            background: #f0f0f0;
            color: #000;
        }
        /* Estilo para el enlace activo */
        .sidebar-nav a.active {
            background: #fda085; /* Color de la juguería */
            color: white;
            font-weight: 600;
        }
        .sidebar-footer a {
            display: block;
            padding: 12px 15px;
            text-decoration: none;
            background: #ffebee; /* Rojo pálido */
            color: #c62828; /* Rojo oscuro */
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
            transition: background 0.3s;
        }
        .sidebar-footer a:hover {
            background: #ef9a9a;
        }

        /* --- Contenido Principal --- */
        .main-content {
            flex-grow: 1; /* Ocupa el resto del espacio */
            padding: 40px;
        }
        .main-content h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 20px;
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
                <a href="mozo_inicio.php" class="active">🏠 Inicio</a>
                <a href="mozo_consultar.php">🥤 Consultar Productos</a>
                <a href="mozo_registrar_pedido.php">📝 Realizar Pedido</a>
            </div>
            <div class="sidebar-footer">
                <a href="logout.php">🚪 Cerrar Sesión</a>
            </div>
        </nav>

        <main class="main-content">
            <h1>Panel de Control del Mozo</h1>
            <p>Selecciona una opción de la barra lateral para comenzar.</p>
            </main>
    </div>
</body>
</html>