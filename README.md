# Checkpoint
Donde puedes ver, notas, genero y agregar tus juegos favoritos

# Instalación y Configuración

## Requisitos

* PHP
* MySQL
* Servidor local (XAMPP, WAMP, Laragon, etc.)
* Cuenta de Twitch

## Configuración de Twitch

Para que la aplicación funcione correctamente, necesitas obtener una clave de Twitch:

1. Crea una cuenta en Twitch si aún no tienes una.
2. Accede al panel de desarrolladores de Twitch.
3. Crea una nueva aplicación y obtén tu **Client ID** y/o la clave necesaria para el proyecto.
4. Abre el archivo `config.php`.
5. Sustituye los valores de configuración por tus propias credenciales de Twitch.

Ejemplo:

```php
$twitch_key = "TU_CLAVE_AQUI";
```

## Configuración de la Base de Datos

El proyecto incluye un archivo SQL con la estructura de la base de datos.

1. Crea una nueva base de datos en MySQL.
2. Importa el archivo `.sql` incluido en el proyecto.
3. Configura los datos de conexión en `config.php`:

```php
$db_host = "localhost";
$db_name = "nombre_base_datos";
$db_user = "usuario";
$db_pass = "contraseña";
```

## Ejecución

1. Copia el proyecto en el directorio de tu servidor local.
2. Asegúrate de que Apache y MySQL estén ejecutándose.
3. Accede al proyecto desde tu navegador:

```
http://localhost/nombre-del-proyecto
```

## Notas

* Este proyecto está diseñado para ejecutarse en un entorno local.
* Es necesario configurar las credenciales de Twitch antes de utilizar la aplicación.
* La base de datos debe importarse previamente para que el sistema funcione correctamente.
