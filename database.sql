-- Crear base de datos
CREATE DATABASE IF NOT EXISTS farmacia_db;
USE farmacia_db;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'vendedor') DEFAULT 'vendedor',
    avatar VARCHAR(255) DEFAULT 'default.png',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de productos/medicamentos
CREATE TABLE productos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    categoria VARCHAR(100),
    proveedor VARCHAR(100),
    precio_compra DECIMAL(10,2) NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    stock_minimo INT DEFAULT 10,
    lote VARCHAR(50),
    fecha_vencimiento DATE,
    imagen VARCHAR(255) DEFAULT 'producto.png',
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de ventas
CREATE TABLE ventas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    folio VARCHAR(20) UNIQUE NOT NULL,
    usuario_id INT,
    total DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') DEFAULT 'efectivo',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla de detalles de venta
CREATE TABLE detalle_ventas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    venta_id INT,
    producto_id INT,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Administrador', 'admin@farmacia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Contraseña: password

-- Insertar productos de ejemplo
INSERT INTO productos (codigo, nombre, descripcion, categoria, proveedor, precio_compra, precio_venta, stock, stock_minimo, lote, fecha_vencimiento) VALUES
('MED001', 'Paracetamol 500mg', 'Analgésico y antipirético', 'Analgésicos', 'Laboratorios ABC', 5.00, 10.00, 100, 20, 'L001', '2025-12-31'),
('MED002', 'Ibuprofeno 400mg', 'Antiinflamatorio', 'Antiinflamatorios', 'Laboratorios ABC', 8.00, 15.00, 80, 15, 'L002', '2025-10-31'),
('MED003', 'Amoxicilina 500mg', 'Antibiótico', 'Antibióticos', 'Laboratorios XYZ', 12.00, 25.00, 50, 10, 'L003', '2025-08-31'),
('MED004', 'Omeprazol 20mg', 'Protector gástrico', 'Gastrointestinales', 'Laboratorios XYZ', 6.00, 12.00, 120, 25, 'L004', '2025-11-30'),
('MED005', 'Losartán 50mg', 'Antihipertensivo', 'Cardiovasculares', 'Laboratorios DEF', 15.00, 30.00, 60, 15, 'L005', '2025-09-30');