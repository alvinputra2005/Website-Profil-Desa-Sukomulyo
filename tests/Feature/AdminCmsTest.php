<?php

namespace Tests\Feature;

use App\Models\{News, NewsCategory, Redirect, Role, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCmsTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $code): User
    {
        $role=Role::create(['name'=>ucwords(str_replace('_',' ',$code)),'code'=>$code]);
        return User::factory()->create(['role_id'=>$role->id,'is_active'=>true]);
    }

    public function test_admin_requires_authentication_and_renders_for_super_admin(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->actingAs($this->user('super_admin'))->get('/admin')->assertOk()->assertSee('OpenSID');
    }

    public function test_roles_are_restricted_to_their_domain(): void
    {
        $content=$this->user('admin_konten');
        $this->actingAs($content)->get('/admin/news')->assertOk();
        $this->actingAs($content)->get('/admin/statistics')->assertForbidden();
        $this->actingAs($content)->get('/admin/users')->assertForbidden();
    }

    public function test_news_crud_sanitizes_html_and_published_news_is_public(): void
    {
        $admin=$this->user('super_admin');
        $category=NewsCategory::create(['name'=>'Kegiatan','slug'=>'kegiatan']);
        $response=$this->actingAs($admin)->post('/admin/news',[
            'category_id'=>$category->id,'title'=>'Berita Aman','slug'=>'berita-aman','excerpt'=>'Ringkasan',
            'content'=>'<p>Konten <strong>aman</strong></p><script>alert(1)</script><a href="javascript:alert(1)">tautan</a>',
            'status'=>'published','published_at'=>now()->format('Y-m-d H:i:s'),
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        $news=News::firstOrFail();
        $response->assertRedirect(route('admin.resources.edit',['news',$news]));
        $this->assertStringNotContainsString('<script',$news->content);
        $this->assertStringNotContainsString('javascript:',$news->content);
        $this->get('/berita/berita-aman')->assertOk()->assertSee('Berita Aman');
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user=$this->user('admin_konten'); $user->update(['is_active'=>false,'password'=>'secret-password']);
        $this->post('/admin/login',['email'=>$user->email,'password'=>'secret-password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_configured_redirect_is_applied_to_public_get_request(): void
    {
        Redirect::create(['old_path'=>'/alamat-lama','new_path'=>'/profil-desa','status_code'=>301]);
        $this->get('/alamat-lama')->assertRedirect('/profil-desa')->assertStatus(301);
    }

    public function test_all_admin_opensid_screens_render_for_super_admin(): void
    {
        $this->actingAs($this->user('super_admin'));
        foreach (array_keys(config('admin.resources')) as $resource) {
            $this->get(route('admin.resources.index',$resource))->assertOk()->assertSee('AdminLTE.min.css');
            $this->get(route('admin.resources.create',$resource))->assertOk();
        }
        foreach (['admin.media.index','admin.messages.index','admin.users.index','admin.activities.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }
}
