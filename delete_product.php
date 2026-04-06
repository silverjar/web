<?php
require_once 'config/db.php';
if(!isset($_SESSION['user_id'])) header('Location: login.php');

// Solo admin puede eliminar
if($_SESSION['user_rol'] != 'admin') {
    header('Location: products.php');
    exit;
}

$id = $_GET['id'] ?? 0;

if($id) {
    // Verificar si el producto existe
    $stmt = $pdo->prepare("SELECT id FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    if($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header('Location: products.php');
exit;
?>