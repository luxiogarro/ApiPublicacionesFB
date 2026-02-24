# Guía de Despliegue en Servidor Producción (cPanel / VPS)
**Dominio Objetivo:** `apifb.luxio.dev`

Esta guía te ayudará paso a paso a montar la API de Publicaciones de manera segura utilizando el código que subimos a tu repositorio de GitHub.

---

## 🛠️ Paso 1: Configurar la Base de Datos Remota
Lo primero es preparar el terreno en tu servidor.

1.  Entra a tu **cPanel** (o al panel de control de tu hosting).
2.  Ve a la sección **Bases de Datos MySQL** (o MySQL Database Wizard).
3.  **Crea una base de datos** (por ejemplo: `luxio_apifb`).
4.  **Crea un usuario** para esa base de datos (con una contraseña segura).
5.  Asegúrate de **asignarle todos los privilegios** a ese usuario sobre la base de datos.
6.  Ve a **phpMyAdmin**, selecciona la base de datos que creaste, y ve a la pestaña **Importar**. 
7.  Sube el archivo `database.sql` que está en tu proyecto local para crear las tablas necesarias.
8.  *¡Guarda el nombre de la DB, el usuario y la contraseña, los necesitaremos en el paso 3!*

---

## 📂 Paso 2: Subir el Código al Servidor
Ahora hay que pasar el código desde GitHub a tu servidor. Hay dos formas principales:

### Método A: Git / Terminal de cPanel (Recomendado)
Si tienes acceso a la herramienta **Git Version Control** o **Terminal** en tu cPanel:
1.  Abre la terminal de cPanel o accede por SSH.
2.  Navega a la carpeta pública (`public_html` si va en la raíz, o crea una subcarpeta `apifb`).
3.  Ejecuta: 
    ```bash
    git clone https://github.com/luxiogarro/ApiPublicacionesFB.git .
    ```
*(El `.` al final es importante para no crear una doble carpeta).*

### Método B: Subida manual vía ZIP
Si tu hosting no soporta Git directamente:
1.  Ve a tu repositorio en GitHub: [github.com/luxiogarro/ApiPublicacionesFB](https://github.com/luxiogarro/ApiPublicacionesFB).
2.  Haz clic en el botón verde **Code** y elige **Download ZIP**.
3.  Ve al **Administrador de Archivos** de cPanel.
4.  Navega al directorio donde debe estar la API (ej: dentro de `public_html`).
5.  Sube el `.zip` y usa la opción **Extraer**.

---

## ⚙️ Paso 3: Configurar Conexión
El código ya está en el servidor, ¡ahora falta conectarlo a la base de datos!

1.  En tu cPanel (Administrador de Archivos), navega a la carpeta `config/`.
2.  A partir del archivo `config.php.example`, crea una copia y llámala `config.php`.
3.  Edita el nuevo archivo `config.php` y coloca los datos reales del servidor:

```php
<?php
// Configuración de la Base de Datos
define('DB_HOST', 'localhost'); // Suele ser localhost, salvo que tu hosting indique otra IP
define('DB_NAME', 'aqui_tu_base_de_datos'); // Lo que creaste en el Paso 1
define('DB_USER', 'aqui_tu_usuario'); // Lo que creaste en el Paso 1
define('DB_PASS', 'aqui_tu_contraseña'); // La contraseña del usuario MySQL

// Otros ajustes
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
// ¡Atención! Cambia el dominio al de producción:
define('BASE_URL', 'https://apifb.luxio.dev/');
```

---

## 🗂️ Paso 4: Permisos de Carpetas (Muy Importante)
Para que el sistema pueda subir imágenes sin restricciones, necesitamos ajustar los permisos en Linux:

1.  Usa el Administrador de Archivos de cPanel.
2.  Ubica la carpeta `uploads/`.
3.  Haz clic derecho -> **Change Permissions** (o Permisos).
4.  Asegúrate de que los permisos estén en **755** (o incluso **775** o **777** si experimentas errores al subir imágenes en tu entorno específico).

---

## 🚀 Paso 5: Prueba Inicial
Ya casi terminamos.

1. Ve a `https://apifb.luxio.dev/` en tu navegador. Si no sale un error de MySQL y carga al menos la página en blanco o el mensaje de estado de la API, ¡estás conectado!
2. Accede a `https://apifb.luxio.dev/admin/login.php`
3. Ingresa con **admin** / **admin123** (la contraseña predeterminada que viene en la DB original si no la cambiaste).
4. Sube una foto de prueba desde el Dashboard para verificar que el Paso 4 funciona bien.

*Una vez terminado, **recomiendo encarecidamente cambiar tu contraseña y la Api Key base** directamente desde el Panel de Administración.*
