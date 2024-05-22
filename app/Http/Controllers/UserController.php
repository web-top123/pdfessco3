<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserEditRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return auth()->user();
    }

    public function update(UserUpdateRequest $request)
    {

        auth()->user()->update($request->all());
        $user = auth()->user();
        $user->admin = !!$user->can('manage');
        $user->superadmin = !!$user->can('create_admins');

        return $user;
    }

    public function reset(ResetPasswordRequest $request)
    {

        auth()->user()->update([
            'password' => bcrypt($request->password),
        ]);
        return response()->json('Password changed');
    }

    // public function create($admin = null, UserCreateRequest $request)
    // {

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => bcrypt($request->password),
    //     ]);

    //     if($admin) {

    //         $user->roles()->attach(Role::where('name', 'admin')->first()->id);

    //     }

    //     return response()->json('User created');

    // }


    public function create(UserCreateRequest $request)
    {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);


        return response()->json('User created');

    }

    // public function edit(UserEditRequest $request)
    // {

    //     $user = User::findOrFail($request->id);
    //     $user->name = $request->name;
    //     $user->email = $request->email;
    //     $user->save();

    //     return response()->json('User updated');
    // }


    public function edit(UserEditRequest $request)
    {
        $user = User::findOrFail($request->id);

        // Verify the old password
        // if (!Hash::check($request->old_password, $user->password)) {
        //     //return response()->json($request->old_password);
        //     return response()->json(['errors' => ['old_password' => ['The old password is incorrect.']]], 422);
        // }

        // Update the user details
        $user->name = $request->name;
        $user->email = $request->email;

        // Update the password if a new password is provided
        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return response()->json('User updated');
    }

    public function delete(Request $request)
    {
        $users = User::query();
        $users->whereIn('id', $request->users)->delete();
        return response()->json('Users deleted');
    }
}
