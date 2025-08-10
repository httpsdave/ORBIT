#!/bin/bash

echo "=== Railway Environment Debug ==="
echo "APP_ENV: $APP_ENV"
echo "APP_URL: $APP_URL"
echo "DB_CONNECTION: $DB_CONNECTION"
echo "DB_HOST: $DB_HOST"
echo "DB_PORT: $DB_PORT"
echo "DB_DATABASE: $DB_DATABASE"
echo "DB_USERNAME: $DB_USERNAME"
echo "DB_PASSWORD: [HIDDEN]"
echo ""

echo "=== Testing Database Connection ==="
php artisan tinker --execute="
try {
    \DB::connection()->getPdo();
    echo 'Database connection: SUCCESS' . PHP_EOL;
} catch(\Exception \$e) {
    echo 'Database connection: FAILED - ' . \$e->getMessage() . PHP_EOL;
}
"

echo ""
echo "=== Laravel Configuration ==="
php artisan config:show database.connections.mysql
