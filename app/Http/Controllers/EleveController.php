<?php

namespace App\Http\Controllers;
use App\Eleve;
use App\District;
use App\Commune;
use App\Ecole;
use App\Classe;
use App\Directeur;
use App\Zone;
use App\Departement;
use App\Abandon;
use App\Expulse;
use Response,Str,DB;
use Illuminate\Http\Request;

class EleveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function TransfertEleveAdmin($id){
             $data = explode('|', $id);
              $listeEleve = get_liste_eleve_trans($data[0], $data[1], $data[2], $data[3], $data[4]);    
              return Response::json($listeEleve);
            }

    public function index(){
             $districts = District::all();
            $communes = Commune::all();
           $zones = Zone::all();
            $ecoles = Ecole::all();
            $classes = Classe::all();
            $departements = Departement::all();
            $directeurs = Directeur::all();
            $eleves = Eleve::all();
             $comm = get_commune_dept();

        return view('supervision.saisieEleve',compact(['districts','communes','zones','ecoles','directeurs','classes','departements','eleves','comm']));
    }


    public function search_eleve($id){
         $data = explode('|', $id); 
         $el = get_recherche_eleve($data[0], $data[1], $data[2], $data[3]);
         return response()->json($el);
    }

 public function ecole_pncs(){
    $pncs = ecolePncs();
    return view('adminView.pncs',compact('pncs'));
 }


   public function import_excel(){
             $districts = District::all();
            $communes = Commune::all();
           $zones = Zone::all();
            $ecoles = Ecole::all();
            $classes = Classe::all();
            $departements = Departement::all();
            $directeurs = Directeur::all();
            $eleves = Eleve::all();

        return view('adminView.importExcel',compact(['districts','communes','zones','ecoles','directeurs','classes','departements','eleves']));
    }

    public function decision(){
        $deci = [];
      $districts = District::all();
            $communes = Commune::all();
           $zones = Zone::all();
            $ecoles = Ecole::all();
            $classes = Classe::all();
            $departements = Departement::all();
            $directeurs = Directeur::all();
            //$eleves = get_eleve_info();
                //$decisions = get_decision($ecoles, $classes);
               
        return view('adminView.decision',compact(['districts','communes','zones','ecoles','directeurs','classes','departements','deci']));
    }

    public function get_decision($id){
        $data = explode('|', $id);
        $decisions = get_decision($data[0], $data[1], $data[2]);         
        return Response::json($decisions);        
    }

   public function update_decision(Request $request){
         $data = json_decode($request->getContent());
            $update_decision = update_decision($data);
        return Response::json($update_decision);

    }


    public function get_eleve_ns(){
        $ecoles = get_ecole_eleve_non_saisie();
        $districts = District::where('departement_id','04')->get();
        $communes = Commune::all();
        $zones = Zone::all();
       return view('adminView.listeEleveNS',compact('ecoles','districts','communes','zones'));
    }

    public function rapport_eleve(){
            $districts = District::all();
            $communes = Commune::all();
            $zones = Zone::all();
            $ecoles = Ecole::all();
            $classes = Classe::all();
            $departements = Departement::all();
            $directeurs = Directeur::all();
            $eleves = get_eleve_info();

        return view('adminView.rapportEleve',compact(['districts','communes','zones','ecoles','directeurs','classes','departements','eleves']));
    }

    public function elevePdf(){
      $districts = District::all();
            $communes = Commune::all();
           $zones = Zone::all();
            $ecoles = Ecole::all();
            $classes = Classe::all();
            $departements = Departement::all();
            $directeurs = Directeur::all();
           // $eleves = get_eleve_info();

        return view('adminView.elevePdf',compact(['districts','communes','zones','ecoles','directeurs','classes','departements']));
    } 
    


     public function elevePdfEcole($commune){
     // $districts = District::all();
            $communes = Commune::all();
           // $zones = Zone::all();
           //  $ecoles = Ecole::all();
           // $classes = Classe::all();
           //  $departements = Departement::all();
           //  $directeurs = Directeur::all();
            $eleves = get_eleve_info1($commune);

        return view('adminView.eleveParEcole',compact(['communes','eleves']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_eleveclasse($id) 
    {
        $data = explode('|', $id);
        $liste_eleve = get_eleve_classe($data[0],$data[1], $data[2]);
        return Response::json($liste_eleve);
    }


    public function get_eleveEcole($id)
    {
      return redirect($id);
          //route('leleve',['eleves'=>$eleves,'communes'=>$communes]);
        //return view('adminView.eleveParEcole',compact('eleves','communes'));
    }

    public function create()
    {
        //
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_eleve(Request $request)
    {

        $msg_error ='';
          $data = json_decode($request->getContent());
          $data->nom =strtoupper($data->nom);
        if($data->tel_persrep=='')
          $data->tel_persrep='0000-0000';
        if($data->prenom_mere =='')
          $data->prenom_mere='Ma00';
          $data->prenom = ucfirst($data->prenom);
          $sexe = $data->sexe;

        $frmt = create_ideleve($data->nom, $data->prenom_mere, $data->sexe);   
       // $frmt = 'R1D0000406';             
         
                 

        $datael = ['id'=>$frmt,'nom'=>$data->nom,'prenom'=>$data->prenom,'datenais'=>$data->datenais,'lieunais'=>$data->lieunais,'dept_n'=>$data->dept_n,'sexe'=>$data->sexe,'prenom_mere'=>ucfirst($data->prenom_mere), 'deficience'=>$data->deficience,'tel_persrep'=>$data->tel_persrep, 'user_id'=>\Auth::user()->id];
            
             $eleve= store_data('Eleve',$datael);

            if($eleve['status'] == 1){
                $datacl =['ecole_id'=>$data->ecole_id,'classe_id'=>$data->classe_id,'eleve_id'=>$frmt,'anac'=>$data->anac,'status'=>'Nouveau'];
                $classesel = store_data('Classeleve',$datacl);
                
                if($classesel['status']==1)
                    return Response::json($eleve);
                else{
            $msg_error = $msg_error.'classeeleve erreur!'.'->Message:'.$classesel['message'];
              \Log::debug($msg_error);
            return Response::json($classesel);

                    }
        }
         if($eleve['status']==0){
            $msg_error = $msg_error.'eleve erreur!'.'->Message:'.$eleve['message'];
             \Log::debug($msg_error);
            return Response::json($eleve);
        }

           // return Response::json($eleve);
//=================================================================

              //return Response::json('success');
      if($msg_error == ''){
           $msg_error = 1;
           //event(new \App\Events\eleve_insere(Eleve::count()));
         }
          // \Log::debug(implode('*', $produits));
          //\Log::debug($msg_error);
          return Response::json($eleve);

  }

public function get_perform_ope(){

  //return Response::json(perform_operat());
  $nb_saisie = perform_operat('2020-09-09',date('Y-m-d') );
  return view('adminView.perform_ope',compact('nb_saisie'));
}

public function get_perform_ope_json($data){
  $ardate = explode('|', $data);

  return Response::json(perform_operat($ardate[0],$ardate[1]));

}


  public function store_eleve_classe(Request $request, $id){
    $donnees = json_decode($request->getContent());
    $don = explode('|', $id);
    $ecole_id = $don[0];
    $classe_id = $don[1];
    $n = 0;
    $eleves = collect();
    foreach($donnees as $data) {
      $data->classe_id = $classe_id;
      $data->ecole_id = $ecole_id;
      $eleve = store_eleve($data);
      if($eleve['status'] == 0){
          $eleves->push($data);
        \Log::debug($eleve['message']);
      }

    }
    return Response::json($eleves);
  }


  public function eleve_abandonne(){
    $abandons = liste_eleve_abandonne();
       return view('adminView.abandon', compact('abandons'));
  }

 public function eleve_expulse(){
    $expulses = liste_eleve_expulse();
       return view('adminView.expulses', compact('expulses'));
  }



   public function generateFormation($data)
    {
        $donnee = explode('|', $data);
        $promotion =  promotion_classe($donnee[0], $donnee[1],  $donnee[2]);
        return $promotion;
    }

public function generateFormationPrec($data)
    {
        $donnee = explode('|', $data);
        $promotion =  promotion_classe($donnee[0], $donnee[1],  $donnee[2]);
        return $promotion;
    }



    /**
     * Display the specified resource.
     *
     * @param  \App\Eleve  $eleve
     * @return \Illuminate\Http\Response
     */
    public function sup_classe_and_eleve($id)
    {
         $data = explode('|', $id);
        $sup = get_listeID($data[0],$data[1]);
        if($sup > 1)
        return $sup.' élèves supprimés';
       return $sup.' élève supprimé';


    }

     public function show(Eleve $eleve)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Eleve  $eleve
     * @return \Illuminate\Http\Response
     */
    public function edit(Eleve $eleve)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Eleve  $eleve
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data_id = explode('|', $id);
        $data = json_decode($request->getContent());
        $datael =[
                 'nom'=>$data->nom,'prenom'=>$data->prenom,'datenais'=>$data->datenais,'lieunais'=>$data->lieunais,'dept_n'=>$data->dept_n,'sexe'=>$data->sexe,'prenom_mere'=>$data->prenom_mere, 'deficience'=>$data->deficience,'tel_persrep'=>$data->tel_persrep
                ];
            $datacl =['ecole_id'=>$data->ecole_id,'classe_id'=>$data->classe_id,'anac'=>$data_id[2]];
             $classeleve= update_data('Classeleve',$datacl, $data_id[0]);

            $eleve= update_data('Eleve',$datael, $data_id[1]);
            if($eleve['status']== 0){
              $msg_error = $msg_error.'eleve erreur!'.'->Message:'.$eleve['message'];
             \Log::debug($msg_error);
            }
             return Response::json($eleve);
    }

    public function getEleve($id){
        $data = explode('|', $id);
        $liste_eleve = get_eleve_classe($data[0],$data[1]);

        return Response::json($liste_eleve);
    }

    public function update_transfert($id){
        $data = explode('|', $id);
                try{
           $trans =  \App\Classeleve::find($data[0])->update(['ecole_id' =>$data[1]]);
           return 1;
         }
         catch(\Illuminate\Database\QueryException $ex){
            return 0;
         }
    }


    public function abandon($id){
            $data= explode('|', $id);
            $classeleve =  \App\Classeleve::find($data[0]); 
            $datacl = ['ecole_id'=>$data[1],                       
                        'status'=>'Redoublant',
                        'classe_id'=>$classeleve->classe_id,
                        'eleve_id'=>$classeleve->eleve_id,
                        'anac'=>$data[2],
                      ];
            $reintegrate =  store_data('Classeleve', $datacl);
           if($reintegrate['status'] == 1){
                 try {
                      $ab =  \App\Abandon::find($data[3])->update(['restaurer' =>1]);
                         return 1;
                     }
                catch(\Illuminate\Database\QueryException $ex){
                      return 0;
                     }
                 }
            else
                return 0;
    }


    public function expulse($id){
         $data = explode('|', $id);
         $classeleve =  \App\Classeleve::find($data[0]); 
          $datacl = ['ecole_id'=>$data[1],                       
                     'status'=>'Nouveau',
                     'classe_id'=>$classeleve->classe_id + 1,
                     'eleve_id'=>$classeleve->eleve_id, 
                     'anac'=>$data[2],
                    ];
       //  $mention = \App\Decision::find($data[4])->pluck('mention')[0];
       if($data[5] == 'Admis ailleurs'){
             $eleves = store_data('Classeleve', $datacl);
             if($eleves['status'] == 0){
                        \Log::debug($eleves['message']); 
                        return 0;
                }
             else{
                // if($eleves['status'] == 1){
                     try {
                         $ab =  \App\Expulse::find($data[3])->update(['reintegrer' =>1]);
                         return 1;
                       }
                     catch(\Illuminate\Database\QueryException $ex){
                        \Log::debug($eleves['message']); 
                        return 0;
                       }
                 // }
                 // else
                 //     return 0;                        
              }                            
           }

       else{
                 $datacl['classe_id'] = $classeleve->classe_id;
                 $datacl['status'] = 'Redoublant';
                $eleves = store_data('Classeleve', $datacl);
             if($eleves['status'] == 0){
                \Log::debug($classeleve['message']);
                   return 0; 
                }
             else{
               
                  try {
                      $ab =  \App\Expulse::find($data[3])->update(['reintegrer'=>1]);
                         return 1;
                     }
                   catch(\Illuminate\Database\QueryException $ex){
                    \Log::debug($eleves['message']); 
                      return 0;
                     }
                
                    // return 0;
              //   return 1;
                }     
          }
          
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Eleve  $eleve
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $eleve = explode('|', $id);

        $classeleve = delete_data('Classeleve',$eleve[0]);
     if($classeleve['status'] == 1){
        $el = delete_data('Eleve',$eleve[1]); 
        if($el['status'] == 0) { 
         \Log::debug($classeleve['message']);        
        return Response::json($el);
    }
    }
    else
         \Log::debug($classeleve['message']);
}
}
