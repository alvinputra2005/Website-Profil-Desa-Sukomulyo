<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

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
