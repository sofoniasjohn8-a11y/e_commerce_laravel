#!/bin/bash
php artisan migrate:with-fallback --fallback=${DB_FALLBACK_CONNECTION:-pgsql}
php artisan config:cache
php artisan route:cache
# Start the server
php artisan serve --host=0.0.0.0 --port=$PORT