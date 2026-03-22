#!/bin/bash

log() {
  echo "[$(date +'%Y-%m-%d %H:%M:%S')] $*"
}

log "📡 Starting PHP-FPM in background..."
php-fpm -D

sleep 2 

log "🌐 Starting Nginx..."
exec "$@"