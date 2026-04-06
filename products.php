<?php
require_once 'config/db.php';
if(!isset($_SESSION['user_id'])) header('Location: login.php');

// Obtener productos
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM productos WHERE nombre LIKE ? OR codigo LIKE ? ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%", "%$search%"]);
$productos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos - Sistema Farmacia</title>
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
            <a href="logout.php">🚪 Cerrar Sesión</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h2>Gestión de Productos</h2>
            <a href="add_product.php" class="btn-primary">+ Nuevo Producto</a>
        </div>
        
        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="Buscar por nombre o código..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">🔍 Buscar</button>
            </form>
        </div>
        
        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Stock</th>
                        <th>Precio Venta</th>
                        <th>Vencimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($productos as $p): ?>
                    <tr>
                        <td><?php echo $p['codigo']; ?></td>
                        <td><?php echo $p['nombre']; ?></td>
                        <td><?php echo $p['categoria']; ?></td>
                        <td>
                            <?php echo $p['stock']; ?>
                            <?php if($p['stock'] <= $p['stock_minimo']): ?>
                                <span class="badge warning">Stock bajo</span>
                            <?php endif; ?>
                        </td>
                        <td>$<?php echo number_format($p['precio_venta'], 2); ?></td>
                        <td><?php echo $p['fecha_vencimiento']; ?></td>
                        <td>
                            <a href="edit_product.php?id=<?php echo $p['id']; ?>" class="btn-edit">✏️</a>
                            <a href="delete_product.php?id=<?php echo $p['id']; ?>" class="btn-delete" onclick="return confirm('¿Eliminar?')">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>