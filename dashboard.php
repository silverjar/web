<?php
require_once 'config/db.php';
if(!isset($_SESSION['user_id'])) header('Location: login.php');

// Estadísticas
$total_productos = $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
$stock_bajo = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock <= stock_minimo")->fetchColumn();
$ventas_hoy = $pdo->query("SELECT COUNT(*) FROM ventas WHERE DATE(fecha) = CURDATE()")->fetchColumn();
$ventas_mes = $pdo->query("SELECT COALESCE(SUM(total),0) FROM ventas WHERE MONTH(fecha) = MONTH(CURDATE())")->fetchColumn();

// Productos próximos a vencer
$vencimientos = $pdo->query("SELECT * FROM productos WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY fecha_vencimiento LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Farmacia</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">🏥 Farmacia</div>
        <nav>
            <a href="dashboard.php" class="active">📊 Dashboard</a>
            <a href="products.php">💊 Productos</a>
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
            <h2>Bienvenido, <?php echo $_SESSION['user_name']; ?></h2>
            <small>Rol: <?php echo $_SESSION['user_rol']; ?></small>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">💊</div>
                <div class="stat-info">
                    <h3><?php echo $total_productos; ?></h3>
                    <p>Productos</p>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">⚠️</div>
                <div class="stat-info">
                    <h3><?php echo $stock_bajo; ?></h3>
                    <p>Stock Bajo</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🛒</div>
                <div class="stat-info">
                    <h3><?php echo $ventas_hoy; ?></h3>
                    <p>Ventas Hoy</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>$<?php echo number_format($ventas_mes, 2); ?></h3>
                    <p>Ventas del Mes</p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h3>📅 Productos por vencer (30 días)</h3>
            <table class="data-table">
                <thead>
                    <tr><th>Producto</th><th>Lote</th><th>Vencimiento</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    <?php foreach($vencimientos as $p): ?>
                    <tr>
                        <td><?php echo $p['nombre']; ?></td>
                        <td><?php echo $p['lote']; ?></td>
                        <td><?php echo $p['fecha_vencimiento']; ?></td>
                        <td><span class="badge warning">Próximo a vencer</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>