<?php
// 1. Iniciamos la sesión
// Esto es OBLIGATORIO para poder usar variables de sesión (como $_SESSION)
session_start();

// 2. Incluimos el archivo de conexión
include 'conexion.php';

// 3. Recibimos los datos del formulario
// Usamos $_POST porque el formulario usó method="POST"
$usuario_form = $_POST['usuario'];
$password_form = $_POST['contrasena'];

// --- IMPORTANTE: Seguridad ---
// 4. Preparamos la consulta para evitar Inyección SQL
// NUNCA insertes las variables $usuario_form y $password_form directamente en el SQL.
// Usamos '?' como marcadores de posición.
$sql = "SELECT id_usuario, rol, password FROM usuarios WHERE nombre_usuario = ?";
$stmt = $conexion->prepare($sql);

// 5. Vinculamos los parámetros
// "s" significa que el parámetro es un String (texto)
$stmt->bind_param("s", $usuario_form);

// 6. Ejecutamos la consulta
$stmt->execute();
$resultado = $stmt->get_result();

// 7. Verificamos si encontramos un usuario
if ($resultado->num_rows === 1) {
    // Usuario encontrado. Ahora verificamos la contraseña.
    $usuario_db = $resultado->fetch_assoc();
    
    // Comparamos la contraseña del formulario con la de la BD
    // (En tu caso, es texto plano. En un proyecto real, aquí se usaría password_verify())
    if ($password_form === $usuario_db['password']) {
        
        // ¡Contraseña correcta!
        // 8. Guardamos los datos del usuario en la sesión
        $_SESSION['id_usuario'] = $usuario_db['id_usuario'];
        $_SESSION['rol'] = $usuario_db['rol'];
        $_SESSION['nombre_usuario'] = $usuario_form; // Guardamos el nombre para mostrarlo
        
        // 9. Redirigimos al usuario según su ROL
        // (Esto es de tu "Diagrama de Requerimientos")
        if ($usuario_db['rol'] == 'mozo') {
            header("Location: mozo_inicio.php");
        } elseif ($usuario_db['rol'] == 'cajero') {
            header("Location: cajero_inicio.php");
        } elseif ($usuario_db['rol'] == 'gerente') {
            header("Location: gerente_inicio.php");
        } else {
            // Rol no reconocido, lo mandamos al login con error
            header("Location: login.php?error=1");
        }
        
    } else {
        // Contraseña incorrecta
        header("Location: login.php?error=1");
    }
    
} else {
    // Usuario no encontrado
    header("Location: login.php?error=1");
}

// 10. Cerramos la conexión y el statement
$stmt->close();
$conexion->close();
?>