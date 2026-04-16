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
