<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CmsResourceRequest;
use App\Services\{ActivityLogger,HtmlSanitizer,ImageProcessor};
use App\Models\{Gallery,GalleryItem,Media,News,Publication,PublicationAttachment};
use App\Support\ImageUploadRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CrudController extends Controller
{
 public function __construct(private ActivityLogger $logger,private HtmlSanitizer $sanitizer,private ImageProcessor $images){}
 private function resource(string $resource): array { $c=config("admin.resources.$resource"); abort_unless($c,404); return $c; }
 private function ensureResourceAuthorized(array $c,string $ability='viewAny',?object $model=null): void { $this->authorize($ability,$model??$c['model']); }
 public function index(Request $r,string $resource){$c=$this->resource($resource);$this->ensureResourceAuthorized($c);$q=$c['model']::query();if($resource==='news'){$q->with(['category','author','featuredImage']);if($status=$r->query('status'))$q->where('status',$status);else$q->where('status','!=','archived');if($category=$r->query('category'))$q->where('category_id',$category);}if($resource==='galleries'){$q->with(['cover','creator'])->withCount('items');if($status=$r->query('status'))$q->where('status',$status);else$q->where('status','!=','archived');}if($resource==='publications'){if($type=$r->query('type')){abort_unless(in_array($type,['announcement','agenda','document','regulation'],true),404);$q->where('type',$type);$c['title']=['announcement'=>'Pengumuman Desa','agenda'=>'Agenda','document'=>'Dokumen Publik','regulation'=>'Peraturan Desa'][$type];unset($c['columns']['type']);}}if($s=trim((string)$r->query('q'))) $q->where(function($x)use($c,$s){foreach($c['search'] as $i=>$field)$i?$x->orWhere($field,'like',"%$s%"): $x->where($field,'like',"%$s%");});$perPage=in_array($resource,['news','galleries','publications'],true)?10:15;$data=['resource'=>$resource,'config'=>$c,'items'=>$q->latest('id')->paginate($perPage)->withQueryString()];if($resource==='news')$data['categories']=$this->relations($c)['category_id'];$view=match($resource){'news'=>'admin.news.index','galleries'=>'admin.galleries.index',default=>'admin.crud.index'};return view($view,$data);}
 public function create(Request $r,string $resource){$c=$this->resource($resource);$this->ensureResourceAuthorized($c,'create');if($resource==='publications'&&$r->query('type'))$c=$this->publicationFormConfig($c,(string)$r->query('type'));return view(match($resource){'news'=>'admin.news.form','publications'=>'admin.publications.form',default=>'admin.crud.form'},$this->formData($resource,$c,new $c['model']));}
 public function store(CmsResourceRequest $r,string $resource){$c=$this->resource($resource);$data=$r->resourceData();if($resource==='news'){$data=$this->prepareNewsImage($r,$data);if(empty($data['slug']))$data['slug']=$this->uniqueNewsSlug($data['title']);}elseif($resource!=='galleries'){$data=$this->prepareMediaUploads($r,$data,$c,$resource);}if($resource==='publications'&&($data['type']??null)==='regulation'&&empty($data['slug']))$data['slug']=$this->uniquePublicationSlug($data['title']); $item=DB::transaction(function()use($c,$data,$resource,$r){$item=$c['model']::create($this->prepare($data,$c));if($resource==='galleries')$this->syncGalleryItems($r,$item);if($resource==='publications')$this->syncPublicationAttachments($r,$item);$this->logger->log('created',$resource,$item,null,$item->fresh()->toArray());return $item;});return redirect()->route('admin.resources.edit',[$resource,$item])->with('success',$c['title'].' berhasil ditambahkan.');}
 public function edit(string $resource,int $id){$c=$this->resource($resource);$item=$c['model']::findOrFail($id);$this->ensureResourceAuthorized($c,'update',$item);if($resource==='news')$item->load(['featuredImage','category']);if($resource==='publications')$c=$this->publicationFormConfig($c,$item->type);return view(match($resource){'news'=>'admin.news.form','publications'=>'admin.publications.form',default=>'admin.crud.form'},$this->formData($resource,$c,$item));}
 public function update(CmsResourceRequest $r,string $resource,int $id){$c=$this->resource($resource);$item=$c['model']::findOrFail($id);$data=$r->resourceData();if($resource==='news'){$data=$this->prepareNewsImage($r,$data);if(empty($data['slug']))$data['slug']=$item->slug;}elseif($resource!=='galleries'){$data=$this->prepareMediaUploads($r,$data,$c,$resource);}if(array_key_exists('slug',$c['fields'])&&empty($data['slug']))$data['slug']=$item->slug; DB::transaction(function()use($item,$data,$c,$resource,$r){$old=$item->toArray();$item->update($this->prepare($data,$c));if($resource==='galleries')$this->syncGalleryItems($r,$item);if($resource==='publications')$this->syncPublicationAttachments($r,$item);$this->logger->log('updated',$resource,$item,$old,$item->fresh()->toArray());});return back()->with('success',$c['title'].' berhasil diperbarui.');}
 public function destroy(string $resource,int $id){$c=$this->resource($resource);$item=$c['model']::findOrFail($id);$this->ensureResourceAuthorized($c,'delete',$item);$this->logger->log('deleted',$resource,$item,$item->toArray());$item->delete();return redirect()->route('admin.resources.index',$resource)->with('success','Data berhasil dihapus.');}
 public function archive(int $id){$c=$this->resource('news');$item=News::findOrFail($id);$this->ensureResourceAuthorized($c,'update',$item);if($item->status==='archived')return back()->with('success','Artikel sudah berada di arsip.');$old=$item->toArray();$item->update(['status'=>'archived']);$this->logger->log('archived','news',$item,$old,$item->fresh()->toArray());return back()->with('success','Artikel berhasil diarsipkan.');}
 public function archiveGallery(int $id){$c=$this->resource('galleries');$this->ensureResourceAuthorized($c);$item=Gallery::findOrFail($id);if($item->status==='archived')return back()->with('success','Galeri sudah berada di arsip.');$old=$item->toArray();$item->update(['status'=>'archived']);$this->logger->log('archived','galleries',$item,$old,$item->fresh()->toArray());return back()->with('success','Galeri berhasil diarsipkan.');}
 private function prepare(array $data,array $c): array {foreach($c['fields'] as $key=>$field){if(($field['type']??'')==='toggle')$data[$key]=(bool)($data[$key]??false);if(($field['type']??'')==='editor')$data[$key]=$this->sanitizer->clean($data[$key]??'');if(str_ends_with($key,'_json')&&isset($data[$key])&&is_string($data[$key]))$data[$key]=json_decode($data[$key],true);}if(array_key_exists('slug',$c['fields'])&&empty($data['slug']))$data['slug']=Str::slug($data['title']??$data['name']);$model=new $c['model'];$columns=$model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());foreach(['author_id','created_by','updated_by'] as $key)if(in_array($key,$columns,true))$data[$key]=auth()->id();return $data;}
 private function relations(array $c): array {$out=[];foreach($c['fields'] as $key=>$f)if(($f['type']??'')==='relation')$out[$key]=$f['model']::orderBy($f['display'])->get(['id',$f['display']]);return $out;}
 private function publicationFormConfig(array $c,string $type): array {$titles=['announcement'=>'Pengumuman Desa','agenda'=>'Agenda','document'=>'Dokumen Publik','regulation'=>'Peraturan Desa'];abort_unless(isset($titles[$type]),404);$c['title']=$titles[$type];$c['publication_type']=$type;if($type==='regulation')$c['fields']=['title'=>$c['fields']['title']];return $c;}
 private function formData(string $resource,array $c,$item): array { $data=['resource'=>$resource,'config'=>$c,'item'=>$item,'relations'=>$this->relations($c)];$imageFields=collect($c['fields'])->filter(fn($field)=>($field['type']??'')==='image');if($imageFields->isNotEmpty()){ $latest=Media::where('mime_type','like','image/%')->latest()->limit(100)->get();$currentIds=$imageFields->keys()->map(fn($key)=>(int)data_get($item,$key))->filter()->values();$current=$currentIds->isEmpty()?collect():Media::whereIn('id',$currentIds)->get();$data['media']=$current->concat($latest)->unique('id')->values(); }if($resource==='news'&&!isset($data['media']))$data['media']=Media::where('mime_type','like','image/%')->latest()->limit(60)->get();if($resource==='galleries')$data['galleryItems']=$item->exists?$item->items()->with('media')->get():collect();if($resource==='publications')$data['publicationAttachments']=$item->exists?$item->attachments()->with('media')->get():collect();return $data; }
 private function addGalleryItems(Request $r,$gallery,array $extra): array { $files=array_values(array_filter($r->file('gallery_item_uploads',[])));if($r->hasFile('gallery_item_upload'))$files[]=$r->file('gallery_item_upload');if(!$files)return [];$disk=config('filesystems.media_disk','public');$folder=Str::slug($gallery->slug?:$gallery->title)?:'umum';$created=[];foreach($files as $index=>$file){$caption=$extra['gallery_item_captions'][$index]??$extra['gallery_item_caption']??null;$media=Media::create(array_merge($this->images->store($file,'galeri/'.$folder,$disk),['original_name'=>$file->getClientOriginalName(),'disk'=>$disk,'alt_text'=>$caption?:$gallery->title,'uploaded_by'=>auth()->id()]));$created[$index]=GalleryItem::create(['gallery_id'=>$gallery->id,'media_id'=>$media->id,'caption'=>$caption,'display_order'=>$index+1]);}return $created;}
 private function syncGalleryItems(CmsResourceRequest $r,$gallery): void { $extra=$r->safe()->only(['gallery_items','remove_gallery_items','gallery_item_uploads','gallery_item_captions','gallery_sequence','gallery_item_upload','gallery_item_caption']);$remove=collect($extra['remove_gallery_items']??[])->map(fn($id)=>(int)$id);foreach($extra['gallery_items']??[] as $id=>$values){$galleryItem=$gallery->items()->find($id);if(!$galleryItem)continue;if($remove->contains((int)$id)){$galleryItem->delete();continue;}$galleryItem->update($values);} $created=$this->addGalleryItems($r,$gallery,$extra);foreach($extra['gallery_sequence']??[] as $order=>$token){[$type,$id]=array_pad(explode(':',$token,2),2,null);$galleryItem=$type==='existing'?$gallery->items()->find((int)$id):($created[(int)$id]??null);$galleryItem?->update(['display_order'=>$order+1]);}$coverId=$gallery->items()->orderBy('display_order')->orderBy('id')->value('media_id');$gallery->update(['cover_media_id'=>$coverId]);}
 private function syncPublicationAttachments(CmsResourceRequest $r,$publication): void {
    $extra=$r->safe()->only(['publication_attachments','remove_publication_attachments','attachment_uploads','attachment_upload_titles','attachment_sequence']);
    $remove=collect($extra['remove_publication_attachments']??[])->map(fn($id)=>(int)$id);
    foreach($extra['publication_attachments']??[] as $id=>$values){
        $attachment=$publication->attachments()->find((int)$id);
        if(!$attachment)continue;
        if($remove->contains((int)$id)){
            $this->logger->log('deleted','publication-attachments',$attachment,$attachment->toArray());
            $attachment->delete();
            continue;
        }
        $attachment->update($values);
    }
    $created=[];
    if($publication->type==='regulation'&&$r->hasFile('attachment_uploads')){
        $publication->attachments()->get()->each(function($attachment){
            $this->logger->log('deleted','publication-attachments',$attachment,$attachment->toArray());
            $attachment->delete();
        });
    }
    $disk=config('filesystems.media_disk','public');
    $folder=Str::slug($publication->slug?:$publication->title)?:'pengumuman';
    foreach(array_values(array_filter($r->file('attachment_uploads',[]))) as $index=>$file){
        $storedName=Str::uuid().'.pdf';
        $path=$file->storeAs('dokumen-publik/'.$publication->type.'/'.$folder,$storedName,$disk);
        $media=Media::create([
            'original_name'=>$file->getClientOriginalName(),
            'stored_name'=>$storedName,
            'disk'=>$disk,
            'storage_path'=>$path,
            'mime_type'=>'application/pdf',
            'extension'=>'pdf',
            'file_size'=>$file->getSize(),
            'width'=>null,
            'height'=>null,
            'alt_text'=>null,
            'caption'=>null,
            'uploaded_by'=>auth()->id(),
        ]);
        $created[$index]=PublicationAttachment::create([
            'publication_id'=>$publication->id,
            'media_id'=>$media->id,
            'title'=>$extra['attachment_upload_titles'][$index]??null,
            'display_order'=>$publication->attachments()->max('display_order')+1,
        ]);
        $this->logger->log('created','publication-attachments',$created[$index],null,$created[$index]->toArray());
    }
    foreach($extra['attachment_sequence']??[] as $order=>$token){
        [$type,$id]=array_pad(explode(':',$token,2),2,null);
        $attachment=$type==='existing'?$publication->attachments()->find((int)$id):($created[(int)$id]??null);
        $attachment?->update(['display_order'=>$order+1]);
    }
 }
 private function prepareMediaUploads(CmsResourceRequest $r,array $data,array $c,string $resource): array { $imageFields=collect($c['fields'])->filter(fn($field)=>($field['type']??'')==='image');if($imageFields->isEmpty())return $data;$extra=$r->validated();foreach($imageFields as $key=>$field){$base=$this->imageUploadBase($key);$uploadField=$base.'_upload';$altField=$base.'_alt';$removeField='remove_'.$base;if($r->boolean($removeField)){$data[$key]=null;continue;}if($r->hasFile($uploadField)){$file=$r->file($uploadField);$disk=config('filesystems.media_disk','public');$folder=Str::slug($data['slug']??$data['title']??$data['name']??$data['section_key']??$resource)?:'umum';$category=$field['media_category']??'news';$media=Media::create(array_merge($this->images->store($file,$this->mediaDirectory($category,$folder),$disk),['original_name'=>$file->getClientOriginalName(),'disk'=>$disk,'alt_text'=>($extra[$altField]??null)?:($data['title']??$data['name']??null),'uploaded_by'=>auth()->id()]));$data[$key]=$media->id;}elseif(!empty($data[$key])&&array_key_exists($altField,$extra)){$media=Media::find($data[$key]);$media?->update(['alt_text'=>$extra[$altField]]);}}return $data; }
 private function imageUploadBase(string $key): string {return str_ends_with($key,'_id')?substr($key,0,-3):$key;}
 private function mediaDirectory(string $category,string $name): string {$root=match($category){'gallery'=>'galeri','officials'=>'perangkat-desa','banners'=>'banner','documents'=>'dokumen-publik','map'=>'peta','profile'=>'profil',default=>'berita'};return $root.'/'.(Str::slug($name)?:'umum');}
 private function prepareNewsImage(CmsResourceRequest $r,array $data): array { $extra=$r->validated();if($r->boolean('remove_featured_image'))$data['featured_image_id']=null;if($r->hasFile('featured_image_upload')){$file=$r->file('featured_image_upload');$disk=config('filesystems.media_disk','public');$folder=Str::slug($data['slug']??$data['title']??'')?:'berita-baru';$media=Media::create(array_merge($this->images->store($file,'berita/'.$folder,$disk),['original_name'=>$file->getClientOriginalName(),'disk'=>$disk,'alt_text'=>($extra['featured_image_alt']??null)?:($data['title']??null),'uploaded_by'=>auth()->id()]));$data['featured_image_id']=$media->id;}elseif(!empty($data['featured_image_id'])&&array_key_exists('featured_image_alt',$extra)){$media=Media::find($data['featured_image_id']);$media?->update(['alt_text'=>$extra['featured_image_alt']]);}return $data; }
 private function uniqueNewsSlug(string $title): string {$base=Str::slug($title)?:'artikel';$slug=$base;$suffix=2;while(News::withTrashed()->where('slug',$slug)->exists())$slug=$base.'-'.$suffix++;return $slug;}
 private function uniquePublicationSlug(string $title): string {$base=Str::slug($title)?:'peraturan-desa';$slug=$base;$suffix=2;while(Publication::withTrashed()->where('slug',$slug)->exists())$slug=$base.'-'.$suffix++;return $slug;}
 public function trash(){ $c=$this->resource('news');$this->ensureResourceAuthorized($c);return view('admin.news.trash',['items'=>News::onlyTrashed()->with(['category','author','featuredImage'])->latest('deleted_at')->paginate(15)]); }
 public function restore(int $id){$c=$this->resource('news');$this->ensureResourceAuthorized($c);$item=News::onlyTrashed()->findOrFail($id);$item->restore();$this->logger->log('restored','news',$item);return back()->with('success','Artikel berhasil dipulihkan.');}
 public function forceDelete(int $id){$c=$this->resource('news');$this->ensureResourceAuthorized($c);$item=News::onlyTrashed()->findOrFail($id);$this->logger->log('force_deleted','news',$item,$item->toArray());$item->forceDelete();return back()->with('success','Artikel dihapus permanen.');}
 public function emptyTrash(){ $c=$this->resource('news');$this->ensureResourceAuthorized($c);News::onlyTrashed()->get()->each(function(News $item){$this->logger->log('force_deleted','news',$item,$item->toArray());$item->forceDelete();});return back()->with('success','Tempat sampah dikosongkan.');}
 public function galleryTrash(Request $r){$c=$this->resource('galleries');$this->ensureResourceAuthorized($c);$q=Gallery::onlyTrashed()->with(['cover','creator'])->withCount('items');if($s=trim((string)$r->query('q')))$q->where(function($query)use($s){$query->where('title','like',"%$s%")->orWhere('slug','like',"%$s%");});return view('admin.galleries.trash',['items'=>$q->latest('deleted_at')->paginate(15)->withQueryString()]);}
 public function restoreGallery(int $id){$c=$this->resource('galleries');$this->ensureResourceAuthorized($c);$item=Gallery::onlyTrashed()->findOrFail($id);$item->restore();$this->logger->log('restored','galleries',$item);return back()->with('success','Galeri berhasil dipulihkan.');}
 public function forceDeleteGallery(int $id){$c=$this->resource('galleries');$this->ensureResourceAuthorized($c);$item=Gallery::onlyTrashed()->findOrFail($id);$this->logger->log('force_deleted','galleries',$item,$item->toArray());$item->forceDelete();return back()->with('success','Galeri dihapus permanen.');}
 public function emptyGalleryTrash(){$c=$this->resource('galleries');$this->ensureResourceAuthorized($c);Gallery::onlyTrashed()->get()->each(function(Gallery $item){$this->logger->log('force_deleted','galleries',$item,$item->toArray());$item->forceDelete();});return back()->with('success','Tempat sampah galeri dikosongkan.');}
}
