<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_the_secure_admin_form_without_public_registration(): void
    {
        $response = $this->get('/admin/login');

        $response
            ->assertOk()
            ->assertSee('Masuk ke Akun')
            ->assertSee('Selamat Datang')
            ->assertSee('name="_token"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="remember"', false)
            ->assertSee('autocomplete="current-password"', false)
            ->assertSee('content="noindex, nofollow"', false)
            ->assertSee(route('login.store'), false)
            ->assertDontSee('Daftar sekarang');
    }

    public function test_active_user_can_login_and_the_session_is_regenerated(): void
    {
        $role = Role::create(['name' => 'Admin Konten', 'code' => 'admin_konten']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'password' => 'KataSandiAman123',
            'is_active' => true,
            'last_login_at' => null,
        ]);

        $this->withSession(['login_marker' => 'before-login']);
        $sessionIdBeforeLogin = session()->getId();

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'KataSandiAman123',
            'remember' => true,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionIdBeforeLogin, session()->getId());
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_login_is_limited_by_normalized_email_and_ip(): void
    {
        $credentials = [
            'email' => 'ADMIN@SUKOMULYO.DESA.ID',
            'password' => 'kata-sandi-salah',
        ];

        foreach (range(1, 5) as $attempt) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.15'])
                ->post('/admin/login', $credentials)
                ->assertRedirect();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.15'])
            ->post('/admin/login', [
                'email' => ' admin@sukomulyo.desa.id ',
                'password' => 'kata-sandi-salah',
            ])
            ->assertTooManyRequests();

        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.15'])
            ->post('/admin/login', [
                'email' => 'pengelola@sukomulyo.desa.id',
                'password' => 'kata-sandi-salah',
            ])
            ->assertRedirect();
    }

    public function test_password_reset_requires_twelve_characters_with_letters_and_numbers(): void
    {
        $this->post('/admin/reset-password', [
            'token' => 'token-pengujian',
            'email' => 'admin@sukomulyo.desa.id',
            'password' => 'abcdefghijk',
            'password_confirmation' => 'abcdefghijk',
        ])->assertSessionHasErrors('password');

        $this->post('/admin/reset-password', [
            'token' => 'token-pengujian',
            'email' => 'admin@sukomulyo.desa.id',
            'password' => '123456789012',
            'password_confirmation' => '123456789012',
        ])->assertSessionHasErrors('password');
    }
}
