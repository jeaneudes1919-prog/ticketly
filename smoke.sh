#!/usr/bin/env bash
# Smoke test Ticketly : démarre un serveur isolé, vérifie les pages et l'API.
set -euo pipefail
cd "$(dirname "$0")"

PORT="${PORT:-8001}"
BASE="http://127.0.0.1:${PORT}"
JAR="$(mktemp)"
LOG="$(mktemp)"
SERVER_PID=""

cleanup() {
    [ -n "$SERVER_PID" ] && kill "$SERVER_PID" 2>/dev/null || true
    rm -f "$JAR" "$LOG"
}
trap cleanup EXIT

fail() { echo "✗ $*"; exit 1; }
ok() { echo "✓ $*"; }

[ -f public/build/manifest.json ] || fail "Assets absents : lancez npm run build."

php artisan serve --host=127.0.0.1 --port="$PORT" >"$LOG" 2>&1 &
SERVER_PID=$!

for _ in $(seq 1 30); do
    if curl -fsS "$BASE/login" >/dev/null 2>&1; then break; fi
    sleep 0.5
done
curl -fsS "$BASE/login" >/dev/null || fail "Le serveur n'a pas démarré (port $PORT)."

check() {
    local path="$1" expected="${2:-200}" code
    code=$(curl -s -o /tmp/ticketly-smoke-page.html -w '%{http_code}' -b "$JAR" "$BASE$path")
    [ "$code" = "$expected" ] || fail "$path : HTTP $code (attendu $expected)"
    if grep -q 'Whoops\|Fatal error\|Undefined variable\|Internal Server Error' /tmp/ticketly-smoke-page.html; then
        fail "$path : erreur PHP détectée"
    fi
    ok "$path"
}

login() {
    local email="$1" page token code
    page=$(curl -s -c "$JAR" "$BASE/login")
    token=$(printf '%s' "$page" | grep -oP 'name="_token" value="\K[^"]+' | head -1 || true)
    [ -n "$token" ] || fail "Jeton CSRF introuvable."
    code=$(curl -s -b "$JAR" -c "$JAR" -o /dev/null -w '%{http_code}' -X POST "$BASE/login" \
        --data-urlencode "_token=$token" \
        --data-urlencode "email=$email" \
        --data-urlencode 'password=Ticketly2026!')
    [ "$code" = "302" ] || fail "Connexion $email : HTTP $code"
    ok "Connexion $email"
}

echo "→ Smoke test Ticketly sur $BASE"
login admin@ticketly.dev
check /dashboard
check /tickets
check /customers
check /reports
check /admin/users
check /admin/categories
check /admin/departments
check /admin/tags
check /admin/sla
check /admin/canned-responses
check /admin/settings

TICKET_ID=$(php artisan tinker --execute="echo App\\Models\\Ticket::query()->value('id');" | tail -1 | tr -d '[:space:]' || true)
[ -n "$TICKET_ID" ] || fail "Aucun ticket en base."
check "/tickets/$TICKET_ID"

# API : jeton + liste des tickets
TOKEN=$(curl -s -X POST "$BASE/api/v1/auth/token" -H 'Content-Type: application/json' \
    -d '{"email":"client@ticketly.dev","password":"Ticketly2026!","device_name":"smoke"}' \
    | grep -oP '"token":"\K[^"]+' | head -1 || true)
[ -n "$TOKEN" ] || fail "Jeton API non obtenu."
CODE=$(curl -s -o /tmp/ticketly-smoke-api.json -w '%{http_code}' "$BASE/api/v1/tickets" -H "Authorization: Bearer $TOKEN")
[ "$CODE" = "200" ] || fail "API /tickets : HTTP $CODE"
grep -q '"data"' /tmp/ticketly-smoke-api.json || fail "Réponse API inattendue."
ok "API /api/v1/tickets"

# Portail client
rm -f "$JAR"
login client@ticketly.dev
check /portal
check /portal/tickets
check /portal/tickets/create
CLIENT_TICKET_ID=$(php artisan tinker --execute="echo App\\Models\\Ticket::whereHas('customer', fn (\$q) => \$q->where('email', 'client@ticketly.dev'))->value('id');" | tail -1 | tr -d '[:space:]' || true)
[ -n "$CLIENT_TICKET_ID" ] || fail "Aucun ticket client."
check "/portal/tickets/$CLIENT_TICKET_ID"

echo ""
echo "✓ Smoke test terminé avec succès."
