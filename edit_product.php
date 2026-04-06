<?php
require_once 'config/db.php';
if(!isset($_SESSION['user_id'])) header('Location: login.php');

$id = $_GET['id'] ?? 0;
$producto = null;

// Obtener producto
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch();

if(!$producto) {
    header('Location: products.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $categoria = $_POST['categoria'];
    $proveedor = $_POST['proveedor'];
    $precio_compra = $_POST['precio_compra'];
    $precio_venta = $_POST['precio_venta'];
    $stock = $_POST['stock'];
    $stock_minimo = $_POST['stock_minimo'];
    $lote = $_POST['lote'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    
    $sql = "UPDATE productos SET codigo=?, nombre=?, descripcion=?, categoria=?, proveedor=?, 
            precio_compra=?, precio_venta=?, stock=?, stock_minimo=?, lote=?, fecha_vencimiento=? 
            WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$codigo, $nombre, $descripcion, $categoria, $proveedor, $precio_compra, 
                   $precio_venta, $stock, $stock_minimo, $lote, $fecha_vencimiento, $id]);
    
    header('Location: products.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">🏥 Farmacia</div>
        <nav>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="products.php" class="active">💊 Productos</a>
            <a href="sales.php">💰 Ventas</a>
            <a href="reports.php">📈 Reportes</a>
            <?php if($_SESSION['user_rol'] == 'admin'): ?>
            <a href="users.php">👥 Usuarios</a>
            <?php endif; ?>
            <a href="logout.php">🚪 Cerrar Sesión</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h2>✏️ Editar Producto</h2>
            <a href="products.php" class="btn-secondary">← Volver</a>
        </div>
        
        <div class="card">
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Código *</label>
                        <input type="text" name="codigo" required value="<?php echo htmlspecialchars($producto['codigo']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" required value="<?php echo htmlspecialchars($producto['nombre']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="3"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Categoría</label>
                        <input type="text" name="categoria" value="<?php echo htmlspecialchars($producto['categoria']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Proveedor</label>
                        <input type="text" name="proveedor" value="<?php echo htmlspecialchars($producto['proveedor']); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Precio Compra</label>
                        <input type="number" step="0.01" name="precio_compra" required value="<?php echo $producto['precio_compra']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Precio Venta</label>
                        <input type="number" step="0.01" name="precio_venta" required value="<?php echo $producto['precio_venta']; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="stock" value="<?php echo $producto['stock']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Stock Mínimo</label>
                        <input type="number" name="stock_minimo" value="<?php echo $producto['stock_minimo']; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Lote</label>
                        <input type="text" name="lote" value="<?php echo htmlspecialchars($producto['lote']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Fecha Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" value="<?php echo $producto['fecha_vencimiento']; ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">💾 Actualizar Producto</button>
            </form>
        </div>
    </div>
</body>
</html>