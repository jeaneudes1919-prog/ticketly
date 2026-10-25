#!/usr/bin/env bash
# Installation locale de Ticketly depuis ce dépôt.
set -euo pipefail
cd "$(dirname "$0")"

command -v php >/dev/null || { echo "PHP 8.2+ est requis"; exit 1; }
command -v composer >/dev/null || { echo "Composer 2 est requis"; exit 1; }
command -v npm >/dev/null || { echo "Node.js 20+ est requis"; exit 1; }

php -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);' || { echo "PHP 8.2+ est requis"; exit 1; }

echo "→ Installation des dépendances PHP…"
composer install --no-interaction

if [ ! -f .env ]; then
    cp .env.example .env
    echo "→ Fichier .env créé."
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --ansi
fi

touch database/database.sqlite

echo "→ Migration et jeu de données de démonstration…"
php artisan migrate:fresh --seed --force

echo "→ Lien de stockage public…"
php artisan storage:link 2>/dev/null || true

echo "→ Dépendances front et build…"
npm install --prefer-offline
npm run build

echo ""
echo "Ticketly est prêt."
echo "Lancement : bash dev.sh"
echo "Adresse   : http://localhost:8000"
echo "Comptes   : admin@ticketly.dev, agent@ticketly.dev, client@ticketly.dev"
echo "Mot de passe : Ticketly2026!"
