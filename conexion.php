<?php
// Datos de tu base de datos (¡asegúrate de que sean correctos!)
$servidor = "localhost";    // El servidor donde está la BD
$usuario_db = "root";       // El usuario de la BD (por defecto en XAMPP es 'root')
$password_db = "";          // La contraseña de la BD (por defecto en XAMPP está vacía)
$basedatos = "db_jugueria"; // El nombre de tu base de datos
$puerto = 3306;             // El puerto que configuraste

// 1. Creamos la conexión
$conexion = new mysqli($servidor, $usuario_db, $password_db, $basedatos, $puerto);

// 2. Verificamos la conexión
if ($conexion->connect_error) {
    // Si la conexión falla, detenemos la ejecución y mostramos el error
    die("Conexión fallida: " . $conexion->connect_error);
}

// Opcional: Si la conexión es exitosa, puedes descomentar la siguiente línea para probar
// echo "Conexión exitosa"; 
?>