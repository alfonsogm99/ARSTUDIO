-- CREATE DATABASE AR_BARBER_PROJECT;
-- USE AR_BARBER_PROJECT;

-- =========================
-- USUARIOS
-- =========================
CREATE TABLE usuario (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  rol ENUM('admin','barbero','cliente') NOT NULL,
  activo BOOLEAN DEFAULT TRUE,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- ADMIN
-- =========================
CREATE TABLE admin (
  id_admin INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT UNIQUE,

  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
  ON DELETE CASCADE
);

-- =========================
-- CLIENTES (AUTH Google)
-- =========================
CREATE TABLE cliente (
  id_cliente INT AUTO_INCREMENT PRIMARY KEY,
  google_id VARCHAR(100) UNIQUE, -- ID Google OAuth
  nombre VARCHAR(100) NOT NULL,
  dni CHAR(9) NOT NULL UNIQUE,
  email VARCHAR(100),
  prefijo_pais VARCHAR(6) DEFAULT '+34',
  telefono VARCHAR(20),
  observaciones TEXT,
  fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,

  id_usuario INT UNIQUE,

  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
  ON DELETE CASCADE
);

CREATE TABLE activacion_usuario (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  token VARCHAR(64) NOT NULL,
  expiracion DATETIME NOT NULL,
  usado BOOLEAN DEFAULT FALSE,

  FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
  ON DELETE CASCADE
);

-- =========================
-- BARBEROS
-- =========================
CREATE TABLE barbero (
  id_barbero INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  especialidad VARCHAR(100),
  activo BOOLEAN DEFAULT TRUE,

  id_usuario INT UNIQUE,

  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
  ON DELETE CASCADE
);

-- =========================
-- SERVICIOS
-- =========================
CREATE TABLE servicio (
  id_servicio INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  precio DECIMAL(6,2) NOT NULL,
  duracion INT NOT NULL -- min
);

-- =========================
-- CITAS
-- =========================
CREATE TABLE cita (
  id_cita INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  id_barbero INT,
  fecha DATE NOT NULL,
  hora TIME NOT NULL,
  estado ENUM('pendiente','confirmada','completada','cancelada','fallida') DEFAULT 'pendiente',
  estado_pago ENUM('pendiente','pagado') DEFAULT 'pendiente',
  notas TEXT,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  duracion_total INT NOT NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,


  UNIQUE (id_barbero, fecha, hora),

  FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
    ON DELETE CASCADE,
  FOREIGN KEY (id_barbero) REFERENCES barbero(id_barbero)
    ON DELETE SET NULL
);

-- =========================
-- DETALLE DE CITA (N:M)
-- =========================
CREATE TABLE detalle_cita (
  id_detalle INT AUTO_INCREMENT PRIMARY KEY,
  id_cita INT NOT NULL,
  id_servicio INT NOT NULL,
  precio_aplicado DECIMAL(6,2), -- 00.00€

  FOREIGN KEY (id_cita) REFERENCES cita(id_cita)
    ON DELETE CASCADE,
  FOREIGN KEY (id_servicio) REFERENCES servicio(id_servicio)
);

-- =========================
-- PAGOS
-- =========================
CREATE TABLE pago (
  id_pago INT AUTO_INCREMENT PRIMARY KEY,
  id_cita INT NOT NULL,
  total DECIMAL(8,2) NOT NULL,
  metodo ENUM('efectivo','tarjeta','bizum'),
  fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (id_cita) REFERENCES cita(id_cita)
    ON DELETE CASCADE
);

-- =========================
-- HISTÓRICO DE CLIENTES
-- =========================
CREATE TABLE historico_cliente (
  id_historico INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  id_cita INT,
  descripcion TEXT,
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
    ON DELETE CASCADE,
  FOREIGN KEY (id_cita) REFERENCES cita(id_cita)
    ON DELETE SET NULL
);

-- =========================
-- ÍNDICES (mejora rendimiento)
-- =========================
CREATE INDEX idx_cliente_google ON cliente(google_id);
CREATE INDEX idx_cita_fecha ON cita(fecha);
CREATE INDEX idx_cita_cliente ON cita(id_cliente);


-- =========================
-- REGISTROS (LOGS)
-- =========================
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tabla VARCHAR(100) NOT NULL,
    accion VARCHAR(20) NOT NULL,   -- INSERT / UPDATE / DELETE
    registro_id INT DEFAULT NULL,
    usuario VARCHAR(100) DEFAULT NULL,
    datos_antes LONGTEXT DEFAULT NULL,
    datos_despues LONGTEXT DEFAULT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);