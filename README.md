# API Publicaciones FB

Sistema de gestión de publicaciones centralizado para múltiples clientes con Dashboard administrativo estilo "Social Media".

## Características
- **API RESTful**: Endpoints para crear, obtener y eliminar publicaciones.
- **Seguridad**: Autenticación vía `X-API-KEY`.
- **Dashboard Premium**: Interfaz moderna con visualización de feed integrado.
- **Optimización de Imágenes**: Conversión automática a WebP y reescalado.
- **Soporte Multimedia**: Imágenes, PDFs y Videos embebidos.
- **Posts Fijados**: Capacidad para destacar noticias por tiempo limitado.

## Requisitos
- Servidor web Apache con `mod_rewrite` habilitado.
- PHP 7.4 o superior.
- MySQL / MariaDB.
- Extensión GD de PHP (para optimización de imágenes).

## Instalación
1. Clona el repositorio: `git clone https://github.com/TU_USUARIO/ApiPublicacionesFB.git`
2. Importa la base de datos desde `database.sql`.
3. Configura las credenciales en `config/config.php` (usa `config/config.php.example` como guía).
4. Asegúrate de que la carpeta `uploads` tenga permisos de escritura.

## Despliegue en Producción
Este proyecto está configurado para ejecutarse bajo el dominio `apifb.luxio.dev`. Asegúrate de ajustar el `BASE_URL` en tu archivo de configuración.

---
© 2026 Admin API - Desarrollo Premium.
