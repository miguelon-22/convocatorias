-- =========================================
-- 1. TABLA EMPRESAS (CON LOGIN Y FOTO)
-- =========================================
CREATE TABLE IF NOT EXISTS empresas (
    id SERIAL PRIMARY KEY,
    nombre_comercial VARCHAR(150) NOT NULL,
    ruc VARCHAR(11) UNIQUE NOT NULL,
    sector VARCHAR(100),
    correo_contacto VARCHAR(150) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,

    -- LOGIN EMPRESA
    password_hash VARCHAR(255) NOT NULL,

    -- FOTO PERFIL
    foto_perfil TEXT, -- URL o ruta

    estado VARCHAR(20) DEFAULT 'pendiente', -- pendiente, activo, rechazado, bloqueado
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- 2. TABLA ADMINISTRADORES (CON FOTO)
-- =========================================
CREATE TABLE IF NOT EXISTS administradores (
    id SERIAL PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100),

    -- FOTO PERFIL
    foto_perfil TEXT,

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- 3. TABLA VACANTES
-- =========================================
CREATE TABLE IF NOT EXISTS vacantes (
    id SERIAL PRIMARY KEY,
    empresa_id INTEGER NOT NULL,
    
    titulo_puesto VARCHAR(150) NOT NULL,
    descripcion_puesto TEXT NOT NULL,
    requisitos_raw TEXT NOT NULL,

    modalidad VARCHAR(50), -- Presencial, Remoto, Híbrido
    ubicacion VARCHAR(100),
    fecha_limite DATE,

    estado VARCHAR(20) DEFAULT 'abierta', -- abierta, cerrada
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_empresa
        FOREIGN KEY (empresa_id)
        REFERENCES empresas(id)
        ON DELETE CASCADE
);

-- =========================================
-- 4. TABLA POSTULACIONES (SIN LOGIN)
-- =========================================
CREATE TABLE IF NOT EXISTS postulaciones (
    id SERIAL PRIMARY KEY,
    vacante_id INTEGER NOT NULL,

    -- DATOS DEL ESTUDIANTE
    dni VARCHAR(15) NOT NULL,
    nombre_completo VARCHAR(200) NOT NULL,
    correo_estudiante VARCHAR(150) NOT NULL,
    celular VARCHAR(20) NOT NULL,
    url_cv_pdf TEXT,

    -- IA / MATCH
    match_porcentaje DECIMAL(5,2) DEFAULT 0.00,
    ia_analisis_descripcion TEXT,

    -- ESTADO
    estado_postulacion VARCHAR(20) DEFAULT 'en_espera', -- en_espera, revisado, descartado, contactado
    fecha_postulacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_vacante
        FOREIGN KEY (vacante_id)
        REFERENCES vacantes(id)
        ON DELETE CASCADE
);

-- =========================================
-- 5. TABLA CONFIGURACIÓN GLOBAL
-- =========================================
CREATE TABLE IF NOT EXISTS configuracion (
    clave VARCHAR(50) PRIMARY KEY,
    valor TEXT
);

INSERT INTO configuracion (clave, valor) VALUES 
('nombre_sitio', 'TalentFlow Pro'),
('modo_mantenimiento', 'off')
ON CONFLICT (clave) DO NOTHING;

-- Insert dummy administrator
INSERT INTO administradores (usuario, password_hash, nombre)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Principal')
ON CONFLICT (usuario) DO NOTHING;
-- Password for dummy admin: password
