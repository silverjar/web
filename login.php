<?php
require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_rol'] = $user['rol'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Email o contraseña incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Farmacia</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>🏥 Sistema Farmacia</h1>
                <p>Inicia sesión para continuar</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>📧 Email</label>
                    <input type="email" name="email" required placeholder="admin@farmacia.com">
                </div>
                <div class="form-group">
                    <label>🔒 Contraseña</label>
                    <input type="password" name="password" required placeholder="password">
                </div>
                <button type="submit" class="btn-login">Ingresar</button>
            </form>
            <div class="login-footer">
                <small>Credenciales: admin@farmacia.com / password</small>
            </div>
        </div>
    </div>
</body>
</html>