#!/bin/bash

echo "Starting application..."

# Clear any cached configs that might have wrong DB settings
php artisan config:clear

# Wait for database to be ready with better error handling
echo "Waiting for database connection..."
MAX_ATTEMPTS=30
ATTEMPT=1

while [ $ATTEMPT -le $MAX_ATTEMPTS ]; do
    echo "Attempt $ATTEMPT/$MAX_ATTEMPTS: Testing database connection..."
    
    if php artisan migrate:status 2>/dev/null; then
        echo "Database connection successful!"
        break
    else
        echo "Database not ready, waiting 5 seconds..."
        sleep 5
        ATTEMPT=$((ATTEMPT + 1))
    fi
done

if [ $ATTEMPT -gt $MAX_ATTEMPTS ]; then
    echo "Failed to connect to database after $MAX_ATTEMPTS attempts"
    echo "Checking environment variables..."
    echo "DB_HOST: $DB_HOST"
    echo "DB_PORT: $DB_PORT"
    echo "DB_DATABASE: $DB_DATABASE"
    echo "DB_USERNAME: $DB_USERNAME"
    echo "Starting web server anyway (database operations will be skipped)..."
else
    # Run migrations
    echo "Running database migrations..."
    php artisan migrate --force

    # Run seeders
    echo "Running database seeders..."
    php artisan db:seed --force

    # Create storage symlink
    echo "Creating storage symlink..."
    php artisan storage:link
fi

# Cache configurations for production
echo "Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the application
echo "Starting web server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port=$PORT
