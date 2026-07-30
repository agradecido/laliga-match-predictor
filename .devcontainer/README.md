# Entorno Docker local

Stack para desarrollar sin instalar PHP, Composer, Node ni un motor de base de datos en el host. La base de datos es SQLite, así que no hay contenedor de base de datos: el fichero `database/database.sqlite` vive en el propio repositorio.

## Servicios

- **app**: PHP 8.4 CLI con las extensiones necesarias (pdo_sqlite, zip, bcmath, pcntl, intl, gd) + Composer. Al arrancar instala dependencias si faltan, crea `.env`, genera `APP_KEY`, crea `database/database.sqlite` y ejecuta las migraciones. Sirve la app con `php artisan serve` en el puerto `8000`.
- **queue**: mismo image que `app`, ejecuta `php artisan queue:listen`.
- **vite**: Node 22, instala dependencias npm si faltan y levanta el dev server de Vite en el puerto `5173` (con HMR).

## Uso

Desde la raíz del proyecto:

```bash
npm run docker:up      # construye (si hace falta) y levanta los contenedores en background
npm run docker:logs    # sigue los logs de todos los servicios
npm run docker:artisan -- migrate:fresh --seed   # ejecutar comandos artisan
npm run docker:sh      # abrir una shell dentro del contenedor app
npm run docker:down    # parar y eliminar los contenedores
```

O directamente con `docker compose -f .devcontainer/docker-compose.yml <comando>`.

La app queda disponible en http://localhost:8000 y los assets de Vite en http://localhost:5173.

Los directorios `vendor/` y `node_modules/` se guardan en volúmenes Docker separados (no en el bind mount) para no mezclar binarios compilados del host con los del contenedor.

Puertos configurables con las variables de entorno `APP_PORT` y `VITE_PORT` (por defecto 8000 y 5173).
