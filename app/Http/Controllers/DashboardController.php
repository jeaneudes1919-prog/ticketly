<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();
        $base = fn () => Ticket::visibleTo($u);
        $answered = $base()->whereNotNull('first_response_at')->get(['created_at', 'first_response_at']);
        $kpis = [
            'open' => $base()->open()->count(),
            'unassigned' => $base()->open()->whereNull('assignee_id')->count(),
            'late' => $base()->open()->where('sla_breached', true)->count(),
            'resolved_today' => $base()->where('resolved_at', '>=', today())->count(),
            'avg_first_response' => (int) round($answered->avg(fn ($t) => $t->created_at->diffInMinutes($t->first_response_at)) ?? 0),
            'satisfaction' => round((float) $base()->whereNotNull('satisfaction')->avg('satisfaction'), 1),
        ];

        $days = collect(range(29, 0))->map(fn ($i) => today()->subDays($i));
        $created = $base()->where('created_at', '>=', $days->first())->get(['created_at'])->groupBy(fn ($t) => $t->created_at->format('Y-m-d'));
        $resolved = $base()->where('resolved_at', '>=', $days->first())->get(['resolved_at'])->groupBy(fn ($t) => $t->resolved_at->format('Y-m-d'));
        $charts = [
            'labels' => $days->map(fn ($d) => $d->translatedFormat('d M'))->all(),
            'created' => $days->map(fn ($d) => $created->get($d->format('Y-m-d'), collect())->count())->all(),
            'resolved' => $days->map(fn ($d) => $resolved->get($d->format('Y-m-d'), collect())->count())->all(),
            'status' => collect(TicketStatus::cases())->map(fn ($s) => ['label' => $s->label(), 'n' => $base()->where('status', $s->value)->count()])->all(),
            'priority' => collect(TicketPriority::cases())->map(fn ($p) => ['label' => $p->label(), 'n' => $base()->open()->where('priority', $p->value)->count()])->all(),
            'category' => Category::all()->map(fn ($c) => ['label' => $c->name, 'n' => $base()->where('category_id', $c->id)->count()])->filter(fn ($x) => $x['n'] > 0)->values()->all(),
        ];
        $urgent = $base()->open()->with(['customer', 'assignee'])
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")->orderBy('due_at')->take(8)->get();
        $leaders = User::whereIn('role', [Role::Agent->value, Role::Admin->value])->get()->map(fn ($a) => [
            'name' => $a->name, 'color' => $a->avatar_color,
            'resolved' => Ticket::where('assignee_id', $a->id)->where('resolved_at', '>=', now()->subDays(30))->count(),
            'open' => Ticket::where('assignee_id', $a->id)->open()->count(),
        ])->sortByDesc('resolved')->take(5)->values();

        return view('dashboard', compact('kpis', 'charts', 'urgent', 'leaders'));
    }

    public function reports(Request $request)
    {
        [$from, $to] = $this->range($request);
        $tickets = Ticket::visibleTo($request->user())->with('assignee')->whereBetween('created_at', [$from, $to])->get();
        $done = $tickets->filter(fn ($t) => $t->resolved_at);
        $stats = [
            'volume' => $tickets->count(),
            'resolved' => $done->count(),
            'avg_resolution_h' => round($done->avg(fn ($t) => $t->created_at->diffInMinutes($t->resolved_at)) / 60, 1),
            'sla_ok' => $tickets->count() ? round(100 * $tickets->where('sla_breached', false)->count() / $tickets->count()) : 100,
        ];
        $byAgent = $tickets->groupBy('assignee_id')->map(fn ($g) => [
            'name' => $g->first()->assignee?->name ?? 'Non assigné', 'total' => $g->count(),
            'resolved' => $g->filter(fn ($t) => $t->resolved_at)->count(), 'late' => $g->where('sla_breached', true)->count(),
        ])->sortByDesc('total')->values();

        return view('reports', ['stats' => $stats, 'byAgent' => $byAgent, 'from' => $from->toDateString(), 'to' => $to->toDateString()]);
    }

    public function export(Request $request)
    {
        [$from, $to] = $this->range($request);
        $rows = Ticket::visibleTo($request->user())->with(['customer', 'assignee', 'category'])->whereBetween('created_at', [$from, $to])->orderBy('id')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Référence', 'Sujet', 'Statut', 'Priorité', 'Catégorie', 'Client', 'Agent', 'Créé le', 'Résolu le', 'SLA dépassé', 'Satisfaction'], ';');
            foreach ($rows as $t) {
                fputcsv($out, [$t->reference, $t->subject, $t->status->label(), $t->priority->label(), $t->category?->name, $t->customer->name, $t->assignee?->name,
                    $t->created_at->format('d/m/Y H:i'), $t->resolved_at?->format('d/m/Y H:i'), $t->sla_breached ? 'Oui' : 'Non', $t->satisfaction], ';');
            }
            fclose($out);
        }, 'tickets-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function range(Request $request): array
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $to = $request->filled('to') ? Carbon::parse($request->query('to'))->endOfDay() : now()->endOfDay();
        $from = $request->filled('from') ? Carbon::parse($request->query('from'))->startOfDay() : now()->subDays(30)->startOfDay();

        return [$from, $to];
    }
}
