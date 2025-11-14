<?php
// 1. Iniciamos la sesión
session_start();

// 2. Verificamos si el usuario es un GERENTE que ha iniciado sesión
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'gerente') {
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
    <title>Panel del Gerente - Juguería Fiori</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* --- ESTILOS BÁSICOS Y DE BARRA LATERAL (Idénticos a los otros paneles) --- */
        body { font-family: 'Poppins', sans-serif; margin: 0; background: #f4f7f6; }
        .container { display: flex; height: 100vh; }
        /* Barra Lateral */
        .sidebar { width: 250px; background: #ffffff; border-right: 1px solid #e0e0e0; display: flex; flex-direction: column; padding: 20px; }
        .sidebar-header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .sidebar-header h3 { color: #333; font-weight: 600; }
        .sidebar-header p { color: #666; font-size: 14px; }
        .sidebar-nav { flex-grow: 1; }
        .sidebar-nav a { display: block; padding: 12px 15px; text-decoration: none; color: #444; border-radius: 8px; margin-bottom: 10px; font-weight: 400; transition: background 0.3s, color 0.3s; }
        .sidebar-nav a:hover { background: #f0f0f0; color: #000; }
        .sidebar-nav a.active { background: #fda085; color: white; font-weight: 600; }
        .sidebar-footer a { display: block; padding: 12px 15px; text-decoration: none; background: #ffebee; color: #c62828; border-radius: 8px; text-align: center; font-weight: 500; transition: background 0.3s; }
        /* Contenido Principal */
        .main-content { flex-grow: 1; padding: 40px; }
        .main-content h1 { color: #333; font-size: 32px; margin-bottom: 20px; }
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
                <a href="gerente_inicio.php" class="active">🏠 Inicio</a>
                <a href="gerente_reportes.php">📈 Reporte de Ventas</a>
                <a href="gerente_reposicion.php">📦 Generar Reposición</a>
            </div>
            <div class="sidebar-footer">
                <a href="logout.php">🚪 Cerrar Sesión</a>
            </div>
        </nav>

        <main class="main-content">
            <h1>Panel de Control del Gerente</h1>
            <p>Selecciona una opción de la barra lateral para ver reportes o gestionar el inventario.</p>
        </main>
    </div>
</body>
</html>