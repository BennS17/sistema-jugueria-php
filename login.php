<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-Http-Equiv_Content-Type">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Juguería Fiori</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(120deg, #f6d365 0%, #fda085 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden; 
        }

        .login-container {
            background: #ffffff;
            width: 400px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden; /* Clave para que la imagen se ajuste a las esquinas */
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- NUEVO: Cabecera de la Imagen --- */
        .login-header-image {
            width: 100%;
            height: 200px; /* Define una altura para la imagen */
            overflow: hidden; /* Se asegura que la imagen no se desborde */
        }

        .login-header-image img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Hace que la imagen cubra el espacio sin distorsionarse */
        }
        /* --- FIN NUEVO --- */


        .login-form {
            padding: 30px;
        }
        
        /* --- MODIFICADO: Título movido al cuerpo del formulario --- */
        .login-form h2 {
            text-align: center;
            color: #333;
            font-weight: 600;
            font-size: 24px;
            margin-bottom: 20px; /* Espacio debajo del título */
        }
        /* --- FIN MODIFICADO --- */

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-weight: 400;
            color: #555;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #fda085; 
            box-shadow: 0 0 0 3px rgba(253, 160, 133, 0.2);
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(to right, #f6d365, #fda085);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(253, 160, 133, 0.4);
        }

        .error-message {
            background: #ffebee; 
            color: #c62828; 
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #ef9a9a;
        }

    </style>
</head>
<body>

    <div class="login-container">
        
        <div class="login-header-image">
            <img src="https://images.pexels.com/photos/109275/pexels-photo-109275.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="Juguería">
        </div>

        <form class="login-form" action="validar_login.php" method="POST">
            
            <h2>JUGUERÍA FIORI</h2>

            <?php
            ini_set('display_errors', 1);
            error_reporting(E_ALL);

            if (isset($_GET['error']) && $_GET['error'] == 1) {
                echo '<div class="error-message">Usuario o contraseña incorrectos.</div>';
            }
            ?>

            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" placeholder="Ingrese su usuario" required>
            </div>
            
            <div class="form-group">
                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" placeholder="Ingrese su contraseña" required>
            </div>
            
            <button type="submit" class="btn-submit">INICIAR SESIÓN</button>
        </form>
    </div>

</body>
</html>