<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title','Admin') | Desa Sukomulyo</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/logo-brighter-sukomulyo.svg') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/bootstrap/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/bootstrap/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/bootstrap/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/AdminLTE.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/skins/_all-skins.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/admin-style.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/js/sweetalert2/sweetalert2.min.css') }}">@vite('resources/css/admin.css')@stack('head')
</head>

<body id="sidebar_collapse" class="hold-transition skin-red-light fixed sidebar-mini">
    <div class="wrapper">
        <header class="main-header"><a href="{{ route('beranda') }}" target="_blank" class="logo"><span class="logo-mini"><b>SID</b></span><span class="logo-lg"><b>OpenSID</b></span></a>
            <nav class="navbar navbar-static-top"><a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button"><span class="sr-only">Buka navigasi</span></a>
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <!-- <li><a href="{{ route('beranda') }}" target="_blank" title="Lihat website"><i class="fa fa-globe"></i><span class="hidden-xs"> Website</span></a></li> -->
                        <li class="dropdown notifications-menu"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell-o"></i>@php($messageCount=\App\Models\ContactMessage::count())@if($messageCount)<span class="label label-warning">{{ $messageCount }}</span>@endif</a>
                            <ul class="dropdown-menu">
                                <li class="header">{{ $messageCount ? "$messageCount pesan masuk" : 'Tidak ada pesan baru' }}</li>
                                <li>
                                    <ul class="menu">
                                        <li><a href="{{ route('admin.messages.index') }}"><i class="fa fa-envelope text-aqua"></i> Buka kotak masuk</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown user user-menu"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="user-image img-circle bg-maroon" style="text-align:center;line-height:25px">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span><span class="hidden-xs">{{ auth()->user()->name }}</span></a>
                            <ul class="dropdown-menu">
                                <li class="user-header"><span class="img-circle bg-maroon" style="display:inline-block;width:90px;height:90px;line-height:90px;font-size:36px">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span>
                                    <p>{{ auth()->user()->name }}<small>Anda masuk sebagai {{ auth()->user()->role->name }}</small></p>
                                </li>
                                <li class="user-footer">
                                    <div class="pull-left"><a href="{{ route('admin.users.edit',auth()->user()) }}" class="btn btn-default btn-flat">Profil</a></div>
                                    <div class="pull-right">
                                        <form method="post" action="{{ route('admin.logout') }}">@csrf<button class="btn btn-default btn-flat">Keluar</button></form>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <!-- <li><a href="#" data-toggle="control-sidebar"><i class="fa fa-question-circle"></i></a></li> -->
                    </ul>
                </div>
            </nav>
        </header>
        <aside class="main-sidebar">
            <section class="sidebar">
                <!-- <div class="user-panel">
                    <div class="pull-left image"><span class="img-circle bg-red" style="display:block;width:45px;height:45px;text-align:center;line-height:45px;font-weight:bold">DS</span></div>
                    <div class="pull-left info">
                        <p>Desa Sukomulyo</p><small>Kabupaten Indonesia</small>
                    </div>
                </div> -->
                <div class="sidebar-form">
                    <div class="input-group"><input type="text" id="cari-menu" class="form-control" placeholder="Pencarian..."><span class="input-group-btn"><button type="button" class="btn btn-flat"><i class="fa fa-search"></i></button></span></div>
                </div>
                @php($groups=[
                ['label'=>'Beranda','icon'=>'fa-dashboard','ability'=>null,'items'=>[['Dashboard','admin.dashboard',null]]],
                ['label'=>'Info Desa','icon'=>'fa-home','ability'=>'manage-content','items'=>[['Identitas Desa','admin.village-content.profile',null],['Visi Misi','admin.village-content.vision-mission.edit',null],['Sejarah Desa','admin.village-content.edit','history'],['Struktur Pemerintahan','admin.officials.index',null],['Potensi Desa','admin.village-content.edit','potential']]],
                ['label'=>'Kependudukan','icon'=>'fa-users','ability'=>'manage-data','items'=>[['Penduduk','admin.population.residents.index',null],['Keluarga','admin.population.families.index',null],['Rumah Tangga','admin.population.households.index',null],['Statistik Kependudukan','admin.population.statistics',null],['Laporan Penduduk','admin.population.report',null]]],
                ['label'=>'Admin Web','icon'=>'fa-desktop','ability'=>'manage-content','items'=>[['Artikel','admin.resources.index','news'],['Kategori Artikel','admin.resources.index','categories'],['Galeri','admin.resources.index','galleries']]],
                ['label'=>'Informasi Publik','icon'=>'fa-file-text','ability'=>'manage-content','items'=>[['Dokumen & Pengumuman','admin.resources.index','publications'],['Lampiran Publikasi','admin.resources.index','publication-attachments']]],
                ['label'=>'Data Desa','icon'=>'fa-bar-chart','ability'=>'manage-data','items'=>[['Statistik','admin.resources.index','statistics'],['Nilai Statistik','admin.resources.index','statistic-values'],['Indeks Desa Membangun','admin.resources.index','idm']]],
                ['label'=>'Pemetaan','icon'=>'fa-map','ability'=>'manage-data','items'=>[['Layer Peta','admin.resources.index','map-layers'],['Fitur Peta','admin.resources.index','map-features']]],
                ['label'=>'Layanan Masyarakat','icon'=>'fa-envelope','ability'=>'manage-content','items'=>[['Pesan Masuk','admin.messages.index',null]]],
                ['label'=>'Pengaturan','icon'=>'fa-cogs','ability'=>'manage-users','items'=>[['Pengguna','admin.users.index',null],['Pengaturan Aplikasi','admin.resources.index','settings'],['Redirect URL','admin.resources.index','redirects'],['Log Aktivitas','admin.activities.index',null]]],
                ])
                <ul class="sidebar-menu" data-widget="tree">
                    <li class="header">MENU UTAMA</li>@foreach($groups as $group)@if(!$group['ability']||auth()->user()->can($group['ability']))@php($active=collect($group['items'])->contains(function($i){$parameter=request()->routeIs('admin.village-content.*')?request()->route('page'):request()->route('resource');$routeActive=request()->routeIs($i[1])||($i[1]==='admin.village-content.profile'&&request()->routeIs('admin.village-content.profile-edit'))||($i[1]!=='admin.dashboard'&&str_ends_with($i[1],'.index')&&request()->routeIs(str_replace('.index','.*',$i[1])));return $routeActive&&(!$i[2]||$parameter===$i[2]);}))@if($group['label']==='Beranda')<li class="{{ $active?'active':'' }}"><a href="{{ route('admin.dashboard') }}"><i class="fa {{ $group['icon'] }} {{ $active?'text-aqua':'' }}"></i><span>Dashboard</span></a></li>@else<li class="treeview {{ $active?'active menu-open':'' }}"><a href="#"><i class="fa {{ $group['icon'] }} {{ $active?'text-aqua':'' }}"></i><span>{{ $group['label'] }}</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
                <ul class="treeview-menu">@foreach($group['items'] as [$label,$route,$resource])@php($parameter=request()->routeIs('admin.village-content.*')?request()->route('page'):request()->route('resource'))@php($routeActive=request()->routeIs($route)||($route==='admin.village-content.profile'&&request()->routeIs('admin.village-content.profile-edit'))||($route!=='admin.dashboard'&&str_ends_with($route,'.index')&&request()->routeIs(str_replace('.index','.*',$route))))<li class="{{ $routeActive&&(!$resource||$parameter===$resource)?'active':'' }}"><a href="{{ route($route,$resource) }}"><i class="fa fa-circle-o"></i>{{ $label }}</a></li>@endforeach</ul>
                    </li>@endif @endif @endforeach
                </ul>
            </section>
        </aside>
        <div class="content-wrapper">
            <section class="content-header">
                <h1>@yield('page-title',trim($__env->yieldContent('title')))<small>@yield('page-description')</small></h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-home"></i> Beranda</a></li>
                    <li class="active">@yield('title')</li>
                </ol>
            </section>
            <section class="content"><x-admin.alert />@yield('content')</section>
        </div>
        <footer class="main-footer">
            <div class="pull-right hidden-xs"><b>Laravel CMS</b> 1.0</div><strong>Pemerintah Desa Sukomulyo</strong>
        </footer>
        <aside class="control-sidebar control-sidebar-dark">
            <div class="tab-content">
                <div class="tab-pane active" style="padding:15px">
                    <h3 class="control-sidebar-heading">Informasi Sistem</h3>
                    <p>Panel administrasi Website Desa Sukomulyo.</p>
                    <ul class="control-sidebar-menu">
                        <li><a href="{{ route('beranda') }}"><i class="menu-icon fa fa-globe bg-red"></i>
                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading">Website Publik</h4>
                                    <p>Lihat hasil publikasi</p>
                                </div>
                            </a></li>
                    </ul>
                </div>
            </div>
        </aside>
        <div class="control-sidebar-bg"></div>
    </div>
    <script src="{{ asset('admin-assets/bootstrap/js/jquery.min.js') }}"></script>
    <script src="{{ asset('admin-assets/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('admin-assets/bootstrap/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('admin-assets/bootstrap/js/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ asset('admin-assets/bootstrap/js/fastclick.js') }}"></script>
    <script src="{{ asset('admin-assets/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('admin-assets/js/sweetalert2/sweetalert2.all.min.js') }}"></script>@vite('resources/js/admin.js')@stack('scripts')
</body>

</html>
