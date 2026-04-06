<?php
require_once 'config/db.php';
if(!isset($_SESSION['user_id'])) header('Location: login.php');

$tipo = $_GET['tipo'] ?? 'ventas';
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Reporte de ventas
if($tipo == 'ventas') {
    $stmt = $pdo->prepare("
        SELECT v.*, u.nombre as vendedor, 
               (SELECT COUNT(*) FROM detalle_ventas WHERE venta_id = v.id) as productos_count
        FROM ventas v 
        JOIN usuarios u ON v.usuario_id = u.id 
        WHERE DATE(v.fecha) BETWEEN ? AND ?
        ORDER BY v.fecha DESC
    ");
    $stmt->execute([$fecha_inicio, $fecha_fin]);
    $ventas = $stmt->fetchAll();
    
    // Total de ventas
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total),0) as total FROM ventas WHERE DATE(fecha) BETWEEN ? AND ?");
    $stmt->execute([$fecha_inicio, $fecha_fin]);
    $total_ventas = $stmt->fetch()['total'];
}

// Reporte de productos
if($tipo == 'productos') {
    $productos = $pdo->query("SELECT * FROM productos ORDER BY stock ASC")->fetchAll();
}

// Reporte de productos por vencer
if($tipo == 'vencimientos') {
    $vencimientos = $pdo->prepare("
        SELECT * FROM productos 
        WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)
        ORDER BY fecha_vencimiento ASC
    ")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes - Sistema Farmacia</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">🏥 Farmacia</div>
        <nav>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="products.php">💊 Productos</a>
            <a href="sales.php">💰 Ventas</a>
            <a href="reports.php" class="active">📈 Reportes</a>
            <?php if($_SESSION['user_rol'] == 'admin'): ?>
            <a href="users.php">👥 Usuarios</a>
            <?php endif; ?>
            <a href="logout.php">🚪 Cerrar Sesión</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h2>📈 Reportes</h2>
        </div>
        
        <div class="report-tabs">
            <a href="?tipo=ventas" class="btn-<?php echo $tipo=='ventas'?'primary':'secondary'; ?>">📊 Ventas</a>
            <a href="?tipo=productos" class="btn-<?php echo $tipo=='productos'?'primary':'secondary'; ?>">💊 Productos</a>
            <a href="?tipo=vencimientos" class="btn-<?php echo $tipo=='vencimientos'?'primary':'secondary'; ?>">⚠️ Próximos a vencer</a>
        </div>
        
        <!-- Filtro de fechas para reporte de ventas -->
        <?php if($tipo == 'ventas'): ?>
        <div class="card">
            <form method="GET" class="filter-form">
                <input type="hidden" name="tipo" value="ventas">
                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha inicio</label>
                        <input type="date" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                    </div>
                    <div class="form-group">
                        <label>Fecha fin</label>
                        <input type="date" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn-primary">Filtrar</button>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Reporte de Ventas -->
        <?php if($tipo == 'ventas'): ?>
        <div class="card">
            <div class="report-summary">
                <h3>Resumen de ventas</h3>
                <p>Período: <?php echo date('d/m/Y', strtotime($fecha_inicio)); ?> - <?php echo date('d/m/Y', strtotime($fecha_fin)); ?></p>
                <p><strong>Total recaudado: $<?php echo number_format($total_ventas, 2); ?></strong></p>
                <p>Total ventas: <?php echo count($ventas); ?></p>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Vendedor</th>
                        <th>Productos</th>
                        <th>Total</th>
                        <th>Método</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($ventas as $v): ?>
                    <tr>
                        <td><?php echo $v['folio']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($v['fecha'])); ?></td>
                        <td><?php echo $v['vendedor']; ?></td>
                        <td><?php echo $v['productos_count']; ?></td>
                        <td>$<?php echo number_format($v['total'], 2); ?></td>
                        <td><?php echo $v['metodo_pago']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Reporte de Productos -->
        <?php if($tipo == 'productos'): ?>
        <div class="card">
            <h3>Inventario de productos</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Stock</th>
                        <th>Stock Mínimo</th>
                        <th>Precio Venta</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($productos as $p): ?>
                    <tr>
                        <td><?php echo $p['codigo']; ?></td>
                        <td><?php echo $p['nombre']; ?></td>
                        <td><?php echo $p['categoria']; ?></td>
                        <td><?php echo $p['stock']; ?></td>
                        <td><?php echo $p['stock_minimo']; ?></td>
                        <td>$<?php echo number_format($p['precio_venta'], 2); ?></td>
                        <td>
                            <?php if($p['stock'] <= $p['stock_minimo']): ?>
                                <span class="badge warning">Stock bajo</span>
                            <?php else: ?>
                                <span class="badge success">Normal</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Reporte de Vencimientos -->
        <?php if($tipo == 'vencimientos'): ?>
        <div class="card">
            <h3>Productos próximos a vencer (60 días)</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Lote</th>
                        <th>Stock</th>
                        <th>Fecha Vencimiento</th>
                        <th>Días restantes</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $vencimientos = $vencimientos ?? [];
                    foreach($vencimientos as $p): 
                        $dias = (strtotime($p['fecha_vencimiento']) - strtotime(date('Y-m-d'))) / 86400;
                    ?>
                    <tr>
                        <td><?php echo $p['nombre']; ?></td>
                        <td><?php echo $p['lote']; ?></td>
                        <td><?php echo $p['stock']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($p['fecha_vencimiento'])); ?></td>
                        <td><?php echo $dias; ?> días</td>
                        <td>
                            <?php if($dias <= 15): ?>
                                <span class="badge warning">Urgente</span>
                            <?php else: ?>
                                <span class="badge info">Próximo</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>