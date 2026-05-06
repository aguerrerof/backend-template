# Proyecto Base

Plantilla generica para iniciar nuevos proyectos backend con Laravel. Este repositorio esta pensado como punto de partida reutilizable: puedes adaptarlo a una API, un panel interno, un gateway de servicios o cualquier otro producto que necesite una base solida de arranque.

## Que incluye

- Estructura estandar de Laravel.
- Configuracion para desarrollo local con PHP, Composer, Node y Vite.
- Soporte para base de datos, colas, cache y sesiones.
- Espacio para integrar servicios externos segun las necesidades de cada proyecto.

## Requisitos

- **PHP 8.2+**
- **Composer 2.4+**
- **Node 18+** y **npm 10+**
- **Base de datos compatible con Laravel** segun tu entorno

## Primer arranque

1. Clona este repositorio y entra en la carpeta del proyecto.
2. Copia la plantilla de entorno.
   ```sh
   cp .env.example .env
   ```
3. Instala dependencias de backend y frontend.
   ```sh
   composer install
   npm install
   ```
4. Genera la clave de la aplicacion.
   ```sh
   php artisan key:generate
   ```
5. Configura la base de datos en `.env`.
6. Ejecuta migraciones y seeders si el proyecto los necesita.
   ```sh
   php artisan migrate
   php artisan db:seed
   ```
7. Crea el enlace de storage si tu proyecto maneja archivos publicos.
   ```sh
   php artisan storage:link
   ```

## Desarrollo local

- Levanta todo el entorno con:
  ```sh
  composer dev
  ```
- Inicia solo el frontend con:
  ```sh
  npm run dev
  ```
- Genera assets de produccion con:
  ```sh
  npm run build
  ```
- Ejecuta tests con:
  ```sh
  php artisan test
  ```

## Configuracion

Este proyecto usa `.env` para definir variables de entorno. Ajusta solo las que realmente necesite tu implementacion.

Variables comunes que suelen aparecer en proyectos de este tipo:

- `APP_NAME`
- `APP_ENV`
- `APP_URL`
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `QUEUE_CONNECTION`
- `CACHE_STORE`
- `SESSION_DRIVER`

Si el proyecto integra servicios externos, documenta aqui las credenciales o parametros adicionales que requiera.

## Estructura

- `app/` logica principal de negocio
- `config/` configuracion de la aplicacion
- `database/` migraciones, seeders y factories
- `resources/` vistas, assets y frontend
- `routes/` definicion de endpoints
- `tests/` pruebas automatizadas

## Comandos utiles

- `php artisan optimize` reconstruye caches de configuracion y rutas.
- `php artisan queue:work` procesa jobs pendientes.
- `php artisan config:clear` limpia configuracion cacheada.
- `php artisan cache:clear` limpia cache de la aplicacion.

## Personalizacion del template

Si vas a reutilizar este repositorio como base de otro proyecto:

- Cambia el nombre del proyecto y su descripcion.
- Reemplaza cualquier referencia a marcas, dominios o integraciones especificas.
- Actualiza `.env.example` con las variables reales que necesite el nuevo producto.
- Revisa seeders, migraciones y documentacion antes de publicar el nuevo repositorio.

## Licencia

Define aqui la licencia que corresponda al proyecto que nazca desde esta base.
