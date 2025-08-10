@echo off
echo Preparing Laravel application for Railway deployment...

echo.
echo 1. Building frontend assets...
call npm run build

echo.
echo 2. Optimizing Composer autoloader...
call composer install --optimize-autoloader --no-dev

echo.
echo 3. Clearing Laravel caches...
call php artisan config:clear
call php artisan route:clear
call php artisan view:clear
call php artisan cache:clear

echo.
echo 4. Preparing for production...
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache

echo.
echo ✅ Application prepared for deployment!
echo.
echo Next steps:
echo 1. Commit and push your changes to GitHub
echo 2. Create a new Railway project
echo 3. Connect your GitHub repository
echo 4. Add a MySQL database service
echo 5. Configure environment variables
echo.
pause
