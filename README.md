# Ticketly

**Helpdesk complet** pour gérer les demandes clients de bout en bout : portail client, boîte de réception des agents, SLA, réponses prédéfinies, notes internes, tableau de bord et administration.

Ticketly est une application **Laravel 11** monolithe, avec **Blade + Alpine.js + Tailwind CSS 3 + Chart.js** et une base **SQLite**. L’interface est entièrement en français, responsive et propose un thème clair/sombre.

## Fonctionnalités

### Portail client
- Création d’un ticket avec catégorie, priorité et pièces jointes (5 fichiers max, 5 Mo chacun).
- Suivi de la discussion, réponses, fermeture et réouverture sous 14 jours.
- Évaluation de la satisfaction (1 à 5 étoiles + commentaire).
- Les notes internes de l’équipe ne sont jamais visibles côté client.

### Espace agents
- Boîte de réception avec vues rapides : tous, mes tickets, non assignés, en retard, résolus.
- Recherche et filtres : statut, priorité, catégorie, agent, étiquette, période, tri.
- Actions groupées : assignation, changement de statut, fermeture.
- Fiche ticket : compte à rebours SLA, réponses prédéfinies avec raccourcis (`/accuse`, `/resolu`…), notes internes, étiquettes, pièces jointes et chronologie d’activité.
- Fusion de deux tickets ouverts d’un même client.
- Tableau de bord avec 4 graphiques Chart.js et classement des agents.
- Rapports par période et export CSV.

### Administration
- Utilisateurs et rôles (admin, agent, client), activation/désactivation, départements.
- Catégories, départements, étiquettes.
- Politiques SLA par priorité (première réponse et résolution).
- Réponses prédéfinies et raccourcis.
- Paramètres d’entreprise : nom et couleur d’accent.

### Automatique et API
- `tickets:check-sla` : alerte 15 minutes avant l’échéance, marque les dépassements et notifie les responsables.
- `tickets:auto-close` : ferme les tickets résolus depuis plus de 3 jours.
- Notifications dans l’application **et par email** (en développement, les emails sont écrits dans `storage/logs/laravel.log`).
- API REST `/api/v1` protégée par Sanctum.
- Réinitialisation de mot de passe (lien envoyé par email / journalisé en local).

## Installation

### Depuis ce dépôt (recommandé)

Prérequis : **PHP 8.2+** (extensions `sqlite`, `mbstring`, `xml`, `curl`, `zip`), **Composer 2**, **Node.js 20+**.

```bash
bash install.sh
bash dev.sh
```

`install.sh` installe les dépendances, crée le fichier `.env`, génère la clé, migre et remplit la base SQLite, crée le lien de stockage et construit les assets. `dev.sh` lance le serveur Laravel (`http://localhost:8000`), le planificateur et Vite.

### Manuellement

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan storage:link
npm install
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Pour le développement front avec rechargement à chaud :

```bash
npm run dev
```

## Comptes de démonstration

Mot de passe commun : **`Ticketly2026!`**

| Rôle | Email |
| --- | --- |
| Administrateur | `admin@ticketly.dev` |
| Agent | `agent@ticketly.dev` |
| Client | `client@ticketly.dev` |

La base de démonstration contient 20 utilisateurs, 4 départements, 6 catégories, 8 étiquettes, 12 réponses prédéfinies, 80 tickets, 250 réponses (dont 70 notes internes), 529 entrées d’activité et 8 notifications.

## Tests

```bash
php artisan test
```

Les tests couvrent les politiques d’accès, les notes internes, le SLA, la fermeture automatique, l’API, la fusion de tickets et la réinitialisation du mot de passe.

## API

Obtenir un jeton :

```bash
curl -X POST http://localhost:8000/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{"email":"client@ticketly.dev","password":"Ticketly2026!","device_name":"demo"}'
```

Endpoints principaux :

| Méthode | URL |
| --- | --- |
| POST | `/api/v1/auth/token` |
| POST | `/api/v1/auth/logout` |
| GET | `/api/v1/me` |
| GET / POST | `/api/v1/tickets` |
| GET | `/api/v1/tickets/{id}` |
| POST | `/api/v1/tickets/{id}/replies` |
| PATCH | `/api/v1/tickets/{id}` |
| GET | `/api/v1/stats/overview` (staff) |

Les endpoints authentifiés attendent l’en-tête `Authorization: Bearer <token>`.

## Architecture

- `app/Services/TicketService.php` : création, réponses, mise à jour, fermeture, fusion, journalisation.
- `app/Services/SlaService.php` : calcul des échéances et détection des dépassements.
- `app/Events` + `app/Listeners` : événements métier et notifications.
- `app/Policies/TicketPolicy.php` + scope `Ticket::visibleTo()` : contrôle d’accès.
- `app/Console/Commands` : tâches planifiées.
- `resources/views` : interface Blade, composants anonymes et layouts.
- `database/seeders/DatabaseSeeder.php` : jeu de données de démonstration.

## Sécurité et limites de la démo

- Les emails sont journalisés (`MAIL_MAILER=log`) et non envoyés réellement.
- Les fichiers sont stockés sur le disque public local.
- SQLite convient à une démonstration ou à un petit déploiement avec disque persistant.
- Le mot de passe oublié fonctionne ; en local, le lien est à récupérer dans `storage/logs/laravel.log`.

## Licence

Projet de démonstration à usage pédagogique et professionnel. MIT.
