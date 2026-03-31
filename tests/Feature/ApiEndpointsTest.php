<?php

namespace Tests\Feature;

use App\Jobs\ExpireReservations;
use App\Models\Matches;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_endpoint_creates_user(): void
    {
        $payload = [
            'name' => 'Test Fan',
            'email' => 'fan@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'User registered successfully.',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'fan@example.com',
            'role' => 'fan',
        ]);
    }

    public function test_login_endpoint_returns_token_for_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'fan@example.com',
            'password' => 'password123',
            'role' => 'fan',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'fan@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['token']);
    }

    public function test_matches_index_returns_data_array(): void
    {
        Matches::factory()->count(2)->create();

        $response = $this->getJson('/api/matches');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'home_team', 'away_team', 'stadium', 'match_date', 'capacity', 'availability'],
                ],
            ]);
    }

    public function test_admin_match_creation_is_forbidden_for_non_admin_user(): void
    {
        $fan = User::factory()->create([
            'role' => 'fan',
        ]);
        Sanctum::actingAs($fan);

        $response = $this->postJson('/api/admin/matches', [
            'home_team' => ['en' => 'Argentina', 'ka' => 'არგენტინა'],
            'away_team' => ['en' => 'France', 'ka' => 'საფრანგეთი'],
            'stadium' => ['en' => 'Lusail', 'ka' => 'ლუსაილი'],
            'match_date' => now()->addDay()->toDateTimeString(),
            'capacity' => 50000,
        ]);

        $response
            ->assertForbidden()
            ->assertJson([
                'message' => 'Unauthorized action',
            ]);
    }

    public function test_authenticated_fan_can_create_reservation(): void
    {
        Queue::fake();

        $fan = User::factory()->create([
            'role' => 'fan',
        ]);
        Sanctum::actingAs($fan);

        $match = Matches::factory()->create();
        $category = TicketCategory::where('match_id', $match->id)->firstOrFail();
        $startingAvailable = $category->available_count;

        $response = $this->postJson('/api/reservations', [
            'match_id' => $match->id,
            'category_id' => $category->id,
            'quantity' => 1,
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure(['reservation_id', 'message']);

        $this->assertDatabaseHas('reservations', [
            'user_id' => $fan->id,
            'ticket_category_id' => $category->id,
            'quantity' => 1,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('ticket_categories', [
            'id' => $category->id,
            'available_count' => $startingAvailable - 1,
        ]);

        Queue::assertPushed(ExpireReservations::class);
    }
}
