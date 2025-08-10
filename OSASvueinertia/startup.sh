#!/bin/bash

echo "Starting application..."

# Debug: Show critical configuration
echo "=== Critical Configuration ==="
echo "APP_URL: ${APP_URL:-'❌ NOT SET - THIS CAUSES REDIRECT LOOPS!'}"
echo "APP_ENV: ${APP_ENV:-'not set'}"
echo "APP_DEBUG: ${APP_DEBUG:-'not set'}"
echo "APP_NAME: ${APP_NAME:-'not set'}"
echo "================================"

# Debug: Show database configuration
echo "=== Database Configuration ==="
echo "DB_CONNECTION: ${DB_CONNECTION:-'not set'}"
echo "DB_HOST: ${DB_HOST:-'not set'}"
echo "DB_PORT: ${DB_PORT:-'not set'}"
echo "DB_DATABASE: ${DB_DATABASE:-'not set'}"
echo "DB_USERNAME: ${DB_USERNAME:-'not set'}"
echo "================================"

# Clear ALL caches to prevent redirect loops
echo "Clearing all caches to prevent redirect issues..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

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
    
    # Only cache if APP_URL is properly set
    if [ ! -z "$APP_URL" ] && [ "$APP_URL" != "" ]; then
        echo "APP_URL is set to: $APP_URL"
        echo "Caching configurations for production..."
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
    else
        echo "⚠️  WARNING: APP_URL is not set! Skipping config cache to prevent redirect loops."
        echo "Add APP_URL=https://orbit-production.up.railway.app to your Railway environment variables."
    fi
    
    echo "Application setup completed successfully!"
fi

# Start the application
echo "Starting web server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
