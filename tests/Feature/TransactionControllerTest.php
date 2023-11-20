<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Seed roles and user
        $this->seedRolesAndUser();
    }

    private function seedRolesAndUser()
    {
        // Seed roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        // Seed regular user with 'user' role
        $user = User::factory()->create();
        $userRole = Role::where('name', 'user')->first();
        $user->roles()->attach($userRole);

        // Seed admin user with 'admin' role
        $admin = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        $admin->roles()->attach($adminRole);
    }

    /** @test */
    public function it_allows_admin_to_store_transaction()
    {
        // Log in as admin
        $admin = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();

        Auth::login($admin);

        // Make a request to store a transaction
        $response = $this->postJson('/api/transactions', [
            'amount' => 100,
            'payer' => 'John Doe',
            'due_on' => now()->addDays(7)->toDateString(),
            'vat' => 20,
            'is_vat_inclusive' => true,
        ]);

        // Assert that the response is successful
        $response->assertStatus(200);

        // Assert that the transaction was created
        $this->assertDatabaseCount('transactions', 1);
    }

    /** @test */
    public function it_denies_user_from_storing_transaction()
    {
        // Log in as a regular user
        $user = User::whereHas('roles', function ($query) {
            $query->where('name', 'user');
        })->first();

        Auth::login($user);

        // Make a request to store a transaction
        $response = $this->postJson('/api/transactions', [
            'amount' => 100,
            'payer' => 'John Doe',
            'due_on' => now()->addDays(7)->toDateString(),
            'vat' => 20,
            'is_vat_inclusive' => true,
        ]);

//        // Debugging information
//        dd($response->getContent(), $response->status());

        // Assert that the response is a 403 Forbidden
        $response->assertStatus(403);

        // Assert that the transaction was not created
        $this->assertDatabaseCount('transactions', 0);
    }

    /** @test */
    public function it_returns_all_transactions_for_admin()
    {
        // Log in as admin
        $admin = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();
        Auth::login($admin);

        // Create some transactions using the factory and associate them with a user
        Transaction::factory()->count(3)->create([
            'user_id' => User::factory()->create()->id,
        ]);

        // Make a request to get all transactions
        $response = $this->getJson('/api/transactions');

        // Assert that the response is successful
        $response->assertStatus(200);

        // Assert that all transactions are returned
        $response->assertJsonCount(3, 'transactions');
    }

//
}
