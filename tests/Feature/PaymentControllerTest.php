<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
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

    public function test_it_can_create_payment_and_update_transaction_status()
    {
        // Authenticate the admin user
        Sanctum::actingAs(User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first());

        // Create a transaction
        $transaction = Transaction::factory()->create([
            'due_on' => now()->addDays(7),
        ]);

        // Make a request to create a payment
        $response = $this->postJson('/api/payments', [
            'transaction_id' => $transaction->id,
            'amount' => 100,
            'paid_on' => now()->format('Y-m-d'),
            'details' => 'Sample payment details',
        ]);

        // Assert a successful response
        $response->assertStatus(201);

        // Assert the payment was created in the database
        $this->assertDatabaseHas('payments', [
            'transaction_id' => $transaction->id,
            'amount' => 100,
            'paid_on' => now()->format('Y-m-d'),
            'details' => 'Sample payment details',
        ]);

        // Assert the transaction status was updated
        $transaction->refresh();
        $this->assertEquals('Paid', $transaction->status);


    }
}
