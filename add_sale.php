<?php
require_once 'config/db.php';
if(!isset($_SESSION['user_id'])) header('Location: login.php');

$error = '';
$success = '';
$productos = $pdo->query("SELECT id, nombre, precio_venta, stock FROM productos WHERE estado='activo' AND stock > 0 ORDER BY nombre")->fetchAll();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $producto_id = $_POST['producto_id'];
    $cantidad = $_POST['cantidad'];
    $metodo_pago = $_POST['metodo_pago'];
    
    // Obtener producto
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch();
    
    if(!$producto) {
        $error = 'Producto no encontrado';
    } elseif($cantidad > $producto['stock']) {
        $error = 'Stock insuficiente. Disponible: ' . $producto['stock'];
    } else {
        try {
            $pdo->beginTransaction();
            
            $total = $cantidad * $producto['precio_venta'];
            $folio = 'FAC-' . date('Ymd') . '-' . rand(1000, 9999);
            
            // Insertar venta
            $stmt = $pdo->prepare("INSERT INTO ventas (folio, usuario_id, total, metodo_pago) VALUES (?,?,?,?)");
            $stmt->execute([$folio, $_SESSION['user_id'], $total, $metodo_pago]);
            $venta_id = $pdo->lastInsertId();
            
            // Insertar detalle
            $stmt = $pdo->prepare("INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?,?,?,?,?)");
            $stmt->execute([$venta_id, $producto_id, $cantidad, $producto['precio_venta'], $total]);
            
            // Actualizar stock
            $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$cantidad, $producto_id]);
            
            $pdo->commit();
            $success = "✅ Venta realizada con éxito. Folio: $folio | Total: $" . number_format($total, 2);
        } catch(Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Venta Rápida</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">🏥 Farmacia</div>
        <nav>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="products.php">💊 Productos</a>
            <a href="sales.php" class="active">💰 Ventas</a>
            <a href="reports.php">📈 Reportes</a>
            <a href="logout.php">🚪 Cerrar Sesión</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h2>➕ Nueva Venta Rápida</h2>
            <a href="sales.php" class="btn-secondary">← Volver a Ventas</a>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label>Producto</label>
                    <select name="producto_id" required>
                        <option value="">Seleccionar producto...</option>
                        <?php foreach($productos as $p): ?>
                            <option value="<?php echo $p['id']; ?>">
                                <?php echo $p['nombre']; ?> - Stock: <?php echo $p['stock']; ?> - $<?php echo number_format($p['precio_venta'], 2); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Cantidad</label>
                    <input type="number" name="cantidad" min="1" required value="1">
                </div>
                
                <div class="form-group">
                    <label>Método de pago</label>
                    <select name="metodo_pago" required>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary">💰 Realizar Venta</button>
            </form>
        </div>
    </div>
</body>
</html>