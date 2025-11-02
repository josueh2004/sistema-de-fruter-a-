CREATE DATABASE sistema;
USE sistema;


CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    contraseña VARCHAR(50) NOT NULL COMMENT 'Contraseña en texto plano',
    correo VARCHAR(100) NOT NULL,
    celular VARCHAR(15) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL UNIQUE,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    contacto VARCHAR(100),
    telefono VARCHAR(15),
    direccion VARCHAR(255),
    activo BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    categoria_id INT,
    proveedor_id INT,
    precio_compra DECIMAL(10,2) NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL,
    stock_actual DECIMAL(10,2) DEFAULT 0,
    stock_minimo DECIMAL(10,2) DEFAULT 5,
    unidad_medida ENUM('kg', 'pieza', 'caja', 'racimo') NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo ENUM('entrada', 'salida') NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    motivo VARCHAR(255),
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE alertas_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    usuario_id INT COMMENT 'Usuario que atendió la alerta',
    mensaje VARCHAR(255) NOT NULL,
    cantidad_actual DECIMAL(10,2) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_atencion DATETIME DEFAULT NULL COMMENT 'Fecha y hora en que se atendió la alerta',
    atendida BOOLEAN DEFAULT FALSE,
    metodo_resolucion ENUM('automatico', 'manual') DEFAULT NULL COMMENT 'Cómo se resolvió: automático (por actualizar stock) o manual (marcar como atendida)',
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------12
CREATE TABLE IF NOT EXISTS tokens_recuperacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expiracion DATETIME NOT NULL,
    usado TINYINT(1) DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expiracion (expiracion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci 
COMMENT='Tabla para almacenar tokens de recuperación de contraseña';

-- ==================================================================
-- PROCEDIMIENTOS ALMACENADOS
-- ==================================================================

-- Procedimiento: generar_alertas_stock
DELIMITER //
CREATE PROCEDURE generar_alertas_stock()
BEGIN
    -- Paso 1: Marcar como atendidas automáticamente las alertas de productos que ya tienen stock suficiente
    UPDATE alertas_stock a
    INNER JOIN productos p ON a.producto_id = p.id
    SET a.atendida = TRUE,
        a.fecha_atencion = NOW(),
        a.metodo_resolucion = 'automatico'
    WHERE a.atendida = FALSE 
      AND p.stock_actual > p.stock_minimo;
    
    -- Paso 2: Eliminar alertas NO atendidas antiguas (más de 30 días)
    DELETE FROM alertas_stock 
    WHERE atendida = FALSE 
      AND DATEDIFF(NOW(), fecha) > 30;
    
    -- Paso 3: Insertar nuevas alertas para productos con stock bajo que NO tengan alerta activa
    INSERT INTO alertas_stock (producto_id, mensaje, cantidad_actual)
    SELECT 
        p.id,
        CONCAT('¡Alerta! Stock bajo de ', p.nombre, ': ', 
               p.stock_actual, ' ', p.unidad_medida, 
               ' (Mínimo requerido: ', p.stock_minimo, ' ', p.unidad_medida, ')'),
        p.stock_actual
    FROM productos p
    WHERE p.stock_actual <= p.stock_minimo 
      AND p.activo = TRUE
      AND NOT EXISTS (
          SELECT 1 
          FROM alertas_stock a 
          WHERE a.producto_id = p.id 
            AND a.atendida = FALSE
      );
END //
DELIMITER ;

-- Procedimiento: atender_alerta_manual
DELIMITER //
CREATE PROCEDURE atender_alerta_manual(
    IN p_alerta_id INT,
    IN p_usuario_id INT
)
BEGIN
    UPDATE alertas_stock
    SET atendida = TRUE,
        usuario_id = p_usuario_id,
        fecha_atencion = NOW(),
        metodo_resolucion = 'manual'
    WHERE id = p_alerta_id
      AND atendida = FALSE;
END //
DELIMITER ;

-- ==================================================================
-- TRIGGERS PARA ACTUALIZACIÓN AUTOMÁTICA DE ALERTAS
-- ==================================================================

-- Trigger: Después de actualizar un producto
DELIMITER //
CREATE TRIGGER after_producto_update
AFTER UPDATE ON productos
FOR EACH ROW
BEGIN
    -- Si el stock aumentó por encima del mínimo, marcar alerta como resuelta automáticamente
    IF NEW.stock_actual > NEW.stock_minimo AND OLD.stock_actual <= OLD.stock_minimo THEN
        UPDATE alertas_stock
        SET atendida = TRUE,
            fecha_atencion = NOW(),
            metodo_resolucion = 'automatico'
        WHERE producto_id = NEW.id
          AND atendida = FALSE;
    END IF;
    
    -- Si el stock bajó nuevamente, crear nueva alerta si no existe
    IF NEW.stock_actual <= NEW.stock_minimo AND NEW.activo = TRUE THEN
        INSERT IGNORE INTO alertas_stock (producto_id, mensaje, cantidad_actual)
        SELECT 
            NEW.id,
            CONCAT('¡Alerta! Stock bajo de ', NEW.nombre, ': ', 
                   NEW.stock_actual, ' ', NEW.unidad_medida, 
                   ' (Mínimo requerido: ', NEW.stock_minimo, ' ', NEW.unidad_medida, ')'),
            NEW.stock_actual
        WHERE NOT EXISTS (
            SELECT 1 FROM alertas_stock 
            WHERE producto_id = NEW.id AND atendida = FALSE
        );
    END IF;
END //
DELIMITER ;

-- Trigger: Después de insertar un movimiento
DELIMITER //
CREATE TRIGGER after_movimiento_insert
AFTER INSERT ON movimientos
FOR EACH ROW
BEGIN
    DECLARE nuevo_stock DECIMAL(10,2);
    DECLARE stock_minimo_prod DECIMAL(10,2);
    DECLARE nombre_prod VARCHAR(100);
    DECLARE unidad_prod VARCHAR(20);
    
    -- Obtener información del producto
    SELECT stock_actual, stock_minimo, nombre, unidad_medida
    INTO nuevo_stock, stock_minimo_prod, nombre_prod, unidad_prod
    FROM productos
    WHERE id = NEW.producto_id;
    
    -- Si el stock subió por encima del mínimo, resolver alertas automáticamente
    IF nuevo_stock > stock_minimo_prod THEN
        UPDATE alertas_stock
        SET atendida = TRUE,
            fecha_atencion = NOW(),
            metodo_resolucion = 'automatico'
        WHERE producto_id = NEW.producto_id
          AND atendida = FALSE;
    END IF;
    
    -- Si el stock está bajo, crear alerta si no existe
    IF nuevo_stock <= stock_minimo_prod THEN
        INSERT IGNORE INTO alertas_stock (producto_id, mensaje, cantidad_actual)
        SELECT 
            NEW.producto_id,
            CONCAT('¡Alerta! Stock bajo de ', nombre_prod, ': ', 
                   nuevo_stock, ' ', unidad_prod, 
                   ' (Mínimo requerido: ', stock_minimo_prod, ' ', unidad_prod, ')'),
            nuevo_stock
        WHERE NOT EXISTS (
            SELECT 1 FROM alertas_stock 
            WHERE producto_id = NEW.producto_id AND atendida = FALSE
        );
    END IF;
END //
DELIMITER ;

-- ==================================================================
-- GENERAR ALERTAS INICIALES
-- ==================================================================

-- Actualizar stock de productos basado en movimientos (si aplica)
SET SQL_SAFE_UPDATES = 0;
UPDATE productos p
SET stock_actual = GREATEST(0, ROUND((
    SELECT COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.cantidad ELSE -m.cantidad END), 0)
    FROM movimientos m
    WHERE m.producto_id = p.id
) + (RAND() * 50), 2));

-- Generar alertas automáticamente
CALL generar_alertas_stock();


-- ----------------------------------------------------------------------------------------

INSERT INTO usuarios (nombre_completo, usuario, contraseña, correo, celular) VALUES
('Josue Pérez Gómez', 'josue', 'josue123', 'josue.perez@gmail.com', '5551234567'),
('María López Díaz', 'marialopez', 'maria456', 'maria.lopez@gmail.com', '5552345678'),
('Carlos García Ruiz', 'carlosgarcia', 'carlos789', 'carlos.garcia@gmail.com', '5553456789'),
('Ana Martínez Vega', 'anamartinez', 'ana012', 'ana.martinez@gmail.com', '5554567890'),
('Luis Hernández Soto', 'luishernandez', 'luis345', 'luis.hernandez@gmail.com', '5555678901');

INSERT INTO usuarios (nombre_completo, usuario, contraseña, correo, celular) VALUES
-- Empleados de la frutería
('Roberto Sánchez Morales', 'roberto_san', 'roberto2024', 'roberto.sanchez@fruteria.com', '5567894321'),
('Carmen Gutiérrez Vega', 'carmen_gut', 'carmen2024', 'carmen.gutierrez@fruteria.com', '5567894322'),
('Fernando Jiménez Castro', 'fernando_jim', 'fernando2024', 'fernando.jimenez@fruteria.com', '5567894323'),
('Patricia Moreno Silva', 'patricia_mor', 'patricia2024', 'patricia.moreno@fruteria.com', '5567894324'),
('Alejandro Rivera Díaz', 'alejandro_riv', 'alejandro2024', 'alejandro.rivera@fruteria.com', '5567894325'),
('Isabel Herrera López', 'isabel_her', 'isabel2024', 'isabel.herrera@fruteria.com', '5567894326'),
('Ricardo Medina Torres', 'ricardo_med', 'ricardo2024', 'ricardo.medina@fruteria.com', '5567894327'),
('Monica Vargas Ruiz', 'monica_var', 'monica2024', 'monica.vargas@fruteria.com', '5567894328'),
('Gabriel Ortega Flores', 'gabriel_ort', 'gabriel2024', 'gabriel.ortega@fruteria.com', '5567894329'),
('Lucia Ramírez Mendoza', 'lucia_ram', 'lucia2024', 'lucia.ramirez@fruteria.com', '5567894330'),

-- Clientes frecuentes
('María Elena Cordero', 'maria_cordero', 'maria123', 'mariaelena.cordero@gmail.com', '5551112233'),
('Juan Carlos Vásquez', 'juan_vasquez', 'juan123', 'juancarlos.vasquez@gmail.com', '5551112234'),
('Esperanza Delgado', 'esperanza_del', 'esperanza123', 'esperanza.delgado@hotmail.com', '5551112235'),
('Raúl Domínguez', 'raul_dominguez', 'raul123', 'raul.dominguez@yahoo.com', '5551112236'),
('Teresa Aguilar', 'teresa_aguilar', 'teresa123', 'teresa.aguilar@gmail.com', '5551112237'),
('Héctor Peña', 'hector_pena', 'hector123', 'hector.pena@outlook.com', '5551112238'),
('Rosa Castañeda', 'rosa_castaneda', 'rosa123', 'rosa.castaneda@gmail.com', '5551112239'),
('Sergio Maldonado', 'sergio_mal', 'sergio123', 'sergio.maldonado@hotmail.com', '5551112240'),
('Beatriz Espinoza', 'beatriz_esp', 'beatriz123', 'beatriz.espinoza@gmail.com', '5551112241'),
('Arturo Salinas', 'arturo_sal', 'arturo123', 'arturo.salinas@yahoo.com', '5551112242'),

-- Proveedores como usuarios
('Víctor Manuel Cervantes', 'victor_cervantes', 'victor2024', 'victor.cervantes@proveedor.com', '5552223344'),
('Andrea Solís Navarro', 'andrea_solis', 'andrea2024', 'andrea.solis@proveedor.com', '5552223345'),
('Enrique Valdez León', 'enrique_valdez', 'enrique2024', 'enrique.valdez@proveedor.com', '5552223346'),
('Claudia Rojas Pineda', 'claudia_rojas', 'claudia2024', 'claudia.rojas@proveedor.com', '5552223347'),
('Rodrigo Campos Herrera', 'rodrigo_campos', 'rodrigo2024', 'rodrigo.campos@proveedor.com', '5552223348');


INSERT INTO usuarios (nombre_completo, usuario, contraseña, correo, celular)
SELECT 
    CONCAT(
        ELT(FLOOR(1 + RAND() * 10), 'Sofía', 'Pedro', 'Laura', 'Diego', 'Elena', 'Andrés', 'Valeria', 'Felipe', 'Camila', 'Miguel'), 
        ' ', 
        ELT(FLOOR(1 + RAND() * 10), 'Ramírez', 'Torres', 'Cruz', 'Flores', 'Reyes', 'Mendoza', 'Vega', 'Ortiz', 'Silva', 'Castro'), 
        ' ', 
        ELT(FLOOR(1 + RAND() * 10), 'Gómez', 'Pérez', 'López', 'Díaz', 'Ruiz', 'Soto', 'Vargas', 'Ríos', 'Moreno', 'Castillo')
    ) AS nombre_completo,
    CONCAT(
        LOWER(ELT(FLOOR(1 + RAND() * 10), 'sofia', 'pedro', 'laura', 'diego', 'elena', 'andres', 'valeria', 'felipe', 'camila', 'miguel')), 
        FLOOR(RAND() * 1000)
    ) AS usuario,
    CONCAT(
        LOWER(ELT(FLOOR(1 + RAND() * 10), 'sofia', 'pedro', 'laura', 'diego', 'elena', 'andres', 'valeria', 'felipe', 'camila', 'miguel')), 
        '123'
    ) AS contraseña,
    CONCAT(
        LOWER(ELT(FLOOR(1 + RAND() * 10), 'sofia', 'pedro', 'laura', 'diego', 'elena', 'andres', 'valeria', 'felipe', 'camila', 'miguel')), 
        FLOOR(RAND() * 1000), '@gmail.com'
    ) AS correo,
    CONCAT('555', FLOOR(RAND() * 10000000)) AS celular
FROM (
    SELECT a.N + b.N * 10 + 1 AS n
    FROM 
        (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
        (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) b
    ORDER BY n
    LIMIT 45
) t;


INSERT INTO categorias (nombre, descripcion) VALUES
('Cítricos', 'Frutas con alto contenido de vitamina C como naranjas y limones'),
('Tropicales', 'Frutas exóticas de climas cálidos como mango y piña'),
('Frutas de hueso', 'Frutas con semilla central como durazno y ciruela'),
('Bayas', 'Frutas pequeñas como fresas y arándanos'),
('Frutas de pepita', 'Frutas como manzanas y peras'),
('Frutas de cáscara', 'Frutas con cáscara dura como coco'),
('Frutas de temporada', 'Frutas disponibles en ciertas épocas del año'),
('Frutas orgánicas', 'Frutas cultivadas sin pesticidas'),
('Frutas importadas', 'Frutas traídas de otros países'),
('Otros', 'Frutas que no encajan en otras categorías');

INSERT INTO categorias (nombre, descripcion) VALUES
('Cítricos', 'Frutas ricas en vitamina C: naranjas, limones, toronjas, mandarinas'),
('Frutas Tropicales', 'Frutas exóticas de clima cálido: mango, piña, papaya, maracuyá'),
('Bayas y Frutos del Bosque', 'Frutos pequeños ricos en antioxidantes: fresas, arándanos, frambuesas'),
('Frutas de Hueso', 'Frutas con semilla central grande: durazno, ciruela, albaricoque, cereza'),
('Frutas de Pepita', 'Frutas con semillas pequeñas: manzana, pera, membrillo'),
('Melones y Sandías', 'Frutas grandes y jugosas de temporada de calor'),
('Frutas de Cáscara Dura', 'Frutas con cáscara resistente: coco, granada'),
('Plátanos y Bananas', 'Variedades de plátano: tabasco, macho, dominico, roatan'),
('Uvas', 'Diferentes variedades de uva: roja, verde, morada, sin semilla'),
('Frutas de Temporada Fría', 'Frutas disponibles en invierno: mandarina, naranja, tejocote'),
('Frutas Exóticas', 'Frutas importadas poco comunes: dragón, rambután, lichi'),
('Frutas Deshidratadas', 'Frutas procesadas sin agua: pasas, dátiles, higos secos'),
('Frutas Orgánicas', 'Frutas cultivadas sin pesticidas ni químicos'),
('Ensaladas de Frutas', 'Mezclas preparadas de frutas cortadas'),
('Jugos Naturales', 'Jugos frescos extraídos de frutas del día');


INSERT INTO proveedores (nombre, contacto, telefono, direccion, activo) VALUES
('Frutas del Valle', 'José Ramírez', '5559876543', 'Calle del Sol 123, Ciudad', TRUE),
('AgroFresh SA', 'Marta Gómez', '5558765432', 'Av. Central 456, Pueblo', TRUE),
('EcoFrut', 'Luis Vargas', '5557654321', 'Camino Verde 789, Villa', TRUE),
('Frutales del Norte', 'Clara Ortiz', '5556543210', 'Calle Luna 101, Norte', TRUE),
('Sabor Tropical', 'Raúl Castro', '5555432109', 'Av. Palmeras 202, Sur', TRUE);

INSERT INTO proveedores (nombre, contacto, telefono, direccion, activo) VALUES
-- Proveedores locales principales
('Huerta San Miguel', 'Miguel Ángel Ruiz', '7831234567', 'Carretera Tuxpan-Tampico Km 12, Tuxpan, Ver.', TRUE),
('Cítricos del Golfo', 'Carmen Vela Santos', '7831234568', 'Ejido La Mata, Tuxpan, Veracruz', TRUE),
('Frutas Tropicales Veracruz', 'José Luis Mendoza', '7831234569', 'Av. Juárez 245, Col. Centro, Tuxpan, Ver.', TRUE),
('Distribuidora El Mango', 'Ana Patricia Herrera', '7831234570', 'Calle Morelos 156, Col. Obrera, Tuxpan, Ver.', TRUE),
('Hortofrutícola Tuxpan', 'Roberto Carlos Sánchez', '7831234571', 'Blvd. Reyes Heroles 890, Tuxpan, Ver.', TRUE),

-- Proveedores regionales
('Central de Abastos Poza Rica', 'Fernando Gutiérrez López', '7821234567', 'Central de Abastos, Poza Rica, Veracruz', TRUE),
('Frutas del Norte Veracruzano', 'María Guadalupe Torres', '7821234568', 'Av. Lázaro Cárdenas 445, Poza Rica, Ver.', TRUE),
('Mercado Regional Papantla', 'Eduardo Ramírez Vega', '7844234567', 'Mercado Municipal, Papantla, Veracruz', TRUE),
('Agrocomercial Totonaca', 'Silvia Elena Morales', '7844234568', 'Carretera Papantla-Poza Rica Km 8, Papantla, Ver.', TRUE),
('Distribuciones Huastecas', 'Armando Castillo Pérez', '7691234567', 'Av. Universidad 234, Tantoyuca, Ver.', TRUE),

-- Proveedores estatales
('Mercado de Abasto Xalapa', 'Gloria Esperanza Díaz', '2281234567', 'Central de Abastos, Xalapa, Veracruz', TRUE),
('Citrus Export Veracruz', 'Ing. Carlos Mendoza Silva', '2291234567', 'Puerto de Veracruz, Zona Industrial', TRUE),
('Tropical Fruits Córdoba', 'Licda. Patricia Jiménez', '2711234567', 'Zona Cafetalera, Córdoba, Veracruz', TRUE),
('Plátanos de Tierra Blanca', 'Juan Manuel Herrera', '2741234567', 'Carretera Costera, Tierra Blanca, Ver.', TRUE),
('Frutales de la Huasteca', 'Rosa María Valdez', '7461234567', 'Región Huasteca, Chicontepec, Ver.', TRUE),

-- Proveedores nacionales
('Central de Abastos CDMX', 'Mario Alberto Gómez', '5551234567', 'Central de Abastos, Iztapalapa, CDMX', TRUE),
('Frutas del Bajío', 'Esperanza Luna Castillo', '4621234567', 'Zona Agrícola, Irapuato, Guanajuato', TRUE),
('Tropical de Nayarit', 'Luis Enrique Moreno', '3111234567', 'Tepic, Nayarit - Zona Manguera', TRUE),
('Berries de Michoacán', 'Sandra Leticia Aguilar', '4431234567', 'Región Frailesca, Michoacán', TRUE),
('Cítricos de Nuevo León', 'Raúl Alejandro Peña', '8181234567', 'Zona Citrícola, Montemorelos, N.L.', TRUE),

-- Importadores
('Importadora Centroamericana', 'Lic. Jorge Enrique Solís', '5562234567', 'Aduana de Veracruz, Ver.', TRUE),
('Global Fruit Import', 'Patricia Elena Rojas', '5562234568', 'Puerto de Altamira, Tamaulipas', TRUE),
('Exotic Fruits México', 'Dr. Alejandro Campos', '5562234569', 'Aeropuerto Internacional CDMX', TRUE),
('Frutas del Mundo SA', 'Ing. Claudia Patricia Herrera', '5562234570', 'Puerto de Manzanillo, Colima', TRUE),
('International Produce', 'Rodrigo Valdez Navarro', '5562234571', 'Laredo, Texas - Nuevo Laredo, Tamps.', TRUE);

INSERT INTO proveedores (nombre, contacto, telefono, direccion, activo)
SELECT 
    CONCAT('Frutas ', ELT(FLOOR(1 + RAND() * 5), 'del Sur', 'del Campo', 'Naturales', 'Frescas', 'Premium'), FLOOR(RAND() * 100)) AS nombre,
    CONCAT(
        ELT(FLOOR(1 + RAND() * 10), 'Andrés', 'Carolina', 'Felipe', 'Valeria', 'Miguel', 'Sofía', 'Pedro', 'Laura', 'Diego', 'Elena'), 
        ' ', 
        ELT(FLOOR(1 + RAND() * 10), 'López', 'Pérez', 'Gómez', 'Ríos', 'Vega', 'Ramírez', 'Torres', 'Cruz', 'Flores', 'Reyes')
    ) AS contacto,
    CONCAT('555', FLOOR(RAND() * 10000000)) AS telefono,
    CONCAT(
        ELT(FLOOR(1 + RAND() * 5), 'Calle ', 'Avenida ', 'Camino ', 'Paseo ', 'Carretera '), 
        ELT(FLOOR(1 + RAND() * 5), 'Sol', 'Luna', 'Estrella', 'Río', 'Cielo'), 
        ' ', FLOOR(RAND() * 1000)
    ) AS direccion,
    TRUE AS activo
FROM (
    SELECT a.N + b.N * 10 + 1 AS n
    FROM 
        (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
        (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2) b
    ORDER BY n
    LIMIT 25
) t;


INSERT INTO productos (nombre, categoria_id, proveedor_id, precio_compra, precio_venta, stock_actual, stock_minimo, unidad_medida, activo)
SELECT 
    ELT(FLOOR(1 + RAND() * 20), 
        'Manzana', 'Pera', 'Naranja', 'Limón', 'Mango', 'Piña', 'Fresa', 'Arándano', 
        'Durazno', 'Ciruela', 'Plátano', 'Uva', 'Sandía', 'Melón', 'Coco', 
        'Kiwi', 'Granada', 'Papaya', 'Guayaba', 'Mandarina'
    ) AS nombre,
    FLOOR(1 + RAND() * 10) AS categoria_id,
    FLOOR(1 + RAND() * 30) AS proveedor_id,
    ROUND(RAND() * 20 + 5, 2) AS precio_compra,
    ROUND((RAND() * 20 + 5) * 1.3, 2) AS precio_venta,
    ROUND(RAND() * 100, 2) AS stock_actual,
    5.00 AS stock_minimo,
    ELT(FLOOR(1 + RAND() * 4), 'kg', 'pieza', 'caja', 'racimo') AS unidad_medida,
    TRUE AS activo
FROM (
    SELECT a.N + b.N * 10 + 1 AS n
    FROM 
        (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
        (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b
    ORDER BY n
    LIMIT 100
) t;


INSERT INTO movimientos (producto_id, usuario_id, tipo, cantidad, fecha, motivo)
SELECT 
    FLOOR(1 + RAND() * 100) AS producto_id,
    FLOOR(1 + RAND() * 50) AS usuario_id,
    ELT(FLOOR(1 + RAND() * 2), 'entrada', 'salida') AS tipo,
    ROUND(RAND() * 50 + 1, 2) AS cantidad,
    DATE_SUB(CURRENT_TIMESTAMP, INTERVAL FLOOR(RAND() * 180) DAY) AS fecha,
    ELT(FLOOR(1 + RAND() * 3), 'Reabastecimiento', 'Venta al cliente', 'Ajuste de inventario') AS motivo
FROM (
    SELECT a.N + b.N * 10 + 1 AS n
    FROM 
        (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
        (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b
    ORDER BY n
    LIMIT 750
) t;

-- ------------2

SET SQL_SAFE_UPDATES = 0;
UPDATE productos p
SET stock_actual = GREATEST(0, ROUND((
    SELECT COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.cantidad ELSE -m.cantidad END), 0)
    FROM movimientos m
    WHERE m.producto_id = p.id
) + (RAND() * 50), 2));


INSERT INTO alertas_stock (producto_id, mensaje, cantidad_actual, atendida)
SELECT
    p.id AS producto_id,
    CONCAT('¡Alerta! Stock bajo de ', p.nombre, ': ', p.stock_actual, ' ', p.unidad_medida, ' (Mínimo requerido: ', p.stock_minimo, ' ', p.unidad_medida, ')') AS mensaje,
    p.stock_actual AS cantidad_actual,
    FALSE AS atendida
FROM productos p
WHERE p.stock_actual <= p.stock_minimo AND p.activo = TRUE
LIMIT 60;




INSERT IGNORE INTO usuarios (nombre_completo, usuario, contraseña, correo, celular)
VALUES ('Josue Pérez Gómez (Admin)', 'josueh5', 'josue123', 'josueh5.perez@gmail.com', '5551234567');


INSERT INTO administradores (usuario_id)
SELECT id FROM usuarios WHERE usuario = 'josueh5'
ON DUPLICATE KEY UPDATE usuario_id = usuario_id;
