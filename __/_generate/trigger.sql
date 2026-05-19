-- =========================
-- (LOGS)
-- =========================

-- =========================
-- DROP TRIGGERS IF EXISTS
-- =========================

-- admin
DROP TRIGGER IF EXISTS admin_after_insert;
DROP TRIGGER IF EXISTS admin_after_update;
DROP TRIGGER IF EXISTS admin_after_delete;

-- usuario
DROP TRIGGER IF EXISTS usuario_after_insert;
DROP TRIGGER IF EXISTS usuario_after_update;
DROP TRIGGER IF EXISTS usuario_after_delete;

-- cliente
DROP TRIGGER IF EXISTS cliente_after_insert;
DROP TRIGGER IF EXISTS cliente_after_update;
DROP TRIGGER IF EXISTS cliente_after_delete;

-- barbero
DROP TRIGGER IF EXISTS barbero_after_insert;
DROP TRIGGER IF EXISTS barbero_after_update;
DROP TRIGGER IF EXISTS barbero_after_delete;

-- servicio
DROP TRIGGER IF EXISTS servicio_after_insert;
DROP TRIGGER IF EXISTS servicio_after_update;
DROP TRIGGER IF EXISTS servicio_after_delete;

-- cita
DROP TRIGGER IF EXISTS cita_after_insert;
DROP TRIGGER IF EXISTS cita_after_update;
DROP TRIGGER IF EXISTS cita_after_delete;

-- bloqueo_horario
DROP TRIGGER IF EXISTS bloqueo_horario_after_insert;
DROP TRIGGER IF EXISTS bloqueo_horario_after_update;
DROP TRIGGER IF EXISTS bloqueo_horario_after_delete;

-- detalle_cita
DROP TRIGGER IF EXISTS detalle_cita_after_insert;
DROP TRIGGER IF EXISTS detalle_cita_after_update;
DROP TRIGGER IF EXISTS detalle_cita_after_delete;

-- pago
DROP TRIGGER IF EXISTS pago_after_insert;
DROP TRIGGER IF EXISTS pago_after_update;
DROP TRIGGER IF EXISTS pago_after_delete;

-- activacion_usuario
DROP TRIGGER IF EXISTS activacion_usuario_after_insert;
DROP TRIGGER IF EXISTS activacion_usuario_after_update;
DROP TRIGGER IF EXISTS activacion_usuario_after_delete;

-- historico_cliente
DROP TRIGGER IF EXISTS historico_cliente_after_insert;
DROP TRIGGER IF EXISTS historico_cliente_after_update;
DROP TRIGGER IF EXISTS historico_cliente_after_delete;

-- horario_barbero
DROP TRIGGER IF EXISTS horario_barbero_after_insert;
DROP TRIGGER IF EXISTS horario_barbero_after_update;
DROP TRIGGER IF EXISTS horario_barbero_after_delete;

-- horario_general
DROP TRIGGER IF EXISTS horario_general_after_insert;
DROP TRIGGER IF EXISTS horario_general_after_update;
DROP TRIGGER IF EXISTS horario_general_after_delete;





-- =========================
-- ALL TRIGGERS
-- =========================

-- =========================
-- TABLA USUARIO
-- =========================


-- =========================
-- INSERT
-- =========================
DELIMITER $$

CREATE TRIGGER usuario_after_insert
AFTER INSERT ON usuario
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'usuario',
        'INSERT',
        NEW.id_usuario,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_usuario', NEW.id_usuario,
            'username', NEW.username,
            'rol', NEW.rol,
            'activo', NEW.activo
        )
    );
END$$

DELIMITER ;



-- =========================
-- UPDATE
-- =========================
DELIMITER $$

CREATE TRIGGER usuario_after_update
AFTER UPDATE ON usuario
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'usuario',
        'UPDATE',
        NEW.id_usuario,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'username', OLD.username,
            'password', OLD.password,
            'rol', OLD.rol,
            'activo', OLD.activo
        ),
        JSON_OBJECT(
            'username', NEW.username,
            'password', NEW.password,
            'rol', NEW.rol,
            'activo', NEW.activo
        )
    );
END$$

DELIMITER ;



-- =========================
-- DELETE
-- =========================
DELIMITER $$

CREATE TRIGGER usuario_after_delete
AFTER DELETE ON usuario
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'usuario',
        'DELETE',
        OLD.id_usuario,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'username', OLD.username,
            'rol', OLD.rol,
            'activo', OLD.activo
        )
    );
END$$

DELIMITER ;



-- =========================
-- TABLA CLIENTE
-- =========================


-- =========================
-- INSERT
-- =========================
DELIMITER $$

CREATE TRIGGER cliente_after_insert
AFTER INSERT ON cliente
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'cliente',
        'INSERT',
        NEW.id_cliente,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cliente', NEW.id_cliente,
            'nombre', NEW.nombre,
            'dni', NEW.dni,
            'email', NEW.email,
            'telefono', NEW.telefono
        )
    );
END$$

DELIMITER ;


-- =========================
-- UPDATE
-- =========================
DELIMITER $$

CREATE TRIGGER cliente_after_update
AFTER UPDATE ON cliente
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'cliente',
        'UPDATE',
        NEW.id_cliente,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'nombre', OLD.nombre,
            'dni', OLD.dni,
            'email', OLD.email,
            'telefono', OLD.telefono
        ),
        JSON_OBJECT(
            'nombre', NEW.nombre,
            'dni', NEW.dni,
            'email', NEW.email,
            'telefono', NEW.telefono
        )
    );
END$$

DELIMITER ;


-- =========================
-- DELETE
-- =========================
DELIMITER $$

CREATE TRIGGER cliente_after_delete
AFTER DELETE ON cliente
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'cliente',
        'DELETE',
        OLD.id_cliente,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'nombre', OLD.nombre,
            'dni', OLD.dni,
            'email', OLD.email,
            'telefono', OLD.telefono
        )
    );
END$$

DELIMITER ;




-- =========================
-- TABLA BARBERO
-- =========================


-- =========================
-- INSERT
-- =========================
DELIMITER $$

CREATE TRIGGER barbero_after_insert
AFTER INSERT ON barbero
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'barbero',
        'INSERT',
        NEW.id_barbero,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_barbero', NEW.id_barbero,
            'nombre', NEW.nombre,
            'especialidad', NEW.especialidad,
            'activo', NEW.activo
        )
    );
END$$

DELIMITER ;

-- =========================
-- UPDATE
-- =========================
DELIMITER $$

CREATE TRIGGER barbero_after_update
AFTER UPDATE ON barbero
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'barbero',
        'UPDATE',
        NEW.id_barbero,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'nombre', OLD.nombre,
            'especialidad', OLD.especialidad,
            'activo', OLD.activo
        ),
        JSON_OBJECT(
            'nombre', NEW.nombre,
            'especialidad', NEW.especialidad,
            'activo', NEW.activo
        )
    );
END$$

DELIMITER ;


-- =========================
-- DELETE
-- =========================
DELIMITER $$

CREATE TRIGGER barbero_after_delete
AFTER DELETE ON barbero
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'barbero',
        'DELETE',
        OLD.id_barbero,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'nombre', OLD.nombre,
            'especialidad', OLD.especialidad,
            'activo', OLD.activo
        )
    );
END$$

DELIMITER ;



-- =========================
-- TABLA SERVICIO
-- =========================


-- =========================
-- INSERT
-- =========================
DELIMITER $$

CREATE TRIGGER servicio_after_insert
AFTER INSERT ON servicio
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'servicio',
        'INSERT',
        NEW.id_servicio,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_servicio', NEW.id_servicio,
            'nombre', NEW.nombre,
            'precio', NEW.precio,
            'duracion', NEW.duracion
        )
    );
END$$

DELIMITER ;



-- =========================
-- UPDATE
-- =========================
DELIMITER $$

CREATE TRIGGER servicio_after_update
AFTER UPDATE ON servicio
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'servicio',
        'UPDATE',
        NEW.id_servicio,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'nombre', OLD.nombre,
            'precio', OLD.precio,
            'duracion', OLD.duracion
        ),
        JSON_OBJECT(
            'nombre', NEW.nombre,
            'precio', NEW.precio,
            'duracion', NEW.duracion
        )
    );
END$$

DELIMITER ;


-- =========================
-- DELETE
-- =========================
DELIMITER $$

CREATE TRIGGER servicio_after_delete
AFTER DELETE ON servicio
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'servicio',
        'DELETE',
        OLD.id_servicio,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'nombre', OLD.nombre,
            'precio', OLD.precio,
            'duracion', OLD.duracion
        )
    );
END$$

DELIMITER ;




-- =========================
-- TABLA CITA
-- =========================

-- =========================
-- INSERT
-- =========================
DELIMITER $$

CREATE TRIGGER cita_after_insert
AFTER INSERT ON cita
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'cita',
        'INSERT',
        NEW.id_cita,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cita', NEW.id_cita,
            'id_cliente', NEW.id_cliente,
            'id_barbero', NEW.id_barbero,
            'fecha', NEW.fecha,
            'hora', NEW.hora,
            'estado', NEW.estado,
            'estado_pago', NEW.estado_pago,
            'duracion_total', NEW.duracion_total
        )
    );

END$$

DELIMITER ;


-- =========================
-- UPDATE
-- =========================
DELIMITER $$

CREATE TRIGGER cita_after_update
AFTER UPDATE ON cita
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'cita',
        'UPDATE',
        NEW.id_cita,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cita', OLD.id_cita,
            'id_cliente', OLD.id_cliente,
            'id_barbero', OLD.id_barbero,
            'fecha', OLD.fecha,
            'hora', OLD.hora,
            'estado', OLD.estado,
            'estado_pago', OLD.estado_pago,
            'duracion_total', OLD.duracion_total
        ),

        JSON_OBJECT(
            'id_cita', NEW.id_cita,
            'id_cliente', NEW.id_cliente,
            'id_barbero', NEW.id_barbero,
            'fecha', NEW.fecha,
            'hora', NEW.hora,
            'estado', NEW.estado,
            'estado_pago', NEW.estado_pago,
            'duracion_total', NEW.duracion_total
        )
    );

END$$

DELIMITER ;


-- =========================
-- DELETE
-- =========================
DELIMITER $$

CREATE TRIGGER cita_after_delete
AFTER DELETE ON cita
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'cita',
        'DELETE',
        OLD.id_cita,
        COALESCE(@usuario_actual, 'system'),

        JSON_OBJECT(
            'id_cita', OLD.id_cita,
            'id_cliente', OLD.id_cliente,
            'id_barbero', OLD.id_barbero,
            'fecha', OLD.fecha,
            'hora', OLD.hora,
            'estado', OLD.estado,
            'estado_pago', OLD.estado_pago,
            'duracion_total', OLD.duracion_total
        )
    );

END$$

DELIMITER ;



-- =========================
-- TABLA BLOQUEO_HORARIO
-- =========================

-- =========================
-- INSERT
-- =========================

DELIMITER $$

CREATE TRIGGER bloqueo_horario_after_insert
AFTER INSERT ON bloqueo_horario
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'bloqueo_horario',
        'INSERT',
        NEW.id_bloqueo,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_bloqueo', NEW.id_bloqueo,
            'id_barbero', NEW.id_barbero,
            'fecha', NEW.fecha,
            'hora_inicio', NEW.hora_inicio,
            'hora_fin', NEW.hora_fin,
            'tipo', NEW.tipo,
            'motivo', NEW.motivo
        )
    );

END$$

DELIMITER ;

-- =========================
-- UPDATE
-- =========================
DELIMITER $$

CREATE TRIGGER bloqueo_horario_after_update
AFTER UPDATE ON bloqueo_horario
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'bloqueo_horario',
        'UPDATE',
        NEW.id_bloqueo,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_barbero', OLD.id_barbero,
            'fecha', OLD.fecha,
            'hora_inicio', OLD.hora_inicio,
            'hora_fin', OLD.hora_fin,
            'tipo', OLD.tipo,
            'motivo', OLD.motivo
        ),
        JSON_OBJECT(
            'id_barbero', NEW.id_barbero,
            'fecha', NEW.fecha,
            'hora_inicio', NEW.hora_inicio,
            'hora_fin', NEW.hora_fin,
            'tipo', NEW.tipo,
            'motivo', NEW.motivo
        )
    );

END$$

DELIMITER ;

-- =========================
-- DELETE
-- =========================
DELIMITER $$

CREATE TRIGGER bloqueo_horario_after_delete
AFTER DELETE ON bloqueo_horario
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'bloqueo_horario',
        'DELETE',
        OLD.id_bloqueo,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_bloqueo', OLD.id_bloqueo,
            'id_barbero', OLD.id_barbero,
            'fecha', OLD.fecha,
            'hora_inicio', OLD.hora_inicio,
            'hora_fin', OLD.hora_fin,
            'tipo', OLD.tipo,
            'motivo', OLD.motivo
        )
    );

END$$

DELIMITER ;



-- =========================
-- TABLA detalle_cita
-- =========================


-- =========================
-- INSERT
-- =========================
DELIMITER $$

CREATE TRIGGER detalle_cita_after_insert
AFTER INSERT ON detalle_cita
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'detalle_cita',
        'INSERT',
        NEW.id_detalle,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_detalle', NEW.id_detalle,
            'id_cita', NEW.id_cita,
            'id_servicio', NEW.id_servicio,
            'precio_aplicado', NEW.precio_aplicado
        )
    );
END$$

DELIMITER ;


-- =========================
-- UPDATE
-- =========================
DELIMITER $$

CREATE TRIGGER detalle_cita_after_update
AFTER UPDATE ON detalle_cita
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'detalle_cita',
        'UPDATE',
        NEW.id_detalle,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cita', OLD.id_cita,
            'id_servicio', OLD.id_servicio,
            'precio_aplicado', OLD.precio_aplicado
        ),
        JSON_OBJECT(
            'id_cita', NEW.id_cita,
            'id_servicio', NEW.id_servicio,
            'precio_aplicado', NEW.precio_aplicado
        )
    );
END$$

DELIMITER ;



-- =========================
-- DELETE
-- =========================
DELIMITER $$

CREATE TRIGGER detalle_cita_after_delete
AFTER DELETE ON detalle_cita
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'detalle_cita',
        'DELETE',
        OLD.id_detalle,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cita', OLD.id_cita,
            'id_servicio', OLD.id_servicio,
            'precio_aplicado', OLD.precio_aplicado
        )
    );
END$$

DELIMITER ;




-- =========================
-- TABLA PAGO
-- =========================


-- =========================
-- INSERT
-- =========================
DELIMITER $$

CREATE TRIGGER pago_after_insert
AFTER INSERT ON pago
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'pago',
        'INSERT',
        NEW.id_pago,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_pago', NEW.id_pago,
            'id_cita', NEW.id_cita,
            'total', NEW.total,
            'metodo', NEW.metodo
        )
    );
END$$

DELIMITER ;

-- =========================
-- UPDATE
-- =========================
DELIMITER $$

CREATE TRIGGER pago_after_update
AFTER UPDATE ON pago
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'pago',
        'UPDATE',
        NEW.id_pago,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'total', OLD.total,
            'metodo', OLD.metodo
        ),
        JSON_OBJECT(
            'total', NEW.total,
            'metodo', NEW.metodo
        )
    );
END$$

DELIMITER ;

-- =========================
-- DELETE
-- =========================
DELIMITER $$

CREATE TRIGGER pago_after_delete
AFTER DELETE ON pago
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'pago',
        'DELETE',
        OLD.id_pago,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cita', OLD.id_cita,
            'total', OLD.total,
            'metodo', OLD.metodo
        )
    );
END$$

DELIMITER ;





-- =========================
-- TABLA ADMIN
-- =========================

-- =========================
-- INSERT
-- =========================
DELIMITER $$

CREATE TRIGGER admin_after_insert
AFTER INSERT ON admin
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'admin',
        'INSERT',
        NEW.id_admin,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_admin', NEW.id_admin,
            'id_usuario', NEW.id_usuario
        )
    );

END$$

DELIMITER ;


-- =========================
-- UPDATE
-- =========================
DELIMITER $$

CREATE TRIGGER admin_after_update
AFTER UPDATE ON admin
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'admin',
        'UPDATE',
        NEW.id_admin,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_usuario', OLD.id_usuario
        ),
        JSON_OBJECT(
            'id_usuario', NEW.id_usuario
        )
    );

END$$

DELIMITER ;


-- =========================
-- DELETE
-- =========================
DELIMITER $$

CREATE TRIGGER admin_after_delete
AFTER DELETE ON admin
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'admin',
        'DELETE',
        OLD.id_admin,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_admin', OLD.id_admin,
            'id_usuario', OLD.id_usuario
        )
    );

END$$

DELIMITER ;





-- =========================
-- TABLA activacion_usuario
-- =========================

-- =========================
-- INSERT
-- =========================
DELIMITER $$

CREATE TRIGGER activacion_usuario_after_insert
AFTER INSERT ON activacion_usuario
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'activacion_usuario',
        'INSERT',
        NEW.id,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id', NEW.id,
            'id_cliente', NEW.id_cliente,
            'token', NEW.token,
            'expiracion', NEW.expiracion,
            'usado', NEW.usado
        )
    );

END$$

DELIMITER ;



-- =========================
-- UPDATE
-- =========================
DELIMITER $$

CREATE TRIGGER activacion_usuario_after_update
AFTER UPDATE ON activacion_usuario
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'activacion_usuario',
        'UPDATE',
        NEW.id,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cliente', OLD.id_cliente,
            'token', OLD.token,
            'expiracion', OLD.expiracion,
            'usado', OLD.usado
        ),
        JSON_OBJECT(
            'id_cliente', NEW.id_cliente,
            'token', NEW.token,
            'expiracion', NEW.expiracion,
            'usado', NEW.usado
        )
    );

END$$

DELIMITER ;



-- =========================
-- DELETE
-- =========================
DELIMITER $$

CREATE TRIGGER activacion_usuario_after_delete
AFTER DELETE ON activacion_usuario
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'activacion_usuario',
        'DELETE',
        OLD.id,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id', OLD.id,
            'id_cliente', OLD.id_cliente,
            'token', OLD.token,
            'expiracion', OLD.expiracion,
            'usado', OLD.usado
        )
    );

END$$

DELIMITER ;










-- =========================
-- TABLA historico_cliente
-- =========================

-- =========================
-- INSERT
-- =========================
DELIMITER $$

CREATE TRIGGER historico_cliente_after_insert
AFTER INSERT ON historico_cliente
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'historico_cliente',
        'INSERT',
        NEW.id_historico,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_historico', NEW.id_historico,
            'id_cliente', NEW.id_cliente,
            'id_cita', NEW.id_cita,
            'descripcion', NEW.descripcion,
            'fecha', NEW.fecha
        )
    );

END$$

DELIMITER ;


-- =========================
-- UPDATE
-- =========================
DELIMITER $$

CREATE TRIGGER historico_cliente_after_update
AFTER UPDATE ON historico_cliente
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'historico_cliente',
        'UPDATE',
        NEW.id_historico,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cliente', OLD.id_cliente,
            'id_cita', OLD.id_cita,
            'descripcion', OLD.descripcion,
            'fecha', OLD.fecha
        ),
        JSON_OBJECT(
            'id_cliente', NEW.id_cliente,
            'id_cita', NEW.id_cita,
            'descripcion', NEW.descripcion,
            'fecha', NEW.fecha
        )
    );

END$$

DELIMITER ;




-- =========================
-- DELETE
-- =========================
DELIMITER $$

CREATE TRIGGER historico_cliente_after_delete
AFTER DELETE ON historico_cliente
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'historico_cliente',
        'DELETE',
        OLD.id_historico,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_historico', OLD.id_historico,
            'id_cliente', OLD.id_cliente,
            'id_cita', OLD.id_cita,
            'descripcion', OLD.descripcion,
            'fecha', OLD.fecha
        )
    );

END$$

DELIMITER ;



-- =========================
-- TABLA horario_barbero
-- =========================

-- =========================
-- INSERT
-- =========================
DELIMITER $$

CREATE TRIGGER horario_barbero_after_insert
AFTER INSERT ON horario_barbero
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'horario_barbero',
        'INSERT',
        NEW.id_horario,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_horario', NEW.id_horario,
            'id_barbero', NEW.id_barbero,
            'dia_semana', NEW.dia_semana,
            'hora_inicio', NEW.hora_inicio,
            'hora_fin', NEW.hora_fin,
            'pausa_inicio', NEW.pausa_inicio,
            'pausa_fin', NEW.pausa_fin,
            'nombre', NEW.nombre,
            'id_general', NEW.id_general
        )
    );

END$$

DELIMITER ;


-- =========================
-- UPDATE
-- =========================
DELIMITER $$

CREATE TRIGGER horario_barbero_after_update
AFTER UPDATE ON horario_barbero
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'horario_barbero',
        'UPDATE',
        NEW.id_horario,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_barbero', OLD.id_barbero,
            'dia_semana', OLD.dia_semana,
            'hora_inicio', OLD.hora_inicio,
            'hora_fin', OLD.hora_fin,
            'pausa_inicio', OLD.pausa_inicio,
            'pausa_fin', OLD.pausa_fin,
            'nombre', OLD.nombre,
            'id_general', OLD.id_general
        ),
        JSON_OBJECT(
            'id_barbero', NEW.id_barbero,
            'dia_semana', NEW.dia_semana,
            'hora_inicio', NEW.hora_inicio,
            'hora_fin', NEW.hora_fin,
            'pausa_inicio', NEW.pausa_inicio,
            'pausa_fin', NEW.pausa_fin,
            'nombre', NEW.nombre,
            'id_general', NEW.id_general
        )
    );

END$$

DELIMITER ;

-- =========================
-- DELETE
-- =========================
DELIMITER $$

CREATE TRIGGER horario_barbero_after_delete
AFTER DELETE ON horario_barbero
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'horario_barbero',
        'DELETE',
        OLD.id_horario,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_horario', OLD.id_horario,
            'id_barbero', OLD.id_barbero,
            'dia_semana', OLD.dia_semana,
            'hora_inicio', OLD.hora_inicio,
            'hora_fin', OLD.hora_fin,
            'pausa_inicio', OLD.pausa_inicio,
            'pausa_fin', OLD.pausa_fin,
            'nombre', OLD.nombre,
            'id_general', OLD.id_general
        )
    );

END$$

DELIMITER ;



-- =========================
-- TABLA horario_barbero
-- =========================

-- =========================
-- INSERT
-- =========================
DELIMITER $$

CREATE TRIGGER horario_general_after_insert
AFTER INSERT ON horario_general
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'horario_general',
        'INSERT',
        NEW.id_general,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_general', NEW.id_general,
            'id_barbero', NEW.id_barbero,
            'nombre', NEW.nombre,
            'activo', NEW.activo
        )
    );

END$$

DELIMITER ;


-- =========================
-- UPDATE
-- =========================
DELIMITER $$

CREATE TRIGGER horario_general_after_update
AFTER UPDATE ON horario_general
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'horario_general',
        'UPDATE',
        NEW.id_general,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_barbero', OLD.id_barbero,
            'nombre', OLD.nombre,
            'activo', OLD.activo
        ),
        JSON_OBJECT(
            'id_barbero', NEW.id_barbero,
            'nombre', NEW.nombre,
            'activo', NEW.activo
        )
    );

END$$

DELIMITER ;

-- =========================
-- DELETE
-- =========================
DELIMITER $$

CREATE TRIGGER horario_general_after_delete
AFTER DELETE ON horario_general
FOR EACH ROW
BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'horario_general',
        'DELETE',
        OLD.id_general,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_general', OLD.id_general,
            'id_barbero', OLD.id_barbero,
            'nombre', OLD.nombre,
            'activo', OLD.activo
        )
    );

END$$

DELIMITER ;