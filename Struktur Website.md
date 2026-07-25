# **ARSITEKTUR SISTEM WEBSITE DESA SUKOMULYO**

## **Laravel 12 Modular Monolith**

---

# **2\. Keputusan arsitektur utama**

Website Desa Sukomulyo  
│  
├── Bentuk Sistem        \-\> Modular Monolith  
├── Framework            \-\> Laravel 12  
├── Bahasa               \-\> PHP 8.3 atau PHP 8.4  
├── Rendering            \-\> Server-Side Rendering  
├── Template Engine      \-\> Blade  
├── UI Framework         \-\> Bootstrap 5  
├── Asset Bundler        \-\> Vite  
├── JavaScript           \-\> Vanilla JavaScript  
├── Database             \-\> MySQL/MariaDB  
├── ORM                  \-\> Eloquent ORM  
├── Authentication       \-\> Session Authentication  
├── Authorization        \-\> Middleware \+ Gates \+ Policies  
├── Grafik               \-\> Chart.js  
├── Peta                 \-\> Leaflet.js \+ GeoJSON  
├── File Storage         \-\> Laravel Filesystem  
├── Cache                \-\> Database Cache  
├── Session              \-\> Database Session  
├── Queue MVP            \-\> Sync  
├── Testing              \-\> Pest atau PHPUnit  
├── Web Server           \-\> Apache/LiteSpeed  
├── Deployment           \-\> GitHub \+ Shared Hosting  
└── Production Runtime   \-\> PHP tanpa Node.js server

Node.js hanya diperlukan pada komputer developer atau pipeline CI untuk:

npm install  
npm run dev  
npm run build

Hasil produksinya disimpan dalam:

public/build/

Server production tidak perlu menjalankan Node.js terus-menerus.

---

# **3\. Gambaran arsitektur keseluruhan**

┌──────────────────────────────────────────────────────────────┐  
│                         PENGGUNA                             │  
├───────────────────────────────┬──────────────────────────────┤  
│ Pengunjung Website            │ Admin / Super Admin          │  
└───────────────┬───────────────┴──────────────┬───────────────┘  
                │ HTTPS                         │ HTTPS  
                ▼                               ▼  
┌──────────────────────────────────────────────────────────────┐  
│                  APACHE / LITESPEED SERVER                   │  
│                                                              │  
│  public/index.php                                            │  
│          │                                                   │  
│          ▼                                                   │  
│  Laravel Router                                              │  
│          │                                                   │  
│          ▼                                                   │  
│  Middleware                                                  │  
│  ├── Web Session                                             │  
│  ├── CSRF                                                    │  
│  ├── Authentication                                          │  
│  ├── Authorization                                           │  
│  ├── Rate Limit                                              │  
│  └── Security Headers                                        │  
│          │                                                   │  
│          ▼                                                   │  
│  Controllers                                                 │  
│  ├── Public Controllers                                      │  
│  └── Admin Controllers                                       │  
│          │                                                   │  
│          ▼                                                   │  
│  Form Requests                                               │  
│          │                                                   │  
│          ▼                                                   │  
│  Actions / Services                                          │  
│          │                                                   │  
│      ┌───┼──────────────┬────────────┐                       │  
│      ▼   ▼              ▼            ▼                       │  
│  Eloquent Models   Laravel Cache  Filesystem   Event/Observer│  
│      │                  │            │            │           │  
│      ▼                  ▼            ▼            ▼           │  
│    MySQL             Database      Storage     Audit Log      │  
└──────────────────────────────────────────────────────────────┘

---

# **4\. Alur request aplikasi**

## **4.1 Request halaman publik**

Browser  
  \-\> Laravel Router  
  \-\> Web Middleware  
  \-\> Public Controller  
  \-\> Service atau Eloquent Query  
  \-\> Cache Check  
  \-\> Database  
  \-\> Blade View  
  \-\> HTML  
  \-\> Browser

Contoh:

GET /berita/pembangunan-jalan-desa  
  \-\> NewsController@show  
  \-\> Route Model Binding berdasarkan slug  
  \-\> NewsService@getPublishedNews  
  \-\> Cache::remember()  
  \-\> News Model  
  \-\> MySQL  
  \-\> resources/views/web/news/show.blade.php

## **4.2 Request admin**

Admin  
  \-\> /admin/\*  
  \-\> auth Middleware  
  \-\> role Middleware  
  \-\> Policy Authorization  
  \-\> Admin Controller  
  \-\> Form Request Validation  
  \-\> Action Class  
  \-\> Eloquent Model  
  \-\> Database Transaction  
  \-\> Activity Log  
  \-\> Cache Invalidated  
  \-\> Redirect \+ Flash Message

Contoh:

POST /admin/berita  
  \-\> auth  
  \-\> role:super\_admin,admin\_konten  
  \-\> StoreNewsRequest  
  \-\> CreateNewsAction  
  \-\> Upload featured image  
  \-\> Insert news  
  \-\> Insert activity log  
  \-\> Clear news cache  
  \-\> Redirect

---

# **5\. Pembagian layer aplikasi**

## **5.1 Presentation Layer**

Berisi:

Routes  
Controllers  
Middleware  
Form Requests  
Blade Views  
View Components  
Responses

Tanggung jawab:

* Menerima request.  
* Mengambil input.  
* Menjalankan authentication dan authorization.  
* Memanggil Action atau Service.  
* Mengembalikan Blade View, redirect, file, atau JSON.  
* Tidak menyimpan query dan aturan bisnis kompleks.

Controller harus tipis:

final class NewsController extends Controller  
{  
    public function store(  
        StoreNewsRequest $request,  
        CreateNewsAction $action  
    ): RedirectResponse {  
        $news \= $action-\>execute(  
            data: $request-\>validated(),  
            user: $request-\>user()  
        );

        return redirect()  
            \-\>route('admin.news.edit', $news)  
            \-\>with('success', 'Berita berhasil ditambahkan.');  
    }  
}

Laravel Form Request cocok untuk validasi yang kompleks karena memisahkan aturan validasi dan authorization dari controller. Form Request dijalankan sebelum controller dipanggil.

---

## **5.2 Application Layer**

Berisi proses aplikasi melalui:

Actions  
Services  
Events  
Listeners  
Data Objects jika diperlukan

### **Gunakan Action untuk satu proses spesifik**

CreateNewsAction  
UpdateNewsAction  
DeleteNewsAction  
PublishNewsAction

CreatePublicationAction  
UpdatePublicationAction

UploadMediaAction  
DeleteMediaAction

UpdateVillageProfileAction  
UpdateStatisticDatasetAction  
ImportGeoJsonAction  
ProcessComplaintAction

### **Gunakan Service untuk kemampuan lintas proses**

MediaService  
SeoService  
CacheService  
MapService  
StatisticService  
ActivityLogService  
ImageProcessingService  
SitemapService  
BackupService

Contoh:

CreateNewsAction  
├── Membuat slug  
├── Menjalankan database transaction  
├── Menyimpan featured image  
├── Menyimpan berita  
├── Memicu NewsCreated event  
├── Mencatat activity log  
└── Menghapus cache berita

---

## **5.3 Domain dan Data Layer**

Berisi:

Eloquent Models  
Model Relationships  
Enums  
Query Scopes  
Policies  
Model Casts  
Value Objects jika diperlukan

Contoh enum:

namespace App\\Enums;

enum NewsStatus: string  
{  
    case Draft \= 'draft';  
    case Published \= 'published';  
    case Archived \= 'archived';  
}

Contoh model:

final class News extends Model  
{  
    use SoftDeletes;

    protected $fillable \= \[  
        'category\_id',  
        'title',  
        'slug',  
        'excerpt',  
        'content',  
        'featured\_image\_id',  
        'status',  
        'published\_at',  
        'author\_id',  
    \];

    protected function casts(): array  
    {  
        return \[  
            'status' \=\> NewsStatus::class,  
            'published\_at' \=\> 'datetime',  
        \];  
    }

    public function author(): BelongsTo  
    {  
        return $this-\>belongsTo(User::class, 'author\_id');  
    }

    public function category(): BelongsTo  
    {  
        return $this-\>belongsTo(NewsCategory::class);  
    }

    public function featuredImage(): BelongsTo  
    {  
        return $this-\>belongsTo(Media::class, 'featured\_image\_id');  
    }

    public function scopePublished(Builder $query): Builder  
    {  
        return $query  
            \-\>where('status', NewsStatus::Published)  
            \-\>whereNotNull('published\_at')  
            \-\>where('published\_at', '\<=', now());  
    }

    public function getRouteKeyName(): string  
    {  
        return 'slug';  
    }  
}

---

## **5.4 Infrastructure Layer**

Berisi integrasi teknis:

Laravel Filesystem  
Database  
Cache  
Session  
Logging  
Image Processing  
Mail  
Scheduler  
Backup  
GeoJSON Processing  
SEO Sitemap Generator

Hindari membungkus semua fitur Laravel dalam service baru. Gunakan abstraction tambahan hanya ketika prosesnya kompleks atau digunakan oleh banyak modul.

---

# **6\. Modul utama aplikasi**

## **6.1 Authentication**

Authentication  
├── Login admin  
├── Logout admin  
├── Session regeneration  
├── Login rate limiting  
├── Password confirmation  
├── Session expiration  
├── Login audit  
└── Reset password oleh super admin

Route:

GET  /admin/login  
POST /admin/login  
POST /admin/logout

Tidak perlu menyediakan registrasi publik.

Laravel menyediakan autentikasi berbasis cookie dan session. Setelah login berhasil, session harus diregenerasi untuk mencegah session fixation.

---

## **6.2 User, Role, dan Authorization**

Role awal:

super\_admin  
admin\_konten  
admin\_data

Hak akses:

| Fitur | Super Admin | Admin Konten | Admin Data |
| ----- | ----- | ----- | ----- |
| Dashboard | Ya | Ya | Ya |
| Profil desa | Kelola | Kelola | Lihat |
| Berita | Kelola | Kelola | Lihat |
| Informasi publik | Kelola | Kelola | Lihat |
| Galeri | Kelola | Kelola | Lihat |
| Statistik | Kelola | Lihat | Kelola |
| Peta desa | Kelola | Lihat | Kelola |
| Pengaduan | Kelola | Terbatas | Terbatas |
| User admin | Kelola | Tidak | Tidak |
| Pengaturan | Kelola | Terbatas | Tidak |
| Audit log | Lihat | Tidak | Tidak |

Implementasi:

Middleware \-\> membatasi kelompok route  
Gate       \-\> akses fungsi umum, seperti dashboard  
Policy     \-\> akses resource, seperti berita atau statistik

Laravel menyediakan Gates dan Policies. Gate cocok untuk aksi umum, sedangkan Policy cocok untuk mengelola izin berdasarkan model atau resource.

Untuk hanya tiga role, tidak perlu memasang package permission kompleks. Gunakan:

roles  
\- id  
\- name  
\- code  
\- created\_at  
\- updated\_at

users  
\- id  
\- role\_id  
\- name  
\- email  
\- password  
\- is\_active  
\- last\_login\_at  
\- created\_at  
\- updated\_at

---

## **6.3 Website Settings**

Digunakan untuk:

Nama website  
Nama desa  
Logo  
Favicon  
Alamat kantor  
Nomor WhatsApp  
Email  
Media sosial  
Jam pelayanan  
Koordinat kantor desa  
Meta description default  
Google Search Console verification  
Footer  
Identitas warna

Tabel:

settings  
\- id  
\- key  
\- value  
\- type  
\- group  
\- is\_public  
\- updated\_by  
\- created\_at  
\- updated\_at

Contoh key:

site\_name  
village\_name  
village\_address  
contact\_whatsapp  
contact\_email  
instagram\_url  
office\_latitude  
office\_longitude  
default\_meta\_description  
google\_site\_verification

Cache seluruh settings selama satu jam dan hapus cache ketika pengaturan diperbarui.

---

## **6.4 Profil Desa**

Profil Desa  
├── Sejarah  
├── Visi  
├── Misi  
├── Gambaran wilayah  
├── Batas wilayah  
├── Luas wilayah  
├── Data lima dusun  
├── Potensi desa  
└── Struktur pemerintahan

Tabel:

village\_profile\_sections  
\- id  
\- section\_key  
\- title  
\- content  
\- image\_id  
\- status  
\- display\_order  
\- updated\_by  
\- created\_at  
\- updated\_at

Nilai `section_key`:

history  
vision  
mission  
territory  
demography  
potential

Struktur perangkat desa:

officials  
\- id  
\- name  
\- position  
\- photo\_id  
\- biography  
\- display\_order  
\- is\_active  
\- created\_at  
\- updated\_at

---

## **6.5 Berita**

news\_categories  
\- id  
\- name  
\- slug  
\- description  
\- created\_at  
\- updated\_at

news  
\- id  
\- category\_id  
\- title  
\- slug  
\- excerpt  
\- content  
\- featured\_image\_id  
\- status  
\- published\_at  
\- author\_id  
\- created\_at  
\- updated\_at  
\- deleted\_at

Status:

draft  
published  
archived

Index database:

UNIQUE slug  
INDEX status  
INDEX published\_at  
INDEX category\_id  
INDEX author\_id  
COMPOSITE INDEX status, published\_at

Fitur:

Draft  
Publish  
Archive  
Preview  
Featured image  
Kategori  
Pencarian  
Pagination  
Soft delete  
Restore  
SEO metadata  
Related news

---

## **6.6 Informasi Publik**

Gunakan satu struktur generik:

publications  
\- id  
\- type  
\- title  
\- slug  
\- excerpt  
\- content  
\- featured\_image\_id  
\- start\_date  
\- end\_date  
\- published\_at  
\- status  
\- author\_id  
\- created\_at  
\- updated\_at  
\- deleted\_at

Jenis:

announcement  
administrative\_service  
agenda  
social\_assistance  
public\_document  
apbdes  
village\_regulation  
development

Lampiran:

publication\_attachments  
\- id  
\- publication\_id  
\- media\_id  
\- title  
\- display\_order  
\- created\_at  
\- updated\_at

---

## **6.7 Statistik Desa**

Gunakan dataset generik:

statistic\_datasets  
\- id  
\- category  
\- title  
\- slug  
\- description  
\- year  
\- unit  
\- visualization\_type  
\- source  
\- status  
\- display\_order  
\- created\_by  
\- created\_at  
\- updated\_at

statistic\_values  
\- id  
\- dataset\_id  
\- label  
\- value  
\- secondary\_value  
\- display\_order  
\- metadata\_json  
\- created\_at  
\- updated\_at

Kategori:

population  
gender  
age  
education  
occupation  
religion  
economy  
agriculture  
livestock  
infrastructure

Jenis visualisasi:

number  
table  
bar  
horizontal\_bar  
line  
pie  
doughnut

IDM dipisahkan:

idm\_scores  
\- id  
\- year  
\- idm\_score  
\- iks\_score  
\- ike\_score  
\- ikl\_score  
\- status\_label  
\- source  
\- created\_at  
\- updated\_at

---

## **6.8 Peta Desa**

Teknologi:

Leaflet.js  
OpenStreetMap basemap  
GeoJSON  
MySQL JSON/LONGTEXT

Tabel:

map\_layers  
\- id  
\- name  
\- slug  
\- description  
\- geometry\_type  
\- style\_json  
\- display\_order  
\- is\_visible  
\- created\_at  
\- updated\_at

map\_features  
\- id  
\- layer\_id  
\- name  
\- description  
\- geometry\_json  
\- latitude  
\- longitude  
\- photo\_id  
\- properties\_json  
\- is\_visible  
\- created\_at  
\- updated\_at

Layer awal:

Batas Desa Sukomulyo  
Dusun Biyan  
Dusun Gumul  
Dusun Kedungrejo  
Dusun Bakir  
Dusun Talasan  
Kantor Desa  
Fasilitas Pendidikan  
Fasilitas Kesehatan  
Tempat Ibadah  
Potensi Desa

GeoJSON yang dikirim ke browser harus:

* Hanya berisi layer aktif.  
* Divalidasi sebelum disimpan.  
* Dicache.  
* Tidak memuat data pribadi warga.  
* Memiliki ukuran geometri yang disederhanakan bila terlalu besar.

---

## **6.9 Galeri**

galleries  
\- id  
\- title  
\- slug  
\- description  
\- event\_date  
\- cover\_media\_id  
\- status  
\- created\_by  
\- created\_at  
\- updated\_at  
\- deleted\_at

gallery\_items  
\- id  
\- gallery\_id  
\- media\_id  
\- caption  
\- display\_order  
\- created\_at  
\- updated\_at

---

## **6.10 Media Library**

media  
\- id  
\- original\_name  
\- stored\_name  
\- disk  
\- storage\_path  
\- mime\_type  
\- extension  
\- file\_size  
\- width  
\- height  
\- alt\_text  
\- caption  
\- uploaded\_by  
\- created\_at  
\- updated\_at  
\- deleted\_at

Fungsi:

Upload gambar  
Upload PDF  
Validasi MIME  
Resize gambar  
Konversi WebP  
Thumbnail  
Alt text  
Pencarian media  
Soft delete  
Deteksi file yang masih digunakan

File publik disimpan di:

storage/app/public/uploads/

Kemudian dihubungkan ke:

public/storage/

Laravel menyediakan disk `public` untuk file yang dapat diakses publik dan secara default menyimpannya di `storage/app/public`. Akses web dibuat melalui symbolic link `public/storage`.

---

## **6.11 Pengaduan Masyarakat**

Apabila CTA “Buat Laporan” memakai form internal:

complaints  
\- id  
\- ticket\_code  
\- category  
\- title  
\- description  
\- reporter\_name  
\- contact\_encrypted  
\- location\_general  
\- status  
\- assigned\_to  
\- response  
\- responded\_at  
\- created\_at  
\- updated\_at

Status:

submitted  
verified  
processing  
resolved  
rejected

Keamanan khusus:

* Kontak warga dienkripsi.  
* Tidak meminta NIK atau nomor KK untuk MVP.  
* Tidak menampilkan laporan secara publik.  
* Gunakan CAPTCHA.  
* Rate limit berdasarkan IP.  
* Lampiran dibatasi.  
* Ticket code dibuat acak.  
* Admin hanya melihat data sesuai kewenangan.  
* Data pribadi tidak dicatat dalam activity log.

Alternatif MVP yang lebih sederhana:

CTA Pengaduan  
\-\> WhatsApp resmi desa  
atau  
\-\> Google Form resmi desa

---

## **6.12 Audit Log**

activity\_logs  
\- id  
\- user\_id  
\- action  
\- module  
\- record\_type  
\- record\_id  
\- description  
\- old\_values\_json  
\- new\_values\_json  
\- ip\_address  
\- user\_agent  
\- created\_at

Aktivitas:

LOGIN\_SUCCESS  
LOGIN\_FAILED  
LOGOUT  
CREATE\_NEWS  
UPDATE\_NEWS  
DELETE\_NEWS  
RESTORE\_NEWS  
UPDATE\_PROFILE  
UPDATE\_STATISTIC  
IMPORT\_GEOJSON  
CREATE\_USER  
CHANGE\_ROLE  
UPDATE\_SETTING

Gunakan Event, Listener, atau Observer untuk audit dan invalidasi cache. Jangan menyimpan aturan bisnis utama di dalam Observer.

---

# **7\. Struktur folder Laravel 12**

website-desa-sukomulyo/  
├── app/  
│   ├── Actions/  
│   │   ├── Auth/  
│   │   ├── News/  
│   │   ├── Publications/  
│   │   ├── Profiles/  
│   │   ├── Statistics/  
│   │   ├── Maps/  
│   │   ├── Media/  
│   │   ├── Galleries/  
│   │   ├── Complaints/  
│   │   └── Users/  
│   │  
│   ├── Enums/  
│   │   ├── NewsStatus.php  
│   │   ├── PublicationStatus.php  
│   │   ├── PublicationType.php  
│   │   ├── ComplaintStatus.php  
│   │   ├── MediaType.php  
│   │   └── UserRole.php  
│   │  
│   ├── Events/  
│   │   ├── NewsPublished.php  
│   │   ├── ContentUpdated.php  
│   │   └── ComplaintSubmitted.php  
│   │  
│   ├── Http/  
│   │   ├── Controllers/  
│   │   │   ├── Web/  
│   │   │   │   ├── HomeController.php  
│   │   │   │   ├── ProfileController.php  
│   │   │   │   ├── NewsController.php  
│   │   │   │   ├── PublicationController.php  
│   │   │   │   ├── StatisticController.php  
│   │   │   │   ├── GalleryController.php  
│   │   │   │   ├── MapController.php  
│   │   │   │   └── ComplaintController.php  
│   │   │   │  
│   │   │   └── Admin/  
│   │   │       ├── AuthController.php  
│   │   │       ├── DashboardController.php  
│   │   │       ├── ProfileController.php  
│   │   │       ├── NewsController.php  
│   │   │       ├── PublicationController.php  
│   │   │       ├── StatisticController.php  
│   │   │       ├── GalleryController.php  
│   │   │       ├── MapController.php  
│   │   │       ├── MediaController.php  
│   │   │       ├── ComplaintController.php  
│   │   │       ├── UserController.php  
│   │   │       ├── SettingController.php  
│   │   │       └── ActivityLogController.php  
│   │   │  
│   │   ├── Middleware/  
│   │   │   ├── EnsureUserHasRole.php  
│   │   │   ├── EnsureUserIsActive.php  
│   │   │   └── SecurityHeaders.php  
│   │   │  
│   │   └── Requests/  
│   │       ├── Auth/  
│   │       ├── Admin/  
│   │       │   ├── News/  
│   │       │   ├── Publications/  
│   │       │   ├── Statistics/  
│   │       │   ├── Maps/  
│   │       │   ├── Media/  
│   │       │   ├── Galleries/  
│   │       │   └── Users/  
│   │       └── Web/  
│   │           └── StoreComplaintRequest.php  
│   │  
│   ├── Listeners/  
│   ├── Models/  
│   │   ├── User.php  
│   │   ├── Role.php  
│   │   ├── Setting.php  
│   │   ├── VillageProfileSection.php  
│   │   ├── Official.php  
│   │   ├── News.php  
│   │   ├── NewsCategory.php  
│   │   ├── Publication.php  
│   │   ├── PublicationAttachment.php  
│   │   ├── StatisticDataset.php  
│   │   ├── StatisticValue.php  
│   │   ├── IdmScore.php  
│   │   ├── MapLayer.php  
│   │   ├── MapFeature.php  
│   │   ├── Gallery.php  
│   │   ├── GalleryItem.php  
│   │   ├── Media.php  
│   │   ├── Complaint.php  
│   │   ├── SeoMetadata.php  
│   │   ├── Redirect.php  
│   │   └── ActivityLog.php  
│   │  
│   ├── Observers/  
│   ├── Policies/  
│   │   ├── NewsPolicy.php  
│   │   ├── PublicationPolicy.php  
│   │   ├── StatisticPolicy.php  
│   │   ├── MapPolicy.php  
│   │   ├── ComplaintPolicy.php  
│   │   └── UserPolicy.php  
│   │  
│   ├── Providers/  
│   ├── Services/  
│   │   ├── MediaService.php  
│   │   ├── SeoService.php  
│   │   ├── CacheService.php  
│   │   ├── SitemapService.php  
│   │   ├── MapService.php  
│   │   ├── StatisticService.php  
│   │   ├── ActivityLogService.php  
│   │   └── ImageProcessingService.php  
│   │  
│   ├── Support/  
│   │   ├── Seo/  
│   │   ├── GeoJson/  
│   │   └── Helpers/  
│   │  
│   └── View/  
│       └── Components/  
│  
├── bootstrap/  
│   ├── app.php  
│   └── cache/  
│  
├── config/  
│   ├── app.php  
│   ├── auth.php  
│   ├── cache.php  
│   ├── database.php  
│   ├── filesystems.php  
│   ├── logging.php  
│   ├── permissions.php  
│   ├── seo.php  
│   └── session.php  
│  
├── database/  
│   ├── factories/  
│   ├── migrations/  
│   └── seeders/  
│  
├── public/  
│   ├── index.php  
│   ├── build/  
│   ├── images/  
│   ├── storage/  
│   ├── favicon.ico  
│   └── robots.txt  
│  
├── resources/  
│   ├── css/  
│   │   ├── app.css  
│   │   ├── public.css  
│   │   └── admin.css  
│   ├── js/  
│   │   ├── app.js  
│   │   ├── map.js  
│   │   └── charts.js  
│   └── views/  
│       ├── components/  
│       ├── layouts/  
│       ├── web/  
│       │   ├── home/  
│       │   ├── profile/  
│       │   ├── news/  
│       │   ├── publications/  
│       │   ├── statistics/  
│       │   ├── galleries/  
│       │   ├── map/  
│       │   └── complaints/  
│       └── admin/  
│           ├── auth/  
│           ├── dashboard/  
│           ├── profiles/  
│           ├── news/  
│           ├── publications/  
│           ├── statistics/  
│           ├── galleries/  
│           ├── maps/  
│           ├── media/  
│           ├── complaints/  
│           ├── users/  
│           ├── settings/  
│           └── activity-logs/  
│  
├── routes/  
│   ├── web.php  
│   ├── admin.php  
│   └── console.php  
│  
├── storage/  
│   ├── app/  
│   │   ├── private/  
│   │   └── public/  
│   │       └── uploads/  
│   ├── framework/  
│   └── logs/  
│  
├── tests/  
│   ├── Feature/  
│   └── Unit/  
│  
├── .env  
├── .env.example  
├── artisan  
├── composer.json  
├── composer.lock  
├── package.json  
├── phpunit.xml  
└── vite.config.js

---

# **8\. Arsitektur route**

## **8.1 Public route**

use App\\Http\\Controllers\\Web\\ComplaintController;  
use App\\Http\\Controllers\\Web\\GalleryController;  
use App\\Http\\Controllers\\Web\\HomeController;  
use App\\Http\\Controllers\\Web\\MapController;  
use App\\Http\\Controllers\\Web\\NewsController;  
use App\\Http\\Controllers\\Web\\ProfileController;  
use App\\Http\\Controllers\\Web\\PublicationController;  
use App\\Http\\Controllers\\Web\\StatisticController;  
use Illuminate\\Support\\Facades\\Route;

Route::get('/', HomeController::class)-\>name('home');

Route::get('/profil-desa', \[ProfileController::class, 'index'\])  
    \-\>name('profile');

Route::get('/berita', \[NewsController::class, 'index'\])  
    \-\>name('news.index');

Route::get('/berita/{news:slug}', \[NewsController::class, 'show'\])  
    \-\>name('news.show');

Route::get('/informasi-desa', \[PublicationController::class, 'index'\])  
    \-\>name('publications.index');

Route::get(  
    '/informasi-desa/{publication:slug}',  
    \[PublicationController::class, 'show'\]  
)-\>name('publications.show');

Route::get('/statistik-desa', \[StatisticController::class, 'index'\])  
    \-\>name('statistics.index');

Route::get('/galeri-desa', \[GalleryController::class, 'index'\])  
    \-\>name('galleries.index');

Route::get('/galeri-desa/{gallery:slug}', \[GalleryController::class, 'show'\])  
    \-\>name('galleries.show');

Route::get('/peta-desa', \[MapController::class, 'index'\])  
    \-\>name('maps.index');

Route::get('/peta-desa/geojson', \[MapController::class, 'geoJson'\])  
    \-\>name('maps.geojson');

Route::get('/pengaduan', \[ComplaintController::class, 'create'\])  
    \-\>name('complaints.create');

Route::post('/pengaduan', \[ComplaintController::class, 'store'\])  
    \-\>middleware('throttle:complaints')  
    \-\>name('complaints.store');

Route::get('/sitemap.xml', SitemapController::class)  
    \-\>name('sitemap');

## **8.2 Admin route**

use App\\Http\\Controllers\\Admin;  
use Illuminate\\Support\\Facades\\Route;

Route::prefix('admin')  
    \-\>name('admin.')  
    \-\>group(function () {  
        Route::middleware('guest')-\>group(function () {  
            Route::get('/login', \[Admin\\AuthController::class, 'create'\])  
                \-\>name('login');

            Route::post('/login', \[Admin\\AuthController::class, 'store'\])  
                \-\>middleware('throttle:admin-login')  
                \-\>name('login.store');  
        });

        Route::middleware(\[  
            'auth',  
            'active-user',  
        \])-\>group(function () {  
            Route::post('/logout', \[Admin\\AuthController::class, 'destroy'\])  
                \-\>name('logout');

            Route::get('/', Admin\\DashboardController::class)  
                \-\>name('dashboard');

            Route::resource('news', Admin\\NewsController::class);  
            Route::resource('publications', Admin\\PublicationController::class);  
            Route::resource('statistics', Admin\\StatisticController::class);  
            Route::resource('galleries', Admin\\GalleryController::class);  
            Route::resource('maps', Admin\\MapController::class);  
            Route::resource('media', Admin\\MediaController::class)  
                \-\>only(\['index', 'store', 'destroy'\]);

            Route::resource('complaints', Admin\\ComplaintController::class)  
                \-\>only(\['index', 'show', 'update'\]);

            Route::middleware('role:super\_admin')-\>group(function () {  
                Route::resource('users', Admin\\UserController::class);  
                Route::get(  
                    '/activity-logs',  
                    Admin\\ActivityLogController::class  
                )-\>name('activity-logs.index');  
            });  
        });  
    });

---

# **9\. Arsitektur keamanan**

## **9.1 Server dan environment**

Wajib:

APP\_ENV=production  
APP\_DEBUG=false  
APP\_URL=https://domain-desa.id

Ketentuan:

* `.env` tidak masuk GitHub.  
* `APP_KEY` harus dibuat dengan `php artisan key:generate`.  
* Domain hanya diarahkan ke folder `public`.  
* Folder `storage` dan `bootstrap/cache` harus writable.  
* Source code tidak boleh terbuka dari internet.  
* Error detail tidak boleh tampil di production.  
* Gunakan HTTPS untuk seluruh halaman.

Laravel 12 membutuhkan minimal PHP 8.2 dan server harus mengarahkan seluruh request ke `public/index.php`. Dokumentasinya secara tegas melarang penyajian aplikasi dari root proyek karena dapat membuka file konfigurasi sensitif.

---

## **9.2 Authentication security**

Gunakan:

Session-based authentication  
Session regeneration setelah login  
Session invalidation saat logout  
Password confirmation untuk aksi sensitif  
Login throttling  
Database session  
Secure cookie  
HTTP-only cookie  
SameSite cookie

Konfigurasi:

SESSION\_DRIVER=database  
SESSION\_LIFETIME=120  
SESSION\_ENCRYPT=false  
SESSION\_SECURE\_COOKIE=true  
SESSION\_HTTP\_ONLY=true  
SESSION\_SAME\_SITE=lax

Setelah logout:

Auth::logout();

$request-\>session()-\>invalidate();  
$request-\>session()-\>regenerateToken();

Aksi sensitif yang memerlukan konfirmasi password:

Mengubah password  
Mengubah email admin  
Menghapus admin  
Mengubah role  
Mengubah domain atau pengaturan keamanan  
Menghapus data secara permanen

---

## **9.3 Password security**

Rekomendasi:

Minimum 12 karakter  
Campuran huruf dan angka  
Tidak menggunakan password default permanen  
Password wajib diganti setelah akun dibuat  
Password disimpan menggunakan Laravel Hash

Contoh:

use Illuminate\\Validation\\Rules\\Password;

'password' \=\> \[  
    'required',  
    'confirmed',  
    Password::min(12)  
        \-\>letters()  
        \-\>numbers(),  
\],

Untuk super admin, aktifkan two-factor authentication pada fase berikutnya.

---

## **9.4 CSRF**

Seluruh form `POST`, `PUT`, `PATCH`, dan `DELETE` wajib menggunakan:

@csrf

Laravel membuat token CSRF untuk setiap session aktif dan memverifikasi bahwa request benar-benar berasal dari pengguna yang memiliki session tersebut.

Jangan mengecualikan route admin dari pemeriksaan CSRF.

---

## **9.5 Authorization**

Pemeriksaan izin harus dilakukan di backend:

$this-\>authorize('update', $news);

atau:

Gate::authorize('manage-settings');

Jangan hanya menyembunyikan tombol berdasarkan role. Pengguna tetap dapat mencoba mengakses URL secara langsung.

---

## **9.6 Rate limiting**

Rekomendasi:

Login admin       \-\> 5 percobaan per menit per email \+ IP  
Pengaduan publik  \-\> 3 pengaduan per jam per IP  
Pencarian         \-\> 60 request per menit  
GeoJSON endpoint  \-\> 60 request per menit  
Reset password    \-\> 3 request per jam

Laravel menyediakan rate limiter yang bekerja bersama cache untuk membatasi tindakan dalam jangka waktu tertentu.

---

## **9.7 File upload security**

Aturan gambar:

JPG, JPEG, PNG, WebP  
Maksimal 2 MB  
Nama file dibuat aplikasi  
MIME diperiksa  
Dimensi diperiksa  
Metadata berbahaya dibuang

Aturan dokumen:

PDF  
Maksimal 5 MB  
Tidak menerima PHP, HTML, JS, EXE, ZIP

GeoJSON:

JSON atau GeoJSON  
Maksimal 5 MB  
Struktur geometry divalidasi  
Properti HTML disanitasi

Contoh validasi:

'image' \=\> \[  
    'required',  
    'file',  
    'mimes:jpg,jpeg,png,webp',  
    'max:2048',  
    'dimensions:max\_width=4000,max\_height=4000',  
\],

Jangan percaya hanya pada ekstensi atau header `Content-Type`. Gunakan allowlist format, pemeriksaan server-side, batas ukuran, nama file acak, dan penyimpanan yang aman.

SVG sebaiknya tidak diterima pada MVP karena dapat mengandung script. Logo SVG hanya boleh digunakan bila berasal dari sumber internal dan sudah disanitasi.

---

## **9.8 XSS dan konten editor**

Blade:

{{ $news-\>title }}

akan digunakan untuk teks biasa.

Konten editor sering ditampilkan dengan:

{\!\! $news-\>content \!\!}

Karena itu, konten dari editor WYSIWYG wajib disanitasi sebelum disimpan atau ditampilkan.

Izinkan hanya elemen yang diperlukan:

p  
h2  
h3  
h4  
ul  
ol  
li  
strong  
em  
a  
blockquote  
table  
thead  
tbody  
tr  
th  
td  
img

Hapus:

script  
iframe  
object  
embed  
form  
style  
event handler seperti onclick  
javascript: URL

---

## **9.9 SQL injection**

Gunakan:

Eloquent ORM  
Query Builder  
Parameter binding

Hindari menyusun query dari input:

// Jangan dilakukan  
DB::select("SELECT \* FROM news WHERE title \= '$title'");

Apabila menggunakan query raw, selalu gunakan binding:

DB::select(  
    'SELECT \* FROM news WHERE title \= ?',  
    \[$title\]  
);

---

## **9.10 Security headers**

Buat middleware `SecurityHeaders` dengan header:

X-Content-Type-Options: nosniff  
X-Frame-Options: SAMEORIGIN  
Referrer-Policy: strict-origin-when-cross-origin  
Permissions-Policy: camera=(), microphone=(), geolocation=()  
Content-Security-Policy  
Strict-Transport-Security

Security header membantu mengurangi risiko XSS, clickjacking, dan information disclosure. CSP harus disesuaikan dengan domain tile OpenStreetMap atau asset eksternal yang benar-benar digunakan.

HSTS baru diaktifkan setelah HTTPS dipastikan stabil:

Strict-Transport-Security: max-age=31536000; includeSubDomains

---

## **9.11 Data privacy**

Untuk MVP, jangan menyimpan:

NIK  
Nomor KK  
Scan KTP  
Alamat lengkap warga  
Data kesehatan  
Data bantuan sosial per individu  
Password plaintext  
Koordinat rumah warga

Gunakan statistik agregat.

Data kontak pengaduan harus:

* Dienkripsi.  
* Tidak ditampilkan di log.  
* Tidak dimasukkan dalam response publik.  
* Hanya dapat diakses admin berwenang.  
* Dihapus sesuai masa retensi.

---

## **9.12 Dependency security**

Pada pipeline:

composer validate \--strict  
composer audit  
npm audit

Aktifkan pembaruan dependency otomatis melalui GitHub Dependabot atau sistem serupa, tetapi setiap pembaruan tetap harus melalui pengujian.

---

# **10\. Strategi optimalisasi performa**

## **10.1 Production optimization**

Saat deployment:

php artisan optimize

Perintah tersebut mencakup cache konfigurasi, event, route, dan Blade view. Laravel merekomendasikan caching file-file tersebut saat deployment production.

Perintah granularnya:

php artisan config:cache  
php artisan event:cache  
php artisan route:cache  
php artisan view:cache

Jangan memanggil `env()` langsung dari controller atau service. Setelah `config:cache`, akses nilai melalui:

config('app.name');

---

## **10.2 Database optimization**

Wajib:

Gunakan index  
Gunakan eager loading  
Gunakan pagination  
Batasi kolom SELECT  
Hindari query dalam loop  
Gunakan database transaction  
Gunakan query scope  
Gunakan unique constraint

Contoh eager loading:

$news \= News::query()  
    \-\>published()  
    \-\>with(\[  
        'category:id,name,slug',  
        'author:id,name',  
        'featuredImage:id,storage\_path,alt\_text',  
    \])  
    \-\>latest('published\_at')  
    \-\>paginate(10);

Index penting:

news.slug                           \-\> UNIQUE  
news.status                         \-\> INDEX  
news.published\_at                   \-\> INDEX  
news(status, published\_at)          \-\> COMPOSITE

publications.slug                   \-\> UNIQUE  
publications(type, status)          \-\> COMPOSITE  
publications.published\_at           \-\> INDEX

statistic\_datasets(category, year)  \-\> COMPOSITE  
map\_layers.slug                     \-\> UNIQUE  
map\_layers.is\_visible               \-\> INDEX  
map\_features(layer\_id, is\_visible)  \-\> COMPOSITE

activity\_logs(user\_id, created\_at)  \-\> COMPOSITE  
complaints(ticket\_code)             \-\> UNIQUE  
complaints(status, created\_at)      \-\> COMPOSITE

---

## **10.3 Cache strategy**

Untuk shared hosting:

CACHE\_STORE=database

Laravel 12 menggunakan database sebagai default cache driver dan menyediakan migration untuk tabel cache.

Data yang dicache:

| Data | Durasi |
| ----- | ----- |
| Website settings | 60 menit |
| Navbar dan footer | 60 menit |
| Profil desa | 60 menit |
| Statistik beranda | 10 menit |
| Berita terbaru | 10 menit |
| Berita detail | 10 menit |
| Informasi publik | 10 menit |
| GeoJSON peta | 30 menit |
| Sitemap | 60 menit |

Contoh:

$settings \= Cache::remember(  
    'site.settings.public',  
    now()-\>addHour(),  
    fn () \=\> Setting::query()  
        \-\>where('is\_public', true)  
        \-\>pluck('value', 'key')  
);

Gunakan nama cache yang eksplisit:

site.settings.public  
home.statistics  
home.latest-news  
profile.sections  
news.detail.{id}  
map.geojson.public  
seo.sitemap

Setelah perubahan:

Update berita  
\-\> hapus cache detail berita  
\-\> hapus cache daftar berita  
\-\> hapus cache berita terbaru  
\-\> hapus cache sitemap

---

## **10.4 Image optimization**

Alur:

Upload  
\-\> Validasi  
\-\> Orientasi diperbaiki  
\-\> Metadata dibuang  
\-\> Resize  
\-\> Konversi WebP  
\-\> Thumbnail dibuat  
\-\> Simpan

Ukuran:

Hero homepage       \-\> 1920 × 1080  
Featured berita     \-\> 1280 × 720  
Thumbnail berita    \-\> 480 × 270  
Galeri medium       \-\> 960 × 640  
Foto perangkat      \-\> 500 × 500  
Thumbnail media     \-\> 300 × 300

Frontend:

\<img  
    src="..."  
    alt="..."  
    width="480"  
    height="270"  
    loading="lazy"  
    decoding="async"  
\>

Hero utama tidak menggunakan `loading="lazy"` karena dapat memperlambat elemen visual terbesar.

---

## **10.5 Frontend optimization**

Gunakan:

Vite production build  
CSS dan JS terkompresi  
Asset hashing  
JavaScript defer  
Lazy loading  
Font lokal atau sistem  
Bootstrap component sesuai kebutuhan  
Leaflet hanya di halaman peta  
Chart.js hanya di halaman statistik

Jangan memuat Leaflet dan Chart.js di seluruh halaman.

Struktur entry:

resources/js/app.js  
resources/js/pages/map.js  
resources/js/pages/statistics.js  
resources/js/admin/editor.js

---

## **10.6 Core Web Vitals**

Target:

LCP \-\> maksimal sekitar 2,5 detik  
INP \-\> kurang dari 200 ms  
CLS \-\> maksimal sekitar 0,1

Core Web Vitals menilai performa loading, responsivitas, dan stabilitas visual. Google menyarankan halaman yang cepat, aman, mobile-friendly, dan tidak menggunakan interstitial mengganggu.

---

# **11\. Arsitektur SEO**

## **11.1 SEO metadata**

Setiap halaman publik memiliki:

title  
meta\_description  
canonical\_url  
robots  
og\_title  
og\_description  
og\_image  
og\_type  
twitter\_card  
schema\_type

Tabel opsional:

seo\_metadata  
\- id  
\- seoable\_type  
\- seoable\_id  
\- meta\_title  
\- meta\_description  
\- canonical\_url  
\- robots  
\- og\_title  
\- og\_description  
\- og\_image\_id  
\- schema\_type  
\- created\_at  
\- updated\_at

Gunakan relasi polymorphic:

News        \-\> SeoMetadata  
Publication \-\> SeoMetadata  
Gallery     \-\> SeoMetadata  
Profile     \-\> SeoMetadata

Fallback:

meta title kosong  
\-\> gunakan judul konten

meta description kosong  
\-\> gunakan excerpt

OG image kosong  
\-\> gunakan featured image

featured image kosong  
\-\> gunakan default image website

---

## **11.2 Format title**

Beranda  
Desa Sukomulyo | Website Resmi Pemerintah Desa

Profil  
Profil Desa Sukomulyo | Website Resmi Desa Sukomulyo

Berita  
Judul Berita | Desa Sukomulyo

Statistik  
Statistik Penduduk Desa Sukomulyo Tahun 2026

Peta  
Peta Wilayah dan Dusun Desa Sukomulyo

Hindari title yang sama untuk seluruh halaman.

---

## **11.3 URL dan slug**

Gunakan URL bersih:

/berita/pembangunan-jalan-desa  
/informasi-desa/jadwal-posyandu-juli-2026  
/galeri-desa/musyawarah-desa-2026  
/statistik-desa  
/peta-desa

Hindari:

/berita?id=25  
/page.php?type=news\&id=25

Ketika slug berubah, simpan redirect:

redirects  
\- id  
\- old\_path  
\- new\_path  
\- status\_code  
\- created\_at

Gunakan status `301` untuk URL lama yang dipindahkan permanen.

---

## **11.4 Canonical**

Setiap halaman indexable menggunakan:

\<link rel="canonical" href="https://domain.id/berita/judul-berita"\>

Canonical mencegah halaman yang sama dari parameter atau variasi URL dianggap sebagai beberapa URL berbeda. Google mendukung `rel="canonical"` untuk menentukan URL utama dari halaman duplikat atau sangat mirip.

---

## **11.5 Robots**

Halaman publik:

index, follow

Halaman berikut:

/admin/\*  
/admin/login  
preview berita  
draft  
hasil pencarian internal tertentu  
halaman filter dengan banyak parameter

menggunakan:

\<meta name="robots" content="noindex, nofollow"\>

Robots meta dapat mengendalikan indexing pada tingkat halaman.

`robots.txt`:

User-agent: \*  
Disallow: /admin/  
Disallow: /preview/  
Disallow: /storage/private/

Sitemap: https://domain-desa.id/sitemap.xml

`robots.txt` bukan sistem keamanan. Route admin tetap harus dilindungi authentication.

---

## **11.6 Sitemap XML**

Sitemap berisi:

Homepage  
Profil desa  
Berita published  
Informasi published  
Galeri published  
Statistik utama  
Peta desa

Jangan memasukkan:

Draft  
Admin  
Login  
Preview  
Konten archived  
URL pencarian  
URL parameter filter

Setiap item memuat:

loc  
lastmod

Sitemap dibuat secara dinamis, dicache, lalu didaftarkan ke Google Search Console. Google menjelaskan bahwa sitemap membantu memberikan informasi URL kepada mesin pencari, tetapi pengiriman sitemap tetap merupakan petunjuk dan bukan jaminan indexing.

---

## **11.7 Structured data JSON-LD**

Homepage:

WebSite  
GovernmentOrganization atau Organization

Berita:

NewsArticle atau Article

Breadcrumb:

BreadcrumbList

Profil desa:

AboutPage  
GovernmentOrganization  
PostalAddress  
GeoCoordinates

Statistik:

Dataset

Contoh berita:

\<script type="application/ld+json"\>  
{\!\! json\_encode(\[  
    '@context' \=\> 'https://schema.org',  
    '@type' \=\> 'NewsArticle',  
    'headline' \=\> $news-\>title,  
    'datePublished' \=\> optional($news-\>published\_at)?-\>toIso8601String(),  
    'dateModified' \=\> $news-\>updated\_at-\>toIso8601String(),  
    'author' \=\> \[  
        '@type' \=\> 'Organization',  
        'name' \=\> config('app.name'),  
    \],  
    'publisher' \=\> \[  
        '@type' \=\> 'GovernmentOrganization',  
        'name' \=\> config('app.name'),  
    \],  
    'image' \=\> $news-\>featuredImage?-\>url,  
    'mainEntityOfPage' \=\> route('news.show', $news),  
\], JSON\_UNESCAPED\_SLASHES | JSON\_UNESCAPED\_UNICODE) \!\!}  
\</script\>

Google merekomendasikan JSON-LD sebagai salah satu format structured data yang didukung. Structured data membantu mesin pencari memahami konten, tetapi harus sesuai dengan konten yang benar-benar terlihat pada halaman.

---

## **11.8 SEO konten**

Setiap berita sebaiknya memiliki:

Satu H1  
Subjudul H2 dan H3  
Tanggal publikasi  
Tanggal pembaruan  
Penulis atau instansi  
Foto relevan  
Alt text  
Sumber data  
Internal link  
Excerpt

Konten statistik harus mencantumkan:

Tahun data  
Satuan  
Sumber  
Tanggal pembaruan  
Penjelasan singkat

SEO tidak cukup hanya dengan metadata. Konten harus akurat, relevan, mudah dibaca, dan bermanfaat bagi warga. Google menekankan konten people-first dan struktur teknis yang dapat dirayapi.

---

# **12\. Logging dan monitoring**

Konfigurasi:

LOG\_CHANNEL=daily  
LOG\_LEVEL=error  
LOG\_DAILY\_DAYS=14

Laravel menyediakan channel `daily` yang merotasi file log setiap hari dan masa retensinya dapat dikonfigurasi.

Log aplikasi:

Exception  
Database failure  
Upload failure  
GeoJSON import failure  
Email failure  
Backup failure  
Unauthorized access

Pisahkan:

storage/logs/laravel-YYYY-MM-DD.log  
activity\_logs table

`activity_logs` digunakan untuk aktivitas admin, sedangkan Laravel log digunakan untuk error teknis.

Jangan mencatat:

Password  
Session token  
CSRF token  
Database password  
Kontak pengaduan lengkap  
Isi file pribadi

Sediakan health endpoint:

/up

Pemeriksaan:

Aplikasi dapat berjalan  
Database dapat terhubung  
Storage dapat ditulis

---

# **13\. Scheduler dan queue**

## **MVP**

QUEUE\_CONNECTION=sync

Artinya proses dijalankan langsung:

Upload gambar  
Activity log  
Cache invalidation

Queue worker terus-menerus belum diperlukan.

Queue baru dipertimbangkan untuk:

Email massal  
Import data besar  
Kompresi banyak gambar  
Generate laporan besar  
Backup besar  
Notifikasi banyak penerima

## **Scheduler**

Gunakan untuk:

Publish berita terjadwal  
Archive agenda kedaluwarsa  
Membersihkan file sementara  
Membersihkan cache tertentu  
Backup database  
Menghapus log lama

Laravel scheduler hanya membutuhkan satu cron entry server.

Cron:

\* \* \* \* \* cd /home/user/apps/sukomulyo && php artisan schedule:run \>\> /dev/null 2\>&1

---

# **14\. Backup dan disaster recovery**

## **Backup database**

Harian  
Sebelum migration  
Sebelum perubahan besar  
Retensi minimal 14–30 hari

## **Backup upload**

Harian atau setiap ada perubahan signifikan  
Folder storage/app/public/uploads  
Disimpan di lokasi berbeda dari hosting utama

## **Strategi**

Database production  
├── Backup harian hosting  
├── Backup manual sebelum deployment  
└── Salinan eksternal berkala

Upload  
├── Backup hosting  
└── Salinan cloud/offsite

Jangan mengandalkan GitHub untuk backup:

Database  
.env  
Upload admin  
Log  
Session

karena semuanya memang tidak disimpan dalam repository.

Uji proses restore secara berkala. Backup yang tidak pernah diuji belum dapat dianggap andal.

---

# **15\. Testing architecture**

## **15.1 Unit test**

Slug generation  
Enum status  
Permission role  
SEO fallback  
Format statistik  
GeoJSON validator  
Image naming  
Cache key generation  
Ticket code generation

## **15.2 Feature test**

Login berhasil  
Login gagal  
Session diregenerasi  
Tamu ditolak dari admin  
Admin konten tidak dapat mengelola user  
Admin data tidak dapat mengubah berita  
Berita draft tidak tampil publik  
Berita published tampil publik  
Soft delete dan restore berita  
Upload format salah ditolak  
Upload terlalu besar ditolak  
GeoJSON tidak valid ditolak  
Pengaduan terkena rate limit

## **15.3 SEO test**

Canonical tersedia  
Meta title tersedia  
Meta description tersedia  
Draft menggunakan noindex  
Admin menggunakan noindex  
Sitemap hanya berisi published content  
Structured data menghasilkan JSON valid  
Slug lama melakukan redirect 301

## **15.4 Smoke test**

GET /  
GET /profil-desa  
GET /berita  
GET /informasi-desa  
GET /statistik-desa  
GET /galeri-desa  
GET /peta-desa  
GET /pengaduan  
GET /admin/login  
GET /sitemap.xml  
GET /up

Laravel mendukung Pest maupun PHPUnit dan seluruh test dapat dijalankan dengan `php artisan test`.

---

# **16\. Deployment shared hosting**

## **16.1 Struktur ideal**

/home/account/apps/sukomulyo/  
├── app/  
├── bootstrap/  
├── config/  
├── database/  
├── public/             \<- document root domain  
├── resources/  
├── routes/  
├── storage/  
├── vendor/  
└── .env

Domain diarahkan ke:

/home/account/apps/sukomulyo/public

Apabila hosting tidak dapat mengubah document root:

/home/account/laravel-app/  
├── app/  
├── bootstrap/  
├── config/  
├── database/  
├── resources/  
├── routes/  
├── storage/  
├── vendor/  
└── .env

/home/account/public\_html/  
├── index.php  
├── .htaccess  
├── build/  
├── images/  
├── storage/  
└── favicon.ico

Source Laravel tetap berada di luar `public_html`. Hanya isi folder `public` yang berada di area publik.

---

## **16.2 File yang masuk GitHub**

app/  
bootstrap/  
config/  
database/  
public/  
resources/  
routes/  
tests/  
composer.json  
composer.lock  
package.json  
package-lock.json  
vite.config.js  
.env.example

Tidak masuk GitHub:

.env  
vendor/  
node\_modules/  
storage/logs/\*  
storage/framework/cache/\*  
storage/framework/sessions/\*  
storage/app/public/uploads/\*  
public/storage  
backup/\*

---

## **16.3 Langkah deployment**

composer install \\  
    \--no-dev \\  
    \--prefer-dist \\  
    \--optimize-autoloader

php artisan optimize:clear

php artisan migrate \--force

php artisan storage:link

php artisan optimize

Untuk deployment dengan maintenance mode:

php artisan down

composer install \--no-dev \--prefer-dist \--optimize-autoloader  
php artisan optimize:clear  
php artisan migrate \--force  
php artisan storage:link  
php artisan optimize

php artisan up

Sebelum migration:

Backup database  
Periksa migration  
Jalankan test  
Periksa ruang penyimpanan

---

## **16.4 Build frontend**

Di lokal atau GitHub Actions:

npm ci  
npm run build

Deploy hasil:

public/build/

Dengan demikian production hosting tidak perlu menjalankan `npm`.

---

# **17\. Konfigurasi production**

APP\_NAME="Website Desa Sukomulyo"  
APP\_ENV=production  
APP\_KEY=base64:GENERATED\_KEY  
APP\_DEBUG=false  
APP\_URL=https://desasukomulyo.id  
APP\_TIMEZONE=Asia/Jakarta  
APP\_LOCALE=id  
APP\_FALLBACK\_LOCALE=id

DB\_CONNECTION=mysql  
DB\_HOST=localhost  
DB\_PORT=3306  
DB\_DATABASE=database\_name  
DB\_USERNAME=database\_user  
DB\_PASSWORD=strong\_database\_password

SESSION\_DRIVER=database  
SESSION\_LIFETIME=120  
SESSION\_ENCRYPT=false  
SESSION\_SECURE\_COOKIE=true  
SESSION\_HTTP\_ONLY=true  
SESSION\_SAME\_SITE=lax

CACHE\_STORE=database  
QUEUE\_CONNECTION=sync  
FILESYSTEM\_DISK=public

LOG\_CHANNEL=daily  
LOG\_LEVEL=error  
LOG\_DAILY\_DAYS=14

MAIL\_MAILER=log

Untuk pengembangan lokal:

APP\_ENV=local  
APP\_DEBUG=true  
LOG\_LEVEL=debug

---

# **18\. CI/CD GitHub Actions**

Pipeline:

Push feature branch  
  \-\> Composer validate  
  \-\> Composer install  
  \-\> Composer audit  
  \-\> Laravel Pint check  
  \-\> PHPUnit/Pest  
  \-\> NPM install  
  \-\> Vite production build  
  \-\> Pull Request  
  \-\> Review  
  \-\> Merge main  
  \-\> Deploy shared hosting  
  \-\> Migration  
  \-\> Optimize  
  \-\> Smoke test

Perintah pemeriksaan:

composer validate \--strict  
composer audit  
vendor/bin/pint \--test  
php artisan test  
npm ci  
npm run build

Struktur branch sederhana:

feature/\*  
    │  
    ▼  
main  
    │  
    ▼  
Production

Untuk tim kecil, branch `develop` dan `staging` belum wajib.

---

# **19\. Prioritas implementasi**

## **Fase 1 — Fondasi**

Laravel installation  
Environment  
Database  
Migration  
Authentication  
Role  
Admin layout  
Public layout  
Settings  
Media library  
Security middleware

## **Fase 2 — Konten utama**

Profil desa  
Berita  
Informasi publik  
Galeri  
Dashboard admin  
Audit log

## **Fase 3 — Data dan peta**

Statistik  
Chart.js  
IDM  
Map layer  
GeoJSON  
Leaflet  
Dusun overlay

## **Fase 4 — SEO dan performa**

SEO metadata  
Canonical  
Sitemap  
Robots  
Structured data  
Cache  
Image optimization  
Database indexes  
Core Web Vitals

## **Fase 5 — Pengaduan dan operasional**

Form pengaduan  
CAPTCHA  
Rate limiting  
Ticket status  
Backup  
Scheduler  
Monitoring  
Testing  
Deployment checklist

---

# **20\. Hal yang tidak diperlukan untuk MVP**

Microservices  
React SPA  
Vue SPA  
Separate backend API  
Redis  
Laravel Horizon  
Laravel Octane  
Laravel Reverb  
WebSocket  
Elasticsearch  
GeoServer  
PostGIS  
Kubernetes  
Docker production  
Dynamic permission builder  
Repository untuk seluruh model  
Event sourcing  
Multiple database

Fitur-fitur tersebut menambah beban deployment dan maintenance tanpa manfaat yang sebanding untuk website desa berskala kecil hingga menengah.

---

# **21\. Baseline final**

Website Desa Sukomulyo  
│  
├── Public Website  
│   ├── Beranda  
│   ├── Profil Desa  
│   ├── Berita  
│   ├── Informasi Desa  
│   ├── Statistik Desa  
│   ├── Galeri Desa  
│   ├── Peta Desa  
│   └── Pengaduan  
│  
├── Admin CMS  
│   ├── Authentication  
│   ├── Dashboard  
│   ├── Profil Desa  
│   ├── Berita  
│   ├── Informasi Publik  
│   ├── Statistik  
│   ├── Galeri  
│   ├── Peta  
│   ├── Media  
│   ├── Pengaduan  
│   ├── User dan Role  
│   ├── Pengaturan  
│   └── Audit Log  
│  
├── HTTP Layer  
│   ├── Routes  
│   ├── Middleware  
│   ├── Controllers  
│   └── Form Requests  
│  
├── Application Layer  
│   ├── Actions  
│   ├── Services  
│   ├── Events  
│   └── Listeners  
│  
├── Domain/Data Layer  
│   ├── Eloquent Models  
│   ├── Enums  
│   ├── Policies  
│   ├── Scopes  
│   └── Casts  
│  
├── Infrastructure  
│   ├── MySQL  
│   ├── Laravel Filesystem  
│   ├── Database Cache  
│   ├── Database Session  
│   ├── Logging  
│   ├── Scheduler  
│   └── Backup  
│  
└── Deployment  
    ├── GitHub  
    ├── Composer  
    ├── Vite Build  
    ├── Artisan Migration  
    ├── Artisan Optimize  
    ├── Shared Hosting  
    └── Smoke Test

## **Keputusan final**

Gunakan:

Laravel 12 Modular Monolith  
\+ PHP 8.3/8.4  
\+ Blade  
\+ Bootstrap 5  
\+ Vite  
\+ Eloquent ORM  
\+ MySQL  
\+ Session Authentication  
\+ Gates dan Policies  
\+ Form Requests  
\+ Actions dan Services  
\+ Chart.js  
\+ Leaflet dan GeoJSON  
\+ Laravel Filesystem  
\+ Database Cache  
\+ Database Session  
\+ GitHub Deployment

Arsitektur ini cukup kuat untuk website desa dan CMS, tetapi tetap ringan untuk shared hosting, mudah dikembangkan oleh tim kecil, dan tidak menambahkan kompleksitas yang belum dibutuhkan.

