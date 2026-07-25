<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
class ContactMessageController extends Controller { public function index(Request $r){abort_unless(auth()->user()->can('manage-content'),403);$q=ContactMessage::query();if($s=$r->query('q'))$q->where(fn($x)=>$x->where('name','like',"%$s%")->orWhere('email','like',"%$s%")->orWhere('message','like',"%$s%"));return view('admin.messages.index',['items'=>$q->latest()->paginate(20)->withQueryString()]);} public function show(ContactMessage $message){abort_unless(auth()->user()->can('manage-content'),403);return view('admin.messages.show',compact('message'));} public function destroy(ContactMessage $message){abort_unless(auth()->user()->can('manage-content'),403);$message->delete();return redirect()->route('admin.messages.index')->with('success','Pesan dihapus.');} }
