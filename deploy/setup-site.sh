#!/usr/bin/env bash
#
# Prepara un sitio de CloudPanel para recibir releases atomicas.
# Se corre UNA vez, como root, despues de crear el sitio con:
#
#   clpctl site:add:php --domainName=<dominio> --phpVersion=8.5 \
#       --vhostTemplate='Laravel 13' --siteUser=<usuario> --siteUserPassword='<pass>'
#   clpctl db:add --domainName=<dominio> --databaseName=<db> \
#       --databaseUserName=<user> --databaseUserPassword='<pass>'
#
# Uso:
#   SITE_USER=... SITE_DOMAIN=... bash setup-site.sh "<clave-publica-de-deploy>"
#
# Requiere que <SITE_DIR>/shared/.env ya exista o se cargue despues: sin el,
# la primera release falla al cachear la config.
#
set -euo pipefail

SITE_USER="${SITE_USER:?falta SITE_USER}"
SITE_DOMAIN="${SITE_DOMAIN:?falta SITE_DOMAIN}"
SITE_DIR="/home/$SITE_USER/htdocs/$SITE_DOMAIN"
QUEUE_SERVICE="${QUEUE_SERVICE:-${SITE_USER}-queue}"
PHP_BIN="${PHP_BIN:-/usr/bin/php8.5}"
DEPLOY_PUBKEY="${1:?falta la clave publica de deploy como primer argumento}"

if [ "$(id -u)" -ne 0 ]; then
    echo "error: correr como root" >&2
    exit 1
fi

echo "==> Estructura de releases"
install -d -o "$SITE_USER" -g "$SITE_USER" -m 770 \
    "$SITE_DIR/releases" \
    "$SITE_DIR/shared" \
    "$SITE_DIR/shared/storage/app/public" \
    "$SITE_DIR/shared/storage/framework/cache/data" \
    "$SITE_DIR/shared/storage/framework/sessions" \
    "$SITE_DIR/shared/storage/framework/views" \
    "$SITE_DIR/shared/storage/logs"

echo "==> public -> current/public"
# El vhost del panel apunta a <dominio>/public. Convirtiendolo en symlink hacia
# current/public, el deploy atomico funciona sin editar el root del vhost.
# El placeholder del panel se aparta en vez de borrarse: reversible.
if [ -d "$SITE_DIR/public" ] && [ ! -L "$SITE_DIR/public" ]; then
    mv "$SITE_DIR/public" "$SITE_DIR/public.placeholder-$(date +%s)"
fi
ln -sfn "$SITE_DIR/current/public" "$SITE_DIR/public"
chown -h "$SITE_USER:$SITE_USER" "$SITE_DIR/public"

echo "==> Parche del vhost: \$document_root -> \$realpath_root"
# CloudPanel genera SCRIPT_FILENAME con $document_root, que NO resuelve
# symlinks. Con realpath_cache_ttl en 86400, tras mover 'current' PHP puede
# seguir sirviendo la release anterior hasta 24 horas. Con $realpath_root cada
# release tiene una ruta real unica y el problema desaparece sin necesidad de
# recargar php-fpm (servicio compartido con otros sitios de la maquina).
VHOST="/etc/nginx/sites-enabled/$SITE_DOMAIN.conf"
if grep -q 'SCRIPT_FILENAME \$document_root' "$VHOST"; then
    cp "$VHOST" "$VHOST.bak-$(date +%s)"
    sed -i 's|fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;|fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;\n    fastcgi_param DOCUMENT_ROOT $realpath_root;|' "$VHOST"
    nginx -t
    systemctl reload nginx
fi

echo "==> Parche del vhost: rutas de assets de Livewire"
# Livewire sirve livewire.min.js por una RUTA de Laravel, no como archivo en
# disco. El bloque de assets estaticos del panel es una location con regex, y
# en nginx las regex tienen prioridad sobre el prefijo `location /`: atrapa
# cualquier *.js, no encuentra el archivo y devuelve 404 sin llegar nunca a
# PHP. Sin esto, ningun componente Livewire funciona en produccion.
# `location ^~` tiene prioridad sobre las regex, asi que intercepta antes.
# El vhost tiene DOS server: el publico (443), que proxya a 8080, y el backend
# (8080), que corre PHP. Cada uno trae su propio bloque de assets, asi que el
# parche difiere: en el publico hay que proxyar, en el backend hay que caer a
# index.php. Poner try_files en el publico NO funciona: ese server no tiene
# manejador de PHP.
if ! grep -q 'location \^~ /livewire' "$VHOST"; then
    cp "$VHOST" "$VHOST.bak-livewire-$(date +%s)"
    awk '
        index($0, "location ~*") > 0 && index($0, "(css|js|jpg") > 0 {
            n++
            print "  location ^~ /livewire {"
            if (n == 1) {
                print "    proxy_pass http://127.0.0.1:8080;"
                print "    proxy_set_header Host $host;"
                print "    proxy_set_header X-Forwarded-Host $host;"
                print "    proxy_set_header X-Real-IP $remote_addr;"
                print "    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;"
                print "    proxy_redirect off;"
            } else {
                print "    try_files $uri $uri/ /index.php?$args;"
            }
            print "  }"
            print ""
        }
        { print }
    ' "$VHOST" > "$VHOST.tmp"
    mv "$VHOST.tmp" "$VHOST"
    nginx -t
    systemctl reload nginx
fi

echo "==> Clave de deploy para $SITE_USER"
install -d -o "$SITE_USER" -g "$SITE_USER" -m 700 "/home/$SITE_USER/.ssh"
AK="/home/$SITE_USER/.ssh/authorized_keys"
touch "$AK"
if ! grep -qF "$DEPLOY_PUBKEY" "$AK" 2>/dev/null; then
    printf '%s\n' "$DEPLOY_PUBKEY" >> "$AK"
fi
chown "$SITE_USER:$SITE_USER" "$AK"
chmod 600 "$AK"

echo "==> Worker de colas ($QUEUE_SERVICE)"
# ContactFormSubmittedMail implementa ShouldQueue: sin worker, los mails del
# formulario de contacto se acumulan en la tabla jobs y nadie se entera.
cat > "/etc/systemd/system/$QUEUE_SERVICE.service" <<EOF
[Unit]
Description=$SITE_DOMAIN queue worker
After=network.target mysql.service

[Service]
User=$SITE_USER
Group=$SITE_USER
Restart=always
RestartSec=5
WorkingDirectory=$SITE_DIR/current
ExecStart=$PHP_BIN $SITE_DIR/current/artisan queue:work \\
    --sleep=3 --tries=3 --max-time=3600 --backoff=10
StopWaitSec=60
KillSignal=SIGTERM

[Install]
WantedBy=multi-user.target
EOF
systemctl daemon-reload
systemctl enable "$QUEUE_SERVICE" >/dev/null
# No se arranca aca: sin release, WorkingDirectory todavia no existe.

echo "==> Sudo acotado para el deploy"
cat > "/etc/sudoers.d/$QUEUE_SERVICE-deploy" <<EOF
$SITE_USER ALL=(root) NOPASSWD: /usr/bin/systemctl restart $QUEUE_SERVICE
$SITE_USER ALL=(root) NOPASSWD: /usr/bin/systemctl status $QUEUE_SERVICE
EOF
chmod 440 "/etc/sudoers.d/$QUEUE_SERVICE-deploy"
visudo -cf "/etc/sudoers.d/$QUEUE_SERVICE-deploy"

echo "==> Listo. Falta cargar $SITE_DIR/shared/.env (600, dueño $SITE_USER)."
