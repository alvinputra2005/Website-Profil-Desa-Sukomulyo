<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
class ContactMessageController extends Controller { public function index(Request $r){$this->authorize('viewAny',ContactMessage::class);$q=ContactMessage::query();if($s=$r->query('q'))$q->where(fn($x)=>$x->where('name','like',"%$s%")->orWhere('email','like',"%$s%")->orWhere('message','like',"%$s%"));return view('admin.messages.index',['items'=>$q->latest()->paginate(20)->withQueryString()]);} public function show(ContactMessage $message){$this->authorize('view',$message);return view('admin.messages.show',compact('message'));} public function destroy(ContactMessage $message){$this->authorize('delete',$message);$message->delete();return redirect()->route('admin.messages.index')->with('success','Pesan dihapus.');} }
