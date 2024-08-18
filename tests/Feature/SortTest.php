<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;
use RefreshDatabase, WithFaker;



class LoginControllerTest extends TestCase
{

    public function test_login_with_valid_credentials()
    {
        $user = factory(User::class)->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertTrue(Auth::check());
    }

    public function test_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'invalid@example.com',
            'password' => 'invalidpassword',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertFalse(Auth::check());
        $response->assertSessionHasErrors('error');
    }

    public function test_redirect_to_google()
    {
        $response = $this->get('/login/google');

        $response->assertRedirect();
    }

    public function test_handle_provider_callback()
    {
        $provider = 'google';
        $socialUser = factory(Socialite::class)->make([
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $this->mock(Socialite::class, function ($mock) use ($provider, $socialUser) {
            $mock->shouldReceive('driver->stateless->user')->andReturn($socialUser);
        });

        $response = $this->get("/login/{$provider}/callback");

        $response->assertRedirect(route('home'));
        $this->assertTrue(Auth::check());
    }

    public function test_logout()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertFalse(Auth::check());
    }

    public function test_active_users()
    {
        $activeUsers = factory(User::class, 3)->create(['active' => true]);
        $inactiveUsers = factory(User::class, 2)->create(['active' => false]);

        $response = $this->get('/active-users');

        $response->assertOk();
        $response->assertJsonCount(3);
    }

    public function test_quick_sort()
    {
        $arr = [5, 2, 8, 1, 9, 3];
        $expectedResult = [1, 2, 3, 5, 8, 9];

        $loginController = new LoginController();
        $result = $loginController->quickSort($arr);

        $this->assertEquals($expectedResult, $result);
    }
}