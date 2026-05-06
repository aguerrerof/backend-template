<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $perPage = (int)$request->query('perPage', 5);
        $users = User::query()
            ->withTrashed()
            ->where('id', '!=', Auth::id())
            ->orderBy('created_at', 'asc')
            ->paginate(
                in_array($perPage, [5, 10, 25, 50])
                    ? $perPage
                    : 5,
            )
            ->withQueryString();
        return view('users.list', compact('users', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->is_admin = (bool)$request->get('is_admin', false);
        $user->save();
        return redirect('/users');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $user = User::find($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id): RedirectResponse
    {
        $user = User::find($id);
        if ($request->has('name')) {
            $user->name = $request->validated()['name'];
        }
        if ($request->has('email')) {
            $user->email = $request->validated()['email'];
        }
        if ($request->has('password')) {
            $user->password = bcrypt($request->validated()['password']);
        }
        if ($request->has('is_admin')) {
            $user->is_admin = (bool)$request->validated()['is_admin'];
        }
        $user->update();
        return redirect('/users');
    }

    public function deactivate(string $id): RedirectResponse
    {
        User::find($id)->delete();
        return redirect('/users');
    }

    public function activate(string $id): RedirectResponse
    {
        User::withTrashed()->find($id)->restore();
        return redirect('/users');
    }
}
