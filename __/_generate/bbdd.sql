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

-- ALTER TABLE usuario
-- ADD COLUMN email VARCHAR(255),
-- ADD COLUMN phone VARCHAR(20),
-- ADD COLUMN otp_code VARCHAR(6),
-- ADD COLUMN otp_expires DATETIME,
-- ADD COLUMN status ENUM('pending','verified','active','rejected') DEFAULT 'pending';

-- ALTER TABLE usuario
-- ADD CONSTRAINT chk_username
-- CHECK (
--    username REGEXP '^[A-Za-z0-9_.-]{3,30}$'
--);

-- ALTER TABLE usuario
-- ADD COLUMN estado_cliente ENUM(
--    'pendiente_vinculacion',
--    'vinculado',
--    'eliminado',
--    'descartado'
-- ) DEFAULT 'pendiente_vinculacion';

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

/* ALTER TABLE cliente DROP COLUMN dni; */

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

--
-- INSERT INTO servicio (nombre, precio, duracion)
-- VALUES ('free-30min', '0', '30');

-- INSERT INTO servicio (nombre, precio, duracion)
-- VALUES ('free-60min', '0', '60');


-- =========================
-- CITAS
-- =========================
CREATE TABLE cita (
  id_cita INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  id_barbero INT,
  fecha DATE NOT NULL,
  hora TIME NOT NULL,
  estado ENUM('pendiente','confirmada','completada','cancelada','fallida', 'pendiente_cancelacion') DEFAULT 'pendiente',
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

-- ALTER TABLE cita MODIFY COLUMN estado ENUM('pendiente', 'confirmada', 'completada', 'cancelada', 'fallida', 'pendiente_cancelacion') DEFAULT 'pendiente';

-- ALTER TABLE cita
-- ADD id_servicio INT NOT NULL;

-- =========================
-- HORARIO
-- =========================
CREATE TABLE horario_barbero (
    id_horario INT AUTO_INCREMENT PRIMARY KEY,
    id_barbero INT NOT NULL,
    dia_semana ENUM('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    pausa_inicio TIME DEFAULT NULL,
    pausa_fin TIME DEFAULT NULL,
    nombre VARCHAR(100) NOT NULL,

    FOREIGN KEY (id_barbero) REFERENCES barbero(id_barbero) ON DELETE CASCADE
);

-- ALTER TABLE horario_barbero ADD COLUMN pausa_inicio TIME NULL AFTER hora_fin, ADD COLUMN pausa_fin TIME NULL AFTER pausa_inicio;
-- ALTER TABLE horario_barbero DROP COLUMN activo;

-- ALTER TABLE horario_barbero
-- MODIFY hora_inicio TIME NULL,
-- MODIFY hora_fin TIME NULL,
-- MODIFY pausa_inicio TIME NULL,
-- MODIFY pausa_fin TIME NULL;


-- =========================
-- HORARIO
-- =========================
CREATE TABLE horario_general (
    id_general INT AUTO_INCREMENT PRIMARY KEY,
    id_barbero INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_barbero) REFERENCES barbero(id_barbero) ON DELETE CASCADE
);

ALTER TABLE horario_barbero ADD COLUMN id_general INT NULL, ADD FOREIGN KEY (id_general) REFERENCES horario_general(id_general) ON DELETE CASCADE;



-- =========================
-- BLOQUEAR HORARIO
-- =========================
CREATE TABLE bloqueo_horario (
    id_bloqueo INT AUTO_INCREMENT PRIMARY KEY,

    id_barbero INT NOT NULL,

    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,

    tipo ENUM(
        'descanso',
        'ausencia',
        'vacaciones',
        'formacion',
        'bloqueo_manual'
    ) NOT NULL DEFAULT 'bloqueo_manual',

    motivo VARCHAR(255) DEFAULT NULL,

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- 🔗 relación con barbero
    CONSTRAINT fk_bloqueo_barbero
        FOREIGN KEY (id_barbero)
        REFERENCES barbero(id_barbero)
        ON DELETE CASCADE
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


-- ALTER TABLE detalle_cita
-- DROP FOREIGN KEY detalle_cita_ibfk_2;

-- ALTER TABLE detalle_cita
-- ADD CONSTRAINT detalle_cita_ibfk_2
-- FOREIGN KEY (id_servicio)
-- REFERENCES servicio(id_servicio)
-- ON DELETE CASCADE;




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

-- ALTER TABLE pago
-- ADD COLUMN propina DECIMAL(8,2) NOT NULL DEFAULT 0.00;

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

-- =========================
-- VIP_HORARIO
-- =========================
CREATE TABLE vip_horario (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_barbero INT NOT NULL,

    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,

    motivo VARCHAR(255) DEFAULT NULL,

    creada_por ENUM('barbero','admin') DEFAULT 'barbero',

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_barbero) REFERENCES barbero(id_barbero)
        ON DELETE CASCADE
);

-- =========================
-- NOTIFICACIONES
-- =========================
CREATE TABLE notificacion (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo VARCHAR(255),
    mensaje TEXT,
    visto BOOLEAN DEFAULT FALSE,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
    ON DELETE CASCADE
);

-- =========================
-- PRODUCTOS
-- =========================
CREATE TABLE producto (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,

    tipo ENUM(
        'cera',
        'polvos',
        'champu',
        'aceite',
        'agua de mar'
    ) NOT NULL,

    descripcion TEXT,

    precio_compra DECIMAL(8,2) DEFAULT NULL,
    precio_venta DECIMAL(8,2) NOT NULL,

    stock INT NOT NULL DEFAULT 0,
    stock_minimo INT DEFAULT 0,

    activo BOOLEAN DEFAULT TRUE,

    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

--ALTER TABLE producto
--ADD COLUMN id_cita INT NULL AFTER id_producto,
--ADD CONSTRAINT fk_producto_cita
--    FOREIGN KEY (id_cita)
--    REFERENCES cita(id_cita)
--    ON DELETE SET NULL
--    ON UPDATE CASCADE;

--ALTER TABLE producto
--DROP COLUMN stock_minimo,
--ADD COLUMN cantidad_cita INT NOT NULL DEFAULT 0 AFTER stock;

-- =========================
-- VENTA DE PRODUCTOS
-- =========================
CREATE TABLE venta_producto (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,

    id_cliente INT DEFAULT NULL,

    total DECIMAL(8,2) NOT NULL,

    metodo ENUM('efectivo','tarjeta','bizum'),

    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_cliente)
        REFERENCES cliente(id_cliente)
        ON DELETE SET NULL
);

--ALTER TABLE venta_producto
--ADD COLUMN id_cita INT NULL AFTER id_venta,
--ADD CONSTRAINT fk_venta_producto_cita
--    FOREIGN KEY (id_cita)
--    REFERENCES cita(id_cita)
--    ON DELETE CASCADE,
--ADD UNIQUE KEY uk_venta_producto_cita (id_cita);

-- =========================
-- DETALLE VENTA PRODUCTOS
-- =========================
CREATE TABLE detalle_venta_producto (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,

    id_venta INT NOT NULL,
    id_producto INT NOT NULL,

    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(8,2) NOT NULL,

    FOREIGN KEY (id_venta)
        REFERENCES venta_producto(id_venta)
        ON DELETE CASCADE,

    FOREIGN KEY (id_producto)
        REFERENCES producto(id_producto)
);
