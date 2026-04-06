<?php
require_once 'config/db.php';
if(!isset($_SESSION['user_id'])) header('Location: login.php');

// Obtener productos para el carrito
$productos = $pdo->query("SELECT id, nombre, codigo, precio_venta, stock FROM productos WHERE estado='activo' ORDER BY nombre")->fetchAll();

// Procesar venta
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['productos'])) {
    try {
        $pdo->beginTransaction();
        
        $productos_json = json_decode($_POST['productos'], true);
        $total = $_POST['total'];
        $metodo_pago = $_POST['metodo_pago'];
        
        // Generar folio único
        $folio = 'FAC-' . date('Ymd') . '-' . rand(1000, 9999);
        
        // Insertar venta
        $stmt = $pdo->prepare("INSERT INTO ventas (folio, usuario_id, total, metodo_pago) VALUES (?,?,?,?)");
        $stmt->execute([$folio, $_SESSION['user_id'], $total, $metodo_pago]);
        $venta_id = $pdo->lastInsertId();
        
        // Insertar detalles y actualizar stock
        foreach($productos_json as $item) {
            $stmt = $pdo->prepare("INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?,?,?,?,?)");
            $stmt->execute([$venta_id, $item['id'], $item['cantidad'], $item['precio'], $item['subtotal']]);
            
            // Actualizar stock
            $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['cantidad'], $item['id']]);
        }
        
        $pdo->commit();
        $mensaje = "✅ Venta registrada con éxito. Folio: $folio";
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = "Error al registrar la venta: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ventas - Sistema Farmacia</title>
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
            <h2>💰 Punto de Venta</h2>
        </div>
        
        <?php if(isset($mensaje)): ?>
            <div class="alert" style="background:#d4edda; color:#155724;"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <div class="pos-container">
            <div class="pos-products">
                <h3>Productos</h3>
                <input type="text" id="searchProduct" placeholder="🔍 Buscar producto...">
                <div class="product-list" id="productList">
                    <?php foreach($productos as $p): ?>
                        <div class="product-item" data-id="<?php echo $p['id']; ?>" data-name="<?php echo $p['nombre']; ?>" data-price="<?php echo $p['precio_venta']; ?>" data-stock="<?php echo $p['stock']; ?>">
                            <strong><?php echo $p['nombre']; ?></strong>
                            <small><?php echo $p['codigo']; ?></small>
                            <span>$<?php echo number_format($p['precio_venta'], 2); ?></span>
                            <span>Stock: <?php echo $p['stock']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="pos-cart">
                <h3>🛒 Carrito de compras</h3>
                <div id="cartItems"></div>
                <div class="cart-total">
                    <strong>Total: $<span id="cartTotal">0.00</span></strong>
                </div>
                <form method="POST" id="saleForm">
                    <input type="hidden" name="productos" id="productosInput">
                    <input type="hidden" name="total" id="totalInput">
                    <select name="metodo_pago" required>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                    <button type="submit" class="btn-primary">✅ Realizar Venta</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        let cart = [];
        
        function addToCart(product) {
            let existing = cart.find(item => item.id === product.id);
            if(existing) {
                if(existing.cantidad < product.stock) {
                    existing.cantidad++;
                    existing.subtotal = existing.cantidad * existing.precio;
                } else {
                    alert('Stock insuficiente');
                }
            } else {
                cart.push({
                    id: product.id,
                    nombre: product.nombre,
                    precio: product.price,
                    cantidad: 1,
                    subtotal: product.price,
                    stock: product.stock
                });
            }
            updateCart();
        }
        
        function updateCart() {
            let cartHtml = '<table><tr><th>Producto</th><th>Cantidad</th><th>Subtotal</th><th></th></tr>';
            let total = 0;
            cart.forEach((item, index) => {
                total += item.subtotal;
                cartHtml += `<tr>
                    <td>${item.nombre}</td>
                    <td>
                        <button onclick="changeQuantity(${index}, -1)">-</button>
                        ${item.cantidad}
                        <button onclick="changeQuantity(${index}, 1)">+</button>
                    </td>
                    <td>$${item.subtotal.toFixed(2)}</td>
                    <td><button onclick="removeFromCart(${index})">🗑️</button></td>
                </tr>`;
            });
            cartHtml += '</table>';
            document.getElementById('cartItems').innerHTML = cartHtml;
            document.getElementById('cartTotal').innerText = total.toFixed(2);
            document.getElementById('totalInput').value = total;
            document.getElementById('productosInput').value = JSON.stringify(cart);
        }
        
        function changeQuantity(index, change) {
            let item = cart[index];
            let newCantidad = item.cantidad + change;
            if(newCantidad > 0 && newCantidad <= item.stock) {
                item.cantidad = newCantidad;
                item.subtotal = item.cantidad * item.precio;
                updateCart();
            }
        }
        
        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCart();
        }
        
        document.querySelectorAll('.product-item').forEach(el => {
            el.addEventListener('click', () => {
                addToCart({
                    id: parseInt(el.dataset.id),
                    nombre: el.dataset.name,
                    price: parseFloat(el.dataset.price),
                    stock: parseInt(el.dataset.stock)
                });
            });
        });
        
        document.getElementById('searchProduct').addEventListener('input', (e) => {
            let term = e.target.value.toLowerCase();
            document.querySelectorAll('.product-item').forEach(el => {
                let name = el.dataset.name.toLowerCase();
                el.style.display = name.includes(term) ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>