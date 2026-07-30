#!/usr/bin/env bash
#
# Vuelve a la release anterior. Se corre en el servidor, como el usuario del
# sitio:
#
#   APP_ROOT=/home/<site-user>/htdocs/<dominio> bash deploy/rollback.sh
#   APP_ROOT=... bash deploy/rollback.sh <release>   # una puntual
#
# OJO: revierte el CÓDIGO, no la base de datos. Si la release rota corrió una
# migración destructiva, esto no la deshace — hay que restaurar el backup.
#
set -euo pipefail

APP_ROOT="${APP_ROOT:?falta APP_ROOT}"
QUEUE_SERVICE="${QUEUE_SERVICE:-leen-queue}"
PHP_BIN="${PHP_BIN:-/usr/bin/php8.5}"
TARGET="${1:-}"

if [ -z "$TARGET" ]; then
    CURRENT="$(basename "$(readlink -f "$APP_ROOT/current")")"
    TARGET="$(
        find "$APP_ROOT/releases" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %f\n' \
            | sort -rn \
            | cut -d' ' -f2- \
            | grep -vx "$CURRENT" \
            | head -n 1
    )"
fi

if [ -z "$TARGET" ] || [ ! -d "$APP_ROOT/releases/$TARGET" ]; then
    echo "error: no hay release a la que volver" >&2
    exit 1
fi

echo "==> Volviendo a $TARGET"
ln -sfn "$APP_ROOT/releases/$TARGET" "$APP_ROOT/current.tmp"
mv -T "$APP_ROOT/current.tmp" "$APP_ROOT/current"

cd "$APP_ROOT/current"
"$PHP_BIN" artisan optimize

sudo /usr/bin/systemctl restart "$QUEUE_SERVICE"

echo "==> Activa: $TARGET"
