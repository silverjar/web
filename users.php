<?php
require_once 'config/db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Obtener usuarios
$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC")->fetchAll();

// Cambiar rol
if(isset($_GET['cambiar_rol']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $nuevo_rol = $_GET['cambiar_rol'];
    $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
    $stmt->execute([$nuevo_rol, $id]);
    header('Location: users.php');
    exit;
}

// Eliminar usuario
if(isset($_GET['eliminar']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    // No permitir eliminar al propio usuario
    if($id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: users.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">🏥 Farmacia</div>
        <nav>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="products.php">💊 Productos</a>
            <a href="sales.php">💰 Ventas</a>
            <a href="reports.php">📈 Reportes</a>
            <a href="users.php" class="active">👥 Usuarios</a>
            <a href="logout.php">🚪 Cerrar Sesión</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h2>👥 Gestión de Usuarios</h2>
            <a href="register.php" class="btn-primary">+ Nuevo Usuario</a>
        </div>
        
        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Fecha registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($usuarios as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $u['rol'] == 'admin' ? 'admin' : 'vendedor'; ?>">
                                <?php echo $u['rol'] == 'admin' ? 'Administrador' : 'Vendedor'; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($u['fecha_registro'])); ?></td>
                        <td>
                            <?php if($u['id'] != $_SESSION['user_id']): ?>
                                <?php if($u['rol'] == 'admin'): ?>
                                    <a href="?cambiar_rol=vendedor&id=<?php echo $u['id']; ?>" class="btn-edit">⬇️ Hacer Vendedor</a>
                                <?php else: ?>
                                    <a href="?cambiar_rol=admin&id=<?php echo $u['id']; ?>" class="btn-edit">⬆️ Hacer Admin</a>
                                <?php endif; ?>
                                <a href="?eliminar=1&id=<?php echo $u['id']; ?>" class="btn-delete" onclick="return confirm('¿Eliminar este usuario?')">🗑️</a>
                            <?php else: ?>
                                <span class="badge info">Tú</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>