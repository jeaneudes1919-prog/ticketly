#!/usr/bin/env bash
set -e
cd "$(dirname "$0")"
npx concurrently -n server,cron,vite -c "#93c5fd,#fb7185,#fdba74" "php artisan serve --host=127.0.0.1 --port=8000" "php artisan schedule:work" "npm run dev"
