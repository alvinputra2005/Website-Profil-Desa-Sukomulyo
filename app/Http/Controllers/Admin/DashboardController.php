<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{ActivityLog,ContactMessage,Gallery,Media,News,Publication,StatisticDataset};
class DashboardController extends Controller { public function __invoke(){return view('admin.dashboard',['stats'=>['Berita'=>News::count(),'Publikasi'=>Publication::count(),'Galeri'=>Gallery::count(),'Media'=>Media::count(),'Dataset'=>StatisticDataset::count(),'Pesan'=>ContactMessage::count()],'messages'=>ContactMessage::latest()->limit(5)->get(),'activities'=>ActivityLog::with('user')->latest('created_at')->limit(8)->get()]);} }
