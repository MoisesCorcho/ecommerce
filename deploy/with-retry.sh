#!/usr/bin/env bash
#
# Reintenta un comando con backoff exponencial.
#
# A diferencia de remote-activate.sh y rollback.sh, este script corre en el
# RUNNER de GitHub Actions, no en el servidor: envuelve las invocaciones de
# ssh y scp del job de deploy.
#
# Existe porque el paso "Upload release" falla de forma intermitente con
# "ssh: connect to host ... port 22: Connection timed out", en cerca de la
# mitad de las corridas. Está diagnosticado: sshd no registra NADA en el
# journal del servidor durante la ventana de la falla, el 22 está abierto en
# ufw y fail2ban no bañea a nadie. La conexión nunca llega a destino, así que
# es un drop de red aguas arriba y no hay nada que arreglar de nuestro lado.
# Todo reintento manual pasó de una.
#
# Uso:
#   bash deploy/with-retry.sh ssh -o ConnectTimeout=15 usuario@host 'comando'
#
# Sólo envolver comandos IDEMPOTENTES: un reintento los ejecuta de nuevo
# desde cero.
#
set -uo pipefail

ATTEMPTS="${RETRY_ATTEMPTS:-5}"
DELAY="${RETRY_DELAY:-10}"

if [ "$#" -eq 0 ]; then
    echo "uso: with-retry.sh <comando> [args...]" >&2
    exit 64
fi

for attempt in $(seq 1 "$ATTEMPTS"); do
    # Se captura $? del comando DIRECTAMENTE, sin un `if` de por medio: cuando un
    # `if` no toma ninguna rama, POSIX dice que devuelve 0, así que leer $? después
    # del `fi` da 0 incluso con el comando fallado. Eso hacía que este script
    # saliera 0 tras agotar los intentos, o sea un deploy roto reportado en verde.
    # Este script no usa `set -e`, así que el fallo no aborta acá.
    "$@"
    status=$?

    if [ "$status" -eq 0 ]; then
        if [ "$attempt" -gt 1 ]; then
            echo "::notice::el comando pasó en el intento $attempt/$ATTEMPTS"
        fi
        exit 0
    fi

    if [ "$attempt" -eq "$ATTEMPTS" ]; then
        echo "::error::el comando falló en los $ATTEMPTS intentos (último exit $status): $*"
        exit "$status"
    fi

    echo "::warning::intento $attempt/$ATTEMPTS falló (exit $status); reintento en ${DELAY}s"
    sleep "$DELAY"
    DELAY=$(( DELAY * 2 ))
done
