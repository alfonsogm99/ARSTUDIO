-- Usuario barbero
INSERT INTO usuario (username, password, rol)
VALUES ('aaleexrp', '', 'barbero');

-- Barbero asociado
INSERT INTO barbero (nombre, especialidad, activo, id_usuario)
VALUES ('Alejandro Rivilla', '', 1, LAST_INSERT_ID());


-- Usuario admin
INSERT INTO usuario (username, password, rol)
VALUES ('alfonsogm', '', 'admin');

-- Admin asociado
INSERT INTO `admin` (id_usuario)
VALUES (LAST_INSERT_ID());