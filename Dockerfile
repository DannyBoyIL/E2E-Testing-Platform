FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive \
    TZ=UTC

# ── System dependencies ───────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
    software-properties-common \
    curl \
    git \
    unzip \
    wget \
    ca-certificates \
    gnupg \
    python3 \
 && rm -rf /var/lib/apt/lists/*

# ── PHP 8.2 ───────────────────────────────────────────────────────────────────
RUN add-apt-repository ppa:ondrej/php -y \
 && apt-get update \
 && apt-get install -y --no-install-recommends \
    php8.2-cli \
    php8.2-mbstring \
    php8.2-mysql \
    php8.2-sqlite3 \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-tokenizer \
 && rm -rf /var/lib/apt/lists/*

# ── Node.js 20 ────────────────────────────────────────────────────────────────
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
 && apt-get install -y --no-install-recommends nodejs \
 && rm -rf /var/lib/apt/lists/*

# ── Composer ──────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# ── PHP dependencies (cached layer) ──────────────────────────────────────────
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-scripts --no-autoloader --no-progress --no-interaction

# ── Node dependencies + Playwright Chromium (cached layer) ───────────────────
COPY package.json package-lock.json ./
RUN npm ci
RUN npx playwright install chromium --with-deps

# ── Application source ────────────────────────────────────────────────────────
COPY . .

# Remove any stale Vite dev-server hot file that may have been in the build context
RUN rm -f public/hot

# ── Storage & cache directories ───────────────────────────────────────────────
RUN mkdir -p storage/logs \
              storage/framework/cache \
              storage/framework/sessions \
              storage/framework/views \
              bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# ── Finalise Composer autoload ────────────────────────────────────────────────
RUN composer dump-autoload --optimize

# ── Entrypoint ────────────────────────────────────────────────────────────────
RUN chmod +x /app/docker-entrypoint.sh

EXPOSE 8000 8080

ENTRYPOINT ["/app/docker-entrypoint.sh"]
