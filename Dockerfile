# ============================================================
# Stage 1: Build Frontend Assets
# ============================================================
FROM node:22-alpine AS frontend-builder
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# ============================================================
# Stage 2: Final Production / Community Container
# ============================================================
FROM dunglas/frankenphp:latest-php8.4-alpine

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

WORKDIR /app

# Copy application codebase
COPY . .

# Copy compiled frontend assets from builder
COPY --from=frontend-builder /app/public/build ./public/build

# Install composer dependencies (optimized production autoloader)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

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
