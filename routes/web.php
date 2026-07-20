<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::controller(SiteController::class)->group(function () {
    // Menu utama
    Route::get('/', 'home')->name('beranda');
    Route::get('/profile-desa', 'profile')->name('profile-desa');
    Route::get('/data-desa-statistik', 'statistics')->name('data-desa-statistik');
    Route::get('/informasi-publik-desa', 'publicInformation')->name('informasi-publik-desa');
    Route::get('/peta-desa', 'map')->name('peta-desa');

    // Berita desa
    Route::get('/berita-desa', 'news')->name('berita-desa.index');
    Route::get('/berita-desa/kategori/{category}', 'category')->name('berita-desa.category');
    Route::get('/berita-desa/arsip/{year?}', 'archive')->where('year', '[0-9]{4}')->name('berita-desa.archive');
    Route::get('/berita-desa/{slug}', 'article')->name('berita-desa.show');
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
