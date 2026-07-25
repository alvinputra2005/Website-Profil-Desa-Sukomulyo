<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
class PasswordController extends Controller {
 public function request(){return view('auth.forgot-password');}
 public function email(Request $r){$r->validate(['email'=>'required|email']);$status=Password::sendResetLink($r->only('email'));return $status===Password::RESET_LINK_SENT?back()->with('status',__($status)):back()->withErrors(['email'=>__($status)]);}
 public function reset(Request $r,string $token){return view('auth.reset-password',['token'=>$token,'email'=>$r->query('email')]);}
 public function update(Request $r){$data=$r->validate(['token'=>'required','email'=>'required|email','password'=>'required|confirmed|min:8']);$status=Password::reset($data,function(User $u,string $p){$u->forceFill(['password'=>$p,'remember_token'=>Str::random(60)])->save();event(new PasswordReset($u));});return $status===Password::PASSWORD_RESET?redirect()->route('login')->with('status',__($status)):back()->withErrors(['email'=>__($status)]);}
}
