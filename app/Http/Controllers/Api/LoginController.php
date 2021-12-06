<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Auth endpoints
 */
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }
            $user = \Auth::user();

        $data = ['token' => $request->user()->createToken($request->input('device_name'))->accessToken,
                    'user'     => $request->user(),   'anac' =>  get_current_year()];

        if($user->user_level > 5){
             return response()->json($data);
            }

         if($user->user_level > 4){
            if(get_ip($user->id) != 0){
                $data['ip'] = get_ip($user->id);
                $data['type'] = \App\Insprincipal::find($data['ip'])->type;
                    return response()->json($data);                     
              }
              else{
                $data['ip'] = 0;
                     return response()->json($data); 
                 }
        }

        if($user->user_level > 2)
        {
            $id_inspect = get_inspect();
          
            if($id_inspect != 0){
                $data['inspect'] = $id_inspect;             
                            
             return response()->json($data);
             }   
             else{
                 $data['inspect'] = 0;
              return response()->json($data);
             }  
        }

           if($user->user_level > 1){
                $id_responsable = responsable_exist($user->id);
                     if($id_responsable != 0){
                         $data['responsable'] = $id_responsable;             
                             return response()->json($data);
                         }   
                     else{
                         $data['responsable'] = 0;
                             return response()->json($data);
                         }       
                  }
            return $data;
       

    }

    /**
     * Log the user out of the application.
     *
     * @authenticated
     * @response status=204 scenario="Success" {}
     * @response status=400 scenario="Unauthenticated" {
     *     "message": "Unauthenticated."
     * }
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return $request->wantsJson()
            ? new Response('', 204)
            : redirect('/');
    }

    /**
     * Handle a login request to the application.
     *
     * @bodyParam email email required The email of the user. Example: demo@demo.com
     * @bodyParam password password required The password of the user. Example: password
     *
     * @response status=422 scenario="Validation error" {
     *    "message": "The given data was invalid.",
     *    "errors": {
     *        "email": [
     *            "The email field is required."
     *        ]
     *    }
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }
}
