#!/bin/bash

# ── Config ────────────────────────────────────────────────────────────────────
DB_HOST="${DB_HOST:-mysql}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-e2e_testing_platform}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-password}"

log() { echo "[$(date '+%H:%M:%S')] $*"; }

# ── Wait for MySQL ────────────────────────────────────────────────────────────
log "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
for i in $(seq 1 30); do
    if php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; then
        log "MySQL is ready."
        break
    fi
    if [ "$i" -eq 30 ]; then
        log "ERROR: MySQL did not become ready in 60 seconds. Aborting."
        exit 1
    fi
    sleep 2
done

# ── Laravel setup ─────────────────────────────────────────────────────────────
log "Configuring Laravel environment..."
cp .env.example .env
{
    echo "DB_CONNECTION=mysql"
    echo "DB_HOST=${DB_HOST}"
    echo "DB_PORT=${DB_PORT}"
    echo "DB_DATABASE=${DB_DATABASE}"
    echo "DB_USERNAME=${DB_USERNAME}"
    echo "DB_PASSWORD=${DB_PASSWORD}"
    echo "APP_URL=http://127.0.0.1:8000"
} >> .env

php artisan key:generate --force --quiet
php artisan migrate --force --quiet
php artisan db:seed --force --quiet
log "Database migrated and seeded."

# ── Build frontend ────────────────────────────────────────────────────────────
log "Building Vite frontend assets..."
rm -f public/hot
npm run build --silent
rm -f public/hot
log "Frontend built."

# ── Start Laravel server ──────────────────────────────────────────────────────
log "Starting Laravel development server on port 8000..."
PHP_CLI_SERVER_WORKERS=4 php artisan serve --host=0.0.0.0 --port=8000 > storage/logs/server.log 2>&1 &

log "Waiting for server to accept connections..."
for i in $(seq 1 30); do
    if curl -s http://127.0.0.1:8000 > /dev/null 2>&1; then
        log "Laravel server is ready."
        break
    fi
    if [ "$i" -eq 30 ]; then
        log "ERROR: Laravel server did not start in time."
        exit 1
    fi
    sleep 1
done

# ── Run test suites ───────────────────────────────────────────────────────────
PHPUNIT_EXIT=0
BEHAT_EXIT=0
PLAYWRIGHT_EXIT=0

echo ""
echo "════════════════════════════════════════════════"
log "Running PHPUnit tests..."
echo "════════════════════════════════════════════════"
php artisan test 2>&1 | tee storage/logs/phpunit.log
PHPUNIT_EXIT=${PIPESTATUS[0]}

echo ""
echo "════════════════════════════════════════════════"
log "Running Behat tests..."
echo "════════════════════════════════════════════════"
./vendor/bin/behat --no-interaction 2>&1 | tee storage/logs/behat.log
BEHAT_EXIT=${PIPESTATUS[0]}

echo ""
echo "════════════════════════════════════════════════"
log "Running Playwright tests (specs + BDD)..."
echo "════════════════════════════════════════════════"
npm test 2>&1 | tee storage/logs/playwright.log
PLAYWRIGHT_EXIT=${PIPESTATUS[0]}

# ── Generate Allure report ────────────────────────────────────────────────────
echo ""
log "Generating unified Allure report..."
npm run allure:generate 2>/dev/null || true

log "Serving Allure report on http://localhost:8080"
python3 -m http.server 8080 --directory allure-report > /dev/null 2>&1 &

# ── Summary ───────────────────────────────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════════╗"
echo "║            TEST RESULTS SUMMARY              ║"
echo "╠══════════════════════════════════════════════╣"
if [ $PHPUNIT_EXIT   -eq 0 ]; then
    echo "║  PHPUnit     ✓ PASSED                        ║"
else
    echo "║  PHPUnit     ✗ FAILED  (exit ${PHPUNIT_EXIT})                  ║"
fi
if [ $BEHAT_EXIT     -eq 0 ]; then
    echo "║  Behat       ✓ PASSED                        ║"
else
    echo "║  Behat       ✗ FAILED  (exit ${BEHAT_EXIT})                  ║"
fi
if [ $PLAYWRIGHT_EXIT -eq 0 ]; then
    echo "║  Playwright  ✓ PASSED                        ║"
else
    echo "║  Playwright  ✗ FAILED  (exit ${PLAYWRIGHT_EXIT})                  ║"
fi
echo "╠══════════════════════════════════════════════╣"
echo "║  Allure report → http://localhost:8080       ║"
echo "║  Laravel app   → http://localhost:8000       ║"
echo "╚══════════════════════════════════════════════╝"

# Keep container alive so the report and app remain accessible
wait
