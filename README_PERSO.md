# Notes personnelles — Ticketly

## Ce qui a été ajouté au projet initial

Le zip fourni contenait déjà une bonne base : portail client, inbox agents, SLA, notes internes, réponses prédéfinies, graphiques, rapports CSV, API et administration. Les éléments listés comme manquants dans `README_PERSO.md` ont été complétés :

- événements et listeners : `TicketCreated`, `TicketReplied`, `TicketAssigned`, `TicketStatusChanged` ;
- notifications par email (`TicketNotice` avec canaux `database` + `mail`) ;
- fusion de tickets ouverts d’un même client, avec déplacement des réponses et pièces jointes ;
- factories `TicketFactory` et `TicketReplyFactory` ;
- réinitialisation du mot de passe avec notification email en français ;
- tests supplémentaires : fusion, réinitialisation, API, politiques d’accès.

## Points à revoir / entraînement

- Remplacer les notifications synchrones par des jobs en file (`ShouldQueue`) pour ne pas bloquer la requête.
- Ajouter des tests de politique par département (agent d’un département vs ticket d’un autre département).
- Ajouter des tests de validation des pièces jointes et du quota de 5 fichiers.
- Ajouter une recherche full-text et des filtres sauvegardés.
- Ajouter l’export PDF des rapports.
- Ajouter un vrai fournisseur d’email (Resend, Mailgun, SMTP) en production.
- Ajouter du Livewire ou des événements broadcast pour les mises à jour en direct.
- Ajouter une page de profil et la gestion des préférences de notification.
- Ajouter la suppression/archivage des tickets avec conservation légale.
- Ajouter une limitation de débit plus fine sur l’API et des jetons avec scopes.

## Concepts Laravel travaillés

Policies, scopes Eloquent, services métier, événements/listeners, notifications multi-canaux, commandes planifiées, FormRequests, API Resources, composants Blade anonymes, Alpine.js (stores, transitions, confirmations), Tailwind CSS, Chart.js, SQLite, tests PHPUnit avec `RefreshDatabase`.

## Rappel

- Base : `database/database.sqlite`.
- Réinitialisation : `php artisan migrate:fresh --seed`.
- Tests : `php artisan test`.
- Comptes : `admin@ticketly.dev`, `agent@ticketly.dev`, `client@ticketly.dev` / `Ticketly2026!`.
- En local, les emails sont dans `storage/logs/laravel.log`.
