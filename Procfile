web: vendor/bin/heroku-php-apache2 public/
release: php artisan optimize:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear && php artisan config:clear && php artisan config:cache && php artisan route:cache
