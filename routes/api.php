<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/test', function(){
    return 'Server ok...';     
});


Route::get('/get-anac', function(){
   return session_new_year();        
});



// statistisques =======================================
 


Route::get('/get-all-info',function(){  
                $anac = session_new_year();
             $ecoles =\App\Ecole::all()->count(); 
             $enseignants =\App\Enseignant::all()->count(); 
             $eleves = \App\Classeleve::where('anac', $anac)->count();    
             $filles = \DB::table('classeleves')
                        ->join('eleves', 'classeleves.eleve_id','eleves.id')->where('sexe',0)
                        ->where('anac', $anac)->count();
               $elevedis = stat_eleve_par_district($anac);    
               $ecoledis = stat_ecole_par_district();  
               $deci = stat_decision($anac); 
              $secteur =  get_eleveSecteur($anac); 
          return response()->json(['anac'=>$anac,'filles'=>$filles, 'ecoles'=>$ecoles, 'eleves'=>$eleves, 'ecoledis'=>$ecoledis, 'elevedis'=>$elevedis,'enseignants'=>$enseignants, 'deci'=>$deci, 'secteur'=>$secteur]);
             });


// register =================================
Route::post('register', 'nRegisterController@register')->name('register');
  
//Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/get-rapportecole/{id}', 'EcoleController@rapportEcole');

//}) 


Route::group(['middleware' => ['auth:api','ip']], function () {
    Route::get('/get-info-ip/{id}',function($id){              
            $type = \App\Insprincipal::where('user_id', $id)->get()[0];
                 $eleves = stat_elevepar_district($id,$type->type);
                 $ecoles = stat_ecolepar_district($id,$type->type);
                  $ecole_zone =  stat_ecoleZone_district($id,$type->type); 
          return response()->json(['eleves'=>$eleves, 'ecoles'=>$ecoles, 'ecole_zone'=>$ecole_zone, 'type'=>$type->type]);
    });


    Route::get('/stat-ecole-ip/{id}',function($id){                 
               $type = \App\Insprincipal::where('user_id', $id)->get()[0];  
            $eleves = stat_eleveTotal_district($id,$type->type);
         return response()->json($eleves);
    });

Route::get('/zone-ip/{id}',function($id){                 
            $zones= zone_par_district($id);
         return response()->json($zones);
    });

Route::get('/ecole-ip/{id}',function($id){ 
                $data = explode('|', $id);                
            $ecoles = get_ecole_by_inspecteur($data[0], $data[1]);
         return response()->json($ecoles);
    });

Route::post('store-ip', 'InsprincipalController@store')->name('storeIp');

});



 // ============== inspecteur =====================================================
Route::group(['middleware' => ['auth:api','inspect']], function () {
    Route::post('store-inspect', 'InspecteurController@storeInspect')->name('storeInspect');
    Route::patch('valider-transfert/{id}', 'TransfertController@valider')->name('validerTransfert');
// stat inspecteur =============================
   Route::get('/get-info-inspect/{user_id}',function($user_id){  

           $ecoles = stat_ecole_zone_insp($user_id);
           $eleves = stat_eleve_zone_insp($user_id);
          $filles = stat_fille_zone_insp($user_id);
          $district =  get_district_inspecteur($user_id);
         return response()->json(['filles'=>$filles, 'ecoles'=>$ecoles, 'eleves'=>$eleves, 'district'=>$district]);
    });

  Route::get('/get-ecole-inspect/{user_id}',function($user_id){        
        $total = stat_eleveTotal_zone_insp($user_id);
          return response()->json($total);
    });

   Route::get('/select-liste-ecole-inspect/{user_id}',function($user_id){     
        $ecoles = select_ecole_zone_insp($user_id);
          return response()->json($ecoles);
   });

    Route::get('/get-liste-ecole-inspect/{user_id}',function($user_id){     
        $ecoles = liste_ecole_zone_insp($user_id);
          return response()->json($ecoles);
   });

   Route::get('/get-eleve-valider/{id}',function($id){            
        $accept = get_eleve_valider($id);
          return response()->json($accept);
   });


});



// ============================ super ====================================================

//Route::group(['middleware' => ['auth:api','sup']], function () {

 Route::get('/get-ecole', 'EcoleController@listeEcole');
   
     Route::get('/get-rapportenseignant/{id}', 'EcoleController@rapportEnseignant');
     Route::get('/get-text/{id}', 'EcoleController@get_text');
     Route::get('/get-enseignant', 'EcoleController@get_enseignant');
   


    Route::get('/get-niveauens',function(){
                $niveau = get_niveau(1);
                return response()->json($niveau);
             });

 Route::get('/liste-reqecole',function(){
                    $listes = get_liste_ecole(0,0,0,-1,0);
              return   response()->json($listes);
             });  


 Route::get('/liste-responsable',function(){
              return  valider_responsable();
             });     
   


//});
// ============================ operateur ====================================================

Route::group(['middleware' => ['auth:api','ope']], function () {
 Route::get('/departement', function(){        
         return get_dept();
    });

 Route::get('/liste-annee', function(){        
         return  get_liste_annee();
    });


 Route::get('/get-departement/{dept_id}', function($dept_id){
        $dept = get_district_by_dept($dept_id);
         return $dept;
    });

  Route::get('/get-district/{district_id}', function($district_id){
      $dept = get_commune_by_district($district_id);
         return $dept;
    });

  Route::get('/get-commune/{commune_id}', function($commune_id){
      return get_zone_by_commune($commune_id);
         
    });
  Route::get('/get-zone/{zone_id}', function($zone_id){
      return get_ecole_by_zone($zone_id);
     });

 Route::get('/get-ecole/{ecole_id}',function($ecole_id){
      return get_niveau_by_ecole($ecole_id);
     }); 
 Route::get('/get-classe/{type}',function($type){          
    return get_classe($type);
});

Route::get('/get-commune-dept/{commune_id}', function($commune_id){
      return get_commune_by_dept($commune_id);         
    });

Route::get('/get-ecole-commune/{ecole_id}', function($ecole_id){
      return get_ecole_by_commune($ecole_id);
         
    });

 Route::get('/get-enseignant-ecole/{ecole_id}',function($ecole_id){
      return get_enseignant_by_ecole($ecole_id);
     }); 
Route::get('/get-enseignant-matiere/{enseignant_id}',function($enseignant_id){
      return get_matiere_by_enseignant($enseignant_id);
     }); 

 Route::get('/getEcole',function(){          
    return \App\Ecole::select('id as value', 'nom as text')->orderBy('nom','asc')->get();
});

 Route::get('/get-eleve/{id}',function($id){
        $data = explode('|', $id);
      return  response()->json(get_eleve_classe($data[0],$data[1], $data[2]));
     });

 Route::patch('/eleve-trans/{id}','TransfertController@TransfertAdmin');
 Route::get('/get-eleve-trans/{id}','EleveController@TransfertEleveAdmin');
 Route::get('/get-etat','EcoleController@get_etat');
Route::post('eleve-store','EleveController@store_eleve')->name('eleveStore');
Route::patch('eleve-edit/{id}','EleveController@update')->name('eleveEdit');
Route::delete('eleve-delete/{id}','EleveController@destroy')->name('eleveDelete');
Route::get('get-decision/{id}', 'EleveController@get_decision')->name('getdecision'); 
Route::patch('update-decision', 'EleveController@update_decision')->name('updatedecision');
 Route::post('/store-data-ecole', 'EcoleController@store');
 Route::get('/generate-formation/{id}', 'EleveController@generateFormation');
 Route::get('/search-eleve/{id}', 'EleveController@search_eleve');
 Route::patch('/update-eleve-trans/{id}', 'EleveController@update_transfert');


  Route::get('/get-info-prec',function(){ 
            $anprec = annee_prec();  
             $ecoles =\App\Ecole::all()->count(); 
             $enseignants =\App\Enseignant::all()->count(); 
             $eleves = \App\Classeleve::where('anac', annee_prec())->count();    
             $filles = \DB::table('classeleves')
                        ->join('eleves', 'classeleves.eleve_id','eleves.id')->where('sexe',0)
                        ->where('anac', annee_prec())->count();
               $elevedis = stat_eleve_par_district_anprec();    
               $ecoledis = stat_ecole_par_district();  
               $deci = stat_decision_anprec(); 
              $secteur =  get_eleveSecteur_anprec(); 
          return response()->json(['anprec'=>$anprec,'filles'=>$filles, 'ecoles'=>$ecoles, 'eleves'=>$eleves, 'ecoledis'=>$ecoledis, 'elevedis'=>$elevedis,'enseignants'=>$enseignants, 'deci'=>$deci, 'secteur'=>$secteur]);
             });

});


// ============================ directeur ====================================================

Route::group(['middleware' => ['auth:api','direct']], function () {

    Route::get('/stat-ecole',function(){
                    $ecoles = stat_eleveTotal();
              return response()->json($ecoles);
             });
    
  
 Route::get('/total-eleve',function($ecole_id, $niveau, $anac){
      return total_eleve_par_classe($ecole_id, $niveau, $anac);
     });

 Route::get('/get-data/{id}',function($id){
      return  response()->json(get_data_ecole($id));
     });

 Route::get('/get-transfert-pendant/{id}',function($id){
      return  get_tranfert_pendant($id);
     }); 

 Route::get('/get-id-transfert/{id}',function($id){
      return  get_id_transfert($id);
     });

 Route::get('/get-eleve-accepter/{id}',function($id){
      return get_eleve_accepter($id);
     });

 

 Route::get('/total-eleve/{id}',function($id){
      $data = explode('|', $id);
      $fille = total_fille_par_classe($data[0], $data[1], $data[2]);
      $total = total_eleve_par_classe($data[0], $data[1], $data[2]);
         return response()->json(['fille'=>$fille, 'total'=>$total]);
    });

Route::post('cert-trans','TransfertController@store')->name('certTrans');
Route::patch('accepter/{id}','TransfertController@update')->name('accepter');
Route::post('store-suggestion','SuggestionController@store')->name('storesuggestion');
Route::get('/check-responsable','EcoleresponsableController@check-responsable')->name('checkresponsable');
Route::get('/get-classe-responsable/{id}','EcoleresponsableController@liste_classe')->name('listeClasse');  
Route::post('store-responsable','EcoleresponsableController@store_responsable')->name('storeresponsable');
Route::delete('delete-group-trans/{id}','TransfertController@destroy_group')->name('delgrouptrans');

 });

   // ====== admin ===============================

Route::group(['middleware' => ['auth:api','admin']], function () {
    Route::patch('/update-user', 'HomeController@updateUser')->name('updateuser');
    Route::patch('/edit-user/{id}', 'UserController@edit_user');
    Route::patch('/user-level/{id}', 'UserController@editPrivileges');
    Route::get('user/delete/{id}', 'UserController@destroy');
    Route::get('get-suggestion','SuggestionController@index')->name('getsuggestion');
    Route::patch('update-suggestion/{id}','SuggestionController@update_lu')->name('updatesuggestion');
    Route::delete('delete-suggestion/{id}','SuggestionController@destroy')->name('deletesuggestion');
    Route::delete('delete-group-suggestion/{id}','SuggestionController@destroy_group')->name('delgroupsuggestion');
    
    Route::get('/generate-formation-prec/{id}', 'EleveController@generateFormationPrec');

    Route::get('/get-info/{anac}',function($anac){  
   
             $ecoles =\App\Ecole::all()->count(); 
             $enseignants =\App\Enseignant::all()->count(); 
             $eleves = \App\Classeleve::where('anac', $anac)->count();    
             $filles = \DB::table('classeleves')
                        ->join('eleves', 'classeleves.eleve_id','eleves.id')->where('sexe',0)
                        ->where('anac', $anac)->count();
               $elevedis = stat_eleve_par_district($anac);    
               $ecoledis = stat_ecole_par_district();  
               $deci = stat_decision($anac); 
              $secteur =  get_eleveSecteur($anac); 
          return response()->json(['filles'=>$filles, 'ecoles'=>$ecoles, 'eleves'=>$eleves, 'ecoledis'=>$ecoledis, 'elevedis'=>$elevedis,'enseignants'=>$enseignants, 'deci'=>$deci, 'secteur'=>$secteur]);
             });

     
    Route::patch('/update-responsable/{id}',function($id){
              return  update_responsable($id);
             }); 

    Route::get('/get-performance',function(){
              return  get_performance_op();
             });
 
  Route::get('/get-perform-decisions',function(){
              return  get_performance_decisions();
             });
  Route::get('/set-anac/{anac}',function($anac){
              return  set_annee($anac);
             });

     Route::get('online-user', 'UserController@index'); 
   
    });



// api namespace =====================================

Route::group(['namespace' => 'Api', 'as' => 'api.'], function () {
    Route::post('login', 'LoginController@login')->name('login');

    // auth api ===========================================

    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('email/verify/{hash}', 'VerificationController@verify')->name('verification.verify');
        Route::get('email/resend', 'VerificationController@resend')->name('verification.resend');
        Route::get('user', 'AuthenticationController@user')->name('user');
        Route::post('logout', 'LoginController@logout')->name('logout');  
         Route::get('get-all-users','AllUserController@getAllUsers')->name('getallusers'); 

    });

    //https://stackoverflow.com/questions/39414956/laravel-passport-key-path-oauth-public-key-does-not-exist-or-is-not-readable/57075696

});
 