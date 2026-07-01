<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

describe('Authentication', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'password' => bcrypt('correct-password'),
            'is_active' => true,
        ]);
    });

    it('displays login page for guests', function () {
        $response = $this->get(route('login'));

        expect($response->status())->toBe(200);
        $response->assertViewIs('auth.login');
    });

    it('redirects authenticated users from login page', function () {
        $response = $this->actingAs($this->user)->get(route('login'));

        $response->assertRedirect(route('dashboard'));
    });

    it('authenticates with valid credentials', function () {
        $response = $this->post(route('login.post'), [
            'email' => $this->user->email,
            'password' => 'correct-password',
            'remember' => true,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->user);
    });

    it('fails authentication with invalid password', function () {
        $response = $this->post(route('login.post'), [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    });

    it('fails authentication with non-existent email', function () {
        $response = $this->post(route('login.post'), [
            'email' => 'nonexistent@example.com',
            'password' => 'any-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    });

    it('fails authentication for inactive users', function () {
        $inactiveUser = User::factory()->inactive()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => $inactiveUser->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    });

    it('rate limits after 5 failed attempts', function () {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.post'), [
                'email' => $this->user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post(route('login.post'), [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        expect($response->session()->get('errors')->first('email'))->toContain('Too many');
    });

    it('clears rate limit on successful login', function () {
        $this->post(route('login.post'), [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $this->post(route('login.post'), [
            'email' => $this->user->email,
            'password' => 'correct-password',
            'remember' => true,
        ]);

        expect(RateLimiter::remaining('login|' . $this->user->email, 5))->toBe(5);
    });

    it('logs out authenticated user', function () {
        $this->actingAs($this->user);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    });

    it('invalidates session on logout', function () {
        $this->actingAs($this->user);
        $oldSession = session()->getId();

        $this->post(route('logout'));

        expect(session()->getId())->not->toBe($oldSession);
    });

    it('requires email field', function () {
        $response = $this->post(route('login.post'), [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    });

    it('requires valid email format', function () {
        $response = $this->post(route('login.post'), [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    });

    it('requires password field', function () {
        $response = $this->post(route('login.post'), [
            'email' => $this->user->email,
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    });

    it('handles XSS in login form', function () {
        $response = $this->post(route('login.post'), [
            'email' => '<script>alert("xss")</script>@test.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    });

    it('handles SQL injection in email field', function () {
        $response = $this->post(route('login.post'), [
            'email' => "' OR '1'='1",
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    });

    it('prevents CSRF attacks', function () {
        $response = $this->withHeaders([
            'X-CSRF-TOKEN' => 'invalid-token',
        ])->post(route('login.post'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(419);
    });

    it('redirects to intended URL after login', function () {
        $response = $this->get(route('customers.index'));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('login.post'), [
            'email' => $this->user->email,
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('customers.index'));
    });
});
