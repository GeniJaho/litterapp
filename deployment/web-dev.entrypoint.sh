#!/bin/sh
#set -xv

#Check if there is a key, if not, create one
# We actually only need the first time we do the deployment,
# it's the app's encryption key that is used for all passwords and other tokens.
# Once set it must not change.
if [ -z "${APP_KEY}" ]; then
  echo "Variable APP_KEY is not set. Creating one."
  php artisan key:generate
else
  echo "Variable APP_KEY is set."
fi

#Just for reference ...
#php artisan migrate:fresh --seed
#Do any migration
echo "Database conversions, done always"
php artisan migrate --force

#Start Supervisor (manages web server and queue workers)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
