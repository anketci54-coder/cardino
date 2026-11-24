#!/usr/bin/env bash
# Cardino: PHP 8.4 + Composer + MariaDB kurulumu (Debian 13)
# root olarak çalıştırın:  bash ortam.sh
set -euo pipefail

DB_NAME="cardino"
DB_USER="cardino"
CRED_FILE="/root/cardino-db.txt"

echo "== 1/4 Port kontrolü =="
if ss -ltn | grep -q ':3306 '; then
    echo "UYARI: 3306 portunu zaten bir veritabanı kullanıyor. Betik durduruldu; çıktıyı Claude'a gönderin."
    ss -ltnp | grep ':3306 ' || true
    exit 1
fi

echo "== 2/4 PHP 8.4 ve eklentiler =="
apt-get update -y
apt-get install -y php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring php8.4-xml \
    php8.4-curl php8.4-zip php8.4-gd php8.4-intl php8.4-bcmath unzip curl
sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 25M/; s/^post_max_size = .*/post_max_size = 30M/' /etc/php/8.4/fpm/php.ini
systemctl enable --now php8.4-fpm
systemctl restart php8.4-fpm

echo "== 3/4 Composer =="
if ! command -v composer >/dev/null; then
    EXPECTED="$(curl -s https://composer.github.io/installer.sig)"
    curl -s -o /tmp/composer-setup.php https://getcomposer.org/installer
    ACTUAL="$(php -r "echo hash_file('sha384', '/tmp/composer-setup.php');")"
    if [ "$EXPECTED" != "$ACTUAL" ]; then echo "Composer kurulum dosyası doğrulanamadı"; exit 1; fi
    php /tmp/composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
    rm -f /tmp/composer-setup.php
fi

echo "== 4/4 MariaDB ve veritabanı =="
apt-get install -y mariadb-server
systemctl enable --now mariadb
# Yalnızca bu makineden bağlantı kabul et
sed -i 's/^#\?bind-address.*/bind-address = 127.0.0.1/' /etc/mysql/mariadb.conf.d/50-server.cnf
systemctl restart mariadb

if [ ! -f "$CRED_FILE" ]; then
    DB_PASS="$(openssl rand -base64 24 | tr -d '/+=' | head -c 28)"
    mariadb -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;"
    mariadb -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
    mariadb -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;"
    printf 'DB_DATABASE=%s\nDB_USERNAME=%s\nDB_PASSWORD=%s\n' "$DB_NAME" "$DB_USER" "$DB_PASS" > "$CRED_FILE"
    chmod 600 "$CRED_FILE"
fi

echo
echo "== Özet =="
php -v | head -1
composer --version 2>/dev/null | head -1
mariadb --version
echo "Veritabanı bilgileri: $CRED_FILE (şifreyi kimseyle paylaşmayın, Claude'a da göndermeyin)"
