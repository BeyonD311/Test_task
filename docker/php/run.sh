#!/usr/bin/env sh
composer install
php /var/www/artisan migrate
supervisord -c /etc/supervisor/supervisord.conf
