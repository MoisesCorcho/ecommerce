#!/usr/bin/env bash
#
# Activa una release ya subida y extraída en $APP_ROOT/releases/<release>.
# Lo invoca el workflow de GitHub Actions por SSH; viaja dentro del propio
# release, así que siempre coincide con el código que está activando.
#
# Layout esperado en el servidor (sitio de CloudPanel):
#
#   $APP_ROOT/                      = /home/<site-user>/htdocs/<dominio>
#     public -> current/public      (lo apunta el vhost del panel)
#     current -> releases/<release>
#     releases/<release>/
#     shared/.env
#     shared/storage/
#
set -euo pipefail

APP_ROOT="${APP_ROOT:?falta APP_ROOT}"
RELEASE="${1:?uso: remote-activate.sh <release>}"
KEEP_RELEASES="${KEEP_RELEASES:-5}"
QUEUE_SERVICE="${QUEUE_SERVICE:-leen-queue}"
# El 'php' generico del servidor es 8.4; el pool del sitio corre 8.5. Cachear
# la config con un interprete distinto al que sirve las paginas es pedir un bug
# raro a las tres de la mañana.
PHP_BIN="${PHP_BIN:-/usr/bin/php8.5}"

RELEASE_PATH="$APP_ROOT/releases/$RELEASE"
SHARED_PATH="$APP_ROOT/shared"

if [ ! -d "$RELEASE_PATH" ]; then
    echo "error: no existe $RELEASE_PATH" >&2
    exit 1
fi

# Sin .env no hay APP_KEY: config:cache generaría una release inarrancable.
if [ ! -f "$SHARED_PATH/.env" ]; then
    echo "error: falta $SHARED_PATH/.env (se carga a mano una sola vez)" >&2
    exit 1
fi

echo "==> Enlazando estado compartido"
rm -rf "$RELEASE_PATH/storage"
ln -s "$SHARED_PATH/storage" "$RELEASE_PATH/storage"
ln -sfn "$SHARED_PATH/.env" "$RELEASE_PATH/.env"

cd "$RELEASE_PATH"

# --force porque este script se reintenta (with-retry.sh): si un intento anterior
# ya creó el link, sin --force la orden imprime "link already exists". Hoy eso sale
# con exit 0 y set -e lo deja pasar, pero depender de un ERROR que no aborta es
# frágil entre versiones de Laravel.
echo "==> storage:link"
"$PHP_BIN" artisan storage:link --force --quiet

# Corre contra el código nuevo pero con la release vieja todavía servida:
# durante estos segundos el código viejo ve el esquema nuevo. Aceptable en
# staging; en producción exigiría migraciones compatibles hacia atrás.
echo "==> Migraciones"
"$PHP_BIN" artisan migrate --force

echo "==> Cacheando config, rutas, vistas y eventos"
"$PHP_BIN" artisan optimize

echo "==> Swap atómico de current"
ln -sfn "$RELEASE_PATH" "$APP_ROOT/current.tmp"
mv -T "$APP_ROOT/current.tmp" "$APP_ROOT/current"

# No se recarga php-fpm: el vhost usa $realpath_root, así que cada release
# tiene una ruta real distinta y ni OPcache ni el realpath cache quedan viejos.
# Además php8.5-fpm es un servicio COMPARTIDO con otros sitios de la máquina:
# no se toca en cada deploy.
echo "==> Reiniciando worker de colas"
sudo /usr/bin/systemctl restart "$QUEUE_SERVICE"

echo "==> Limpiando releases viejas (se conservan $KEEP_RELEASES)"
find "$APP_ROOT/releases" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %p\n' \
    | sort -rn \
    | tail -n "+$((KEEP_RELEASES + 1))" \
    | cut -d' ' -f2- \
    | xargs -r rm -rf

echo "==> Release $RELEASE activa"
