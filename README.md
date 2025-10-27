# CARSHARING 🚗

Una aplicación web para compartir vehículos (car sharing) desarrollada con PHP, HTML y CSS.

## Características

- Sistema de inicio de sesión y registro de usuarios
- Conexión a base de datos MySQL
- Interfaz moderna y responsive
- Backend en PHP con programación orientada a objetos
- Gestión de sesiones de usuario
- Menú principal con opciones de navegación

## Estructura del Proyecto

```
CARSHARING/
│
├── css/
│   └── style.css           # Estilos de la aplicación
│
├── includes/
│   └── Database.php        # Clase de conexión y operaciones de base de datos
│
├── index.php               # Página de inicio de sesión
├── register.php            # Página de registro
├── menu.php                # Menú principal (requiere autenticación)
├── logout.php              # Cierre de sesión
├── database_setup.sql      # Script SQL para crear la base de datos
└── README.md               # Este archivo
```

## Instalación

### Requisitos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx) o PHP built-in server

### Pasos de instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/QPC-Salle/CARSHARING.git
   cd CARSHARING
   ```

2. **Configurar la base de datos**
   - Crear la base de datos ejecutando el archivo `database_setup.sql`:
   ```bash
   mysql -u root -p < database_setup.sql
   ```
   
   - O importar desde phpMyAdmin/MySQL Workbench

3. **Configurar la conexión a la base de datos**
   - Editar el archivo `includes/Database.php` si es necesario:
   ```php
   private $host = "localhost";
   private $db_name = "carsharing";
   private $username = "root";
   private $password = "";
   ```

4. **Iniciar el servidor**
   
   Opción A - PHP built-in server:
   ```bash
   php -S localhost:8000
   ```
   
   Opción B - Apache/Nginx:
   - Copiar el proyecto a la carpeta del servidor web (htdocs, www, etc.)
   - Acceder desde el navegador

5. **Acceder a la aplicación**
   - Abrir el navegador en `http://localhost:8000` (o la URL configurada)
   - Crear una cuenta nueva en el enlace "Regístrate aquí"
   - Iniciar sesión con las credenciales creadas

## Uso

1. **Registro**: Crear una nueva cuenta con nombre, email y contraseña
2. **Inicio de sesión**: Acceder con email y contraseña
3. **Menú principal**: Navegar por las diferentes opciones (próximamente funcionales)
4. **Cerrar sesión**: Botón en el menú principal

## Base de Datos

La aplicación utiliza las siguientes tablas:

- **users**: Información de usuarios registrados
- **vehicles**: Vehículos disponibles para compartir
- **reservations**: Reservas realizadas por los usuarios
- **payments**: Historial de pagos
- **reviews**: Valoraciones de vehículos

## Tecnologías

- **Backend**: PHP 7.4+ con PDO
- **Frontend**: HTML5, CSS3
- **Base de datos**: MySQL
- **Seguridad**: Password hashing con `password_hash()`, consultas preparadas (PDO)

## Características de Seguridad

- Contraseñas hasheadas con `password_hash()`
- Consultas preparadas para prevenir SQL injection
- Validación de sesiones
- Escape de datos de salida con `htmlspecialchars()`

## Desarrollador

Desarrollado por: GitHub Copilot Agent

## Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.