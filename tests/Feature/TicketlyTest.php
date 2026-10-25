<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Services\TicketService;
use Database\Seeders\SlaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class TicketlyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SlaSeeder::class);
    }

    private function user(Role $role): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    private function ticket(User $customer, array $extra = []): Ticket
    {
        $t = app(TicketService::class)->create($customer, ['subject' => 'Problème de connexion', 'description' => 'Je ne peux plus me connecter.', 'priority' => 'medium']);

        return $extra ? tap($t)->update($extra) : $t;
    }

    public function test_customer_cannot_see_another_customers_ticket(): void
    {
        $a = $this->user(Role::Customer);
        $b = $this->user(Role::Customer);
        $t = $this->ticket($a);
        $this->actingAs($b)->get(route('portal.show', $t))->assertForbidden();
        $this->actingAs($a)->get(route('portal.show', $t))->assertOk();
    }

    public function test_customer_does_not_see_internal_notes(): void
    {
        $c = $this->user(Role::Customer);
        $agent = $this->user(Role::Agent);
        $t = $this->ticket($c);
        app(TicketService::class)->reply($t, $agent, 'Note secrète de l\'équipe', true);
        $this->actingAs($c)->get(route('portal.show', $t))->assertOk()->assertDontSee('Note secrète de l\'équipe');
        $this->actingAs($agent)->get(route('tickets.show', $t))->assertOk()->assertSee('Note secrète de l\'équipe');
    }

    public function test_ticket_creation_sets_reference_due_date_and_notifies_staff(): void
    {
        $admin = $this->user(Role::Admin);
        $c = $this->user(Role::Customer);
        $t = $this->ticket($c);
        $this->assertMatchesRegularExpression('/^TCK-\d{5}$/', $t->reference);
        $this->assertEquals($t->created_at->copy()->addMinutes(2880)->timestamp, $t->due_at->timestamp);
        $this->assertCount(1, $admin->notifications);
    }

    public function test_agent_reply_sets_first_response_and_pending(): void
    {
        $t = $this->ticket($this->user(Role::Customer));
        $agent = $this->user(Role::Agent);
        app(TicketService::class)->reply($t, $agent, 'Bonjour, nous regardons cela.');
        $t->refresh();
        $this->assertNotNull($t->first_response_at);
        $this->assertSame(TicketStatus::Pending, $t->status);
        $this->assertSame($agent->id, $t->assignee_id);
    }

    public function test_customer_reply_reopens_a_pending_ticket(): void
    {
        $c = $this->user(Role::Customer);
        $t = $this->ticket($c, ['status' => 'pending']);
        app(TicketService::class)->reply($t, $c, 'Voici plus de détails.');
        $this->assertSame(TicketStatus::Open, $t->refresh()->status);
    }

    public function test_check_sla_command_marks_late_tickets(): void
    {
        $t = $this->ticket($this->user(Role::Customer), ['priority' => 'urgent']);
        $t->update(['due_at' => now()->subHour(), 'created_at' => now()->subHours(5)]);
        $this->artisan('tickets:check-sla')->assertSuccessful();
        $this->assertTrue($t->refresh()->sla_breached);
    }

    public function test_auto_close_closes_old_resolved_tickets(): void
    {
        $t = $this->ticket($this->user(Role::Customer), ['status' => 'resolved', 'resolved_at' => now()->subDays(4)]);
        $this->artisan('tickets:auto-close')->assertSuccessful();
        $this->assertSame(TicketStatus::Closed, $t->refresh()->status);
    }

    public function test_customer_cannot_change_priority(): void
    {
        $c = $this->user(Role::Customer);
        $t = $this->ticket($c);
        $this->actingAs($c)->patch(route('tickets.update', $t), ['priority' => 'urgent'])->assertForbidden();
    }

    public function test_api_token_flow_and_ticket_listing(): void
    {
        $c = User::factory()->create(['role' => Role::Customer, 'is_active' => true, 'password' => 'secret-pass-123']);
        $this->ticket($c);
        $this->getJson('/api/v1/tickets')->assertUnauthorized();
        $token = $this->postJson('/api/v1/auth/token', ['email' => $c->email, 'password' => 'secret-pass-123', 'device_name' => 'tests'])->assertOk()->json('token');
        $this->withToken($token)->getJson('/api/v1/tickets')->assertOk()->assertJsonCount(1, 'data');
        $this->withToken($token)->postJson('/api/v1/tickets', ['subject' => 'Nouveau ticket API', 'description' => 'Créé depuis l\'API de test.', 'priority' => 'low'])->assertCreated();
    }

    public function test_staff_can_merge_two_tickets_of_the_same_customer(): void
    {
        $customer = $this->user(Role::Customer);
        $agent = $this->user(Role::Agent);
        $source = $this->ticket($customer);
        $target = $this->ticket($customer);
        $reply = app(TicketService::class)->reply($source, $agent, 'Réponse à déplacer.');

        $this->actingAs($agent)
            ->post(route('tickets.merge', $source), ['target_id' => $target->id])
            ->assertRedirect(route('tickets.show', $target));

        $this->assertSame(TicketStatus::Closed, $source->refresh()->status);
        $this->assertSame($target->id, $reply->refresh()->ticket_id);
        $this->assertDatabaseHas('activity_logs', ['ticket_id' => $target->id, 'action' => 'merged_from']);
        $this->assertDatabaseHas('activity_logs', ['ticket_id' => $source->id, 'action' => 'merged_into']);
    }

    public function test_password_reset_link_can_be_requested_and_used(): void
    {
        Notification::fake();
        $user = $this->user(Role::Customer);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPasswordNotification::class);

        $token = Password::createToken($user);
        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('nouveau-mot-de-passe', $user->refresh()->password));
    }
}
