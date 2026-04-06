<?php
require_once 'config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } else {
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'El email ya está registrado';
        } else {
            // Registrar usuario
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'vendedor')");
            if ($stmt->execute([$nombre, $email, $hashed_password])) {
                $success = 'Registro exitoso. Ahora puedes iniciar sesión.';
                // Limpiar campos
                $nombre = $email = '';
            } else {
                $error = 'Error al registrar usuario';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sistema Farmacia</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>📝 Crear Cuenta</h1>
                <p>Regístrate para comenzar</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>👤 Nombre completo</label>
                    <input type="text" name="nombre" required value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>📧 Email</label>
                    <input type="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>🔒 Contraseña</label>
                    <input type="password" name="password" required>
                    <small>Mínimo 6 caracteres</small>
                </div>
                <div class="form-group">
                    <label>🔒 Confirmar contraseña</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-login">Registrarse</button>
            </form>
            <div class="login-footer">
                <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
            </div>
        </div>
    </div>
</body>
</html>