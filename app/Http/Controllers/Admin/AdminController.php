<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\CannedResponse;
use App\Models\Category;
use App\Models\Department;
use App\Models\Setting;
use App\Models\SlaPolicy;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    private const CATALOG = ['categories' => Category::class, 'departments' => Department::class, 'tags' => Tag::class];

    public function users(Request $request)
    {
        $query = User::with('departments')->orderBy('role')->orderBy('name');
        if ($term = $request->query('q')) {
            $query->where(fn ($where) => $where
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%"));
        }

        return view('admin.users', [
            'users' => $query->paginate(15)->withQueryString(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function saveUser(Request $request)
    {
        $id = $request->input('id');
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:60'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'role' => ['required', Rule::enum(Role::class)],
            'password' => [$id ? 'nullable' : 'required', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
            'department_ids' => ['nullable', 'array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
        ]);

        $user = $id ? User::findOrFail($id) : new User(['avatar_color' => '#3B82F6']);
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active'),
        ]);
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        if ($user->is($request->user()) && ! $user->is_active) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }
        $user->save();
        $user->departments()->sync($data['role'] === Role::Agent->value ? ($data['department_ids'] ?? []) : []);

        return back()->with('success', $id ? 'Utilisateur mis à jour.' : 'Utilisateur créé.');
    }

    public function catalog()
    {
        return view('admin.catalog', [
            'categories' => Category::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function categories()
    {
        return $this->catalogPage('categories', 'Catégories', Category::orderBy('name')->get(), true);
    }

    public function departments()
    {
        return $this->catalogPage('departments', 'Départements', Department::withCount('agents')->orderBy('name')->get(), false);
    }

    public function tags()
    {
        return $this->catalogPage('tags', 'Étiquettes', Tag::orderBy('name')->get(), true);
    }

    private function catalogPage(string $type, string $title, $items, bool $color)
    {
        return view('admin.catalog-type', compact('type', 'title', 'items', 'color'));
    }

    public function saveCatalog(Request $request, string $type)
    {
        $model = self::CATALOG[$type] ?? abort(404);
        $data = $request->validate([
            'id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:60'],
            'color' => ['nullable', 'string', 'max:9'],
            'description' => ['nullable', 'string', 'max:160'],
        ]);
        $payload = array_filter([
            'name' => $data['name'],
            'color' => $data['color'] ?? null,
            'description' => $data['description'] ?? null,
        ], fn ($value) => $value !== null);
        if ($type === 'departments') {
            unset($payload['color']);
        } else {
            unset($payload['description']);
        }
        $request->filled('id')
            ? $model::findOrFail($data['id'])->update($payload)
            : $model::create($payload);

        return back()->with('success', 'Enregistré.');
    }

    public function deleteCatalog(string $type, int $id)
    {
        (self::CATALOG[$type] ?? abort(404))::findOrFail($id)->delete();

        return back()->with('success', 'Élément supprimé.');
    }

    public function sla()
    {
        return view('admin.sla', [
            'policies' => SlaPolicy::orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")->get(),
        ]);
    }

    public function saveSla(Request $request)
    {
        $data = $request->validate([
            'policies' => ['required', 'array'],
            'policies.*.first_response_minutes' => ['required', 'integer', 'min:1'],
            'policies.*.resolution_minutes' => ['required', 'integer', 'min:1'],
        ]);
        foreach ($data['policies'] as $priority => $values) {
            SlaPolicy::where('priority', $priority)->update($values);
        }

        return back()->with('success', 'Politiques SLA enregistrées. Elles s’appliquent aux nouveaux tickets.');
    }

    public function cannedResponses()
    {
        return view('admin.canned', ['canned' => CannedResponse::orderBy('title')->get()]);
    }

    public function saveCanned(Request $request)
    {
        $id = $request->input('id');
        $data = $request->validate([
            'id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:80'],
            'body' => ['required', 'string', 'max:3000'],
            'shortcut' => ['nullable', 'string', 'max:20', 'regex:/^\/[a-z0-9-]+$/i', Rule::unique('canned_responses', 'shortcut')->ignore($id)],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
        $payload = [
            'title' => $data['title'],
            'body' => $data['body'],
            'shortcut' => $data['shortcut'] ?? null,
            'user_id' => $data['user_id'] ?? null,
        ];
        $request->filled('id')
            ? CannedResponse::findOrFail($id)->update($payload)
            : CannedResponse::create($payload);

        return back()->with('success', 'Réponse prédéfinie enregistrée.');
    }

    public function deleteCanned(CannedResponse $canned)
    {
        $canned->delete();

        return back()->with('success', 'Réponse supprimée.');
    }

    public function settings()
    {
        return view('admin.settings', [
            'companyName' => Setting::get('company_name', config('app.name', 'Ticketly')),
            'accentColor' => Setting::get('accent_color', '#3B82F6'),
        ]);
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:60'],
            'accent_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
        Setting::set('company_name', $data['company_name']);
        Setting::set('accent_color', $data['accent_color']);

        return back()->with('success', 'Paramètres de l’entreprise enregistrés.');
    }
}
