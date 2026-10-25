<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\ActivityLog;
use App\Models\CannedResponse;
use App\Models\Category;
use App\Models\Department;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketNotice;
use App\Services\SlaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(42);
        $this->call(SlaSeeder::class);

        Setting::set('company_name', 'Ticketly');
        Setting::set('accent_color', '#3B82F6');

        $colors = ['#3B82F6', '#8B5CF6', '#10B981', '#F59E0B', '#EC4899', '#06B6D4', '#F43F5E'];
        $makeUser = fn (string $name, string $email, Role $role, int $index) => User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'Ticketly2026!',
            'role' => $role,
            'avatar_color' => $colors[$index % 7],
            'phone' => $role === Role::Customer ? '+229 01 '.mt_rand(40, 99).' '.mt_rand(10, 99).' '.mt_rand(10, 99) : null,
            'is_active' => true,
            'last_login_at' => now()->subHours($index + 1),
        ]);

        $admin = $makeUser('Administrateur Ticketly', 'admin@ticketly.dev', Role::Admin, 0);
        $agents = collect([
            ['Kossi Amegan', 'agent@ticketly.dev'],
            ['Aïcha Bio', 'aicha@ticketly.dev'],
            ['Rodrigue Hounsa', 'rodrigue@ticketly.dev'],
            ['Sènami Dossou', 'senami@ticketly.dev'],
        ])->map(fn ($agent, $index) => $makeUser($agent[0], $agent[1], Role::Agent, $index + 1));

        $customers = collect([
            'Client Démo' => 'client@ticketly.dev',
            'Yves Hounkpè' => 'yves@client.bj',
            'Chantal Ahouansou' => 'chantal@client.bj',
            'Marcel Zinsou' => 'marcel@client.bj',
            'Rita Adjovi' => 'rita@client.bj',
            'Eric Sossou' => 'eric@client.bj',
            'Nadia Fassinou' => 'nadia@client.bj',
            'Boris Dansou' => 'boris@client.bj',
            'Félicité Gbaguidi' => 'felicite@client.bj',
            'Armand Kiki' => 'armand@client.bj',
            'Carine Adjaho' => 'carine@client.bj',
            'Marius Tokpa' => 'marius@client.bj',
            'Prisca Tchibozo' => 'prisca@client.bj',
            'Serge Agbo' => 'serge@client.bj',
            'Josiane Medji' => 'josiane@client.bj',
        ])->map(fn ($email, $name) => $makeUser($name, $email, Role::Customer, 3))->values();

        $departments = collect([
            ['Support technique', 'Diagnostic et résolution des incidents produit, intégrations et performances.'],
            ['Facturation', 'Abonnements, factures, moyens de paiement et remboursements.'],
            ['Commercial', 'Devis, offres entreprise et accompagnement des nouveaux clients.'],
            ['Sécurité', 'Accès, authentification, activité suspecte et conformité.'],
        ])->map(fn ($department) => Department::create(['name' => $department[0], 'description' => $department[1]]));

        $agents->each(fn ($agent, $index) => $agent->departments()->attach([
            $departments[$index % 4]->id,
            $departments[($index + 1) % 4]->id,
        ]));

        $categories = collect([
            ['Compte et accès', '#3B82F6'],
            ['Facturation', '#10B981'],
            ['Bug technique', '#F43F5E'],
            ['Demande de fonctionnalité', '#8B5CF6'],
            ['Sécurité', '#F59E0B'],
            ['Autre', '#64748B'],
        ])->map(fn ($category) => Category::create(['name' => $category[0], 'color' => $category[1]]));

        $tags = collect([
            ['VIP', '#F59E0B'],
            ['Bug', '#EF4444'],
            ['Remboursement', '#10B981'],
            ['Urgent client', '#F43F5E'],
            ['Documentation', '#3B82F6'],
            ['Intégration', '#8B5CF6'],
            ['Mobile', '#06B6D4'],
            ['Relance', '#64748B'],
        ])->map(fn ($tag) => Tag::create(['name' => $tag[0], 'color' => $tag[1]]));

        foreach ([
            ['Accusé de réception', '/accuse', "Bonjour,\n\nMerci pour votre message. Nous avons bien reçu votre demande et nous la traitons en priorité."],
            ['Demande d’informations', '/infos', "Bonjour,\n\nPouvez-vous nous préciser les étapes pour reproduire le problème, ainsi qu’une capture d’écran si possible ?"],
            ['Réinitialisation de mot de passe', '/mdp', "Bonjour,\n\nUtilisez le lien « Mot de passe oublié » sur la page de connexion. Vous recevrez un email de réinitialisation sous quelques minutes."],
            ['Problème résolu', '/resolu', "Bonjour,\n\nLe problème est désormais corrigé. N’hésitez pas à nous dire si tout fonctionne de votre côté."],
            ['Facture envoyée', '/facture', "Bonjour,\n\nVotre facture a été envoyée par email. Elle est également disponible dans votre espace client."],
            ['Escalade technique', '/escalade', "Bonjour,\n\nVotre demande a été transmise à notre équipe technique, qui reviendra vers vous sous 24 heures."],
            ['Remerciement', '/merci', "Bonjour,\n\nMerci pour votre retour. Nous restons disponibles si vous avez besoin de quoi que ce soit."],
            ['Demande de fermeture', '/fermeture', "Bonjour,\n\nNous allons clôturer ce ticket. Si le problème revient, vous pouvez le rouvrir pendant 14 jours depuis votre espace client."],
            ['Suivi de remboursement', '/remboursement', "Bonjour,\n\nVotre demande de remboursement est en cours de traitement. Le délai bancaire habituel est de 5 à 7 jours ouvrés."],
            ['Bug confirmé', '/bug', "Bonjour,\n\nNous avons reproduit le problème et notre équipe technique prépare une correction. Nous vous tiendrons informé dès sa mise en production."],
            ['Vérification de sécurité', '/securite', "Bonjour,\n\nPar mesure de sécurité, nous vous recommandons de changer votre mot de passe et d’activer la double authentification."],
            ['Relance client', '/relance', "Bonjour,\n\nSans retour de votre part, nous allons clôturer ce ticket. Vous pouvez répondre à tout moment si vous avez encore besoin d’aide."],
        ] as $canned) {
            CannedResponse::create(['title' => $canned[0], 'shortcut' => $canned[1], 'body' => $canned[2]]);
        }

        $subjects = [
            ['Impossible de me connecter à mon compte', 'Je reçois une erreur à chaque tentative de connexion depuis ce matin, même après avoir changé mon mot de passe.', 0],
            ['Facture en double ce mois-ci', 'J’ai été débité deux fois pour mon abonnement. Pouvez-vous vérifier et me rembourser le surplus ?', 1],
            ['L’application plante au démarrage', 'Depuis la dernière mise à jour, l’application se ferme immédiatement sur mon téléphone Android.', 2],
            ['Ajouter un export PDF des rapports', 'Il serait très utile de pouvoir exporter les rapports mensuels au format PDF directement depuis le tableau de bord.', 3],
            ['Activité suspecte sur mon compte', 'J’ai reçu des notifications de connexion depuis un pays que je n’ai jamais visité. Mon compte est-il compromis ?', 4],
            ['Question sur mon forfait', 'Quelle est la différence exacte entre les offres Pro et Entreprise ? Je souhaite changer de formule.', 5],
            ['Erreur 500 lors de l’envoi d’un formulaire', 'Le formulaire de contact renvoie une erreur 500 depuis hier soir. Plusieurs de mes clients sont impactés.', 2],
            ['Changer l’adresse email du compte', 'Je souhaite remplacer mon adresse email professionnelle par une nouvelle, comment procéder ?', 0],
            ['Remboursement non reçu', 'Mon remboursement a été accepté il y a deux semaines mais je n’ai toujours rien reçu sur mon compte.', 1],
            ['Lenteurs importantes sur la plateforme', 'Les pages mettent plus de 10 secondes à charger depuis lundi, ce qui bloque toute mon équipe.', 2],
            ['Authentification à deux facteurs', 'Je n’arrive plus à recevoir le code de vérification par SMS. Pouvez-vous le désactiver temporairement ?', 4],
            ['Demande de devis pour 50 licences', 'Notre entreprise souhaite équiper 50 collaborateurs. Pouvez-vous nous envoyer un devis détaillé ?', 5],
        ];

        $statuses = array_merge(
            array_fill(0, 20, 'open'),
            array_fill(0, 15, 'in_progress'),
            array_fill(0, 15, 'pending'),
            array_fill(0, 20, 'resolved'),
            array_fill(0, 10, 'closed'),
        );
        shuffle($statuses);

        $priorities = array_merge(array_fill(0, 16, 'low'), array_fill(0, 32, 'medium'), array_fill(0, 20, 'high'), array_fill(0, 12, 'urgent'));
        shuffle($priorities);

        $sla = app(SlaService::class);
        $agentReplies = [
            'Bonjour, nous avons bien pris en compte votre demande et analysons la situation.',
            'Merci pour ces précisions. Nous avons identifié la cause et appliquons une correction.',
            'Pouvez-vous essayer à nouveau et nous confirmer si le problème persiste ?',
            'La correction a été déployée. Nous restons disponibles si besoin.',
        ];
        $customerReplies = [
            'Merci, j’attends votre retour.',
            'J’ai essayé de nouveau, le problème est toujours là.',
            'C’est bon, tout fonctionne maintenant. Merci beaucoup !',
            'Voici plus de détails : cela se produit surtout le matin.',
        ];

        $tickets = collect();
        foreach (range(0, 79) as $index) {
            $subject = $subjects[$index % count($subjects)];
            $status = $statuses[$index];
            $priority = $priorities[$index];

            if (in_array($status, ['resolved', 'closed'], true)) {
                $closedAt = now()->subDays(mt_rand(4, 38))->subMinutes(mt_rand(0, 600));
                $resolvedAt = $status === 'closed' ? $closedAt->copy()->subDays(3) : $closedAt;
                $createdAt = $resolvedAt->copy()->subDays(mt_rand(1, 20))->subMinutes(mt_rand(0, 600));
            } else {
                $closedAt = null;
                $resolvedAt = null;
                $createdAt = now()->subDays(mt_rand(0, 20))->subHours(mt_rand(0, 20))->subMinutes(mt_rand(0, 59));
            }

            $agent = $status === 'open'
                ? (mt_rand(1, 100) <= 60 ? $agents->random() : null)
                : $agents->random();

            $ticket = Ticket::create([
                'subject' => $subject[0],
                'description' => $subject[1],
                'priority' => $priority,
                'status' => $status,
                'category_id' => $categories[$subject[2]]->id,
                'department_id' => $departments[mt_rand(0, 3)]->id,
                'customer_id' => $customers->random()->id,
                'assignee_id' => $agent?->id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $policy = $sla->policy($ticket->priority);
            $ticket->reference = 'TCK-'.str_pad((string) $ticket->id, 5, '0', STR_PAD_LEFT);
            $ticket->due_at = $createdAt->copy()->addMinutes($policy->resolution_minutes);
            $ticket->resolved_at = $resolvedAt;
            $ticket->closed_at = $closedAt;
            $ticket->save();

            $ticket->tags()->sync($tags->random(mt_rand(0, 2))->pluck('id'));

            ActivityLog::create([
                'ticket_id' => $ticket->id,
                'user_id' => $ticket->customer_id,
                'action' => 'created',
                'created_at' => $createdAt,
            ]);
            if ($agent) {
                ActivityLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $admin->id,
                    'action' => 'assignee_id_changed',
                    'meta' => ['from' => null, 'to' => $agent->id],
                    'created_at' => $createdAt->copy()->addMinutes(5),
                ]);
            }

            $replyEnd = $resolvedAt ?? now();
            $cursor = $createdAt->copy();
            $authors = [];
            if ($status === 'open') {
                $authors = $agent
                    ? [[$ticket->customer, false], [$agent, false], [$ticket->customer, false]]
                    : [[$ticket->customer, false], [$ticket->customer, false], [$ticket->customer, false]];
            } elseif ($agent) {
                $authors = [[$ticket->customer, false], [$agent, false], [$agent, true]];
            }

            $firstResponseAt = null;
            foreach ($authors as $position => [$author, $internal]) {
                $cursor = $cursor->copy()->addMinutes(mt_rand(20, 420));
                if ($cursor->gt($replyEnd)) {
                    $cursor = $replyEnd->copy()->subMinutes((3 - $position) * 7);
                }
                if ($cursor->lt($createdAt)) {
                    $cursor = $createdAt->copy()->addMinutes(10 + $position);
                }
                $ticket->replies()->create([
                    'user_id' => $author->id,
                    'body' => $internal
                        ? 'Note interne : vérifier les journaux du serveur et confirmer la correction avant de répondre au client.'
                        : ($author->isStaff() ? $agentReplies[$position % 4] : $customerReplies[$position % 4]),
                    'is_internal' => $internal,
                    'created_at' => $cursor,
                    'updated_at' => $cursor,
                ]);
                ActivityLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $author->id,
                    'action' => $internal ? 'internal_note' : 'replied',
                    'created_at' => $cursor,
                ]);
                if ($author->isStaff() && ! $internal) {
                    $firstResponseAt ??= $cursor->copy();
                }
            }

            if ($firstResponseAt) {
                $ticket->first_response_at = $firstResponseAt;
            }
            if (in_array($status, ['in_progress', 'pending', 'resolved', 'closed'], true) && $resolvedAt) {
                ActivityLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $agent?->id,
                    'action' => 'status_changed',
                    'meta' => ['from' => 'open', 'to' => $status],
                    'created_at' => $resolvedAt,
                ]);
            }
            $ticket->sla_breached = ($resolvedAt ?? now())->gt($ticket->due_at)
                || (! $ticket->first_response_at && now()->gt($createdAt->copy()->addMinutes($policy->first_response_minutes)));

            if (in_array($status, ['resolved', 'closed'], true) && mt_rand(1, 100) <= 70) {
                $ticket->satisfaction = mt_rand(3, 5);
                $ticket->satisfaction_comment = $ticket->satisfaction >= 5 ? 'Équipe réactive et solution claire.' : null;
                ActivityLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $ticket->customer_id,
                    'action' => 'rated',
                    'meta' => ['score' => $ticket->satisfaction],
                    'created_at' => ($resolvedAt ?? now())->copy()->addHours(2),
                ]);
            }
            if ($ticket->sla_breached) {
                ActivityLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => null,
                    'action' => 'sla_breached',
                    'created_at' => $ticket->due_at,
                ]);
            }

            $ticket->updated_at = $replyEnd;
            $ticket->save();
            $tickets->push($ticket);
        }

        // Dix notes internes supplémentaires pour atteindre exactement 250 réponses.
        $openTickets = $tickets->filter(fn (Ticket $ticket) => ! $ticket->isFinal())->values();
        foreach (range(1, 10) as $index) {
            $ticket = $openTickets->random();
            $agent = $ticket->assignee ?? $agents->random();
            $date = now()->subMinutes(mt_rand(5, 600));
            $ticket->replies()->create([
                'user_id' => $agent->id,
                'body' => 'Note interne : suivi qualité n°'.$index.' — vérifier la satisfaction client avant clôture.',
                'is_internal' => true,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
            ActivityLog::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'action' => 'internal_note',
                'created_at' => $date,
            ]);
        }

        foreach ($tickets->take(8) as $ticket) {
            $target = $ticket->assignee ?? $admin;
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => TicketNotice::class,
                'notifiable_type' => User::class,
                'notifiable_id' => $target->id,
                'data' => json_encode([
                    'title' => 'Nouveau ticket '.$ticket->reference,
                    'body' => $ticket->subject,
                    'url' => route('tickets.show', $ticket),
                ]),
                'read_at' => null,
                'created_at' => now()->subMinutes(mt_rand(5, 300)),
                'updated_at' => now()->subMinutes(mt_rand(5, 300)),
            ]);
        }
    }
}
