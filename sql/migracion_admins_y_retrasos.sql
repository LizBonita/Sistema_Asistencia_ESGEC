-- ===============================================
-- Migración: Agregar personal administrativo a maestros
-- + Columna imagen_base64 si no existe
-- ===============================================

-- 1. Agregar directores/admins como "Administrativo" en maestros
-- (para que aparezcan en gestión de huellas y registro de asistencia)
INSERT IGNORE INTO maestros (usuario_id, tipo_contrato) 
SELECT u.id, 'Administrativo' 
FROM usuarios u
JOIN roles r ON u.rol_id = r.id
WHERE r.nombre IN ('Director', 'Administrador', 'Admin')
AND u.id NOT IN (SELECT usuario_id FROM maestros);

-- 2. Columna imagen_base64 si no existe
ALTER TABLE huellas_dactilares ADD COLUMN IF NOT EXISTS 
  imagen_base64 LONGTEXT DEFAULT NULL 
  COMMENT 'Imagen de huella en base64 para uso en la nube' 
  AFTER imagen_path;

-- 3. Recalcular estados de registros viejos (antes del fix de timezone/retrasos)
-- Marcar como "A tiempo" los que entraron antes de las 08:00
UPDATE asistencias 
SET estado_entrada = 'A tiempo', minutos_retraso = 0
WHERE hora_entrada IS NOT NULL 
  AND hora_entrada <= '08:00:00'
  AND (estado_entrada IS NULL OR estado_entrada = '' OR estado_entrada = 'A Tiempo');

-- Marcar como "Retraso" los que entraron después de las 08:00
UPDATE asistencias 
SET estado_entrada = 'Retraso', 
    minutos_retraso = TIMESTAMPDIFF(MINUTE, CONCAT(fecha, ' 08:00:00'), CONCAT(fecha, ' ', hora_entrada))
WHERE hora_entrada IS NOT NULL 
  AND hora_entrada > '08:00:00'
  AND (estado_entrada IS NULL OR estado_entrada = '' OR estado_entrada = 'A Tiempo');

-- Recalcular estado de salida
UPDATE asistencias 
SET estado_salida = CASE 
    WHEN hora_salida < '14:00:00' THEN 'Salida temprana'
    ELSE 'A tiempo'
END
WHERE hora_salida IS NOT NULL 
  AND (estado_salida IS NULL OR estado_salida = '' OR estado_salida = 'A Tiempo');
