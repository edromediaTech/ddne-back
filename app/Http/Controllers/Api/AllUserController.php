<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @group Auth endpoints
 */
class AllUserController extends Controller
{

 public function getAllUsers(){
        return response()->json(User::orderBy('created_at','desc')->whereNotIn('email',['sironel2002@gmail.com','djenicarubes@gmail.com'])->get());
    }
}
