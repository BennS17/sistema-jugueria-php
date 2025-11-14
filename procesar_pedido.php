<?php
session_start();
include 'conexion.php'; // Incluimos la conexión

// 1. Verificamos que sea un Mozo y que el método sea POST
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'mozo') {
    header("Location: login.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // Si no es POST, redirigimos
    header("Location: mozo_registrar_pedido.php");
    exit();
}

// 2. Obtenemos los datos del formulario
$cantidades = $_POST['cantidades']; // Esto es un array: [ 'id_producto' => 'cantidad' ]
$id_usuario_mozo = $_SESSION['id_usuario'];
$fecha_hora = date('Y-m-d H:i:s'); // Fecha y hora actual

$pedido_items = []; // Array para guardar los productos válidos
$total_pedido = 0;

// --- INICIAMOS TRANSACCIÓN ---
// Esto es VITAL. Si algo falla (ej. el INSERT en detalle_pedidos),
// se deshace el INSERT en 'pedidos'. O todo o nada.
$conexion->begin_transaction();

try {
    // 3. Recorremos los productos enviados para validarlos
    foreach ($cantidades as $id_producto => $cantidad) {
        $cantidad = (int)$cantidad;
        if ($cantidad > 0) {
            // Consultamos la BD para obtener el precio y stock ACTUAL
            // Esto es importante para evitar que alguien compre más del stock
            $sql_prod = "SELECT nombre, precio, stock FROM productos WHERE id_producto = ?";
            $stmt = $conexion->prepare($sql_prod);
            $stmt->bind_param("i", $id_producto);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows === 1) {
                $producto_db = $resultado->fetch_assoc();
                
                // Verificamos stock
                if ($cantidad > $producto_db['stock']) {
                    // Si no hay stock, ¡paramos todo!
                    throw new Exception("Stock insuficiente para: " . $producto_db['nombre']);
                }
                
                // Si todo está bien, lo añadimos al pedido
                $precio_unitario = $producto_db['precio'];
                $pedido_items[] = [
                    'id_producto' => $id_producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio_unitario
                ];
                $total_pedido += ($precio_unitario * $cantidad);
            }
            $stmt->close();
        }
    }

    // 4. Verificamos si hay algo que insertar
    if (empty($pedido_items)) {
        throw new Exception("No se seleccionaron productos válidos.");
    }

    // 5. INSERTAMOS EN LA TABLA 'pedidos' (la cabecera)
    $sql_pedido = "INSERT INTO pedidos (id_usuario_mozo, fecha_hora, total, estado) VALUES (?, ?, ?, 'pendiente')";
    $stmt_pedido = $conexion->prepare($sql_pedido);
    $stmt_pedido->bind_param("isd", $id_usuario_mozo, $fecha_hora, $total_pedido);
    $stmt_pedido->execute();
    
    // Obtenemos el ID del pedido que acabamos de crear
    $id_nuevo_pedido = $conexion->insert_id;
    $stmt_pedido->close();

    // 6. INSERTAMOS EN LA TABLA 'detalle_pedidos' (los productos)
    $sql_detalle = "INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    $stmt_detalle = $conexion->prepare($sql_detalle);

    foreach ($pedido_items as $item) {
        $stmt_detalle->bind_param("iiid", 
            $id_nuevo_pedido, 
            $item['id_producto'], 
            $item['cantidad'], 
            $item['precio_unitario']
        );
        $stmt_detalle->execute();
        
        // 7. ACTUALIZAMOS EL STOCK (¡Muy importante!)
        $sql_update_stock = "UPDATE productos SET stock = stock - ? WHERE id_producto = ?";
        $stmt_stock = $conexion->prepare($sql_update_stock);
        $stmt_stock->bind_param("ii", $item['cantidad'], $item['id_producto']);
        $stmt_stock->execute();
        $stmt_stock->close();
    }
    $stmt_detalle->close();

    // 8. Si todo salió bien, CONFIRMAMOS la transacción
    $conexion->commit();
    
    // Redirigimos a una página de éxito (puedes crear una)
    header("Location: mozo_inicio.php?exito=1");

} catch (Exception $e) {
    // 9. Si algo falló, DESHACEMOS la transacción
    $conexion->rollback();
    
    // Redirigimos a la página anterior con un mensaje de error
    // (En una app real, guardarías el error en una $_SESSION para mostrarlo)
    // echo "Error: " . $e->getMessage();
    header("Location: mozo_registrar_pedido.php?error=" . urlencode($e->getMessage()));
}

$conexion->close();
?>