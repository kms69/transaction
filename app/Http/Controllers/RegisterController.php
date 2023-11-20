<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{

    protected function register(Request $request)
    {

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
//            'password' => 'required|confirmed',
//            'mobile' => 'required|string|max:11|unique:users',

        ]);
        $input = $request->all();
        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);
        $userRole = Role::where('name', 'user')->first();
        $user->roles()->attach($userRole);

        $token = $user->createToken('API Token')->plainTextToken;

        return response(['user' => $user, 'token' => $token]);


    }
}
