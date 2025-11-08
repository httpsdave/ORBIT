@echo off
REM Windows/XAMPP Optimization Script
REM Run this locally before deploying to Railway

echo Starting optimization process...
echo.

REM 1. Clear existing caches
echo Clearing old caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

REM 2. Cache configuration files
echo.
echo Caching configuration...
php artisan config:cache
php artisan route:cache
php artisan view:cache

REM 3. Optimize autoloader
echo.
echo Optimizing autoloader...
composer dump-autoload --optimize --classmap-authoritative

REM 4. Laravel optimize command
echo.
echo Running Laravel optimize...
php artisan optimize

echo.
echo ======================================
echo Optimization complete!
echo ======================================
echo.
echo Expected TTFB improvements:
echo - Config cache: ~30-50ms reduction
echo - Route cache: ~20-40ms reduction  
echo - View cache: ~10-20ms reduction
echo - Autoloader: ~10-15ms reduction
echo.
echo Total expected: 70-125ms faster TTFB!
echo.
echo Now commit these cached files and deploy to Railway.
pause
