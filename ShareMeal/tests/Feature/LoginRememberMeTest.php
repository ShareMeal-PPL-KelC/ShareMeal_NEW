<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ShareMealState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LoginRememberMeTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_without_remember_me_does_not_set_remember_cookie(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'role' => 'consumer',
            'email' => 'consumer@sharemeal.com',
            'password' => Hash::make($password),
        ]);

        $response = $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => $password,
            'user_type' => 'consumer',
            'remember' => '0',
        ]);

        $response->assertRedirect(route('consumer.dashboard'));
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Session::get('sharemeal.current_user_id'));
        
        // Assert that the remember cookie is NOT present
        $response->assertCookieMissing(Auth::guard()->getRecallerName());
    }

    public function test_login_with_remember_me_sets_remember_cookie(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'role' => 'consumer',
            'email' => 'consumer@sharemeal.com',
            'password' => Hash::make($password),
        ]);

        $response = $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => $password,
            'user_type' => 'consumer',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('consumer.dashboard'));
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Session::get('sharemeal.current_user_id'));
        
        // Assert that the remember cookie is present
        $response->assertCookie(Auth::guard()->getRecallerName());
    }

    public function test_remember_me_session_restoration(): void
    {
        $user = User::factory()->create([
            'role' => 'consumer',
            'email' => 'consumer@sharemeal.com',
        ]);

        // Mock being logged in via remember me but the session is empty
        $this->actingAs($user);
        Session::forget('sharemeal.current_user_id');

        // Check if currentUser syncs it back
        $currentUserInfo = ShareMealState::currentUser();
        $this->assertEquals($user->id, $currentUserInfo['id']);
        $this->assertEquals($user->id, Session::get('sharemeal.current_user_id'));
    }
}
