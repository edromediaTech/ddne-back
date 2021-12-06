<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class nRegisterController extends Controller
{
      protected $redirectTo = RouteServiceProvider::HOME;


    public function __construct()
    {
        $this->middleware('guest');
    }


    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }


    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }


    protected function registered(Request $request, $user)
    {
        return response()->json([
            'token'    => $user->createToken($request->input('device_name'))->accessToken,
            'user'     => $request->user()
        ]);
    }

    // get all users

    public function getAllUsers(){
        return response()->json(User::orderBy('name')->whereNotIn('email',['sironel2002@gmail.com','djenicarubes@gmail.com'])->get());
    }

    public function register(Request $request)
    {
        $user = new User;
       $user->name = $request->get('name');
        $user->email = $request->get('email');
       $user->password = Hash::make($request->get('password'));
       $user->api_token = Str::random(60);
       $user->save();
    }
}
