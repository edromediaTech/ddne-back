<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Cache;
use Illuminate\Support\Facades\Auth;
use Validator, Session;

class UserController extends Controller
{
    function edit_user(Request $request, $id){
        $data = json_decode($request->getContent());
        try{
           $user = User::find($id)->update(['name'=>$data->name, 'password' =>bcrypt($data->password)]);
           return 1;
         }
         catch(\Illuminate\Database\QueryException $ex){
            return 0;
         }
         
    }

    public function index(Request $request)
    { 
        $users = User::whereNotNull('last_seen')
                        ->orderBy('created_at', 'DESC')->whereNotIn('email',['sironel2002@gmail.com','djenicarubes@gmail.com','lynceerubes@gmail.com'])
                        ->get();
     
            $data_user = [];
           foreach ($users as $user) {
            $ut = $user;
             $ls = \Carbon\Carbon::parse($user->last_seen)->diffForHumans();
             if(Cache::has('user-is-online-'. $user->id))
                $statut = 'Online';
            else
                $statut = 'Offline';
            $ut['ls'] = $ls;
            $ut['statut'] = $statut;
             array_push($data_user, $ut);

            }
        return response()->json($data_user);

    }

   public function login(Request $request){
       $logindata = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
       ]);
       if(!auth()->attempt($logindata)){
        return response(['message'=>'Invalid credentials']);
       }
       $accessToken = auth()->user()->createToken('authToken')->accessToken;
       $user = auth()->user();
       if ($user->user_level == 2) {
           $message = 'Salut inspecteur';
       }else{
         $message = 'Vous n\'etes pas un inspecteur';
       }
       return response(['user' => $user, 'access_token' => $accessToken, 'message' => $message]);
    }


  public function get_ecoles($inspecteur_id){
    $ecoles = DB::table('notes')
                ->select('etudiants.id','nom','prenom','cours.libelle','cour_id', DB::raw('SUM(note) as note'), DB::raw('SUM(pourcentage) as pourcentage'))
                ->where('valider','=',true)
                ->where('session','=',$session)
                ->join('etudiants','etudiants.id','notes.etudiant_id')
                ->join('cours','cours.id','notes.cour_id')
                ->groupBy('etudiants.id','nom','prenom','cour_id','cours.libelle')
                ->get();

                $accessToken = auth()->user()->createToken('authToken')->accessToken;
                return response(['ecoles' => $ecoles, 'access_token' => $accessToken]);

  }

  public function getUsers(){
       Session::put('menactive','2');
    $privilege = array('User', 'Operateur','Dir.','IZ','Coach','IP','Admin');
    $emailAuth = ['sironel2002@gmail.com','djenicarubes@gmail.com','lynceerubes@gmail.com'];
    $users=User::whereNotIn('email',$emailAuth)->orderBy('created_at', 'desc')->paginate(10);
    $nbuser=User::whereNotIn('email',$emailAuth)->count();
    return view('adminView.user')->with('users', $users)->with('privilege',$privilege)->with('nbuser',$nbuser);
  }

  public function bloquer($id){
    $user = User::find($id);
    $usercurrent = auth()->user();
    if ($id <>$usercurrent->id){
          if ($user->lock == true){
            $user->lock = false;
          }
          else{
            $user->lock = true;
          }

       $user->save();
    }
    return redirect()->back();
  }

  //  public function editPrivileges($id){
  //    $user = User::find($id);
  //    $usercurrent = auth()->user();
  //   if ($id <>$usercurrent->id){
  //         if ($user->userlevel== 0){
  //           $user->userlevel= 5;
  //         }
  //         else{
  //           $user->userlevel = 0;
  //         }

  //      $user->save();
  //   }
  //   return redirect()->back();
  // }

    public function editPrivileges($id){
        $data = explode('|', $id);
         
     $user = User::find($data[0]);
  
        $user->user_level= $data[1];
        $user->save();
        return response()->json($user);


  }

   public function destroy($id)
    {try {
        User::destroy($id);
        return redirect('/get-users')->with('success',' Utilisateur supprimé avec succès.');
         } catch ( \Exception $e) {

         return redirect('/get-users')->with('error',' On ne peut pas supprimer cet utilisateur.');
       }
    }


    // public $successStatus = 200;


    // public function login(Request $request){
    //     if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
    //         $user = Auth::user();
    //         $success['token'] =  $user->createToken('MyApp')-> accessToken;
    //         return response()->json(['success' => $success], $this-> successStatus);
    //     }
    //     else{
    //         return response()->json(['error'=>'Unauthorised'], 401);
    //     }
    // }


//     public function register(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'name' => 'required',
//             'email' => 'required|email',
//             'password' => 'required',
//             'c_password' => 'required|same:password',
//         ]);
// if ($validator->fails()) {
//             return response()->json(['error'=>$validator->errors()], 401);
//         }
// $input = $request->all();
//         $input['password'] = bcrypt($input['password']);
//         $user = User::create($input);
//         $success['token'] =  $user->createToken('MyApp')-> accessToken;
//         $success['name'] =  $user->name;
// return response()->json(['success'=>$success], $this-> successStatus);
//     }


//     public function details()
//     {
//         $user = Auth::user();
//         return response()->json(['success' => $user], $this-> successStatus);
//     }
}
