#!/bin/sh
set -e

# Render sets $PORT. Locally it is unset, so fall back to 10000.
PORT="${PORT:-10000}"

sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" \
    /etc/apache2/sites-available/000-default.conf

# Apache resolves ServerName at boot; without this it warns on every start.
echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf
a2enconf servername >/dev/null 2>&1 || true

exec "$@"
