<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveUserRequest;
use App\Models\Role;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.index', [
            'items' => User::with('role')->latest()->paginate(20),
        ]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('admin.users.form', [
            'item' => new User,
            'roles' => Role::all(),
        ]);
    }

    public function store(SaveUserRequest $request)
    {
        $data = $request->validated();
        User::create($data + ['is_active' => (bool) ($data['is_active'] ?? false)]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna ditambahkan.');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('admin.users.form', [
            'item' => $user,
            'roles' => Role::all(),
        ]);
    }

    public function update(SaveUserRequest $request, User $user)
    {
        $data = $request->validated();

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
}
