<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class LoginController extends Controller {
 public function create(){return view('auth.login');}
 public function store(Request $r){$data=$r->validate(['email'=>'required|email','password'=>'required|string','remember'=>'nullable|boolean']); if(!Auth::attempt(['email'=>$data['email'],'password'=>$data['password'],'is_active'=>true],(bool)($data['remember']??false))){ActivityLog::create(['action'=>'login_failed','module'=>'auth','description'=>'Login gagal untuk '.$data['email'],'ip_address'=>$r->ip(),'user_agent'=>$r->userAgent(),'created_at'=>now()]); return back()->withErrors(['email'=>'Email, kata sandi, atau status akun tidak valid.'])->onlyInput('email');} $r->session()->regenerate(); $r->user()->update(['last_login_at'=>now()]); return redirect()->intended(route('admin.dashboard'));}
 public function destroy(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('login')->with('status','Anda telah keluar.');}
}
