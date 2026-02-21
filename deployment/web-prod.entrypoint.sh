#!/bin/sh

echo "Running database migrations"
php artisan migrate --force

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
