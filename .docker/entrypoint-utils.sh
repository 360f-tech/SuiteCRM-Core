#!/bin/bash

# Utility functions for SuiteCRM Docker Entrypoint

log() {
  echo "[$(date +'%Y-%m-%d %H:%M:%S')] $*"
}

set_permissions() {
  log "🔐 Setting permissions..."
  find "$APP_DIR" -type d -not -perm 2755 -exec chmod 2755 {} \;
  find "$APP_DIR" -type f -not -perm 0644 -exec chmod 0644 {} \;
  find "$APP_DIR" ! -user www-data -exec chown www-data:www-data {} \;
  chmod +x "$APP_DIR"/bin/console
  log "✅ Permissions set."
}

write_oauth2_keys() {
  log "🔑 Writing OAuth2 keys from Environment Variables..."
  mkdir -p "$API_OAUTH2_DIR"
  if [[ -z "${OAUTH2_PRIVATE_KEY:-}" || -z "${OAUTH2_PUBLIC_KEY:-}" ]]; then
    log "❌ FATAL ERROR: OAUTH2_PRIVATE_KEY or OAUTH2_PUBLIC_KEY is missing!"
    log "💡 API and Front-end will not function without OAuth2 keys. Terminating process..."
    exit 1
  fi
  echo -e "$OAUTH2_PRIVATE_KEY" > "$API_OAUTH2_DIR/private.key"
  echo -e "$OAUTH2_PUBLIC_KEY" > "$API_OAUTH2_DIR/public.key"
  if [[ ! -s "$API_OAUTH2_DIR/private.key" ]]; then
    log "❌ FATAL ERROR: Failed to write keys to disk at $API_OAUTH2_DIR"
    exit 1
  fi
  chmod 600 "$API_OAUTH2_DIR/private.key" "$API_OAUTH2_DIR/public.key"
  chown www-data:www-data "$API_OAUTH2_DIR/private.key" "$API_OAUTH2_DIR/public.key"
  log "✅ OAuth2 keys successfully written and secured."
}