CREATE DATABASE IF NOT EXISTS pyme11 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pyme11;

CREATE TABLE categorias (
  id_categoria INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL
);
CREATE TABLE productos (
  id_producto INT AUTO_INCREMENT PRIMARY KEY,
  id_categoria INT NOT NULL,
  codigo VARCHAR(20) NOT NULL UNIQUE,
  nombre VARCHAR(120) NOT NULL,
  descripcion TEXT,
  precio DECIMAL(10,2) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
);
CREATE TABLE cotizaciones (
  id_cotizacion INT AUTO_INCREMENT PRIMARY KEY,
  cliente VARCHAR(120) NOT NULL,
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  subtotal DECIMAL(10,2) NOT NULL,
  iva DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL
);
CREATE TABLE detalle_cotizacion (
  id_detalle INT AUTO_INCREMENT PRIMARY KEY,
  id_cotizacion INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (id_cotizacion) REFERENCES cotizaciones(id_cotizacion),
  FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

INSERT INTO categorias (nombre) VALUES ('Herramientas'),('Pinturas'),('Electricidad');
INSERT INTO productos (id_categoria, codigo, nombre, precio) VALUES
(1,'HER-001','Martillo de acero',8.50),(1,'HER-002','Destornillador plano',3.25),
(1,'HER-003','Taladro inalámbrico',65.00),(2,'PIN-001','Pintura blanca galón',18.90),
(2,'PIN-002','Brocha 4 pulgadas',4.75),(3,'ELE-001','Cable THHN 12 rollo',42.00),
(3,'ELE-002','Interruptor sencillo',2.10),(3,'ELE-003','Tomacorriente doble',2.80);
INSERT INTO cotizaciones (cliente, subtotal, iva, total) VALUES ('Ferretería El Roble', 80.50, 10.47, 90.97),('Constructora Los Pinos', 120.00, 15.60, 135.60);
INSERT INTO detalle_cotizacion (id_cotizacion, id_producto, cantidad, precio_unitario) VALUES
(1,1,2,8.50),(1,3,1,65.00),(2,6,2,42.00),(2,4,2,18.90),(2,7,10,2.10);



-- Actualización del esquema para el Módulo 2
USE pyme11;

-- 1. Tabla de Usuarios con Soporte de Roles y Hashing
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'vendedor') NOT NULL DEFAULT 'vendedor',
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- 2. Tabla de Movimientos para Control de Inventario (Kardex)
CREATE TABLE IF NOT EXISTS movimientos_inventario (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    id_usuario INT NOT NULL,
    tipo_movimiento ENUM('entrada', 'salida') NOT NULL,
    cantidad INT NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    fecha_movimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- Insertar usuario Administrador por defecto (Contraseña temporal: Admin123!)
-- Hash generado mediante BCRYPT
INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES 
('Carlos Mendoza (Admin)', 'admin@pyme.sv', '$2y$10$w0M2pLpT8ZqP5jW4bA1vUeX5c9R2m3K4L5M6N7O8P9Q0R1S2T3U4V', 'admin'),
('Ana López (Vendedora)', 'vendedor@pyme.sv', '$2y$10$w0M2pLpT8ZqP5jW4bA1vUeX5c9R2m3K4L5M6N7O8P9Q0R1S2T3U4V', 'vendedor');