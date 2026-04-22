-- =========================
-- REGISTROS (LOGS)
-- =========================


-- =========================
-- INSERT
-- =========================
DELIMITER $$

CREATE TRIGGER cita_after_insert
AFTER INSERT ON cita
FOR EACH ROW
BEGIN

    IF @usuario_actual IS NULL THEN
        SET @usuario_actual = 'system';
    END IF;

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues,
        ip
    )
    VALUES (
        'cita',
        'INSERT',
        NEW.id_cita,
        @usuario_actual,
        JSON_OBJECT(
            'id_cita', NEW.id_cita,
            'id_cliente', NEW.id_cliente,
            'id_barbero', NEW.id_barbero,
            'fecha', NEW.fecha,
            'hora', NEW.hora,
            'estado', NEW.estado,
            'estado_pago', NEW.estado_pago,
            'duracion_total', NEW.duracion_total
        ),
        @ip_actual
    );

END$$

DELIMITER ;


-- =========================
-- UPDATE
-- =========================
DELIMITER $$

DELIMITER $$

CREATE TRIGGER cita_after_update
AFTER UPDATE ON cita
FOR EACH ROW
BEGIN

    IF @usuario_actual IS NULL THEN
        SET @usuario_actual = 'system';
    END IF;

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues,
        ip
    )
    VALUES (
        'cita',
        'UPDATE',
        NEW.id_cita,

        @usuario_actual,

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
        ),

        @ip_actual
    );

END$$

DELIMITER ;


-- =========================
-- DELETE
-- =========================
DELIMITER $$

DELIMITER $$

CREATE TRIGGER cita_after_delete
AFTER DELETE ON cita
FOR EACH ROW
BEGIN

    IF @usuario_actual IS NULL THEN
        SET @usuario_actual = 'system';
    END IF;

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        ip
    )
    VALUES (
        'cita',
        'DELETE',
        OLD.id_cita,
        @usuario_actual,

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

        @ip_actual
    );

END$$

DELIMITER ;