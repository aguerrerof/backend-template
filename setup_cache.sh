#!/bin/bash

PROJECT_DIR=$(pwd)
CACHE_HOME="$HOME/laravel_cache"
ENV_FILE="$PROJECT_DIR/.env"
CACHE_CONFIG_FILE="$PROJECT_DIR/config/cache.php"

# 1️⃣ Crear carpetas necesarias
mkdir -p "$CACHE_HOME/framework/cache/data"
mkdir -p "$CACHE_HOME/framework/sessions"
mkdir -p "$CACHE_HOME/framework/views"
chmod -R 700 "$CACHE_HOME"

echo "✅ Carpetas de cache creadas en $CACHE_HOME"

# 2️⃣ Actualizar o agregar variables en .env
grep -q "^CACHE_DRIVER=" "$ENV_FILE" && sed -i "s|^CACHE_DRIVER=.*|CACHE_DRIVER=file|" "$ENV_FILE" || echo "CACHE_DRIVER=file" >> "$ENV_FILE"
grep -q "^CACHE_PATH=" "$ENV_FILE" && sed -i "s|^CACHE_PATH=.*|CACHE_PATH=$CACHE_HOME/framework/cache/data|" "$ENV_FILE" || echo "CACHE_PATH=$CACHE_HOME/framework/cache/data" >> "$ENV_FILE"

echo "✅ Variables CACHE_DRIVER y CACHE_PATH actualizadas en .env"

# 3️⃣ Actualizar config/cache.php de forma segura usando perl
perl -0777 -i -pe "s/'file' =>.*?,/'file' => env('CACHE_PATH', storage_path('framework\/cache\/data')),/" "$CACHE_CONFIG_FILE"
echo "✅ config/cache.php actualizado para usar CACHE_PATH"

# 4️⃣ Limpiar todas las caches de Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

echo "✅ Cache de Laravel limpiada y optimizada"
echo "Laravel cache ahora apunta a: $CACHE_HOME/framework/cache/data"
