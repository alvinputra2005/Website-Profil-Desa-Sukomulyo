<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    private function allow(): void
    {
        abort_unless(auth()->user()->can('manage-users'), 403);
    }

    public function index()
    {
        $this->allow();

        return view('admin.users.index', [
            'items' => User::with('role')->latest()->paginate(20),
        ]);
    }

    public function create()
    {
        $this->allow();

        return view('admin.users.form', [
            'item' => new User,
            'roles' => Role::all(),
        ]);
    }

    public function store(Request $request)
    {
        $this->allow();

        $data = $request->validate($this->rules());
        User::create($data + ['is_active' => (bool) ($data['is_active'] ?? false)]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna ditambahkan.');
    }

    public function edit(User $user)
    {
        $this->allow();

        return view('admin.users.form', [
            'item' => $user,
            'roles' => Role::all(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->allow();

        $data = $request->validate($this->rules($user));

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        abort_if(
            $user->is(auth()->user()) && ! $data['is_active'],
            422,
            'Tidak dapat menonaktifkan akun sendiri.'
        );

        $user->update($data);

        return back()->with('success', 'Pengguna diperbarui.');
    }

    private function rules(?User $user = null): array
    {
        $password = Password::min(12)->letters()->numbers();

        return [
            'role_id' => ['required', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'password' => $user
                ? ['nullable', 'confirmed', $password]
                : ['required', 'confirmed', $password],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
