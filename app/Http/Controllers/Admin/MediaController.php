<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Media,News};
use App\Services\{ActivityLogger,ImageProcessor};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class MediaController extends Controller {
 public function __construct(private ActivityLogger $logger,private ImageProcessor $images){}
 private function allow(){abort_unless(auth()->user()->can('manage-media'),403);}
 public function index(Request $r){$this->allow();$q=Media::query();if($s=$r->query('q'))$q->where('original_name','like',"%$s%");return view('admin.media.index',['items'=>$q->latest()->paginate(24)->withQueryString()]);}
 public function store(Request $r){$this->allow();$data=$r->validate(['file'=>'required|file|max:10240|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,csv','alt_text'=>'nullable|string|max:255','caption'=>'nullable|string|max:2000']);$file=$data['file'];$attributes=str_starts_with((string)$file->getMimeType(),'image/')?$this->images->store($file,'cms/'.now()->format('Y/m')):$this->storeDocument($file);$media=Media::create(array_merge($attributes,['original_name'=>$file->getClientOriginalName(),'disk'=>'public','alt_text'=>$data['alt_text']??null,'caption'=>$data['caption']??null,'uploaded_by'=>auth()->id()]));$this->logger->log('uploaded','media',$media);return back()->with('success','Media berhasil diunggah.');}
 public function editorUpload(Request $r){$this->allow();$data=$r->validate(['image'=>'required|image|mimes:jpg,jpeg,png,webp|max:5120']);$file=$data['image'];$media=Media::create(array_merge($this->images->store($file,'cms/'.now()->format('Y/m')),['original_name'=>$file->getClientOriginalName(),'disk'=>'public','alt_text'=>pathinfo($file->getClientOriginalName(),PATHINFO_FILENAME),'uploaded_by'=>auth()->id()]));$this->logger->log('uploaded','media',$media);return response()->json(['url'=>$media->url,'alt'=>$media->alt_text]);}
 public function destroy(Media $media){$this->allow();$used=collect(['village_profile_sections.image_id','officials.photo_id','news.featured_image_id','publications.featured_image_id','publication_attachments.media_id','map_features.photo_id','galleries.cover_media_id','gallery_items.media_id'])->contains(function($ref)use($media){[$table,$column]=explode('.',$ref);return \DB::table($table)->where($column,$media->id)->exists();});$inlineUsed=News::withTrashed()->where('content','like','%/storage/'.addcslashes($media->storage_path,'%_\\').'%')->exists();if($used||$inlineUsed)return back()->withErrors(['media'=>'Media masih digunakan dan tidak dapat dihapus.']);Storage::disk($media->disk)->delete(array_filter([$media->storage_path,$media->thumbnail_path]));$this->logger->log('deleted','media',$media);$media->delete();return back()->with('success','Media dihapus.');}
 private function storeDocument($file): array {$path=$file->store('cms/'.now()->format('Y/m'),'public');return ['stored_name'=>basename($path),'storage_path'=>$path,'mime_type'=>$file->getMimeType(),'extension'=>strtolower($file->extension()),'file_size'=>$file->getSize(),'width'=>null,'height'=>null];}
}
