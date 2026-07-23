<?php

use App\Http\Controllers\SiteController;
use App\Http\Controllers\MediaFileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{LoginController,PasswordController};
use App\Http\Controllers\Admin\{ActivityController,ContactMessageController,CrudController,DashboardController,MediaController,OfficialController,UserController,VillageContentController};
use App\Http\Controllers\Admin\{
    FamilyController, HouseholdController, PopulationGroupController, PopulationGroupMemberController,
    PopulationReportController, PopulationStatisticsController, ResidentController
};

Route::middleware('guest')->group(function(){
    Route::get('/admin/login',[LoginController::class,'create'])->name('login');
    Route::post('/admin/login',[LoginController::class,'store'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/admin/lupa-password',[PasswordController::class,'request'])->name('password.request');
    Route::post('/admin/lupa-password',[PasswordController::class,'email'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/admin/reset-password/{token}',[PasswordController::class,'reset'])->name('password.reset');
    Route::post('/admin/reset-password',[PasswordController::class,'update'])->name('password.update');
});

Route::get('/media-file/{media}/{variant?}', MediaFileController::class)
    ->where('variant', 'original|thumbnail')
    ->middleware('throttle:120,1')
    ->name('media.file');

Route::middleware(['auth','active'])->prefix('admin')->name('admin.')->group(function(){
    Route::post('/logout',[LoginController::class,'destroy'])->name('logout');
    Route::get('/',DashboardController::class)->name('dashboard');
    Route::get('/media',[MediaController::class,'index'])->name('media.index'); Route::post('/media',[MediaController::class,'store'])->name('media.store'); Route::delete('/media/{media}',[MediaController::class,'destroy'])->name('media.destroy');
    Route::post('/media/editor-upload',[MediaController::class,'editorUpload'])->name('media.editor-upload');
    Route::resource('messages',ContactMessageController::class)->only(['index','show','destroy']);
    Route::resource('users',UserController::class)->except(['show','destroy']);
    Route::get('/activities',ActivityController::class)->name('activities.index');
    Route::get('/info-desa/profile',[VillageContentController::class,'showProfile'])->name('village-content.profile');
    Route::get('/info-desa/profile/edit',[VillageContentController::class,'editProfile'])->name('village-content.profile-edit');
    Route::get('/info-desa/{page}',[VillageContentController::class,'edit'])->name('village-content.edit');
    Route::put('/info-desa/{page}',[VillageContentController::class,'update'])->name('village-content.update');
    Route::middleware('can:manage-content')->group(function () {
        Route::get('/officials/organization', [OfficialController::class, 'organization'])->name('officials.organization');
        Route::get('/officials/print', [OfficialController::class, 'print'])->name('officials.print');
        Route::get('/officials/export', [OfficialController::class, 'export'])->name('officials.export');
        Route::delete('/officials/bulk', [OfficialController::class, 'bulkDestroy'])->name('officials.bulk-destroy');
        Route::patch('/officials/{official}/status', [OfficialController::class, 'toggleStatus'])->name('officials.toggle-status');
        Route::patch('/officials/{official}/move/{direction}', [OfficialController::class, 'move'])->where('direction', 'up|down')->name('officials.move');
        Route::resource('officials', OfficialController::class)->except('show');
    });
    Route::middleware('can:manage-data')->prefix('kependudukan')->name('population.')->group(function () {
        Route::resource('penduduk', ResidentController::class)->parameters(['penduduk' => 'resident'])->names('residents');
        Route::resource('keluarga', FamilyController::class)->parameters(['keluarga' => 'family'])->except('show')->names('families');
        Route::resource('rumah-tangga', HouseholdController::class)->parameters(['rumah-tangga' => 'household'])->except('show')->names('households');
        Route::resource('kelompok', PopulationGroupController::class)->parameters(['kelompok' => 'group'])->names('groups');
        Route::get('kelompok/{group}/anggota/create', [PopulationGroupMemberController::class, 'create'])->name('groups.members.create');
        Route::post('kelompok/{group}/anggota', [PopulationGroupMemberController::class, 'store'])->name('groups.members.store');
        Route::get('kelompok/{group}/anggota/{membership}/edit', [PopulationGroupMemberController::class, 'edit'])->name('groups.members.edit');
        Route::put('kelompok/{group}/anggota/{membership}', [PopulationGroupMemberController::class, 'update'])->name('groups.members.update');
        Route::delete('kelompok/{group}/anggota/{membership}', [PopulationGroupMemberController::class, 'destroy'])->name('groups.members.destroy');
        Route::get('statistik', PopulationStatisticsController::class)->name('statistics');
        Route::get('laporan-penduduk', [PopulationReportController::class, 'index'])->name('report');
        Route::get('laporan-penduduk/export', [PopulationReportController::class, 'export'])->name('report.export');
    });
    Route::get('/news-trash',[CrudController::class,'trash'])->name('news.trash');
    Route::patch('/news-trash/{id}/restore',[CrudController::class,'restore'])->name('news.restore');
    Route::delete('/news-trash/{id}/force',[CrudController::class,'forceDelete'])->name('news.force-delete');
    Route::delete('/news-trash',[CrudController::class,'emptyTrash'])->name('news.empty-trash');
    Route::get('/{resource}',[CrudController::class,'index'])->name('resources.index');
    Route::get('/{resource}/create',[CrudController::class,'create'])->name('resources.create');
    Route::post('/{resource}',[CrudController::class,'store'])->name('resources.store');
    Route::get('/{resource}/{id}/edit',[CrudController::class,'edit'])->name('resources.edit');
    Route::put('/{resource}/{id}',[CrudController::class,'update'])->name('resources.update');
    Route::delete('/{resource}/{id}',[CrudController::class,'destroy'])->name('resources.destroy');
});

Route::controller(SiteController::class)->group(function () {
    // Menu utama
    Route::get('/', 'home')->name('beranda');
    Route::get('/profile-desa', 'profile')->name('profile-desa');
    Route::get('/data-desa-statistik', 'statistics')->name('data-desa-statistik');
    Route::get('/laporan-penduduk', 'populationReport')->name('laporan-penduduk');
    Route::get('/informasi-publik-desa', 'publicInformation')->name('informasi-publik-desa');
    Route::get('/peta-desa', 'map')->name('peta-desa');

    // Berita desa
    Route::get('/berita-desa', 'news')->name('berita-desa.index');
    Route::get('/berita-desa/kategori/{category}', 'category')->name('berita-desa.category');
    Route::get('/berita-desa/arsip/{year?}', 'archive')->where('year', '[0-9]{4}')->name('berita-desa.archive');
    Route::get('/berita-desa/{slug}', 'article')->name('berita-desa.show');
    Route::get('/berita/{slug}', 'article')->name('berita.legacy-show');
    Route::get('/pencarian-berita', 'search')->name('berita-desa.search');

    // Form kontak pendukung informasi publik
    Route::get('/kontak', 'contact')->name('kontak.index');
    Route::post('/kontak', 'sendContact')->name('kontak.store');
});

Route::redirect('/profil-desa', '/profile-desa', 301);
Route::redirect('/pemerintahan-desa', '/informasi-publik-desa', 301);
Route::redirect('/potensi-desa', '/data-desa-statistik', 301);
Route::redirect('/berita', '/berita-desa', 301);

Route::fallback([SiteController::class, 'notFound']);
