-- ===============================================
-- Migración: Tabla de Huellas Dactilares
-- Sistema de Asistencia - Escuela Secundaria "Emperador Cuauhtémoc"
-- Fecha: 2026-04-15
-- ===============================================

CREATE TABLE IF NOT EXISTS huellas_dactilares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    maestro_id INT NOT NULL,
    dedo VARCHAR(30) DEFAULT 'right-index-finger',
    template_data LONGTEXT NOT NULL COMMENT 'Template de minutiae codificado en base64',
    imagen_path VARCHAR(255) DEFAULT NULL COMMENT 'Ruta relativa a imagen BMP de la huella',
    imagen_base64 LONGTEXT DEFAULT NULL COMMENT 'Imagen de huella en base64 para uso en la nube',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (maestro_id) REFERENCES maestros(id) ON DELETE CASCADE,
    UNIQUE KEY uk_maestro_dedo (maestro_id, dedo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índice para búsquedas rápidas por maestro
CREATE INDEX idx_huellas_maestro ON huellas_dactilares(maestro_id);
CREATE INDEX idx_huellas_activo ON huellas_dactilares(activo);

-- Migración para instalaciones existentes (agregar columna si no existe)
ALTER TABLE huellas_dactilares ADD COLUMN IF NOT EXISTS imagen_base64 LONGTEXT DEFAULT NULL COMMENT 'Imagen de huella en base64 para uso en la nube' AFTER imagen_path;
