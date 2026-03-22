#!/bin/bash

set -euo pipefail

# --- Path Variables ---
APP_DIR="/var/www/html/SuiteCRM"

# --- Config & DB ---
DB_CHECK_SCRIPT="$APP_DIR/public/legacy/check_db.php"
API_OAUTH2_DIR="$APP_DIR/public/legacy/Api/V8/OAuth2"

# --- Source utility functions ---
source /usr/local/bin/entrypoint-utils.sh

# --- Main Entrypoint ---
main() {

  # copy_custom_php

  my_log "🚀 Starting SuiteCRM Docker Entrypoint..."
  my_log "⏳ Waiting for Database connection (PHP PDO with SSL)..."

  # Wait for DB ready
  local MAX_RETRIES=3
  local COUNT=0
  local RESULT=""
  while true; do
    RESULT=$(php "$DB_CHECK_SCRIPT")
    if [[ "$RESULT" == "EXISTS" || "$RESULT" == "EMPTY" ]]; then
      my_log "✅ Database is reachable! State: $RESULT"
      break
    fi
    COUNT=$((COUNT+1))
    if (( COUNT >= MAX_RETRIES )); then
      my_log "❌ Error: Could not connect to DB after $MAX_RETRIES attempts."
      exit 1
    fi
    my_log "... retrying ($COUNT/$MAX_RETRIES) ..."
    sleep 3
  done

  # Install or Repair
  if [[ "$RESULT" == "EMPTY" ]]; then
    my_log "🚀 Database is empty. Running SuiteCRM Install..."
    
    DB_USER=$(php -r "echo parse_url(getenv('DATABASE_URL'), PHP_URL_USER);")
    DB_PASS=$(php -r "echo parse_url(getenv('DATABASE_URL'), PHP_URL_PASS);")
    DB_HOST=$(php -r "echo parse_url(getenv('DATABASE_URL'), PHP_URL_HOST);")
    DB_PORT=$(php -r "echo parse_url(getenv('DATABASE_URL'), PHP_URL_PORT) ?: '3306';")
    DB_NAME=$(php -r "echo ltrim(parse_url(getenv('DATABASE_URL'), PHP_URL_PATH), '/');")
    
    php ./SuiteCRM/bin/console suitecrm:app:install \
      -u "${ADMIN_USER:-admin}" \
      -p "${ADMIN_PASS:-admin123}" \
      -U "$DB_USER" \
      -P "$DB_PASS" \
      -H "$DB_HOST:$DB_PORT" \
      -N "$DB_NAME" \
      -S "${SITE_URL:-http://localhost:8080}" \
      -d "${DEMO_DATA:-no}" \
      -n;

    set_permissions  
    my_log "✅ Install completed!"
  fi

  
  write_oauth2_keys

  local ENV_FILE="$APP_DIR/.env.local"
  cat <<EOF > "$ENV_FILE"
APP_ENV=prod
APP_SECRET=${APP_SECRET:-my-fixed-secret}
DATABASE_URL=${DATABASE_URL:-my-fixed-url}
EOF
  my_log ".env.local created temporarily for container."

  # Overwrite config from template
  my_log "🔄 Applying stateless config.php template..."
  cp /var/www/html/SuiteCRM/public/legacy/config_template.php /var/www/html/SuiteCRM/public/legacy/config.php
  cp /var/www/html/SuiteCRM/public/legacy/config_override_template.php /var/www/html/SuiteCRM/public/legacy/config_override.php

  su -s /bin/bash www-data -c "php /var/www/html/SuiteCRM/public/legacy/repair.php"

if [[ "$1" == *"nginx"* ]]; then
      my_log "📡 Nginx detected! Starting PHP-FPM in background first..."
      php-fpm -D
      
      my_log "🌐 Now starting Nginx as the main process..."
      exec "$@"
  else
      # If CMD is "php-fpm" (default) or any, run as normal
      my_log "🚀 Running command: $@"
      exec "$@"
  fi
}

main "$@"