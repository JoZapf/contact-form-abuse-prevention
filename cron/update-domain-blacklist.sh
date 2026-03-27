#!/bin/bash
# ==============================================================================
# update-domain-blacklist.sh — Wöchentlicher Update der Disposable-Domain-Liste
# ==============================================================================
# Ablage:   ~/public_html/cron/contactform/update-domain-blacklist.sh
# Cronjob:  0 3 * * 0 /usr/home/jozapf/public_html/cron/contactform/update-domain-blacklist.sh
#           (Sonntag 03:00 Uhr)
#
# Quellen:
#   Upstream: https://disposable.github.io/disposable-email-domains/domains.txt
#   Custom:   data/domain-blacklist-custom.txt (manuell gepflegt, versioniert in Git)
#
# Ergebnis:
#   data/domain-blacklist.txt (Upstream + Custom, dedupliziert, sortiert)
#
# Konzept: docs/contact-form-feature/KONZEPT-EMAIL-SPAM-VALIDIERUNG.md
# ==============================================================================

set -euo pipefail

# --- Konfiguration ---
PROJECT_ROOT="/usr/home/jozapf/public_html/jozapf-de"
DATA_DIR="${PROJECT_ROOT}/assets/php/data"
LOG_DIR="${PROJECT_ROOT}/assets/php/logs"
UPSTREAM_URL="https://disposable.github.io/disposable-email-domains/domains.txt"
UPSTREAM_FILE="${DATA_DIR}/domain-blacklist-upstream.txt"
CUSTOM_FILE="${DATA_DIR}/domain-blacklist-custom.txt"
MERGED_FILE="${DATA_DIR}/domain-blacklist.txt"
LOG="${LOG_DIR}/domain-blacklist-update.log"
MIN_LINES=1000  # Sicherheitscheck: Upstream muss mindestens so viele Zeilen haben

# --- Logging ---
log() {
    echo "[$(date -Iseconds)] $1" >> "$LOG"
}

log "=== Domain blacklist update started ==="

# --- Prüfung: Custom-Datei vorhanden? ---
if [ ! -f "$CUSTOM_FILE" ]; then
    log "WARNING: Custom file not found at ${CUSTOM_FILE} — using upstream only"
fi

# --- Download Upstream (mit Backup) ---
TMPFILE=$(mktemp)
trap 'rm -f "$TMPFILE"' EXIT

HTTP_CODE=$(curl -sSL --max-time 30 -w "%{http_code}" -o "$TMPFILE" "$UPSTREAM_URL" 2>> "$LOG")

if [ "$HTTP_CODE" != "200" ]; then
    log "ERROR: Upstream download failed (HTTP ${HTTP_CODE})"
    exit 1
fi

UPSTREAM_LINES=$(wc -l < "$TMPFILE")

if [ "$UPSTREAM_LINES" -lt "$MIN_LINES" ]; then
    log "ERROR: Upstream suspiciously small (${UPSTREAM_LINES} lines, min ${MIN_LINES}) — keeping old file"
    exit 1
fi

# Backup der alten Upstream-Datei
if [ -f "$UPSTREAM_FILE" ]; then
    cp "$UPSTREAM_FILE" "${UPSTREAM_FILE}.bak"
fi

mv "$TMPFILE" "$UPSTREAM_FILE"
log "Downloaded ${UPSTREAM_LINES} domains from upstream"

# --- Merge: Custom + Upstream → dedupliziert und sortiert ---
if [ -f "$CUSTOM_FILE" ]; then
    CUSTOM_COUNT=$(grep -cv '^\s*#\|^\s*$' "$CUSTOM_FILE" 2>/dev/null || echo 0)
    cat "$CUSTOM_FILE" "$UPSTREAM_FILE" \
        | grep -v '^\s*#' \
        | grep -v '^\s*$' \
        | tr '[:upper:]' '[:lower:]' \
        | sort -u \
        > "$MERGED_FILE"
    log "Merged: ${CUSTOM_COUNT} custom + ${UPSTREAM_LINES} upstream domains"
else
    grep -v '^\s*#' "$UPSTREAM_FILE" \
        | grep -v '^\s*$' \
        | tr '[:upper:]' '[:lower:]' \
        | sort -u \
        > "$MERGED_FILE"
fi

TOTAL=$(wc -l < "$MERGED_FILE")
log "Result: ${TOTAL} unique domains in blacklist"

log "=== Update complete ==="
