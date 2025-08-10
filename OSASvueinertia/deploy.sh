#!/bin/bash

# Exit on any error
set -e

echo "Starting deployment..."

# Run database migrations
php artisan migrate --force

# Run database seeders
php artisan db:seed --force

# Clear and cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink if it doesn't exist
php artisan storage:link

echo "Deployment completed successfully!"
