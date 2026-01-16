#!/bin/sh

#Do any migration
echo "Running database migrations"
php artisan migrate --force

#Start Supervisor (manages web server and queue workers)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
