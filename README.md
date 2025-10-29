CarSharing - esqueleto web

Este repositorio contiene un esqueleto básico de aplicación web para un proyecto de Car Sharing.

Contenido creado:
- `index.php` - página de inicio (pública)
- `register.php` - formulario y lógica de registro (POST)
- `login.php` - formulario y lógica de inicio de sesión (POST)
- `menu.php` - menú principal (protegido, muestra tu nombre)
- `logout.php` - cierra la sesión
- `config.php` - configuración de la base de datos (rellenar credenciales)
- `classes/Sql.php` - clase con funciones generales para la BD (PDO)
- `css/style.css` - estilos básicos

Base de datos
------------
Crear una base de datos MySQL llamada `carsharing` (o ajustar `config.php`). Crear la tabla `users` con este SQL:

```sql
CREATE TABLE users (
	email VARCHAR(190) NOT NULL UNIQUE PRIMARY KEY,
	name VARCHAR(120) NOT NULL,
	password VARCHAR(255) NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Configurar
---------
Editar `config.php` y poner los datos reales de conexión:

```php
return [
	'host' => '127.0.0.1',
	'dbname' => 'carsharing',
	'user' => 'tu_usuario',
	'pass' => 'tu_contraseña',
];
```

Probar localmente (PHP built-in server)
-------------------------------------
En PowerShell, desde la carpeta del proyecto:

```powershell
php -S 127.0.0.1:8000
```

Abrir en el navegador `http://127.0.0.1:8000`.

Notas de seguridad y siguientes pasos
-----------------------------------
- Este es un esqueleto: añadir validaciones, protección CSRF, manejo de sesiones seguro y limpiezas al desplegar.
- Añadir páginas para buscar coches, reservas y administración.
# CARSHARING