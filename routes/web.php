<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\BulkDeleteStatisticDatasetController;
use App\Http\Controllers\Admin\ApbdesController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CrudController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FamilyController;
use App\Http\Controllers\Admin\LetterApplicationController as AdminLetterApplicationController;
use App\Http\Controllers\Admin\LetterApplicationStatusController;
use App\Http\Controllers\Admin\LetterApplicationWhatsAppController;
use App\Http\Controllers\Admin\LetterServiceController as AdminLetterServiceController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\Officials\BulkDeleteOfficialController;
use App\Http\Controllers\Admin\Officials\MoveOfficialController;
use App\Http\Controllers\Admin\Officials\OfficialController;
use App\Http\Controllers\Admin\Officials\OfficialExportController;
use App\Http\Controllers\Admin\Officials\OfficialOrganizationController;
use App\Http\Controllers\Admin\Officials\ToggleOfficialStatusController;
use App\Http\Controllers\Admin\Population\Residents\ResidentController;
use App\Http\Controllers\Admin\Population\Residents\ResidentImportController;
use App\Http\Controllers\Admin\PopulationGroupController;
use App\Http\Controllers\Admin\PopulationGroupMemberController;
use App\Http\Controllers\Admin\PopulationReportController;
use App\Http\Controllers\Admin\PopulationStatisticsController;
use App\Http\Controllers\Admin\PopulationStatisticController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\StatisticImportController;
use App\Http\Controllers\Admin\StatisticDatasetController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\Village\VillageIdentityController;
use App\Http\Controllers\Admin\Village\VillageSectionController;
use App\Http\Controllers\Admin\Village\VisionMissionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\MediaFileController;
use App\Http\Controllers\Web\AnnouncementAttachmentController;
use App\Http\Controllers\Web\AnnouncementController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\ErrorController;
use App\Http\Controllers\Web\GalleryController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LetterApplicationController;
use App\Http\Controllers\Web\LetterServiceController;
use App\Http\Controllers\Web\LetterTrackingController;
use App\Http\Controllers\Web\LetterWhatsAppConfirmationController;
use App\Http\Controllers\Web\NewsController;
use App\Http\Controllers\Web\PublicationController;
use App\Http\Controllers\Web\SeoController;
use App\Http\Controllers\Web\VillageMapController;
use App\Http\Controllers\Web\VillageProfileController;
use App\Http\Controllers\Web\VillageStatisticController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [LoginController::class, 'create'])->name('login');
    Route::post('/admin/login', [LoginController::class, 'store'])->middleware('throttle:admin-login')->name('login.store');
    Route::get('/admin/lupa-password', [PasswordController::class, 'request'])->name('password.request');
    Route::post('/admin/lupa-password', [PasswordController::class, 'email'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/admin/reset-password/{token}', [PasswordController::class, 'reset'])->name('password.reset');
    Route::post('/admin/reset-password', [PasswordController::class, 'update'])->name('password.update');
});

Route::get('/media-file/{media}/{variant?}', MediaFileController::class)
    ->where('variant', 'original|medium|thumbnail')
    ->middleware('throttle:120,1')
    ->name('media.file');

Route::middleware(['auth', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
    Route::post('/media/editor-upload', [MediaController::class, 'editorUpload'])->name('media.editor-upload');
    Route::resource('messages', ContactMessageController::class)->only(['index', 'show', 'destroy']);
    Route::resource('users', UserController::class)->except(['show', 'destroy']);
    Route::get('/activities', ActivityController::class)->name('activities.index');
    Route::get('/info-desa/profile', [VillageIdentityController::class, 'show'])->name('village-content.profile');
    Route::get('/info-desa/profile/edit', [VillageIdentityController::class, 'edit'])->name('village-content.profile-edit');
    Route::put('/info-desa/profile', [VillageIdentityController::class, 'update'])->defaults('page', 'profile')->name('village-content.profile-update');
    Route::get('/info-desa/vision-mission', [VisionMissionController::class, 'edit'])->defaults('page', 'vision-mission')->name('village-content.vision-mission.edit');
    Route::put('/info-desa/vision-mission', [VisionMissionController::class, 'update'])->defaults('page', 'vision-mission')->name('village-content.vision-mission.update');
    Route::get('/info-desa/{page}', [VillageSectionController::class, 'edit'])->where('page', 'history|potential')->name('village-content.edit');
    Route::put('/info-desa/{page}', [VillageSectionController::class, 'update'])->where('page', 'history|potential')->name('village-content.update');
    Route::prefix('wilayah')->name('regions.')->group(function () {
        Route::get('/provinces', [RegionController::class, 'provinces'])->name('provinces');
        Route::get('/regencies/{province}', [RegionController::class, 'regencies'])->where('province', '[0-9]{2}')->name('regencies');
        Route::get('/districts/{regency}', [RegionController::class, 'districts'])->where('regency', '[0-9]{2}\.[0-9]{2}')->name('districts');
        Route::get('/villages/{district}', [RegionController::class, 'villages'])->where('district', '[0-9]{2}\.[0-9]{2}\.[0-9]{2}')->name('villages');
    });
    Route::middleware('can:manage-content')->group(function () {
        Route::get('/officials/organization', OfficialOrganizationController::class)->name('officials.organization');
        Route::get('/officials/print', [OfficialExportController::class, 'print'])->name('officials.print');
        Route::get('/officials/export', [OfficialExportController::class, 'csv'])->name('officials.export');
        Route::delete('/officials/bulk', BulkDeleteOfficialController::class)->name('officials.bulk-destroy');
        Route::patch('/officials/{official}/status', ToggleOfficialStatusController::class)->name('officials.toggle-status');
        Route::patch('/officials/{official}/move/{direction}', MoveOfficialController::class)->where('direction', 'up|down')->name('officials.move');
        Route::resource('officials', OfficialController::class)->except('show');
    });
    Route::middleware('can:manage-data')->prefix('statistik')->name('statistics.')->group(function () {
        Route::get('/import', [StatisticImportController::class, 'create'])->name('import.create');
        Route::post('/import', [StatisticImportController::class, 'store'])->name('import.store');
        Route::get('/import/{import:public_id}/preview', [StatisticImportController::class, 'preview'])->name('import.preview');
        Route::post('/import/{import:public_id}/process', [StatisticImportController::class, 'processImport'])->name('import.process');
    });
    Route::middleware('can:manage-data')->prefix('statistics')->name('statistics.')->group(function () {
        Route::get('/', [StatisticDatasetController::class, 'index'])->name('index');
        Route::get('/population', [PopulationStatisticController::class, 'show'])->name('population.show');
    });
    Route::middleware('can:manage-data')->prefix('statistics')->name('statistics.categories.')->group(function () {
        Route::get('/{category:slug}', [StatisticDatasetController::class, 'show'])->name('show');
        Route::get('/{category:slug}/create', [StatisticDatasetController::class, 'create'])->name('create');
        Route::post('/{category:slug}', [StatisticDatasetController::class, 'store'])->name('store');
        Route::get('/{category:slug}/{dataset:slug}/export/csv', [StatisticDatasetController::class, 'exportCsv'])->name('export.csv');
        Route::get('/{category:slug}/{dataset:slug}/edit', [StatisticDatasetController::class, 'edit'])->name('edit');
        Route::put('/{category:slug}/{dataset:slug}', [StatisticDatasetController::class, 'update'])->name('update');
    });
    Route::middleware('can:manage-data')->prefix('kependudukan')->name('population.')->group(function () {
        Route::get('penduduk/template-import', [ResidentImportController::class, 'template'])->name('residents.import-template');
        Route::post('penduduk/import', [ResidentImportController::class, 'store'])->name('residents.import');
        Route::resource('penduduk', ResidentController::class)->parameters(['penduduk' => 'resident'])->names('residents');
        Route::resource('keluarga', FamilyController::class)->parameters(['keluarga' => 'family'])->except('show')->names('families');
        Route::resource('kelompok', PopulationGroupController::class)->parameters(['kelompok' => 'group'])->names('groups');
        Route::get('kelompok/{group}/anggota/create', [PopulationGroupMemberController::class, 'create'])->name('groups.members.create');
        Route::post('kelompok/{group}/anggota', [PopulationGroupMemberController::class, 'store'])->name('groups.members.store');
        Route::get('kelompok/{group}/anggota/{membership}/edit', [PopulationGroupMemberController::class, 'edit'])->name('groups.members.edit');
        Route::put('kelompok/{group}/anggota/{membership}', [PopulationGroupMemberController::class, 'update'])->name('groups.members.update');
        Route::delete('kelompok/{group}/anggota/{membership}', [PopulationGroupMemberController::class, 'destroy'])->name('groups.members.destroy');
        Route::get('statistik', [PopulationStatisticController::class, 'show'])->name('statistics');
        Route::get('laporan-penduduk', [PopulationReportController::class, 'index'])->name('report');
        Route::get('laporan-penduduk/export', [PopulationReportController::class, 'export'])->name('report.export');
    });
    Route::middleware('can:manage-data')->group(function () {
        Route::patch('/apbdes/{apbdes}/publikasi', [ApbdesController::class, 'togglePublication'])->name('apbdes.toggle-publication');
        Route::resource('apbdes', ApbdesController::class)
            ->parameters(['apbdes' => 'apbdes'])
            ->except('show');
    });
    Route::middleware('can:manage-letter-applications')->prefix('permohonan-surat')->name('letter-applications.')->group(function () {
        Route::get('/', [AdminLetterApplicationController::class, 'index'])->name('index');
        Route::get('/{application:public_id}', [AdminLetterApplicationController::class, 'show'])->name('show');
        Route::get('/{application:public_id}/dokumen/{document}', [AdminLetterApplicationController::class, 'document'])->name('document');
        Route::get('/{application:public_id}/dokumen/{document}/preview-url', [AdminLetterApplicationController::class, 'previewUrl'])->name('document.preview-url');
        Route::get('/{application:public_id}/dokumen/{document}/preview-content', [AdminLetterApplicationController::class, 'previewContent'])->name('document.preview-content');
        Route::patch('/{application:public_id}/dokumen/{document}', [AdminLetterApplicationController::class, 'reviewDocument'])->name('document.review');
        Route::patch('/{application:public_id}/status', [LetterApplicationStatusController::class, 'update'])->name('status.update');
        Route::patch('/{application:public_id}/hubungkan-penduduk', [AdminLetterApplicationController::class, 'linkResident'])->name('resident.link');
        Route::post('/{application:public_id}/whatsapp', LetterApplicationWhatsAppController::class)->name('whatsapp.open');
    });
    Route::middleware('can:manage-letter-services')->prefix('layanan-surat')->name('letter-services.')->group(function () {
        Route::get('/', [AdminLetterServiceController::class, 'index'])->name('index');
        Route::get('/create', [AdminLetterServiceController::class, 'create'])->name('create');
        Route::post('/', [AdminLetterServiceController::class, 'store'])->name('store');
        Route::get('/{letterService:slug}/edit', [AdminLetterServiceController::class, 'edit'])->name('edit');
        Route::put('/{letterService:slug}', [AdminLetterServiceController::class, 'update'])->name('update');
        Route::delete('/{letterService:slug}', [AdminLetterServiceController::class, 'destroy'])->name('destroy');
    });
    Route::get('/news-trash', [CrudController::class, 'trash'])->name('news.trash');
    Route::patch('/news-trash/{id}/restore', [CrudController::class, 'restore'])->name('news.restore');
    Route::delete('/news-trash/{id}/force', [CrudController::class, 'forceDelete'])->name('news.force-delete');
    Route::delete('/news-trash', [CrudController::class, 'emptyTrash'])->name('news.empty-trash');
    Route::patch('/news/{id}/archive', [CrudController::class, 'archive'])->name('news.archive');
    Route::get('/gallery-trash', [CrudController::class, 'galleryTrash'])->name('galleries.trash');
    Route::patch('/gallery-trash/{id}/restore', [CrudController::class, 'restoreGallery'])->name('galleries.restore');
    Route::delete('/gallery-trash/{id}/force', [CrudController::class, 'forceDeleteGallery'])->name('galleries.force-delete');
    Route::delete('/gallery-trash', [CrudController::class, 'emptyGalleryTrash'])->name('galleries.empty-trash');
    Route::patch('/galleries/{id}/archive', [CrudController::class, 'archiveGallery'])->name('galleries.archive');
    Route::delete('/statistics/bulk', BulkDeleteStatisticDatasetController::class)->name('statistics.bulk-destroy');
    Route::get('/{resource}', [CrudController::class, 'index'])->name('resources.index');
    Route::get('/{resource}/create', [CrudController::class, 'create'])->name('resources.create');
    Route::post('/{resource}', [CrudController::class, 'store'])->name('resources.store');
    Route::get('/{resource}/{id}/edit', [CrudController::class, 'edit'])->name('resources.edit');
    Route::put('/{resource}/{id}', [CrudController::class, 'update'])->name('resources.update');
    Route::delete('/{resource}/{id}', [CrudController::class, 'destroy'])->name('resources.destroy');
});

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

Route::prefix('layanan-surat')->name('letter-services.')->group(function () {
    Route::get('/', [LetterServiceController::class, 'index'])->name('index');
    Route::get('/lacak', [LetterTrackingController::class, 'create'])->name('track.form');
    Route::post('/lacak', [LetterTrackingController::class, 'store'])->middleware('throttle:letter-tracking')->name('track.store');
    Route::get('/lacak/{application:public_id}', [LetterTrackingController::class, 'showSession'])->name('track.session');
    Route::get('/t/{token}', [LetterTrackingController::class, 'showToken'])->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:60,1')->name('track.token');
    Route::post('/t/{token}/konfirmasi-whatsapp', LetterWhatsAppConfirmationController::class)->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:10,1')->name('whatsapp.confirm');
    Route::get('/t/{token}/perbaiki', [LetterApplicationController::class, 'edit'])->where('token', '[A-Za-z0-9]{64}')->name('application.edit');
    Route::get('/t/{token}/dokumen', [LetterApplicationController::class, 'documents'])->where('token', '[A-Za-z0-9]{64}')->name('application.documents');
    Route::post('/t/{token}/dokumen', [LetterApplicationController::class, 'uploadDocuments'])->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:letter-application-update')->name('application.documents.upload');
    Route::post('/t/{token}/dokumen/presign', [LetterApplicationController::class, 'presign'])->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:letter-document-upload')->name('application.documents.presign');
    Route::post('/t/{token}/dokumen/complete', [LetterApplicationController::class, 'completeDocument'])->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:letter-document-upload')->name('application.documents.complete');
    Route::get('/t/{token}/dokumen/{document}/preview', [LetterApplicationController::class, 'previewDocument'])->where('token', '[A-Za-z0-9]{64}')->name('application.documents.preview');
    Route::put('/t/{token}/perbaiki', [LetterApplicationController::class, 'update'])->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:letter-application-update')->name('application.update');
    Route::patch('/t/{token}/batalkan', [LetterApplicationController::class, 'cancel'])->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:5,1')->name('application.cancel');
    Route::get('/{letterService:slug}/ajukan', [LetterApplicationController::class, 'create'])->name('application.create');
    Route::post('/{letterService:slug}/ajukan', [LetterApplicationController::class, 'store'])->middleware('throttle:letter-application')->name('application.store');
    Route::get('/{letterService:slug}', [LetterServiceController::class, 'show'])->name('show');
});

Route::get('/', HomeController::class)->name('beranda');
Route::get('/profile-desa', [VillageProfileController::class, 'index'])->name('profile-desa');
Route::post('/profile-desa/komentar', [VillageProfileController::class, 'storeComment'])->middleware('throttle:5,1')->name('profile-desa.comment');
Route::get('/profile-desa/komentar-identitas-desa', [VillageProfileController::class, 'comments'])->name('profile-desa.comments');
Route::post('/profile-desa/komentar-identitas-desa/{comment}/suka', [VillageProfileController::class, 'likeComment'])->middleware('throttle:30,1')->name('profile-desa.comments.like');
Route::get('/profile-desa/{section}/komentar', [VillageProfileController::class, 'sectionComments'])
    ->where('section', 'sejarah|visi-misi|struktur-pemerintahan|wilayah-desa|potensi-desa')
    ->name('profile-desa.section-comments');
Route::get('/profile-desa/{section}', [VillageProfileController::class, 'show'])->where('section', 'sejarah|visi-misi')->name('profile-desa.detail');
Route::get('/pemerintahan-desa', [VillageProfileController::class, 'government'])->name('pemerintahan-desa');
Route::get('/potensi-desa', [VillageProfileController::class, 'potentials'])->name('potensi-desa');
Route::get('/data-desa-statistik', [VillageStatisticController::class, 'index'])->name('data-desa-statistik');
Route::get('/data-statistik/penduduk', [VillageStatisticController::class, 'population'])->name('data-statistik.population');
Route::get('/data-statistik/{category}/{dataset}', [VillageStatisticController::class, 'importedDataset'])->name('data-statistik.imported.show');
Route::get('/data-statistik/{section}', [VillageStatisticController::class, 'show'])->name('data-statistik.detail');
Route::get('/transparansi-apbdes', [VillageStatisticController::class, 'budgetHistory'])->name('transparansi-apbdes');
Route::get('/transparansi-apbdes/{year}', [VillageStatisticController::class, 'budgetDetail'])->whereNumber('year')->name('transparansi-apbdes.show');
Route::get('/informasi-publik-desa', [PublicationController::class, 'index'])->name('informasi-publik-desa');
Route::prefix('informasi-desa/pengumuman')->name('announcements.')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index'])->name('index');
    Route::get('/{publication:slug}/lampiran/{attachment}/lihat', [AnnouncementAttachmentController::class, 'preview'])
        ->middleware('throttle:120,1')
        ->name('attachments.preview');
    Route::get('/{publication:slug}/lampiran/{attachment}/unduh', [AnnouncementAttachmentController::class, 'download'])
        ->middleware('throttle:60,1')
        ->name('attachments.download');
    Route::get('/{publication:slug}', [AnnouncementController::class, 'show'])->name('show');
});
Route::get('/informasi-desa/{section}', [PublicationController::class, 'show'])->where('section', 'layanan-administrasi|agenda|bantuan-sosial|informasi-publik')->name('informasi-desa.detail');
Route::get('/peta-desa', [VillageMapController::class, 'index'])->name('peta-desa');
Route::get('/peta-desa/geojson', [VillageMapController::class, 'geoJson'])->middleware('throttle:60,1')->name('peta-desa.geojson');
Route::get('/galeri-desa', [GalleryController::class, 'index'])->name('galeri-desa');

Route::get('/berita-desa', [NewsController::class, 'index'])->name('berita-desa.index');
Route::get('/berita-desa/kategori/{category}', [NewsController::class, 'category'])->name('berita-desa.category');
Route::get('/berita-desa/arsip/{year?}', [NewsController::class, 'archive'])->where('year', '[0-9]{4}')->name('berita-desa.archive');
Route::get('/berita-desa/{slug}', [NewsController::class, 'show'])->name('berita-desa.show');
Route::get('/berita/{slug}', [NewsController::class, 'show'])->name('berita.legacy-show');
Route::get('/pencarian-berita', [NewsController::class, 'search'])->name('berita-desa.search');

Route::get('/kontak', [ContactController::class, 'create'])->name('kontak.index');
Route::post('/kontak', [ContactController::class, 'store'])->name('kontak.store');

Route::redirect('/profil-desa', '/profile-desa', 301);
Route::redirect('/berita', '/berita-desa', 301);

Route::fallback(ErrorController::class);
