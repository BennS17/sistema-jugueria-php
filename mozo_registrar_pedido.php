<?php
// 1. Iniciamos la sesión y verificamos (igual que siempre)
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'mozo') {
    header("Location: login.php");
    exit();
}
$nombre_usuario = $_SESSION['nombre_usuario'];
$id_usuario_mozo = $_SESSION['id_usuario']; // Guardamos el ID del mozo para el pedido

// 2. Incluimos la conexión a la base de datos
include 'conexion.php';

// 3. Obtenemos TODOS los productos
// (Solo mostraremos los que tienen stock, pero necesitamos todos para el JS)
$sql = "SELECT id_producto, nombre, precio, stock FROM productos ORDER BY nombre ASC";
$resultado = $conexion->query($sql);

$productos = [];
if ($resultado->num_rows > 0) {
    while($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila; // Guardamos todos los productos en un array
    }
}
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Realizar Pedido - Juguería Fiori</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* --- ESTILOS BÁSICOS Y DE BARRA LATERAL (Igual que antes) --- */
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
        .sidebar-footer a:hover { background: #ef9a9a; }
        .main-content { flex-grow: 1; padding: 40px; overflow-y: auto; }
        .main-content h1 { color: #333; font-size: 32px; margin-bottom: 20px; }

        /* --- ESTILOS ESPECIALES PARA ESTA PÁGINA --- */
        .pedido-container {
            display: flex;
            gap: 30px; /* Espacio entre la tabla y el resumen */
        }
        .tabla-productos {
            flex: 2; /* Ocupa 2/3 del espacio */
        }
        .resumen-pedido {
            flex: 1; /* Ocupa 1/3 del espacio */
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            height: fit-content; /* Se ajusta al contenido */
        }
        .resumen-pedido h3 {
            text-align: center;
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        /* Estilos de la Tabla (Como tu prototipo) */
        .product-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
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
        .stock-disponible { color: #2e7d32; }
        .stock-agotado { color: #d32f2f; }
        
        /* Estilo para el input de cantidad */
        .input-cantidad {
            width: 60px;
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .input-cantidad:disabled {
            background: #f5f5f5;
        }

        /* Estilos del Resumen */
        #lista-productos-seleccionados {
            margin-top: 15px;
            font-size: 14px;
            color: #333;
            min-height: 50px; /* Espacio mínimo */
        }
        #lista-productos-seleccionados div {
            margin-bottom: 5px;
        }
        .total-container {
            border-top: 2px solid #333;
            margin-top: 20px;
            padding-top: 15px;
            font-size: 20px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
        }
        /* Botones del formulario */
        .btn-submit {
            width: 100%;
            background: #28a745; /* Verde para confirmar */
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            margin-top: 15px;
        }
        .btn-cancel {
            width: 100%;
            background: #dc3545; /* Rojo para cancelar */
            color: white;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
            text-decoration: none;
            display: block;
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
                <a href="mozo_consultar.php">🥤 Consultar Productos</a>
                <a href="mozo_registrar_pedido.php" class="active">📝 Realizar Pedido</a>
            </div>
            <div class="sidebar-footer">
                <a href="logout.php">🚪 Cerrar Sesión</a>
            </div>
        </nav>

        <main class="main-content">
            <h1>Realizar Pedido</h1>
            
            <form id="form-pedido" action="procesar_pedido.php" method="POST">
                <div class="pedido-container">
                    
                    <div class="tabla-productos">
                        <table class="product-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (empty($productos)) {
                                    echo "<tr><td colspan='4'>No hay productos registrados</td></tr>";
                                } else {
                                    foreach ($productos as $prod) {
                                        $disponible = $prod['stock'] > 0;
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($prod['nombre']) . "</td>";
                                        echo "<td>S/ " . number_format($prod['precio'], 2) . "</td>";
                                        
                                        if ($disponible) {
                                            echo "<td class='stock-disponible'>SI (" . $prod['stock'] . ")</td>";
                                        } else {
                                            echo "<td class='stock-agotado'>NO</td>";
                                        }

                                        // --- Input de Cantidad ---
                                        // Guardamos los datos del producto en atributos 'data-*' para que JavaScript los lea
                                        echo '<td><input type="number" 
                                                       class="input-cantidad" 
                                                       name="cantidades[' . $prod['id_producto'] . ']" 
                                                       data-id_producto="' . $prod['id_producto'] . '"
                                                       data-nombre="' . htmlspecialchars($prod['nombre']) . '"
                                                       data-precio="' . $prod['precio'] . '"
                                                       data-stock="' . $prod['stock'] . '"
                                                       min="0" 
                                                       max="' . $prod['stock'] . '" 
                                                       value="0" ' .
                                                       ($disponible ? '' : 'disabled') . '></td>';
                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="resumen-pedido">
                        <h3>Productos Seleccionados:</h3>
                        <div id="lista-productos-seleccionados">
                            <p style="color:#888;">Añade productos de la lista...</p>
                        </div>
                        <div class="total-container">
                            <span>Total:</span>
                            <span id="total-pedido">S/ 0.00</span>
                        </div>
                        <button type="submit" class="btn-submit">Confirmar Pedido</button>
                        <a href="mozo_inicio.php" class="btn-cancel">Cancelar</a>
                    </div>

                </div>
            </form>

        </main>
    </div>

    <script>
        // 1. Esperamos a que todo el HTML esté cargado
        document.addEventListener('DOMContentLoaded', function() {
            
            // 2. Obtenemos todos los inputs de cantidad
            const inputsCantidad = document.querySelectorAll('.input-cantidad');
            
            // 3. Obtenemos los elementos del resumen
            const listaResumen = document.getElementById('lista-productos-seleccionados');
            const totalResumen = document.getElementById('total-pedido');
            const formPedido = document.getElementById('form-pedido');

            // 4. Creamos un objeto para guardar el pedido
            // Usará el ID del producto como llave: { 1: {nombre: 'Jugo', cantidad: 2, subtotal: 9.00}, ... }
            let pedido = {};

            // 5. Escuchamos por cambios en CUALQUIER input de cantidad
            inputsCantidad.forEach(function(input) {
                input.addEventListener('input', function() {
                    // Obtenemos los datos del producto desde los atributos 'data-*'
                    const id = this.dataset.id_producto;
                    const nombre = this.dataset.nombre;
                    const precio = parseFloat(this.dataset.precio);
                    const stock = parseInt(this.dataset.stock);
                    let cantidad = parseInt(this.value);

                    // Validamos la cantidad (no más que el stock, no menos de 0)
                    if (cantidad > stock) {
                        cantidad = stock;
                        this.value = stock;
                    } else if (cantidad < 0 || isNaN(cantidad)) {
                        cantidad = 0;
                        this.value = 0;
                    }

                    // Actualizamos nuestro objeto 'pedido'
                    if (cantidad > 0) {
                        pedido[id] = {
                            nombre: nombre,
                            cantidad: cantidad,
                            subtotal: cantidad * precio
                        };
                    } else {
                        // Si la cantidad es 0, lo borramos del pedido
                        delete pedido[id];
                    }
                    
                    // 6. Actualizamos el resumen visual
                    actualizarResumen();
                });
            });

            // 7. Función para redibujar el resumen
            function actualizarResumen() {
                // Limpiamos el resumen
                listaResumen.innerHTML = '';
                let granTotal = 0;

                // Verificamos si el pedido está vacío
                if (Object.keys(pedido).length === 0) {
                    listaResumen.innerHTML = '<p style="color:#888;">Añade productos de la lista...</p>';
                    totalResumen.textContent = 'S/ 0.00';
                    return; // Salimos de la función
                }

                // Recorremos el objeto 'pedido' y creamos el HTML
                for (let id in pedido) {
                    const item = pedido[id];
                    
                    // Creamos una línea: "Jugo de Papaya x2 = S/9.00"
                    const linea = document.createElement('div');
                    linea.textContent = `${item.nombre} x${item.cantidad} = S/ ${item.subtotal.toFixed(2)}`;
                    listaResumen.appendChild(linea);
                    
                    // Sumamos al gran total
                    granTotal += item.subtotal;
                }

                // Actualizamos el total en el HTML
                totalResumen.textContent = `S/ ${granTotal.toFixed(2)}`;
            }

            // 8. (Opcional pero recomendado) Validar antes de enviar
            formPedido.addEventListener('submit', function(event) {
                if (Object.keys(pedido).length === 0) {
                    alert('No has seleccionado ningún producto.');
                    event.preventDefault(); // Detiene el envío del formulario
                }
            });

        });
    </script>
    </body>
</html>