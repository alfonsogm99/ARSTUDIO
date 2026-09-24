-- Usuario barbero
INSERT INTO usuario (username, email, password, rol)
VALUES ('aaleexrp', 'alex@none.es', '', 'barbero');

-- Barbero asociado
INSERT INTO barbero (nombre, especialidad, activo, id_usuario)
VALUES ('Alejandro Rivilla', '', 1, LAST_INSERT_ID());


-- Usuario admin
INSERT INTO usuario (username, email, password, rol)
VALUES ('alfonsogm','alfonsogm@none.es', '', 'admin');

-- Admin asociado
INSERT INTO `admin` (id_usuario)
VALUES (LAST_INSERT_ID());


-- Usuario client
INSERT INTO usuario (username, email, password, rol)
VALUES (
    'cliente',
    'unknown@none.es',
    '',
    'cliente'
);

INSERT INTO cliente (id_usuario, nombre, dni, email, telefono)
VALUES (
    LAST_INSERT_ID(),
    'Juan de Dios',
    '12345678A',
    'juande@arstudio.es',
    '666777888'
);



-- cliente arbarber.online
INSERT INTO usuario (username, password, rol)
VALUES (
    'juanD',
    '', -- admin
    'cliente'
);

INSERT INTO cliente (id_usuario, nombre, email, telefono)
VALUES (
    LAST_INSERT_ID(),
    'Juan de Dios',
    'juande@arstudio.es',
    '666777888'
);