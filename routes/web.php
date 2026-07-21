<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{LoginController,PasswordController};
use App\Http\Controllers\Admin\{ActivityController,ContactMessageController,CrudController,DashboardController,MediaController,UserController};

Route::middleware('guest')->group(function(){
    Route::get('/admin/login',[LoginController::class,'create'])->name('login');
    Route::post('/admin/login',[LoginController::class,'store'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/admin/lupa-password',[PasswordController::class,'request'])->name('password.request');
    Route::post('/admin/lupa-password',[PasswordController::class,'email'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/admin/reset-password/{token}',[PasswordController::class,'reset'])->name('password.reset');
    Route::post('/admin/reset-password',[PasswordController::class,'update'])->name('password.update');
});
Route::middleware(['auth','active'])->prefix('admin')->name('admin.')->group(function(){
    Route::post('/logout',[LoginController::class,'destroy'])->name('logout');
    Route::get('/',DashboardController::class)->name('dashboard');
    Route::get('/media',[MediaController::class,'index'])->name('media.index'); Route::post('/media',[MediaController::class,'store'])->name('media.store'); Route::delete('/media/{media}',[MediaController::class,'destroy'])->name('media.destroy');
    Route::resource('messages',ContactMessageController::class)->only(['index','show','destroy']);
    Route::resource('users',UserController::class)->except(['show','destroy']);
    Route::get('/activities',ActivityController::class)->name('activities.index');
    Route::get('/{resource}',[CrudController::class,'index'])->name('resources.index');
    Route::get('/{resource}/create',[CrudController::class,'create'])->name('resources.create');
    Route::post('/{resource}',[CrudController::class,'store'])->name('resources.store');
    Route::get('/{resource}/{id}/edit',[CrudController::class,'edit'])->name('resources.edit');
    Route::put('/{resource}/{id}',[CrudController::class,'update'])->name('resources.update');
    Route::delete('/{resource}/{id}',[CrudController::class,'destroy'])->name('resources.destroy');
});

Route::controller(SiteController::class)->group(function () {
    
    //Landing Page
    Route::get('/', 'home')->name('home');
    Route::get('/profil-desa', 'profile')->name('profile');
    Route::get('/pemerintahan-desa', 'government')->name('government');
    Route::get('/potensi-desa', 'potentials')->name('potentials');

    //Berita
    Route::get('/berita', 'news')->name('news.index');
    Route::get('/berita/kategori/{category}', 'category')->name('news.category');
    Route::get('/berita/arsip/{year?}', 'archive')->where('year', '[0-9]{4}')->name('news.archive');
    Route::get('/berita/{slug}', 'article')->name('news.show');


    //Halaman Lainnya
    Route::get('/pencarian', 'search')->name('search');
    Route::get('/galeri', 'gallery')->name('gallery');
    Route::get('/kontak', 'contact')->name('contact.index');
    Route::post('/kontak', 'sendContact')->name('contact.store');
});

Route::fallback([SiteController::class, 'notFound']);
