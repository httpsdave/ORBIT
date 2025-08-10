#!/bin/bash

echo "Starting application..."

# Wait for database to be ready
echo "Waiting for database connection..."
until php artisan migrate:status 2>/dev/null; do
    echo "Database not ready, waiting 2 seconds..."
    sleep 2
done

echo "Database is ready!"

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Run seeders
echo "Running database seeders..."
php artisan db:seed --force

# Cache configurations for production
echo "Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink
echo "Creating storage symlink..."
php artisan storage:link

# Start the application
echo "Starting web server..."
exec php artisan serve --host=0.0.0.0 --port=$PORT
