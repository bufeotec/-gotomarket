## Plantilla Base para GoToMarket

Esta plantilla proporciona una base sólida y versátil para el desarrollo de proyectos en Laravel. Incluye todas las funcionalidades esenciales para acelerar el proceso de desarrollo, manteniendo una estructura confiable y optimizada para proyectos escalables.

### Características Principales:

- **Autenticación**: Implementada con Laravel Fortify, ofreciendo un sistema seguro y flexible para gestionar la autenticación de usuarios.
- **Roles y Permisos**: Gestión avanzada de roles y permisos con la potente librería Laravel Spatie.
- **Gestión de Menús y Submenús**: Configuración dinámica de menús, submenús y permisos, proporcionando un control detallado sobre la navegación de la aplicación.
- **Edición de Perfil de Usuario**: Interfaz para que los usuarios actualicen su información personal de manera intuitiva.
- **Notificaciones en Tiempo Real**: Integración con Laravel Reverb para enviar notificaciones instantáneas sin necesidad de recargar la página.
- **Interactividad con Livewire**: Utiliza Laravel Livewire para mejorar la experiencia del usuario, eliminando la sobrecarga de recargas de página y haciendo la interfaz más fluida.

### Instrucciones de Instalación

Sigue estos pasos para instalar las dependencias y configurar el entorno del proyecto:

1. Clona el repositorio y navega a la carpeta del proyecto:

    ```bash
    git clone <url-del-repositorio>
    cd nombre-del-proyecto
    ```

2. Instala las dependencias de Composer:

    ```bash
    composer install
    ```

3. Copia el archivo de configuración de entorno y genera la clave de aplicación:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

   Esto creará la carpeta `vendor` con todas las dependencias del proyecto, el archivo `.env` para las variables de entorno y la clave necesaria para el funcionamiento de Laravel.
4. Generar Variables Globales para Laravel Reverb

   Genera las variables de configuración para Laravel Reverb ejecutando el siguiente comando:
    ```bash
    php artisan reverb:install
    ```
   Esto creará  las variables para la configuracion de laravel reverb

### Configuración de un Servidor Local con Laragon

Sigue estos pasos para configurar un servidor local en Laragon:

1. Abre Laragon y navega al archivo de configuración predeterminado de Apache:
    - Ve a **Menú > Apache > sites-enabled > 00-default.conf** y abre el archivo.

2. Registra el nombre del servidor y la ubicación del proyecto en el archivo `00-default.conf` añadiendo lo siguiente:

```apache
<VirtualHost *:80>
    ServerName nombre_proyecto.local
    DocumentRoot "C:/laragon/www/nombre_proyecto/public"

    <Directory "C:/laragon/www/nombre_proyecto/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
3. Abre el archivo `hosts` en la ubicación `C:\Windows\System32\drivers\etc\hosts` con permisos de administrador.

4. Agrega la siguiente línea para registrar el host virtual:

    ```lua
    127.0.0.1   nombre_proyecto.local
    ```

   Por ejemplo, si tu proyecto se llama `proyecto`, la línea sería:

    ```lua
    127.0.0.1   proyecto.local
    ```

5. Guarda los cambios y reinicia Laragon.
6. Finalmente, abre tu navegador y accede al proyecto utilizando el nombre del host virtual (por ejemplo, `http://proyecto.local`).

### Configuración del archivo `.env`

A continuación, se deben ajustar algunas variables de entorno en el archivo `.env` para que tu proyecto funcione correctamente con el entorno local y el virtual host.

#### Modificar la URL de la aplicación

Establece el nombre del virtual host en la variable `APP_URL`:

```env
APP_URL=http://nombre_proyecto.local/
```

#### Configurar la base de datos
Define los valores de conexión a la base de datos según tu entorno:

```env
DB_CONNECTION=mysql        # Tipo de base de datos
DB_HOST=127.0.0.1          # Host de la base de datos
DB_PORT=3306               # Puerto de conexión
DB_DATABASE=nombre_bd      # Nombre de la base de datos
DB_USERNAME=usuario_bd     # Usuario de la base de datos
DB_PASSWORD=               # Contraseña (dejar vacío si no hay)
 ```

#### Configurar la sesión
Define cómo se manejarán las sesiones y su tiempo de vida:

```env
SESSION_DRIVER=file        # Driver para almacenar sesiones
SESSION_LIFETIME=120       # Duración de las sesiones en minutos (2 horas por defecto)
 ```
#### Configuración de Broadcasting
Si usas broadcasting, ajusta la conexión de acuerdo con tu servidor:

```env
BROADCAST_CONNECTION=reverb   # Conexión para broadcasting (Reverb)
FILESYSTEM_DISK=local         # Sistema de archivos predeterminado
QUEUE_CONNECTION=sync         # Conexión para la cola de trabajos
 ```

#### Configurar Laravel Reverb
Para habilitar notificaciones en tiempo real con Reverb, asegúrate de configurar correctamente las credenciales y parámetros:

```env
REVERB_APP_ID=tu_reverb_id        # ID de la aplicación en Reverb
REVERB_APP_KEY=tu_reverb_key      # Clave de la aplicación en Reverb
REVERB_APP_SECRET=tu_reverb_secret # Secreto de la aplicación en Reverb
REVERB_HOST=nombre_proyecto.local  # Host para Reverb (virtual host) dentro de ""
REVERB_PORT=8080                   # Puerto del servidor WebSocket
REVERB_SCHEME=http                 # Esquema (http o https)

```
### Notas adicionales
1. **APP_URL**: Asegúrate de que `APP_URL` coincida con el nombre de tu host virtual para evitar problemas de routing.
2. **Base de datos**: Verifica que la base de datos esté creada y que el usuario tenga los permisos adecuados.
3. **Reverb**: Revisa que Reverb esté correctamente configurado y que las credenciales sean válidas.


### Levantar el Servidor WebSocket

Para habilitar las notificaciones en tiempo real, sigue estos pasos:

1. Inicia el servidor WebSocket con Laravel Reverb:

    ```bash
    php artisan reverb:start
    ```

2. Compila los assets de frontend con:

    ```bash
    npm run dev
    ```

### Recomendaciones Técnicas

- **Versión mínima de PHP**: 8.2
- **Última versión de Composer**: Asegúrate de tener la versión más reciente de Composer instalada.

---

