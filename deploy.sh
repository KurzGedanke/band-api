#!/usr/bin/env bash
#
# deploy.sh — pull, build and release the Band API on the server.
#
# Usage:
#   ./deploy.sh                 # deploy the configured branch
#   BRANCH=main ./deploy.sh     # override branch
#   RUN_MIGRATIONS=0 ./deploy.sh
#
# Assumptions:
#   - This repo is checked out on the server and the web server's docroot
#     points at <repo>/public.
#   - Production secrets (APP_SECRET, DATABASE_URL, ...) live in .env.local
#     or in the real environment — NEVER committed.
#   - Run as the deploy user (the one that owns the files), not root.
#
set -Eeuo pipefail

# ---------------------------------------------------------------------------
# Config (override via environment)
# ---------------------------------------------------------------------------
APP_DIR="${APP_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)}"
BRANCH="${BRANCH:-main}"
REMOTE="${REMOTE:-origin}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-1}"   # set to 0 if you manage the schema manually
PHP="${PHP:-php}"
COMPOSER="${COMPOSER:-composer}"

export APP_ENV=prod
export APP_DEBUG=0

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------
log()  { printf '\033[1;34m▶ %s\033[0m\n' "$*"; }
ok()   { printf '\033[1;32m✔ %s\033[0m\n' "$*"; }
die()  { printf '\033[1;31m✘ %s\033[0m\n' "$*" >&2; exit 1; }

PREV_COMMIT=""
on_error() {
  printf '\033[1;31m✘ Deploy failed.\033[0m\n' >&2
  [ -n "$PREV_COMMIT" ] && printf 'To roll back the code:  git -C %s reset --hard %s\n' "$APP_DIR" "$PREV_COMMIT" >&2
  exit 1
}
trap on_error ERR

# ---------------------------------------------------------------------------
# Pre-flight
# ---------------------------------------------------------------------------
cd "$APP_DIR"
[ -d .git ] || die "No git repo at $APP_DIR"
[ "$(id -u)" -ne 0 ] || die "Refusing to run as root — use the deploy user."
command -v "$COMPOSER" >/dev/null || die "composer not found"
"$PHP" -v >/dev/null || die "php not found"

PREV_COMMIT="$(git rev-parse HEAD)"
log "Deploying $REMOTE/$BRANCH to $APP_DIR (current: ${PREV_COMMIT:0:8})"

# ---------------------------------------------------------------------------
# 1. Pull new code (discard local changes to TRACKED files only)
#    Untracked data — var/, public/images uploads, .env.local, the SQLite db —
#    is left untouched.
# ---------------------------------------------------------------------------
log "Fetching latest code…"
git fetch --prune "$REMOTE"
git reset --hard "$REMOTE/$BRANCH"
git submodule update --init --recursive 2>/dev/null || true
NEW_COMMIT="$(git rev-parse HEAD)"
ok "Checked out ${NEW_COMMIT:0:8}"

# ---------------------------------------------------------------------------
# 2. Install PHP dependencies (production)
# ---------------------------------------------------------------------------
log "Installing composer dependencies…"
"$COMPOSER" install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist

# ---------------------------------------------------------------------------
# 3. Remove stale prod cache, then rebuild & warm it
# ---------------------------------------------------------------------------
log "Clearing old cache…"
rm -rf var/cache/prod
"$PHP" bin/console cache:clear --no-interaction
"$PHP" bin/console cache:warmup --no-interaction
ok "Cache rebuilt"

# ---------------------------------------------------------------------------
# 4. Assets — install bundle assets (Nelmio Swagger UI, EasyAdmin) and
#    compile the asset-mapper output into public/assets.
# ---------------------------------------------------------------------------
log "Building assets…"
"$PHP" bin/console assets:install public --no-interaction
"$PHP" bin/console importmap:install --no-interaction || true
"$PHP" bin/console asset-map:compile --no-interaction
ok "Assets compiled"

# ---------------------------------------------------------------------------
# 5. Database
#    The shipped migrations are MySQL-specific. If you run MySQL/Postgres in
#    prod, migrations apply cleanly. On SQLite (or first boot) set
#    RUN_MIGRATIONS=0 and create the schema once with:
#        php bin/console doctrine:schema:create
# ---------------------------------------------------------------------------
if [ "$RUN_MIGRATIONS" = "1" ]; then
  log "Running database migrations…"
  "$PHP" bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
  ok "Migrations applied"
else
  log "Skipping migrations (RUN_MIGRATIONS=0)"
fi

# ---------------------------------------------------------------------------
# 6. Reset OPcache so new code is picked up (best effort)
# ---------------------------------------------------------------------------
if "$PHP" -r 'exit(function_exists("opcache_reset")?0:1);' 2>/dev/null; then
  log "Resetting OPcache…"
  "$PHP" -r 'opcache_reset();' 2>/dev/null || true
  # If PHP-FPM runs as a service, a reload also clears OPcache:
  #   sudo systemctl reload php8.4-fpm
fi

ok "Deploy complete: ${PREV_COMMIT:0:8} → ${NEW_COMMIT:0:8}"
