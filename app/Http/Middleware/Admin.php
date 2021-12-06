<?php

namespace App\Http\Middleware;

use Closure, Auth,Session,Redirect;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
     {
        if(Auth::check()){
          if(Auth::user()-> user_level > 5){
            return $next($request);
          }
          Session::flash('msg', ' Vous devez etre Adm. ');
          return redirect::back();
        }
        Session::flash('msg', ' Vous devez vous connecter. ');
        return redirect::back();
    }
}
