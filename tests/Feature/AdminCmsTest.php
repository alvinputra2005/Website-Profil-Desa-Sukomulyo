<?php

namespace Tests\Feature;

use App\Models\{Media, News, NewsCategory, Redirect, Role, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_blank_news_slugs_are_unique_and_existing_slug_is_preserved(): void
    {
        $admin=$this->user('super_admin');
        $category=NewsCategory::create(['name'=>'Desa','slug'=>'desa']);
        $payload=['category_id'=>$category->id,'title'=>'Musyawarah Desa','slug'=>'','content'=>'<p>Isi berita desa.</p>','status'=>'draft'];
        $this->actingAs($admin)->post('/admin/news',$payload)->assertSessionHasNoErrors();
        $this->post('/admin/news',$payload)->assertSessionHasNoErrors();
        $this->assertSame(['musyawarah-desa','musyawarah-desa-2'],News::orderBy('id')->pluck('slug')->all());
        $first=News::first();
        $this->put('/admin/news/'.$first->id,array_merge($payload,['title'=>'Judul Baru']))->assertSessionHasNoErrors();
        $this->assertSame('musyawarah-desa',$first->fresh()->slug);
    }

    public function test_draft_preview_works_without_published_news_and_editor_old_input_is_sanitized(): void
    {
        $admin=$this->user('super_admin');
        $category=NewsCategory::create(['name'=>'Kegiatan','slug'=>'kegiatan']);
        $draft=News::create(['category_id'=>$category->id,'title'=>'Draf Tunggal','slug'=>'draf-tunggal','content'=>'<p>Draf</p>','status'=>'draft','author_id'=>$admin->id]);
        $this->actingAs($admin)->get('/berita-desa/'.$draft->slug)->assertOk()->assertSee('Draf Tunggal');
        $this->from('/admin/news/create')->post('/admin/news',['title'=>'Pendek','content'=>'<img src=x onerror=alert(1)><script>alert(2)</script>'])->assertRedirect('/admin/news/create');
        $this->withSession(['_old_input'=>['content'=>'<img src=x onerror=alert(1)><script>alert(2)</script>']])->get('/admin/news/create')->assertOk()->assertDontSee('<img src=x onerror=',false)->assertDontSee('<script>alert(2)',false);
    }

    public function test_images_are_optimized_inline_media_is_protected_and_news_can_be_restored(): void
    {
        Storage::fake('public');
        $admin=$this->user('super_admin');
        $this->actingAs($admin)->post('/admin/media',['file'=>UploadedFile::fake()->image('foto.jpg',2400,1800),'alt_text'=>'Kegiatan'])->assertSessionHasNoErrors();
        $media=Media::firstOrFail();
        $this->assertSame('webp',$media->extension);
        $this->assertLessThanOrEqual(1920,max($media->width,$media->height));
        Storage::disk('public')->assertExists($media->storage_path);
        Storage::disk('public')->assertExists($media->thumbnail_path);
        $category=NewsCategory::create(['name'=>'Berita','slug'=>'berita']);
        $news=News::create(['category_id'=>$category->id,'title'=>'Dengan Gambar','slug'=>'dengan-gambar','content'=>'<p><img src="/storage/'.$media->storage_path.'"></p>','status'=>'draft','author_id'=>$admin->id]);
        $this->delete('/admin/media/'.$media->id)->assertSessionHasErrors('media');
        $this->delete('/admin/news/'.$news->id)->assertRedirect('/admin/news');
        $this->get('/admin/news-trash')->assertOk()->assertSee('Dengan Gambar');
        $this->patch('/admin/news-trash/'.$news->id.'/restore')->assertSessionHasNoErrors();
        $this->assertNull($news->fresh()->deleted_at);
    }
}
