-- ============================================
-- SISTEMA AVITECH - Base de Datos MySQL 3FN
-- Sistema Integral de Gestión Avícola
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Eliminar base de datos si existe y crear nueva
DROP DATABASE IF EXISTS avitech_db;
CREATE DATABASE avitech_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE avitech_db;

-- ============================================
-- MÓDULO DE AUTENTICACIÓN Y USUARIOS
-- ============================================

-- Tabla de roles
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    nivel_acceso INT NOT NULL DEFAULT 1,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_nombre_rol (nombre_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de permisos
CREATE TABLE permisos (
    id_permiso INT AUTO_INCREMENT PRIMARY KEY,
    nombre_permiso VARCHAR(100) NOT NULL UNIQUE,
    modulo VARCHAR(50) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_modulo (modulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla intermedia roles_permisos
CREATE TABLE roles_permisos (
    id_rol INT NOT NULL,
    id_permiso INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_rol, id_permiso),
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE CASCADE,
    FOREIGN KEY (id_permiso) REFERENCES permisos(id_permiso) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla principal de usuarios
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    direccion TEXT,
    ciudad VARCHAR(100),
    departamento VARCHAR(100),
    codigo_postal VARCHAR(10),
    activo TINYINT(1) DEFAULT 1,
    verificado TINYINT(1) DEFAULT 0,
    token_verificacion VARCHAR(100),
    token_recuperacion VARCHAR(100),
    fecha_token_expira DATETIME,
    ultimo_acceso TIMESTAMP NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol),
    INDEX idx_email (email),
    INDEX idx_rol (id_rol),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de sesiones de usuario
CREATE TABLE sesiones_usuario (
    id_sesion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    token_sesion VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion DATETIME NOT NULL,
    activa TINYINT(1) DEFAULT 1,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_token (token_sesion),
    INDEX idx_usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MÓDULO DE AVEOLOGÍA (CONOCIMIENTO TÉCNICO)
-- ============================================

-- Categorías de conocimiento
CREATE TABLE categorias_aveologia (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL,
    descripcion TEXT,
    icono VARCHAR(50),
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Artículos de base de conocimientos
CREATE TABLE articulos_aveologia (
    id_articulo INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    contenido LONGTEXT NOT NULL,
    resumen TEXT,
    imagen_principal VARCHAR(255),
    autor_id INT,
    visitas INT DEFAULT 0,
    destacado TINYINT(1) DEFAULT 0,
    tags VARCHAR(255),
    activo TINYINT(1) DEFAULT 1,
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias_aveologia(id_categoria),
    FOREIGN KEY (autor_id) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_categoria (id_categoria),
    INDEX idx_slug (slug),
    INDEX idx_destacado (destacado),
    FULLTEXT KEY ft_busqueda (titulo, contenido, tags)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de síntomas
CREATE TABLE sintomas (
    id_sintoma INT AUTO_INCREMENT PRIMARY KEY,
    nombre_sintoma VARCHAR(150) NOT NULL,
    descripcion TEXT,
    gravedad ENUM('leve', 'moderado', 'grave', 'critico') DEFAULT 'moderado',
    categoria VARCHAR(100),
    keywords TEXT COMMENT 'Palabras clave para búsqueda semántica',
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_gravedad (gravedad),
    FULLTEXT KEY ft_sintomas (nombre_sintoma, descripcion, keywords)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de enfermedades
CREATE TABLE enfermedades (
    id_enfermedad INT AUTO_INCREMENT PRIMARY KEY,
    nombre_enfermedad VARCHAR(200) NOT NULL,
    nombre_cientifico VARCHAR(200),
    tipo_enfermedad ENUM('viral', 'bacteriana', 'parasitaria', 'fungica', 'nutricional', 'ambiental') NOT NULL,
    descripcion TEXT NOT NULL,
    causas TEXT,
    prevencion TEXT,
    contagiosidad ENUM('no_contagioso', 'baja', 'media', 'alta') DEFAULT 'media',
    mortalidad_estimada DECIMAL(5,2) COMMENT 'Porcentaje de mortalidad',
    edad_susceptible VARCHAR(100),
    imagen VARCHAR(255),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo_enfermedad),
    FULLTEXT KEY ft_enfermedades (nombre_enfermedad, nombre_cientifico, descripcion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Relación síntomas-enfermedades (muchos a muchos)
CREATE TABLE sintomas_enfermedades (
    id_enfermedad INT NOT NULL,
    id_sintoma INT NOT NULL,
    frecuencia ENUM('raro', 'ocasional', 'frecuente', 'muy_frecuente') DEFAULT 'frecuente',
    intensidad ENUM('leve', 'moderado', 'severo') DEFAULT 'moderado',
    PRIMARY KEY (id_enfermedad, id_sintoma),
    FOREIGN KEY (id_enfermedad) REFERENCES enfermedades(id_enfermedad) ON DELETE CASCADE,
    FOREIGN KEY (id_sintoma) REFERENCES sintomas(id_sintoma) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de tratamientos
CREATE TABLE tratamientos (
    id_tratamiento INT AUTO_INCREMENT PRIMARY KEY,
    id_enfermedad INT NOT NULL,
    nombre_tratamiento VARCHAR(200) NOT NULL,
    tipo_tratamiento ENUM('medicamento', 'vacuna', 'manejo', 'nutricional', 'quirurgico') NOT NULL,
    descripcion TEXT NOT NULL,
    dosificacion TEXT,
    duracion VARCHAR(100),
    efectividad DECIMAL(5,2) COMMENT 'Porcentaje de efectividad',
    costo_aproximado DECIMAL(10,2),
    advertencias TEXT,
    requiere_veterinario TINYINT(1) DEFAULT 0,
    orden_recomendacion INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_enfermedad) REFERENCES enfermedades(id_enfermedad) ON DELETE CASCADE,
    INDEX idx_enfermedad (id_enfermedad),
    INDEX idx_tipo (tipo_tratamiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de remedios (caseros o alternativos)
CREATE TABLE remedios (
    id_remedio INT AUTO_INCREMENT PRIMARY KEY,
    id_enfermedad INT,
    nombre_remedio VARCHAR(200) NOT NULL,
    tipo_remedio ENUM('casero', 'herbal', 'preventivo', 'paliativo') NOT NULL,
    ingredientes TEXT NOT NULL,
    preparacion TEXT NOT NULL,
    modo_uso TEXT,
    efectividad_estimada ENUM('baja', 'media', 'alta') DEFAULT 'media',
    contraindicaciones TEXT,
    nota_importante TEXT COMMENT 'Advertencias de seguridad',
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_enfermedad) REFERENCES enfermedades(id_enfermedad) ON DELETE CASCADE,
    INDEX idx_enfermedad (id_enfermedad),
    INDEX idx_tipo (tipo_remedio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MÓDULO DE CALCULADORA DE RECURSOS
-- ============================================

-- Tipos de aves
CREATE TABLE tipos_ave (
    id_tipo_ave INT AUTO_INCREMENT PRIMARY KEY,
    nombre_tipo VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    proposito ENUM('carne', 'huevos', 'dual', 'ornamental') NOT NULL,
    imagen VARCHAR(255),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Etapas de vida de las aves
CREATE TABLE etapas_vida (
    id_etapa INT AUTO_INCREMENT PRIMARY KEY,
    nombre_etapa VARCHAR(100) NOT NULL,
    edad_inicio_dias INT NOT NULL,
    edad_fin_dias INT NOT NULL,
    descripcion TEXT,
    orden INT DEFAULT 0,
    CHECK (edad_fin_dias > edad_inicio_dias),
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parámetros nutricionales por tipo de ave y etapa
CREATE TABLE parametros_nutricionales (
    id_parametro INT AUTO_INCREMENT PRIMARY KEY,
    id_tipo_ave INT NOT NULL,
    id_etapa INT NOT NULL,
    consumo_alimento_gr_dia DECIMAL(8,2) NOT NULL COMMENT 'Gramos por ave por día',
    consumo_agua_ml_dia DECIMAL(8,2) NOT NULL COMMENT 'Mililitros por ave por día',
    proteina_porcentaje DECIMAL(5,2),
    energia_kcal DECIMAL(8,2),
    notas TEXT,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tipo_ave) REFERENCES tipos_ave(id_tipo_ave) ON DELETE CASCADE,
    FOREIGN KEY (id_etapa) REFERENCES etapas_vida(id_etapa) ON DELETE CASCADE,
    UNIQUE KEY uk_tipo_etapa (id_tipo_ave, id_etapa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Historial de cálculos de usuarios
CREATE TABLE historial_calculos (
    id_calculo INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    id_tipo_ave INT NOT NULL,
    id_etapa INT NOT NULL,
    cantidad_aves INT NOT NULL,
    edad_dias INT NOT NULL,
    resultado_alimento_kg DECIMAL(10,2),
    resultado_agua_litros DECIMAL(10,2),
    fecha_calculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    FOREIGN KEY (id_tipo_ave) REFERENCES tipos_ave(id_tipo_ave),
    FOREIGN KEY (id_etapa) REFERENCES etapas_vida(id_etapa),
    INDEX idx_usuario (id_usuario),
    INDEX idx_fecha (fecha_calculo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MÓDULO DE COMERCIO ELECTRÓNICO
-- ============================================

-- Categorías de productos
CREATE TABLE categorias_producto (
    id_categoria_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    icono VARCHAR(50),
    imagen VARCHAR(255),
    parent_id INT DEFAULT NULL COMMENT 'Para subcategorías',
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categorias_producto(id_categoria_producto) ON DELETE SET NULL,
    INDEX idx_parent (parent_id),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla principal de productos
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria_producto INT NOT NULL,
    codigo_producto VARCHAR(50) UNIQUE,
    nombre_producto VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    descripcion_corta TEXT,
    descripcion_larga LONGTEXT,
    precio_unitario DECIMAL(10,2) NOT NULL,
    precio_oferta DECIMAL(10,2),
    unidad_medida ENUM('unidad', 'kg', 'lb', 'docena', 'caja', 'saco') DEFAULT 'unidad',
    peso_gramos DECIMAL(8,2),
    stock_total INT DEFAULT 0 COMMENT 'Stock total en granja central',
    stock_minimo INT DEFAULT 5,
    requiere_refrigeracion TINYINT(1) DEFAULT 0,
    dias_frescura INT COMMENT 'Días de vida útil del producto',
    destacado TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria_producto) REFERENCES categorias_producto(id_categoria_producto),
    INDEX idx_categoria (id_categoria_producto),
    INDEX idx_slug (slug),
    INDEX idx_destacado (destacado),
    INDEX idx_activo (activo),
    FULLTEXT KEY ft_productos (nombre_producto, descripcion_corta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Atributos específicos de productos (para clasificación dinámica)
CREATE TABLE atributos_producto (
    id_atributo INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    nombre_atributo VARCHAR(100) NOT NULL COMMENT 'Ej: raza, calibre, edad',
    valor_atributo VARCHAR(255) NOT NULL COMMENT 'Ej: Rhode Island, Jumbo, 1 día',
    grupo_atributo VARCHAR(50) COMMENT 'Para agrupar atributos similares',
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
    INDEX idx_producto (id_producto),
    INDEX idx_nombre (nombre_atributo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Imágenes de productos
CREATE TABLE imagenes_producto (
    id_imagen INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    url_imagen VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    es_principal TINYINT(1) DEFAULT 0,
    orden INT DEFAULT 0,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
    INDEX idx_producto (id_producto),
    INDEX idx_principal (es_principal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MÓDULO DE SUCURSALES Y GEOLOCALIZACIÓN
-- ============================================

-- Tabla de sucursales
CREATE TABLE sucursales (
    id_sucursal INT AUTO_INCREMENT PRIMARY KEY,
    codigo_sucursal VARCHAR(20) UNIQUE NOT NULL,
    nombre_sucursal VARCHAR(200) NOT NULL,
    id_encargado INT COMMENT 'Usuario responsable de la sucursal',
    direccion_completa TEXT NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    departamento VARCHAR(100) NOT NULL,
    codigo_postal VARCHAR(10),
    latitud DECIMAL(10, 8) NOT NULL COMMENT 'Para geolocalización',
    longitud DECIMAL(11, 8) NOT NULL COMMENT 'Para geolocalización',
    telefono VARCHAR(20),
    email VARCHAR(150),
    capacidad_almacenamiento DECIMAL(10,2) COMMENT 'En metros cúbicos o kg',
    horario_apertura TIME,
    horario_cierre TIME,
    dias_atencion VARCHAR(100) DEFAULT 'Lunes a Sábado',
    permite_delivery TINYINT(1) DEFAULT 1,
    permite_pickup TINYINT(1) DEFAULT 1,
    radio_cobertura_km DECIMAL(5,2) DEFAULT 10.00 COMMENT 'Radio de delivery',
    imagen_fachada VARCHAR(255),
    activo TINYINT(1) DEFAULT 1,
    fecha_apertura DATE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_encargado) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_ciudad (ciudad),
    INDEX idx_activo (activo),
    INDEX idx_coords (latitud, longitud)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventario por sucursal
CREATE TABLE inventario_sucursal (
    id_inventario INT AUTO_INCREMENT PRIMARY KEY,
    id_sucursal INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad_disponible INT NOT NULL DEFAULT 0,
    cantidad_reservada INT DEFAULT 0 COMMENT 'Productos en pedidos pendientes',
    stock_minimo INT DEFAULT 5,
    stock_maximo INT,
    ultima_reposicion DATE,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sucursal) REFERENCES sucursales(id_sucursal) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
    UNIQUE KEY uk_sucursal_producto (id_sucursal, id_producto),
    INDEX idx_sucursal (id_sucursal),
    INDEX idx_producto (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Movimientos de inventario
CREATE TABLE movimientos_inventario (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_inventario INT NOT NULL,
    tipo_movimiento ENUM('entrada', 'salida', 'ajuste', 'transferencia', 'devolucion') NOT NULL,
    cantidad INT NOT NULL,
    motivo VARCHAR(255),
    id_usuario INT COMMENT 'Usuario que realizó el movimiento',
    id_pedido INT COMMENT 'Si es por una venta',
    referencia VARCHAR(100),
    fecha_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_inventario) REFERENCES inventario_sucursal(id_inventario) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_inventario (id_inventario),
    INDEX idx_fecha (fecha_movimiento),
    INDEX idx_tipo (tipo_movimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Horarios específicos de sucursales
CREATE TABLE horarios_sucursal (
    id_horario INT AUTO_INCREMENT PRIMARY KEY,
    id_sucursal INT NOT NULL,
    dia_semana ENUM('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo') NOT NULL,
    hora_apertura TIME NOT NULL,
    hora_cierre TIME NOT NULL,
    cerrado TINYINT(1) DEFAULT 0,
    FOREIGN KEY (id_sucursal) REFERENCES sucursales(id_sucursal) ON DELETE CASCADE,
    UNIQUE KEY uk_sucursal_dia (id_sucursal, dia_semana)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MÓDULO DE VENTAS Y PEDIDOS
-- ============================================

-- Métodos de entrega
CREATE TABLE metodos_entrega (
    id_metodo_entrega INT AUTO_INCREMENT PRIMARY KEY,
    nombre_metodo VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    costo_base DECIMAL(10,2) DEFAULT 0.00,
    costo_por_km DECIMAL(10,2) DEFAULT 0.00,
    tiempo_estimado_horas INT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estados de pedido
CREATE TABLE estados_pedido (
    id_estado INT AUTO_INCREMENT PRIMARY KEY,
    nombre_estado VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    color_hex VARCHAR(7) DEFAULT '#6c757d',
    orden INT DEFAULT 0,
    es_final TINYINT(1) DEFAULT 0 COMMENT 'Estado terminal (entregado, cancelado)',
    activo TINYINT(1) DEFAULT 1,
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla principal de pedidos
CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(20) UNIQUE NOT NULL,
    id_cliente INT NOT NULL,
    id_sucursal INT NOT NULL COMMENT 'Sucursal asignada para el pedido',
    id_estado INT NOT NULL,
    id_metodo_entrega INT NOT NULL,
    
    -- Datos de entrega
    direccion_entrega TEXT,
    ciudad_entrega VARCHAR(100),
    departamento_entrega VARCHAR(100),
    codigo_postal_entrega VARCHAR(10),
    latitud_entrega DECIMAL(10, 8),
    longitud_entrega DECIMAL(11, 8),
    distancia_km DECIMAL(6,2) COMMENT 'Distancia desde sucursal a cliente',
    
    -- Datos de contacto
    nombre_receptor VARCHAR(150),
    telefono_receptor VARCHAR(20),
    email_receptor VARCHAR(150),
    notas_cliente TEXT,
    
    -- Datos financieros
    subtotal DECIMAL(10,2) NOT NULL,
    costo_envio DECIMAL(10,2) DEFAULT 0.00,
    descuento DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    
    -- Datos de seguimiento
    fecha_estimada_entrega DATETIME,
    fecha_entrega_real DATETIME,
    comprobante_pago VARCHAR(255),
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'yape', 'plin') DEFAULT 'efectivo',
    pagado TINYINT(1) DEFAULT 0,
    
    -- Timestamps
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_cliente) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_sucursal) REFERENCES sucursales(id_sucursal),
    FOREIGN KEY (id_estado) REFERENCES estados_pedido(id_estado),
    FOREIGN KEY (id_metodo_entrega) REFERENCES metodos_entrega(id_metodo_entrega),
    
    INDEX idx_numero (numero_pedido),
    INDEX idx_cliente (id_cliente),
    INDEX idx_sucursal (id_sucursal),
    INDEX idx_estado (id_estado),
    INDEX idx_fecha (fecha_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Detalle de pedidos
CREATE TABLE detalle_pedido (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    notas TEXT,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_pedido (id_pedido),
    INDEX idx_producto (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Historial de estados de pedido (trazabilidad)
CREATE TABLE historial_pedido (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_estado INT NOT NULL,
    id_usuario INT COMMENT 'Usuario que realizó el cambio',
    comentario TEXT,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_estado) REFERENCES estados_pedido(id_estado),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_pedido (id_pedido),
    INDEX idx_fecha (fecha_cambio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Carrito de compras (temporal)
CREATE TABLE carrito (
    id_carrito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    session_id VARCHAR(100) COMMENT 'Para usuarios no logueados',
    id_producto INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario),
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Calificaciones y reseñas
CREATE TABLE calificaciones (
    id_calificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_cliente INT NOT NULL,
    id_producto INT,
    calificacion_producto TINYINT CHECK (calificacion_producto BETWEEN 1 AND 5),
    calificacion_servicio TINYINT CHECK (calificacion_servicio BETWEEN 1 AND 5),
    comentario TEXT,
    respuesta_vendedor TEXT,
    fecha_respuesta DATETIME,
    aprobado TINYINT(1) DEFAULT 0,
    fecha_calificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE SET NULL,
    INDEX idx_pedido (id_pedido),
    INDEX idx_producto (id_producto),
    INDEX idx_aprobado (aprobado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MÓDULO DE NOTIFICACIONES
-- ============================================

CREATE TABLE notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo_notificacion ENUM('pedido', 'sistema', 'promocion', 'alerta') DEFAULT 'sistema',
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    enlace VARCHAR(255),
    leida TINYINT(1) DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario),
    INDEX idx_leida (leida),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MÓDULO DE CONFIGURACIÓN
-- ============================================

CREATE TABLE configuracion_sistema (
    id_config INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    tipo_dato ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    descripcion TEXT,
    grupo VARCHAR(50),
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_clave (clave),
    INDEX idx_grupo (grupo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS INICIALES (SEEDS)
-- ============================================

-- Insertar roles básicos
INSERT INTO roles (nombre_rol, descripcion, nivel_acceso) VALUES
('Administrador', 'Control total del sistema, gestión de granja central', 100),
('Encargado Sucursal', 'Gestión de sucursal asignada, inventario y pedidos', 50),
('Cliente', 'Usuario final que realiza compras', 10),
('Veterinario', 'Gestión de contenido de Aveología', 30);

-- Insertar permisos básicos
INSERT INTO permisos (nombre_permiso, modulo, descripcion) VALUES
-- Usuarios
('usuarios.ver', 'usuarios', 'Ver lista de usuarios'),
('usuarios.crear', 'usuarios', 'Crear nuevos usuarios'),
('usuarios.editar', 'usuarios', 'Editar información de usuarios'),
('usuarios.eliminar', 'usuarios', 'Eliminar usuarios'),
-- Productos
('productos.ver', 'productos', 'Ver catálogo de productos'),
('productos.crear', 'productos', 'Crear nuevos productos'),
('productos.editar', 'productos', 'Editar productos existentes'),
('productos.eliminar', 'productos', 'Eliminar productos'),
-- Sucursales
('sucursales.ver', 'sucursales', 'Ver sucursales'),
('sucursales.crear', 'sucursales', 'Crear nuevas sucursales'),
('sucursales.editar', 'sucursales', 'Editar sucursales'),
('sucursales.eliminar', 'sucursales', 'Eliminar sucursales'),
('sucursales.inventario', 'sucursales', 'Gestionar inventario de sucursal'),
-- Pedidos
('pedidos.ver', 'pedidos', 'Ver pedidos'),
('pedidos.gestionar', 'pedidos', 'Gestionar estados de pedidos'),
('pedidos.cancelar', 'pedidos', 'Cancelar pedidos'),
-- Aveología
('aveologia.ver', 'aveologia', 'Consultar base de conocimientos'),
('aveologia.crear', 'aveologia', 'Crear artículos y contenido'),
('aveologia.editar', 'aveologia', 'Editar contenido existente');

-- Asignar permisos a roles
INSERT INTO roles_permisos (id_rol, id_permiso) 
SELECT 1, id_permiso FROM permisos; -- Administrador tiene todos los permisos

INSERT INTO roles_permisos (id_rol, id_permiso)
SELECT 2, id_permiso FROM permisos 
WHERE nombre_permiso IN (
    'productos.ver', 
    'sucursales.inventario', 
    'pedidos.ver', 
    'pedidos.gestionar',
    'aveologia.ver'
); -- Encargado Sucursal

INSERT INTO roles_permisos (id_rol, id_permiso)
SELECT 3, id_permiso FROM permisos 
WHERE nombre_permiso IN (
    'productos.ver',
    'aveologia.ver'
); -- Cliente

-- Usuario administrador por defecto (password: admin123)
INSERT INTO usuarios (id_rol, nombre_completo, email, telefono, password_hash, activo, verificado) VALUES
(1, 'Administrador del Sistema', 'admin@avitech.com', '987654321', '$2y$10$YourPasswordHashHereForAdmin123.1234567890123456789012345678901234567890', 1, 1);

-- Categorías de Aveología
INSERT INTO categorias_aveologia (nombre_categoria, descripcion, icono, orden) VALUES
('Enfermedades Comunes', 'Enfermedades más frecuentes en aves de corral', 'fa-virus', 1),
('Nutrición y Alimentación', 'Guías de alimentación balanceada', 'fa-utensils', 2),
('Bioseguridad', 'Protocolos de prevención y bioseguridad', 'fa-shield-alt', 3),
('Reproducción', 'Manejo reproductivo y incubación', 'fa-egg', 4),
('Manejo Sanitario', 'Protocolos de limpieza y desinfección', 'fa-spray-can', 5);

-- Tipos de ave
INSERT INTO tipos_ave (nombre_tipo, descripcion, proposito) VALUES
('Pollo de Engorde', 'Ave especializada para producción de carne', 'carne'),
('Gallina Ponedora', 'Ave especializada para producción de huevos', 'huevos'),
('Gallina de Doble Propósito', 'Ave para producción mixta', 'dual'),
('Pato', 'Aves acuáticas para carne', 'carne'),
('Codorniz', 'Ave pequeña para huevos y carne', 'dual');

-- Etapas de vida
INSERT INTO etapas_vida (nombre_etapa, edad_inicio_dias, edad_fin_dias, descripcion, orden) VALUES
('Pollito (0-7 días)', 0, 7, 'Primera semana crítica', 1),
('Iniciación (8-21 días)', 8, 21, 'Etapa de crecimiento inicial', 2),
('Crecimiento (22-42 días)', 22, 42, 'Desarrollo rápido', 3),
('Acabado (43-56 días)', 43, 56, 'Etapa final antes de venta', 4),
('Ponedora Joven (57-120 días)', 57, 120, 'Inicio de postura', 5),
('Ponedora Adulta (121+ días)', 121, 500, 'Producción máxima de huevos', 6);

-- Parámetros nutricionales (Pollo de Engorde)
INSERT INTO parametros_nutricionales (id_tipo_ave, id_etapa, consumo_alimento_gr_dia, consumo_agua_ml_dia, proteina_porcentaje, energia_kcal) VALUES
(1, 1, 15, 30, 23.0, 3000),
(1, 2, 45, 90, 21.0, 3100),
(1, 3, 95, 190, 19.0, 3200),
(1, 4, 130, 260, 18.0, 3200);

-- Parámetros nutricionales (Gallina Ponedora)
INSERT INTO parametros_nutricionales (id_tipo_ave, id_etapa, consumo_alimento_gr_dia, consumo_agua_ml_dia, proteina_porcentaje, energia_kcal) VALUES
(2, 1, 12, 25, 20.0, 2850),
(2, 2, 40, 85, 18.0, 2900),
(2, 5, 85, 180, 16.0, 2750),
(2, 6, 110, 250, 17.5, 2800);

-- Categorías de productos
INSERT INTO categorias_producto (nombre_categoria, slug, descripcion, icono) VALUES
('Alimentos Balanceados', 'alimentos', 'Alimentos especializados por etapa de crecimiento', 'fa-weight'),
('Aves Vivas', 'aves-vivas', 'Pollos, gallinas y otras aves en venta', 'fa-kiwi-bird'),
('Huevos', 'huevos', 'Huevos frescos clasificados por calibre', 'fa-egg');

-- Productos de ejemplo
INSERT INTO productos (id_categoria_producto, codigo_producto, nombre_producto, slug, descripcion_corta, precio_unitario, unidad_medida, stock_total, destacado, activo) VALUES
(1, 'ALM-001', 'Alimento Iniciador para Pollos BB', 'alimento-iniciador-pollos', 'Alimento balanceado especial para pollitos de 0-3 semanas', 85.00, 'saco', 100, 1, 1),
(1, 'ALM-002', 'Alimento Crecimiento Pollos', 'alimento-crecimiento', 'Alimento para pollos de 4-6 semanas', 78.00, 'saco', 150, 1, 1),
(1, 'ALM-003', 'Alimento Ponedoras Premium', 'alimento-ponedoras', 'Alimento especial para gallinas en postura', 92.00, 'saco', 80, 1, 1),
(2, 'AVE-001', 'Pollitos BB Rhode Island (1 día)', 'pollitos-rhode-island', 'Pollitos recién nacidos, raza Rhode Island Red', 8.50, 'unidad', 500, 1, 1),
(2, 'AVE-002', 'Pollo de Engorde (6 semanas)', 'pollo-engorde-6sem', 'Pollos parrilleros listos para consumo', 35.00, 'unidad', 200, 1, 1),
(2, 'AVE-003', 'Gallina Ponedora Adulta', 'gallina-ponedora', 'Gallinas en plena producción de huevos', 55.00, 'unidad', 100, 0, 1),
(3, 'HUE-001', 'Huevos Rosados Jumbo', 'huevos-jumbo', 'Huevos extra grandes, calibre Jumbo', 18.00, 'docena', 300, 1, 1),
(3, 'HUE-002', 'Huevos Rosados Primera', 'huevos-primera', 'Huevos calibre primera, frescos del día', 15.00, 'docena', 500, 1, 1),
(3, 'HUE-003', 'Huevos Blancos Segunda', 'huevos-segunda', 'Huevos calibre segunda, económicos', 12.00, 'docena', 400, 0, 1);

-- Métodos de entrega
INSERT INTO metodos_entrega (nombre_metodo, descripcion, costo_base, costo_por_km, tiempo_estimado_horas) VALUES
('Recojo en Sucursal', 'El cliente recoge el pedido en la sucursal seleccionada', 0.00, 0.00, 0),
('Delivery Standard', 'Entrega a domicilio en 24-48 horas', 5.00, 1.50, 24),
('Delivery Express', 'Entrega a domicilio el mismo día', 10.00, 2.00, 4);

-- Estados de pedido
INSERT INTO estados_pedido (nombre_estado, descripcion, color_hex, orden, es_final) VALUES
('Pendiente', 'Pedido registrado, esperando confirmación', '#ffc107', 1, 0),
('Confirmado', 'Pedido confirmado, en preparación', '#17a2b8', 2, 0),
('Preparando', 'Pedido en proceso de preparación', '#007bff', 3, 0),
('En Camino', 'Pedido en ruta de entrega', '#fd7e14', 4, 0),
('Listo para Recoger', 'Pedido disponible en sucursal', '#20c997', 5, 0),
('Entregado', 'Pedido entregado exitosamente', '#28a745', 6, 1),
('Cancelado', 'Pedido cancelado', '#dc3545', 7, 1);

-- Síntomas comunes
INSERT INTO sintomas (nombre_sintoma, descripcion, gravedad, categoria, keywords) VALUES
('Sangrado en el pico', 'Presencia de sangre en el pico del ave', 'grave', 'Respiratorio/Traumático', 'sangre, pico, hemorragia, lesion'),
('Diarrea verdosa', 'Heces líquidas de color verde', 'moderado', 'Digestivo', 'diarrea, verde, heces, deposiciones'),
('Plumas erizadas', 'Ave con plumaje erizado, aspecto desaliñado', 'leve', 'General', 'plumas, erizado, apariencia, enfermo'),
('Dificultad respiratoria', 'Respiración dificultosa, jadeo', 'grave', 'Respiratorio', 'respirar, jadeo, ahogo, tos'),
('Decaimiento', 'Ave quieta, sin energía, aislada del grupo', 'moderado', 'General', 'debil, apatia, quieto, letargo'),
('Pérdida de apetito', 'Ave no consume alimento', 'moderado', 'Digestivo', 'no come, inapetencia, anorexia'),
('Cresta pálida', 'Cresta de color pálido o azulado', 'moderado', 'Circulatorio', 'cresta, palido, azul, cianosis'),
('Hinchazón de patas', 'Patas inflamadas o aumentadas de tamaño', 'moderado', 'Articular', 'patas, inflamacion, hinchado, articulacion');

-- Enfermedades comunes
INSERT INTO enfermedades (nombre_enfermedad, nombre_cientifico, tipo_enfermedad, descripcion, causas, prevencion, contagiosidad, mortalidad_estimada) VALUES
('Newcastle', 'Paramyxovirus aviar tipo 1', 'viral', 'Enfermedad viral altamente contagiosa que afecta el sistema respiratorio, nervioso y digestivo de las aves', 'Virus Paramyxovirus transmitido por contacto directo, aire, agua o alimento contaminado', 'Vacunación obligatoria, bioseguridad estricta, cuarentena de aves nuevas', 'alta', 90.00),
('Coccidiosis', 'Eimeria spp.', 'parasitaria', 'Enfermedad parasitaria intestinal causada por protozoarios del género Eimeria', 'Parásitos microscópicos que se desarrollan en el intestino, favorecidos por humedad y hacinamiento', 'Higiene rigurosa, camas secas, uso de anticoccidianos preventivos', 'media', 20.00),
('Bronquitis Infecciosa', 'Coronavirus aviar', 'viral', 'Enfermedad respiratoria viral que afecta principalmente el tracto respiratorio superior', 'Coronavirus altamente contagioso transmitido por aerosoles', 'Vacunación sistemática, ventilación adecuada, control de bioseguridad', 'alta', 25.00),
('Viruela Aviar', 'Avipoxvirus', 'viral', 'Enfermedad viral que produce lesiones en piel, cresta y mucosas', 'Virus transmitido principalmente por mosquitos y contacto directo', 'Control de insectos vectores, vacunación, aislamiento de aves afectadas', 'media', 10.00);

-- Relación síntomas-enfermedades
INSERT INTO sintomas_enfermedades (id_enfermedad, id_sintoma, frecuencia, intensidad) VALUES
(1, 4, 'muy_frecuente', 'severo'), -- Newcastle - Dificultad respiratoria
(1, 5, 'muy_frecuente', 'severo'), -- Newcastle - Decaimiento
(1, 2, 'frecuente', 'moderado'),   -- Newcastle - Diarrea verdosa
(2, 2, 'muy_frecuente', 'severo'), -- Coccidiosis - Diarrea
(2, 6, 'muy_frecuente', 'severo'), -- Coccidiosis - Pérdida apetito
(2, 5, 'frecuente', 'moderado'),   -- Coccidiosis - Decaimiento
(3, 4, 'muy_frecuente', 'severo'), -- Bronquitis - Dificultad respiratoria
(3, 5, 'frecuente', 'moderado'),   -- Bronquitis - Decaimiento
(4, 3, 'frecuente', 'moderado'),   -- Viruela - Plumas erizadas
(4, 1, 'ocasional', 'leve');       -- Viruela - Sangrado en pico (lesiones)

-- Tratamientos
INSERT INTO tratamientos (id_enfermedad, nombre_tratamiento, tipo_tratamiento, descripcion, efectividad, requiere_veterinario) VALUES
(1, 'Vacuna La Sota', 'vacuna', 'Vacuna viva atenuada contra Newcastle', 95.00, 0),
(1, 'Vitaminas del complejo B', 'nutricional', 'Suplementación para fortalecer sistema inmune', 30.00, 0),
(2, 'Sulfaquinoxalina', 'medicamento', 'Anticoccidiano específico para tratamiento de coccidiosis', 85.00, 1),
(2, 'Amprolium', 'medicamento', 'Anticoccidiano preventivo y curativo', 80.00, 1),
(3, 'Vacuna H120', 'vacuna', 'Vacuna contra bronquitis infecciosa', 90.00, 0),
(4, 'Vacuna Viruela Aviar', 'vacuna', 'Vacuna preventiva contra viruela', 95.00, 0);

-- Configuración del sistema
INSERT INTO configuracion_sistema (clave, valor, tipo_dato, descripcion, grupo) VALUES
('sitio_nombre', 'AVITECH', 'string', 'Nombre del sistema', 'general'),
('sitio_email', 'contacto@avitech.com', 'string', 'Email de contacto', 'general'),
('sitio_telefono', '987654321', 'string', 'Teléfono de contacto', 'general'),
('costo_delivery_base', '5.00', 'integer', 'Costo base de delivery', 'ventas'),
('costo_por_km', '1.50', 'integer', 'Costo adicional por kilómetro', 'ventas'),
('radio_cobertura_km', '15', 'integer', 'Radio máximo de cobertura para delivery', 'ventas'),
('iva_porcentaje', '18', 'integer', 'Porcentaje de IGV', 'ventas'),
('moneda', 'PEN', 'string', 'Código de moneda', 'general'),
('items_por_pagina', '12', 'integer', 'Items por página en catálogo', 'general');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- FIN DEL SCRIPT
-- ============================================
