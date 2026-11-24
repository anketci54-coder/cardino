#!/usr/bin/env bash
# Cardino: Laravel uygulamasını kurar ve nginx'e bağlar (sunucuda root olarak)
set -euo pipefail
export COMPOSER_ALLOW_SUPERUSER=1
BASE=/var/www/cardino
APP=$BASE/app
CRED=/root/cardino-db.txt
SITE=/etc/nginx/sites-available/cardino.space

echo "== 1/4 Laravel projesi =="
if [ ! -f "$APP/artisan" ]; then
    composer create-project laravel/laravel "$APP" --no-interaction --prefer-dist
fi

echo "== 2/4 .env =="
cd "$APP"
. "$CRED"
set_env() { if grep -q "^#\?\s*$1=" .env; then sed -i "s|^#\?\s*$1=.*|$1=$2|" .env; else echo "$1=$2" >> .env; fi; }
set_env APP_NAME Cardino
set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_URL https://panel.cardino.space
set_env APP_LOCALE tr
set_env APP_FALLBACK_LOCALE en
set_env APP_TIMEZONE Europe/Istanbul
set_env DB_CONNECTION mariadb
set_env DB_HOST 127.0.0.1
set_env DB_PORT 3306
set_env DB_DATABASE "$DB_DATABASE"
set_env DB_USERNAME "$DB_USERNAME"
set_env DB_PASSWORD "$DB_PASSWORD"
grep -q '^APP_KEY=base64' .env || php artisan key:generate --force
php artisan migrate --force
php artisan config:cache
php artisan route:cache
chown -R www-data:www-data "$APP"
chmod -R ug+rwX "$APP/storage" "$APP/bootstrap/cache"

echo "== 3/4 nginx =="
cp "$SITE" "/root/cardino.space.nginx.bak.$(date +%s)"
sed -i "s|root /var/www/cardino/public;|root $APP/public;|" "$SITE"
if ! grep -q 'fastcgi_pass' "$SITE"; then
    sed -i '/location ~ \/\\.(?!well-known) { deny all; }/i\    location ~ \\.php$ {\n        include snippets/fastcgi-php.conf;\n        fastcgi_pass unix:/run/php/php8.4-fpm.sock;\n        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;\n    }\n' "$SITE"
fi
nginx -t
systemctl reload nginx

echo "== 4/4 Kontrol =="
php artisan --version
curl -s -o /dev/null -w "panel.cardino.space HTTP %{http_code}\n" https://panel.cardino.space/
