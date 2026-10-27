# ============================================================
# Stage 1: Install Composer Dependencies
# ============================================================
FROM composer:2 AS composer-builder
WORKDIR /app

COPY composer*.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --ignore-platform-reqs

# ============================================================
# Stage 2: Build Frontend Assets
# ============================================================
FROM node:22-alpine AS frontend-builder
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
# Copy vendor from composer-builder so Tailwind/Flux can resolve CSS and Blade stubs
COPY --from=composer-builder /app/vendor ./vendor
RUN npm run build

# ============================================================
# Stage 3: Final Production / Community Container
# ============================================================
FROM dunglas/frankenphp:1-php8.4-alpine

LABEL org.opencontainers.image.title="Agentoom Community Edition" \
      org.opencontainers.image.description="Autonomous AI Agent Orchestration Platform" \
      org.opencontainers.image.url="https://github.com/agentoom/agentoom" \
      org.opencontainers.image.source="https://github.com/agentoom/agentoom" \
      org.opencontainers.image.licenses="MIT"

# Install system dependencies & required PHP extensions for Agentoom
RUN apk add --no-cache curl bash && \
    install-php-extensions \
    pdo_sqlite \
    sqlite3 \
    pdo_pgsql \
    pgsql \
    redis \
    bcmath \
    intl \
    zip \
    pcntl \
    gd

# Copy Composer binary from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application codebase
COPY . .

# Copy pre-installed vendor and compiled assets
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=frontend-builder /app/public/build ./public/build

# Finish autoloader optimization
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Prepare directories and permissions
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs database && \
    chmod -R 775 storage bootstrap/cache database && \
    chown -R www-data:www-data storage bootstrap/cache database

# Entrypoint setup
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80 443 8080

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
