
#Check if there is a key, if not, create one
# We actually only need the first time we do the deployment,
# it's the app's encryption key that is used for all passwords and other tokens.
# Once set it must not change.
if [ -z "${APP_KEY}" ]; then
  echo "Variable YOUR_VARIABLE is not set. Creating one."
  # Run your default command here
  php artisan key:generate
fi

#Just for reference ...
#php artisan migrate:fresh --seed
#Do any migration
php artisan migrate #--force

#Run the development server
php artisan serve --host=0.0.0.0 --port=9000