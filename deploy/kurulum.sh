#!/usr/bin/env bash
# Cardino: nginx + ücretsiz SSL (Let's Encrypt) kurulumu
# Ubuntu / Debian için. root olarak çalıştırın:  bash kurulum.sh
set -euo pipefail

DOMAIN="cardino.space"
PANEL="panel.cardino.space"
EMAIL="kursat_akyol@hotmail.com"
ROOT="/var/www/cardino/public"

echo "== 1/5 Paketler kuruluyor =="
apt-get update -y
apt-get install -y nginx certbot python3-certbot-nginx dnsutils

echo "== 2/5 Site klasörü =="
mkdir -p "$ROOT"
if [ ! -f "$ROOT/index.html" ]; then
cat > "$ROOT/index.html" <<'HTML'
<!doctype html><html lang="tr"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cardino</title></head>
<body style="font-family:system-ui;display:grid;place-items:center;height:100vh;margin:0">
<div style="text-align:center"><h1>Cardino</h1><p>KKTC oto galerileri için araç yönetim sistemi. Yakında.</p></div>
</body></html>
HTML
fi
chown -R www-data:www-data /var/www/cardino

echo "== 3/5 nginx ayarı =="
cat > /etc/nginx/sites-available/cardino.space <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN $PANEL;
    root $ROOT;
    index index.php index.html;

    client_max_body_size 25M;   # araç fotoğrafları için

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ /\.(?!well-known) { deny all; }
}
NGINX
ln -sf /etc/nginx/sites-available/cardino.space /etc/nginx/sites-enabled/cardino.space
nginx -t
systemctl enable --now nginx
systemctl reload nginx

echo "== 4/5 Güvenlik duvarı (varsa) =="
if command -v ufw >/dev/null && ufw status | grep -q "Status: active"; then
    ufw allow 'Nginx Full'
fi

echo "== 5/5 DNS kontrolü ve SSL =="
IP=$(curl -s https://api.ipify.org || true)
A=$(dig +short "$DOMAIN" A @1.1.1.1 | tail -1)
W=$(dig +short "$PANEL" A @1.1.1.1 | tail -1)
echo "Sunucu IP: $IP | $DOMAIN -> $A | $PANEL -> $W"
if [ "$A" = "$IP" ] && [ "$W" = "$IP" ]; then
    certbot --nginx -d "$DOMAIN" -d "$PANEL" -m "$EMAIL" --agree-tos --no-eff-email --redirect -n
    echo "TAMAM: https://$DOMAIN"
else
    echo "DNS henüz bu sunucuyu göstermiyor. SSL atlandı."
    echo "DNS yayılınca şu komutla SSL'i kurun:"
    echo "certbot --nginx -d $DOMAIN -d $PANEL -m $EMAIL --agree-tos --no-eff-email --redirect -n"
fi
