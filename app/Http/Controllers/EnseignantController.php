<?php

namespace App\Http\Controllers;

use App\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB, Response;
use App\Ecole;
use App\Matiere;
use App\Affectation;
use App\Funiversitaire;
use App\Fprofessionnelle;
use App\Fsouhaitee;
use App\Zone;
use App\Statut;
use App\Classe;
use App\District;
use App\Commune;

class EnseignantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */




private function format_prof($prof, $frmt){
     $stat =[
              'enseignant_id'=> $frmt,
              'statut'=> $prof->statut,             
              ];
       $statut = store_data('Statut', $stat);
       if($statut['status']==0)
                    return 0;
           
    // ==================================Renseignement academique ====================
      $rac =[
            'enseignant_id'=> $frmt,
            'nUniversitaire'=> $prof->nUniversitaire,
            'nClassique'=>$prof->nClassique
           ];
        $racademique = store_data('Racademique', $rac);
     if($racademique['status']==0)
                    return 0;

//============================================ chaire =============================
if($prof->statut == '3'){
       $chaire=[
        'enseignant_id'=> $frmt,
        'type_chaire'=>$prof->type_chaire
       ];
       $chaires = store_data('Chaire', $chaire);
      if($chaires['status']==0)
                   return 0;

//======================================Finance ===================================

  if($prof->statut =='3' || $prof->statut =='2'){
      $fin =[
        'enseignant_id'=> $frmt,
        'type'=> $prof->statut,
        'code_budgetaire'=> $prof->code_budgetaire,
        'date_nomination'=> $prof->date_nomination
       ];

    $finance = store_data('Finance', $fin);
      if($finance['status']==0)
                    return 0;
}
}


//====================================Formation souhaitee=========================

     
        $fsouhait=[
         'enseignant_id'=> $frmt,
        'titref'=> $prof->titre,
        'description'=> $prof->description
      ];
        $fsouhaitee = store_data('Fsouhaitee',$fsouhait);
        if($fsouhaitee['status']==0)
                    return 0;

return 1;
}


private function formationU($fu,$prof){

    for($i=0; $i<count($fu); $i++){
        $fu[$i]->enseignant_id= $prof;
          $f = store_data('Funiversitaire',$fu[$i]);
          if($f['status']==0)
            return 0;        
       }
    return 1;
}
private function formationP($fp,$prof){

    for($i=0; $i<count($fp); $i++){
        $fp[$i]->enseignant_id= $prof;
          $f = store_data('Fprofessionnelle',$fp[$i]);
          if($f['status']==0)
            return 0;        
       }
    return 1;
}

private function info_tree($affectation, $niveau, $matiere,$prof_id){
                $msg_error ='';
  // insertion niveau
    for($i=0; $i<count($niveau); $i++){
        $dataniv = ['enseignant_id' =>$prof_id,
                    'niveau'=> $niveau[$i],
                    ];
        $niv =  store_data('Enseignantniveau', $dataniv);
          if($niv['status'] == 0)
             return 0;
       }
    //insertion affectation
    for($j=0; $j<count($affectation); $j++){
        $dataAff = ['enseignant_id'=>$prof_id,
                    'ecole_id'=>$affectation[$j]->ecole_id,
                    'classe_id'=> $affectation[$j]->classe_id,
                    ];
        $aff =  store_data('Affectation',$dataAff);
        if($aff['status'] == 0)
            return 0;
    }

    //insertion matiere enseignee
    for($k=0; $k<count($matiere); $k++){
        $aff_id = \App\Affectation::where('classe_id',$matiere[$k]->classe_id)
                 ->where('ecole_id',$matiere[$k]->ecole_id)
                 ->where('enseignant_id',$prof_id)->pluck('id')[0];
        $datamat = ['affectation_id' =>$aff_id,
                    'matiere_id'=> $matiere[$k]->matiere_id,
                    'nb_heure'=> $matiere[$k]->nb_heure
                    ];
        $ensmat =  store_data('Enseignantmatiere',$datamat);
          if($ensmat['status'] == 0)
           return 0;
    }
    return 1;
}


private function info_enseignant($infoprof){
    $frmt = create_stringID($infoprof->nom, $infoprof->prenom, $infoprof->sexe);
        $ens =[
              'id'=> $frmt,
              'nom'=> strtoupper($infoprof->nom),
               'prenom'=> ucfirst($infoprof->prenom),
               'adresse'=>$infoprof->adresse,
               'sexe'=> $infoprof->sexe,
               'nif'=>$infoprof->nif,
               'date_EFonction'=>$infoprof->date_EFonction,
               'date_naissance'=>$infoprof->date_naissance,
               'telephone'=>$infoprof->telephone,
               'email'=>$infoprof->email,
               'dept_n'=>$infoprof->dept_n,
               'dept_h'=>$infoprof->dept_h,
               'commune_h'=>$infoprof->commune_h,
               'commune_n'=>$infoprof->commune_n,
               'cin'=>$infoprof->cin,
               'statutmat'=>$infoprof->statutmat,
               'lieunais'=>$infoprof->lieunais
              ];
        $enseignant = store_data('Enseignant', $ens);
        return $enseignant;
}

public function store_enseignant(Request $request){
      $data = json_decode($request->getContent());
    $message=[];
  $enseignants = $this->info_enseignant($data->prof);
   if($enseignants['status']==0){
            $message[0] ='enregistrement donnees prof échoué';
         return response()->json(['reponse'=>0, 'message'=>$message]);
        }    
    else{
       if($this->info_tree($data->affectation, $data->niveau, $data->matiere,$enseignants['data']['id']) == 0)
             array_push($message,'Enregistrement des donnees d\'Affectation échoué');            
               
        if($this->formationU($data->formation, $enseignants['data']['id'])== 0)
                    array_push($message,'Enregistrement des donnees sur la formation universitaire échoué');
            
      if($this->formationP($data->formatp, $enseignants['data']['id'])== 0)
                array_push($message,'Enregistrement des donnees sur la formation professionnelle échoué');
             
        if($this->format_prof($data->prof,$enseignants['data']['id']) == 0)
                 array_push($message,'Enregistrement des donnees sur la finance ou statut échoué');
             
         }
   return response()->json(['reponse'=>1, 'message'=>$message]);
}


    public function get_info_cours($id){
        
        $data = explode('|', $id);
                if($data[0] == 'commune')
            return response()->json(get_commune_by_dept1($data[1]));
         if($data[0] == 'ecole')
            return response()->json(get_ecole_by_commune_prof($data[1]));
         if($data[0] == 'niveau')
            return response()->json(get_niveau_by_ecole_prof($data[1]));
         if($data[0] == 'classe')
            return response()->json(get_classe_by_niveau_prof($data[1]));
         if($data[0] == 'cours')
            return response()->json(get_matiere_classe_prof($data[1]));
        
    }

    public function index()
    {
      $districts= District::orderBy('nom','asc')->get();
       $enseignants = get_enseignant_by_discom();

      return view('adminView.listeEnseignant',compact(['districts','enseignants']));

    }

    function get_liste_Prof($districts){
      $enseignants= get_enseignant_pdf($districts);
      return Response::json($enseignants);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function liste( $id)
    {

      $zones= Zone::all();
      $enseignants= get_list_enseignant();
      $communes= Commune::all();
      $ecoles= Ecole::all();
      $classe= -1;
      $classes= Classe::all();
      $districts= District::all();


  if($id == 1){
      return view('adminView.liste',['zones'=>$zones, 'communes'=>$communes, 'enseignants'=>$enseignants, 'ecoles'=>$ecoles, 'classes'=>$classes, 'districts'=>$districts,'id'=>$id, 'supe'=>-1, 'nb'=>$enseignants->count()]);
  }

        $enseignants = get_observ_sup($classe);
        if($classe <= 5)
         $supe = 0;
        else
            $supe = 1;
        $nb_questions = nb_question(5,1);
    return view('adminView.liste',['zones'=>$zones, 'communes'=>$communes, 'ecoles'=>$ecoles, 'classes'=>$classes, 'districts'=>$districts, 'enseignants'=>$enseignants,'id'=>$id, 'nb_questions'=>$nb_questions, 'supe'=>$supe]);
    }


    public function get_data_enseignant(Request $request, $id)
    {$district =$request->get('district');
      $zone =$request->get('zone');
      $commune =$request->get('commune');
      $ecole=$request->get('ecole');
      $classe=$request->get('classe');
      $matiere=$request->get('matiere');
      $formation=$request->get('formation');


    if($id == 1){
        $enseignants = get_liste_enseignant($district,  $commune ,$zone, $ecole, $classe, $formation);


     $nb = $enseignants->count();
     $nb_questions = 0;
     $supe = -1;
      $text = get_text($id, $district,  $commune ,$zone, $ecole, $classe, $formation);
    }

    if($id ==2){
         $enseignants = get_observ_sup($district,  $commune ,$zone, $ecole, $classe, $formation);
        if($classe <= 5)
         $supe = 0;
        else
         $supe = 1;

        if($enseignants->count()>0){
            $classe=$enseignants[0]->classe_id;
            $matiere=$enseignants[0]->matiere_id;
            $nb_questions = nb_question($classe,$matiere);
             $nb = $enseignants->count();
              $text = get_text($id, $district,  $commune ,$zone, $ecole, $classe, $formation);

        }else {
            $nb=0;
             $nb_questions = nb_question($classe,$matiere);
             $nb = $enseignants->count();
              $text = get_text($id, $district,  $commune ,$zone, $ecole, $classe, $formation);

        }
    }
     return Response::json(['nb'=>$nb,'text'=>$text, 'enseignants'=>$enseignants,'nb_questions'=>$nb_questions, 'supe'=>$supe]);
}

    public function get_rapport_sup(){
        $observations = get_observ_sup();
        if($observations->count()>0){
        $classe=$observations[0]->classe_id;
        $matiere=$observations[0]->matiere_id;
        $nb_questions = nb_question($classe,$matiere);
      return view('adminView.rapportsup',compact('observations','nb_questions'));
  }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

      $msg_error ='';
          $nom =strtoupper($request->get('nom'));
          $prenom = ucfirst($request->get('prenom'));
          $sexe = $request->get('sexe');


          if(Enseignant::all()->count()>0){
            $lastrecord=DB::table('enseignants')->latest()->first();
            $last=$lastrecord->id;
          }
          else{
            $last="00000000";
          }

          $lastid=Str::substr($last, 3, 5);
          $enscount=(int) $lastid;
          $enscount++;

         $frmt=$enscount;
          if($enscount<10)
             {
            $frmt='0000'.$frmt;
          }
           elseif($enscount<100){
            $frmt='000'.$frmt;
          }
          elseif($enscount<1000){
            $frmt='00'.$frmt;
          }
          elseif($enscount<10000){
            $frmt='0'.$frmt;
          }
            else{
              $frmt=$frmt;
            }

          $frmt=Str::substr($nom, 0, 1).$sexe.Str::substr($prenom, 0, 1).$frmt;

        $ens =[
              'id'=> $frmt,
              'nom'=> $nom,
               'prenom'=> $prenom,
               'adresse'=> $request->get('adresse'),
               'sexe'=> $sexe,
               'nif'=> $request->get('nif'),
               'date_EFonction'=> $request->get('date_EFonction'),
               'date_naissance'=> $request->get('date_naissance'),
               'telephone'=> $request->get('telephone'),
               'email'=> $request->get('email'),
               'dept_n'=> $request->get('dept_n'),
               'dept_h'=> $request->get('dept_h'),
               'commune_h'=> $request->get('commune_h'),
               'commune_n'=> $request->get('commune_n'),
               'cin'=> $request->get('cin'),
               'statutmat'=> $request->get('statutmat'),
               'lieunais'=> $request->get('lieunais')
              ];
        $enseignant = store_data('Enseignant', $ens);
        if($enseignant['status']==0)
                    $msg_error = $msg_error.'Enseignant erreur!'.'->Message:'.$enseignant['message'];

    // ================================Affectation =============================
         $ecole = explode('%', $request->get('rfecole'));
         $nivo = explode('%', $request->get('rfniveau'));
         $classe = explode('%', $request->get('rfclasse'));
         $matiere = explode('%', $request->get('rfmatiere'));

         for($n=0; $n<count($nivo); $n++){
          $niv = explode('-', $nivo[$n]);
           $nive =[
              'enseignant_id' => $frmt,
              'niveau' => $niv[1]

            ];
               $niveau = store_data('Enseignantniveau',$nive);
                if($niveau['status']==0)
                    $msg_error = $msg_error.'Niveau erreur!'.'->Message:'.$niveau['message'];
         }

        for($i=0; $i<count($ecole); $i++)
          for($j=0; $j<count($classe); $j++){
            $cl = explode('-', $classe[$j]);
            if($ecole[$i] == $cl[0]){
              $affect =[
              'enseignant_id' => $frmt,
              'classe_id' => $cl[1],
               'ecole_id' => $ecole[$i],
                'date_affectation' => $request->get('date_affectation')
            ];
               $affectation = store_data('Affectation',$affect);
                if($affectation['status']==0)
                    $msg_error = $msg_error.'Affectation erreur!'.'->Message:'.$affectation['message'];
              $affectation_id = $affectation['data']['id'];
           }
if( $ecole[$i] > 9){
        for($k=0; $k<count($matiere); $k++){
          $mat = explode('-', $matiere[$k]);
            if($cl[1] == $mat[0] && $ecole[$i] == $cl[0]){
              $matier =[
              'affectation_id' => $affectation_id,
               'matiere_id' => $mat[1],
                'nb_heure' => $mat[2]
            ];
               $matie = store_data('Enseignantmatiere',$matier);
                if($matie['status']==0)
                    $msg_error = $msg_error.'Enseignantmatiere erreur!'.'->Message:'.$matie['message'];
           }
        }
      }
    }


    // ======================================statut =======================

        $stat =[
              'enseignant_id'=> $frmt,
              'statut'=> $request->get('statut'),
              'date_statut'=> $request->get('date_statut')
              ];
       $statut = store_data('Statut', $stat);
       if($statut['status']==0)
                    $msg_error = $msg_error.'statut erreur!'.'->Message:'.$statut['message'];

           $statu =  $request->get('statut');


// ==================================Renseignement academique ====================
      $rac =[
            'enseignant_id'=> $frmt,
            'nUniversitaire'=> $request->get('nUniversitaire'),
            'nClassique'=> $request->get('nClassique')
           ];
        $racademique = store_data('Racademique', $rac);
     if($racademique['status']==0)
                    $msg_error = $msg_error.'racademique erreur!'.'->Message:'.$racademique['message'];




//============================================ chaire =============================
if($request->get('secteur') == 0){
       $chaire=[
        'enseignant_id'=> $frmt,
        'type_chaire'=>$request->get('type_chaire')
       ];
       $chaires = store_data('Chaire', $chaire);
      if($chaires['status']==0)
                    $msg_error = $msg_error.'chaires erreur!'.'->Message:'.$chaires['message'];

//======================================Finance ===================================

  if($request->get('statut')=='3' || $request->get('statut')=='2'){
      $fin =[
        'enseignant_id'=> $frmt,
        'type'=> $statu,
        'code_budgetaire'=> $request->get('code_budgetaire'),
        'date_nomination'=> $request->get('date_nomination')
       ];

    $finance = store_data('Finance', $fin);
      if($finance['status']==0)
                    $msg_error = $msg_error.'finance erreur!'.'->Message:'.$finance['message'];
}
}

//====================================== formation universitaire ==================

        $rep = $request->get('rfacad');
        $ligne = explode('%', $rep);
        for($i=0; $i<count($ligne); $i++){
          $ch = explode('|', $ligne[$i]);

          $funiv =[
             'enseignant_id'=> $frmt,
            'nomf'=> $ch[0],
            'lieu'=> $ch[1],
            'date_debut'=> $ch[2],
            'date_fin'=> $ch[3]
          ];
        $funiversitaire = store_data('Funiversitaire', $funiv);
        if($funiversitaire['status']==0)
                    $msg_error = $msg_error.'funiversitaire erreur!'.'->Message:'.$funiversitaire['message'];

      }

//====================================Formation continue =========================
 if($request->get('formationsu')=='1'){
    $rep = $request->get('rep');
    $ligne = explode('%', $rep);
    for($i=0; $i<count($ligne); $i++){
        $ch = explode('|', $ligne[$i]);

        $fprof = [
         'enseignant_id'=> $frmt,
           'titre'=> $ch[0],
           'duree'=> $ch[1],
           'lieu'=> $ch[2],
           'organisateur' =>$ch[3],
           'datef' =>$ch[4]
      ];
        $fprofessionnelle= store_data('Fprofessionnelle', $fprof);
          if($fprofessionnelle['status']==0)
                    $msg_error = $msg_error.'fprofessionnelle erreur!'.'->Message:'.$fprofessionnelle['message'];

    }
}

//==================================== formation souhaitee =======================

 if($request->get('formationso')=='1'){
        $fsouhait=[
         'enseignant_id'=> $frmt,
        'titref'=> $request->get('titref'),
        'description'=> $request->get('description')
      ];
        $fsouhaitee = store_data('Fsouhaitee',$fsouhait);
        if($fsouhaitee['status']==0)
                    $msg_error = $msg_error.'fsouhaitee erreur!'.'->Message:'.$fsouhaitee['message'];
     }

//==================================================================================

              // return Response::json('success');
      if($msg_error == ''){
           $msg_error = 1;
           event(new \App\Events\formsubmit(Enseignant::count()));
         }
          // \Log::debug(implode('*', $produits));
           \Log::debug($msg_error);
          return Response::json($msg_error);

  }


    /**
     * Display the specified resource.
     *
     * @param  \App\Enseignant  $enseignant
     * @return \Illuminate\Http\Response
     */

     public function EnseignantPDF(Request $request)
    { $district =$request->get('district');     
      
          $enseignants= get_enseignant_by_discom($district);
          $nb = $enseignants->count();
        if($district != 0){
             $text ='Liste des Enseignants du District Scolaire de '.get_nom($district,'District');
          }
   
        $liste = json_decode($enseignants);
        $data = ['liste'=>$liste, 'nb'=>$nb, 'text'=>$text];
        $pdf = \PDF::loadView('PdfView.enseignant', $data)->setPaper('legal', 'landscape');
      //$filename = public_path('enseignant.pdf');// path obline
        $filename = storage_path('app/public/enseignant.pdf'); // path local
        $pdf->save($filename);
        return Response::download($filename);
    }



 public function generatePDF(Request $request, $id)
    { $district =$request->get('district');
      $zone =$request->get('zone');
      $commune =$request->get('commune');
      $ecole=$request->get('ecole');
      $classe=$request->get('classe');
      $formation=$request->get('formation');
      if($id == 1){
          $enseignants = get_liste_enseignant($district,  $commune ,$zone, $ecole, $classe, $formation);
          $nb = $enseignants->count();
          $nb_questions = 0;
          $supe =-1;
          $text = get_text($id, $district,  $commune ,$zone, $ecole, $classe, $formation);
       }

    if($id ==2){
        if($classe<= 5)
         $supe = 0;
        else
         $supe = 1;
        $enseignants = get_observ_sup($classe);

        if($enseignants->count()>0){
            $classe=$enseignants[0]->classe_id;
            $matiere=$enseignants[0]->matiere_id;
            $nb_questions = nb_question($classe,$matiere);
            $nb = $enseignants->count();
          $text = get_text($id, $district,  $commune ,$zone, $ecole, $classe, $formation);
        }
      }

        $liste = json_decode($enseignants);
        $data = ['liste'=>$liste,'text'=>$text, 'id'=>$id, 'nb_questions'=>$nb_questions, 'nb'=>$nb, 'supe'=>$supe];
        $pdf = \PDF::loadView('PdfView.listeEnseignant', $data)->setPaper('legal', 'landscape');
        $filename = public_path('listeEnseignant.pdf');
        $pdf->save($filename);
        return Response::download($filename);
    }




    public function show(Enseignant $enseignant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Enseignant  $enseignant
     * @return \Illuminate\Http\Response
     */
    public function edit(Enseignant $enseignant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Enseignant  $enseignant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Enseignant $enseignant)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Enseignant  $enseignant
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       return suppression_enseignant($id);
    }
}
