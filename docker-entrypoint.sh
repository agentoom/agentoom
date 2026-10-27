#!/bin/sh
set -e

echo "==> Starting Agentoom Community Edition initialization..."

# 1. Environment configuration
if [ ! -f /app/.env ]; then
    echo "==> Creating .env from .env.example..."
    cp /app/.env.example /app/.env
fi

# 2. Ensure SQLite database file exists if DB_CONNECTION is sqlite
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-/app/database/database.sqlite}"
    mkdir -p "$(dirname "$DB_FILE")"
    if [ ! -f "$DB_FILE" ]; then
        echo "==> Initializing SQLite database at $DB_FILE..."
        touch "$DB_FILE"
        chown -R www-data:www-data "$(dirname "$DB_FILE")"
    fi
fi

# 3. Application Key
if ! grep -q "APP_KEY=base64:" /app/.env && [ -z "$APP_KEY" ]; then
    echo "==> Generating application encryption key..."
    php artisan key:generate --force
fi

# 4. Storage link
if [ ! -L /app/public/storage ]; then
    php artisan storage:link || true
fi

# 5. Database migrations
echo "==> Running database migrations..."
php artisan migrate --force

# 6. Agentoom core bootstrap (roles, languages, starter admin)
echo "==> Running Agentoom system installer..."
php artisan agentoom:install --no-interaction || true

# 7. Tool discovery & system users
echo "==> Discovering AI tools and initializing system resources..."
php artisan agentoom:discover-tools --ansi --no-interaction || true
php artisan agentoom:create-missing-project-system-users --ansi --no-interaction || true

# 8. Production caching
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "==> Caching configuration and routes for production..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# 9. Start background queue worker
echo "==> Starting Agentoom background task worker..."
php artisan queue:work --tries=3 --timeout=90 --daemon &

# 10. Start Reverb WebSocket server if enabled
if [ "${BROADCAST_CONNECTION:-reverb}" = "reverb" ]; then
    echo "==> Starting Reverb WebSocket server on port 8080..."
    php artisan reverb:start --host=0.0.0.0 --port=8080 &
fi

echo "==> Agentoom initialization complete! Web server starting on port 80..."

exec "$@"
