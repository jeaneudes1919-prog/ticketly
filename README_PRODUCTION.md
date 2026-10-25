# Production — Ticketly

Cette version est une **démo locale** prête à être présentée. Aucun déploiement public n’est configuré dans le dépôt.

## Checklist avant mise en production

1. `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://votre-domaine`.
2. Générer une clé : `php artisan key:generate --force`.
3. Configurer une base persistante. SQLite fonctionne si le disque est persistant ; sinon passer à PostgreSQL/MySQL.
4. Configurer un vrai mailer (`MAIL_MAILER=smtp`, Resend, Mailgun…) et `MAIL_FROM_ADDRESS`.
5. `php artisan migrate --force`, `php artisan storage:link`.
6. `npm ci && npm run build`.
7. Optimiser : `php artisan config:cache`, `route:cache`, `view:cache`.
8. Planifier `php artisan schedule:run` chaque minute (cron) pour `tickets:check-sla` et `tickets:auto-close`.
9. Mettre en place les sauvegardes de la base et des fichiers (`storage/app/public`).
10. Servir en HTTPS derrière Nginx + PHP-FPM ou une plateforme managée.

## Hébergement gratuit et SQLite

Un hébergement 100 % gratuit avec **SQLite persistant** est le point délicat : beaucoup de plateformes gratuites utilisent un système de fichiers éphémère, ce qui efface la base à chaque redéploiement. Avant de choisir :

- vérifier que le disque est persistant et sauvegardé ;
- sinon migrer vers PostgreSQL/MySQL gratuit (Neon, Supabase, etc.) ;
- vérifier les quotas et la mise en veille des offres gratuites.

Pistes à évaluer au moment du déploiement : Fly.io avec volume persistant, Render avec disque persistant (selon offre), Railway (crédit d’essai), ou un petit serveur auto-hébergé. Les conditions changent souvent : toujours vérifier les tarifs actuels.

## Éléments à adapter

- Stockage des pièces jointes : disque public local → S3/Supabase Storage.
- Emails : `log` → SMTP/API.
- Files d’attente : `sync` → Redis/database + worker.
- Cache et sessions : base → Redis.
- Journalisation et monitoring : Sentry, Flare, logs centralisés.
- Sauvegardes automatiques et politique de rétention.
- Limitation de débit et protection anti-abus sur l’API.

## Commandes utiles

```bash
php artisan about
php artisan migrate:status
php artisan schedule:list
php artisan tickets:check-sla
php artisan tickets:auto-close
php artisan optimize:clear
```
