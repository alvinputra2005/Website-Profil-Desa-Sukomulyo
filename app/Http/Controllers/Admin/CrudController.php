<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\{ActivityLogger,HtmlSanitizer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CrudController extends Controller
{
 public function __construct(private ActivityLogger $logger,private HtmlSanitizer $sanitizer){}
 private function resource(string $resource): array { $c=config("admin.resources.$resource"); abort_unless($c,404); return $c; }
 private function authorizeResource(array $c): void { abort_unless(auth()->user()->can($c['ability']),403); }
 public function index(Request $r,string $resource){$c=$this->resource($resource);$this->authorizeResource($c);$q=$c['model']::query();if($s=trim((string)$r->query('q'))) $q->where(function($x)use($c,$s){foreach($c['search'] as $i=>$field)$i?$x->orWhere($field,'like',"%$s%"): $x->where($field,'like',"%$s%");});return view('admin.crud.index',['resource'=>$resource,'config'=>$c,'items'=>$q->latest('id')->paginate(15)->withQueryString()]);}
 public function create(string $resource){$c=$this->resource($resource);$this->authorizeResource($c);return view('admin.crud.form',['resource'=>$resource,'config'=>$c,'item'=>new $c['model'],'relations'=>$this->relations($c)]);}
 public function store(Request $r,string $resource){$c=$this->resource($resource);$this->authorizeResource($c);$data=$this->validated($r,$c);$item=DB::transaction(function()use($c,$data,$resource){$item=$c['model']::create($this->prepare($data,$c));$this->logger->log('created',$resource,$item,null,$item->toArray());return $item;});return redirect()->route('admin.resources.edit',[$resource,$item])->with('success',$c['title'].' berhasil ditambahkan.');}
 public function edit(string $resource,int $id){$c=$this->resource($resource);$this->authorizeResource($c);$item=$c['model']::findOrFail($id);return view('admin.crud.form',compact('resource','c','item')+['config'=>$c,'relations'=>$this->relations($c)]);}
 public function update(Request $r,string $resource,int $id){$c=$this->resource($resource);$this->authorizeResource($c);$item=$c['model']::findOrFail($id);$data=$this->validated($r,$c,$id);DB::transaction(function()use($item,$data,$c,$resource){$old=$item->toArray();$item->update($this->prepare($data,$c));$this->logger->log('updated',$resource,$item,$old,$item->fresh()->toArray());});return back()->with('success',$c['title'].' berhasil diperbarui.');}
 public function destroy(string $resource,int $id){$c=$this->resource($resource);$this->authorizeResource($c);$item=$c['model']::findOrFail($id);$this->logger->log('deleted',$resource,$item,$item->toArray());$item->delete();return redirect()->route('admin.resources.index',$resource)->with('success','Data berhasil dihapus.');}
 private function validated(Request $r,array $c,?int $id=null): array {$rules=[];foreach($c['fields'] as $key=>$field)$rules[$key]=str_replace('{id}',(string)($id??'NULL'),$field['rules']??'nullable');return $r->validate($rules);}
 private function prepare(array $data,array $c): array {foreach($c['fields'] as $key=>$field){if(($field['type']??'')==='toggle')$data[$key]=(bool)($data[$key]??false);if(($field['type']??'')==='editor')$data[$key]=$this->sanitizer->clean($data[$key]??'');if(str_ends_with($key,'_json')&&isset($data[$key])&&is_string($data[$key]))$data[$key]=json_decode($data[$key],true);}if(array_key_exists('slug',$c['fields'])&&empty($data['slug']))$data['slug']=Str::slug($data['title']??$data['name']);$model=new $c['model'];$columns=$model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());foreach(['author_id','created_by','updated_by'] as $key)if(in_array($key,$columns,true))$data[$key]=auth()->id();return $data;}
 private function relations(array $c): array {$out=[];foreach($c['fields'] as $key=>$f)if(($f['type']??'')==='relation')$out[$key]=$f['model']::orderBy($f['display'])->get(['id',$f['display']]);return $out;}
}
