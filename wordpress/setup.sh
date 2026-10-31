#!/usr/bin/env bash
# Start the local WordPress site and (re)seed it from the theme's patterns.
#
#   ./setup.sh              start containers, install WordPress if needed, seed pages
#   SLS_SEED_KEEP=1 ./setup.sh   same, but keep edited content of existing pages
#
# Site: http://localhost:${SLS_WP_PORT:-8090}  ·  Admin: /wp-admin (user "admin")
set -euo pipefail
cd "$(dirname "$0")"

PORT="${SLS_WP_PORT:-8090}"
URL="http://localhost:${PORT}"
PASSWORD_FILE=".admin-password"

if command -v podman-compose >/dev/null 2>&1; then
  ENGINE=podman COMPOSE=(podman-compose)
elif docker compose version >/dev/null 2>&1; then
  ENGINE=docker COMPOSE=(docker compose)
else
  echo "Needs podman-compose or docker compose." >&2
  exit 1
fi

# WP-CLI in a throwaway container on the project's network and volume
# (names follow compose's "<project>_<name>" convention; project is sls-wp).
wp() {
  "$ENGINE" run --rm -i --network sls-wp_default --user 33:33 \
    -e WORDPRESS_DB_HOST=db -e WORDPRESS_DB_USER=wordpress -e WORDPRESS_DB_PASSWORD=wordpress -e WORDPRESS_DB_NAME=wordpress \
    -e SLS_SEED_KEEP="${SLS_SEED_KEEP:-}" -e SLS_FRESH_INSTALL="${SLS_FRESH_INSTALL:-}" \
    -v sls-wp_wp_data:/var/www/html \
    -v "$PWD/wp-content/themes/sls:/var/www/html/wp-content/themes/sls:ro" \
    -v "$PWD/seed:/seed:ro" \
    docker.io/library/wordpress:cli wp "$@"
}

echo "Starting containers…"
if ! out="$("${COMPOSE[@]}" up -d db wordpress 2>&1)"; then
  echo "$out" >&2
  exit 1
fi

echo -n "Waiting for WordPress and the database"
for _ in $(seq 1 60); do
  if wp db query "SELECT 1" >/dev/null 2>&1; then
    echo " ready."
    break
  fi
  echo -n "."
  sleep 2
done
wp db query "SELECT 1" >/dev/null

if ! wp core is-installed >/dev/null 2>&1; then
  password="$(head -c 18 /dev/urandom | base64 | tr -d '/+=' | head -c 20)"
  wp core install --url="$URL" --title="Smart Logic Systems" \
    --admin_user=admin --admin_password="$password" --admin_email=admin@example.com --skip-email
  (umask 077 && printf '%s\n' "$password" > "$PASSWORD_FILE")
  export SLS_FRESH_INSTALL=1
fi

wp theme activate sls
wp rewrite structure '/%postname%/'
wp eval-file /seed/seed.php --user=admin

echo
echo "Site:  $URL"
echo "Admin: $URL/wp-admin  (user: admin, password in wordpress/$PASSWORD_FILE)"
