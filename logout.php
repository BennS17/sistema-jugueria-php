<?php
// 1. Iniciamos la sesión
session_start();

// 2. Destruimos todas las variables de sesión
session_unset();

// 3. Destruimos la sesión
session_destroy();

// 4. Redirigimos al login
header("Location: login.php");
exit();
?>