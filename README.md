# AVITECH - Sistema Integral de Gestión Avícola

## 🚀 Instalación y Configuración

### Requisitos Previos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache con mod_rewrite habilitado
- Laragon (recomendado) o XAMPP/WAMP

### Pasos de Instalación

#### 1. Importar la Base de Datos

1. Abre **HeidiSQL**
2. Conecta a tu servidor MySQL local
3. Click derecho en el panel izquierdo → **Ejecutar archivo SQL**
4. Selecciona el archivo `database.sql`
5. Ejecuta el script

La base de datos `avitech_db` será creada automáticamente con:
- 30+ tablas normalizadas (3FN)
- Datos iniciales (roles, permisos, categorías)
- Usuario administrador por defecto

#### 2. Configurar la Conexión

Edita el archivo `/config/config.php` y verifica las credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'avitech_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Tu contraseña de MySQL
```

#### 3. Configurar Apache

Si usas Laragon, el proyecto debe estar en:
```
C:\laragon\www\Proyecto_Sistema_Avicola1\
```

La URL de acceso será:
```
http://localhost/Proyecto_Sistema_Avicola1/public/
```

#### 4. Verificar mod_rewrite

Asegúrate de que mod_rewrite esté habilitado en Apache.

#### 5. Crear Carpetas de Uploads

Crea la carpeta para subir imágenes:
```
/public/uploads/
/public/uploads/productos/
/public/uploads/avatars/
```

---

## 👤 Credenciales de Acceso

### Usuario Administrador
- **Email:** admin@avitech.com
- **Contraseña:** admin123

### Crear Nuevos Usuarios
Puedes registrar nuevos usuarios desde:
```
http://localhost/Proyecto_Sistema_Avicola1/public/auth/register
```

---

## 📁 Estructura del Proyecto

```
Proyecto_Sistema_Avicola1/
├── config/
│   └── config.php              # Configuración general
├── core/
│   ├── Database.php            # Conexión PDO (Singleton)
│   ├── Session.php             # Manejo de sesiones
│   └── Controller.php          # Controlador base
├── controllers/
│   ├── AuthController.php      # Autenticación
│   ├── HomeController.php      # Página de inicio
│   └── ErrorController.php     # Manejo de errores
├── models/
│   ├── Usuario.php             # Modelo de usuarios
│   ├── Producto.php            # Modelo de productos
│   ├── Sucursal.php            # Modelo de sucursales
│   ├── Pedido.php              # Modelo de pedidos
│   ├── Aveologia.php           # Modelo de Aveología
│   └── Calculadora.php         # Modelo de calculadora
├── views/
│   ├── layouts/
│   │   ├── header.php          # Encabezado común
│   │   └── footer.php          # Pie de página
│   ├── auth/
│   │   ├── login.php           # Vista de login
│   │   └── register.php        # Vista de registro
│   ├── home/
│   │   └── index.php           # Página de inicio
│   └── errors/
│       └── 404.php             # Página de error 404
├── public/
│   ├── css/
│   │   ├── style.css           # Estilos principales
│   │   ├── navbar.css          # Estilos del navbar
│   │   └── auth.css            # Estilos de autenticación
│   ├── js/
│   │   ├── main.js             # JavaScript principal
│   │   └── navbar.js           # JavaScript del navbar
│   ├── uploads/                # Imágenes subidas
│   ├── index.php               # Front Controller
│   └── .htaccess               # Reescritura de URLs
└── database.sql                # Script de base de datos
```

---

## 🎯 Módulos del Sistema

### 1. Autenticación
- ✅ Login con validación
- ✅ Registro de usuarios
- ✅ Gestión de sesiones seguras
- ✅ Control de acceso por roles (RBAC)

### 2. Aveología (Base de Conocimientos)
- ✅ Búsqueda de síntomas
- ✅ Diagnóstico de enfermedades
- ✅ Tratamientos y remedios
- ✅ Artículos educativos

### 3. Calculadora de Recursos
- ✅ Cálculo de alimento por ave
- ✅ Cálculo de consumo de agua
- ✅ Proyecciones semanales y mensuales
- ✅ Recomendaciones nutricionales

### 4. E-commerce
- ✅ Catálogo de productos (Alimentos, Aves, Huevos)
- ✅ Sistema de carrito
- ✅ Gestión de inventario por sucursal
- ✅ Pedidos con delivery o pick-up

### 5. Sucursales
- ✅ Gestión multi-sucursal
- ✅ Geolocalización y cálculo de distancias
- ✅ Inventario independiente por sucursal
- ✅ Asignación automática de pedidos

### 6. Pedidos
- ✅ Estados de pedido (Pendiente → Entregado)
- ✅ Cálculo de costos de envío
- ✅ Historial y trazabilidad
- ✅ Sistema de calificaciones

---

## 🎨 Características de Diseño

### Tecnologías Frontend
- **CSS Puro** con variables CSS (Custom Properties)
- **Glassmorphism** (efecto de vidrio esmerilado)
- **Modo Oscuro** por defecto
- **Diseño Responsive** (Mobile-first)
- **Animaciones** suaves y micro-interacciones
- **Font Awesome** para iconos
- **Google Fonts** (Inter + Poppins)

### Colores Premium
- Primary: #2ecc71 (Verde avícola)
- Secondary: #3498db (Azul profesional)
- Accent: #f39c12 (Naranja energético)
- Background: Gradientes oscuros con transparencias

---

## 🔒 Seguridad Implementada

- ✅ Prepared Statements (PDO) contra SQL Injection
- ✅ Password hashing con `password_hash()`
- ✅ CSRF Token en formularios
- ✅ Sanitización de entradas con `htmlspecialchars()`
- ✅ Validación de sesiones
- ✅ Control de acceso basado en roles

---

## 🗄️ Base de Datos (3FN)

### Tablas Principales
- **usuarios, roles, permisos** - Autenticación
- **categorias_aveologia, articulos_aveologia, sintomas, enfermedades** - Aveología
- **tipos_ave, etapas_vida, parametros_nutricionales** - Calculadora
- **productos, categorias_producto, imagenes_producto** - E-commerce
- **sucursales, inventario_sucursal** - Gestión de sucursales
- **pedidos, detalle_pedido, estados_pedido** - Ventas

Todas las tablas están normalizadas en **Tercera Forma Normal (3FN)** con:
- Llaves primarias
- Llaves foráneas con restricciones
- Índices optimizados
- Campos con tipos de datos apropiados

---

## 📝 Uso del Sistema

### Flujo de Usuario Cliente

1. **Registro/Login**
   - Ir a `/auth/register` o `/auth/login`
   - Completar formulario
   - Sistema asigna rol "Cliente" automáticamente

2. **Explorar Productos**
   - Ver catálogo en `/tienda`
   - Filtrar por categoría
   - Ver detalles del producto

3. **Realizar Compra**
   - Agregar productos al carrito
   - Seleccionar método de entrega (Delivery/Pick-up)
   - Sistema asigna la sucursal más cercana
   - Confirmar pedido

4. **Consultar Aveología**
   - Ir a `/aveologia`
   - Buscar síntomas
   - Ver diagnósticos sugeridos
   - Consultar tratamientos

5. **Usar Calculadora**
   - Ir a `/calculadora`
   - Seleccionar tipo de ave
   - Ingresar cantidad y edad
   - Obtener requerimientos de alimento y agua

### Flujo de Administrador

1. **Login como Admin**
   - Email: admin@avitech.com
   - Pass: admin123

2. **Dashboard**
   - Ver estadísticas
   - Gestionar usuarios
   - CRUD de productos
   - Gestionar sucursales
   - Ver y procesar pedidos

---

## 🚧 Próximas Funcionalidades (Opcionales)

- [ ] Panel de reportes y estadísticas
- [ ] Sistema de notificaciones en tiempo real
- [ ] Integración con pasarelas de pago
- [ ] App móvil con React Native
- [ ] Chat en vivo con soporte
- [ ] Sistema de cupones y descuentos
- [ ] API REST para integraciones

---

## 📞 Soporte

Para problemas o consultas sobre el sistema:
- Email: contacto@avitech.com
- Teléfono: 987654321

---

## 📄 Licencia

Sistema desarrollado para fines educativos y comerciales.
© 2024 AVITECH - Todos los derechos reservados.

---

## ✨ Desarrollado con

- ❤️ Pasión por la tecnología
- 🐔 Amor por la industria avícola
- 🚀 Deseo de innovar

**¡Gracias por usar AVITECH!**
