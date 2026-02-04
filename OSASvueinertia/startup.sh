#!/bin/bash

echo "Starting application..."

# Check if Railway volume is mounted
echo "=== Storage Volume Check ==="
echo "Checking if Railway volume is mounted at /app/storage/app/public..."
if [ -d "/app/storage/app/public" ]; then
    echo "✅ Storage volume directory exists"
    echo "Volume permissions: $(ls -ld /app/storage/app/public)"
    echo "Volume disk usage: $(df -h /app/storage/app/public | tail -1)"
else
    echo "⚠️  Storage volume directory not found - creating it"
    mkdir -p /app/storage/app/public
    chmod 755 /app/storage/app/public
fi
echo "================================"

# Debug: Show critical configuration
echo "=== Critical Configuration ==="
echo "APP_URL: ${APP_URL:-'❌ NOT SET'}"
echo "APP_ENV: ${APP_ENV:-'not set'}"
echo "APP_DEBUG: ${APP_DEBUG:-'not set'}"
echo "SESSION_SECURE_COOKIE: ${SESSION_SECURE_COOKIE:-'not set'}"
echo "SESSION_DOMAIN: ${SESSION_DOMAIN:-'not set'}"
echo "TRUSTED_PROXIES: ${TRUSTED_PROXIES:-'not set'}"
echo "FORCE_HTTPS: ${FORCE_HTTPS:-'not set'}"
echo "SSL_VERIFY: ${SSL_VERIFY:-'not set'}"
echo "================================"

# Debug: Show database configuration
echo "=== Database Configuration ==="
echo "DB_CONNECTION: ${DB_CONNECTION:-'not set'}"
echo "DB_HOST: ${DB_HOST:-'not set'}"
echo "DB_PORT: ${DB_PORT:-'not set'}"
echo "DB_DATABASE: ${DB_DATABASE:-'not set'}"
echo "DB_USERNAME: ${DB_USERNAME:-'not set'}"
echo "================================"

# Clear cached files
echo "🧹 Clearing cache files..."
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*
rm -rf storage/logs/*.log

# Clear Laravel caches once (reduces memory usage)
echo "🧹 Clearing Laravel caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan event:clear

# Test database connection
echo "Testing database connection..."
MAX_ATTEMPTS=10
ATTEMPT=1

while [ $ATTEMPT -le $MAX_ATTEMPTS ]; do
    echo "Attempt $ATTEMPT/$MAX_ATTEMPTS: Testing database connection..."
    
    if php -r "
        try {
            \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
            echo 'Database connection successful!';
            exit(0);
        } catch (Exception \$e) {
            echo 'Connection failed: ' . \$e->getMessage();
            exit(1);
        }
    " 2>/dev/null; then
        echo "Database connection verified!"
        break
    else
        echo "Database not ready, waiting 10 seconds..."
        sleep 10
        ATTEMPT=$((ATTEMPT + 1))
    fi
done

if [ $ATTEMPT -gt $MAX_ATTEMPTS ]; then
    echo "Failed to connect to database after $MAX_ATTEMPTS attempts"
    echo "Starting web server anyway for debugging..."
    
    echo "Starting web server on port ${PORT:-8000}..."
    exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
else
    echo "Database connection successful! Proceeding with setup..."
    
    # Install migrations table if it doesn't exist
    echo "Installing migration repository..."
    php artisan migrate:install --force 2>/dev/null || echo "Migration table already exists or created"
    
    # Run migrations
    echo "Running database migrations..."
    php artisan migrate --force
    
    # Check if database has data (to avoid duplicate seeding)
    echo "Checking if database needs seeding..."
    USERS_COUNT=$(php -r "
        require 'vendor/autoload.php';
        \$app = require 'bootstrap/app.php';
        \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        try {
            \$count = DB::table('users')->count();
            echo \$count;
        } catch (Exception \$e) {
            echo '0';
        }
    ")
    
    if [ "$USERS_COUNT" -eq "0" ]; then
        echo "Database is empty. Running seeders..."
        php artisan db:seed --force
    else
        echo "Database already has $USERS_COUNT users. Skipping seeders."
    fi
    
    # Create storage symlink
    echo "Creating storage symlink..."
    php artisan storage:link 2>/dev/null || echo "Storage link already exists"
    
    # Verify storage symlink and volume
    echo "=== Storage Verification ==="
    echo "Storage symlink exists: $([ -L public/storage ] && echo 'YES' || echo 'NO')"
    echo "Storage symlink target: $(readlink public/storage 2>/dev/null || echo 'Not found')"
    echo "Volume mounted files count: $(find /app/storage/app/public -type f 2>/dev/null | wc -l)"
    echo "================================"
    
    # Clean up missing profile photos
    echo "Cleaning up missing profile photos..."
    php artisan users:clean-missing-photos
fi

# Test configuration before caching
echo "🔍 Testing current configuration..."
php artisan about --only=environment
php artisan config:show app.url

# DO NOT CACHE ANYTHING - Run without caching to avoid redirect loops
echo "⚠️  RUNNING WITHOUT CONFIG CACHE TO PREVENT REDIRECT LOOPS"
echo "Application setup completed!"

# Note: Laravel scheduler disabled to save memory (50-100MB)
# Use Railway cron jobs or external cron service instead
# See: https://docs.railway.app/reference/cron-jobs
echo "⚠️  Laravel scheduler disabled - use Railway cron jobs for scheduled tasks"

# Start the application with custom router for cache headers
echo "Starting web server with cache headers on port ${PORT:-8000}..."
exec php -S 0.0.0.0:${PORT:-8000} -t public router.php
