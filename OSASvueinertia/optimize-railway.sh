#!/bin/bash
# Railway Deployment Optimization Script
# Run this on Railway or add to your build process

echo "🚀 Starting optimization process..."

# 1. Clear existing caches
echo "📦 Clearing old caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 2. Cache configuration files
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Optimize autoloader
echo "🔧 Optimizing autoloader..."
composer dump-autoload --optimize --classmap-authoritative

# 4. Laravel optimize command (combines multiple optimizations)
echo "🎯 Running Laravel optimize..."
php artisan optimize

# 5. Clear and optimize OPcache (if available)
if command -v php-fpm &> /dev/null; then
    echo "🔄 Reloading PHP-FPM for OPcache..."
    php-fpm reload || true
fi

echo "✅ Optimization complete!"
echo ""
echo "Expected TTFB improvements:"
echo "- Config cache: ~30-50ms reduction"
echo "- Route cache: ~20-40ms reduction"
echo "- View cache: ~10-20ms reduction"
echo "- Autoloader: ~10-15ms reduction"
echo ""
echo "Total expected: 70-125ms faster TTFB!"
