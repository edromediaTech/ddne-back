<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use App\Ecole;
use App\Commune;
use App\Zone;
use App\Form;
use App\Categorie;
use App\Niveauenseignement;
use App\Vacation;
use App\Directeur;
use App\Structurebatiment;
use App\Groupe;
use App\District;
use App\Section_communale;
use Illuminate\Http\Request;
use Response, DB, Auth,Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EcolesExport;
use App\Exports\EcolesViewExport;
//use App\Imports\EtudiantsImport;



class EcoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function index()
        {
          $user_id = Auth::user()->id;
          $communes = Commune::orderBy('nom', 'asc')->get();
          $zones = Zone::orderBy('nom', 'asc')->get();
          $ecoles = Ecole::all();
          $districts= District::all();
          $form_id = 1;
          $form = Form::find($form_id);
          $groupes = Groupe::where('form_id', $form_id)->get();
          $options = get_form_option($form_id);
          $questions = get_form_question($form_id);
          $sectioncommunales= Section_communale::all();
          $categories = ['Laique', 'Communautaire', 'Congréganiste Anglicane', 'Congréganiste Romaine',
                          'Presbytérale', 'Protestante','Nationale', 'Lycée', 'Mission Baptiste', 'Mission Adventiste',
                        'Mission Méthodiste', 'Autres'];
          $niveau = ['Prescolaire', 'Fondamental', 'Secondaire', 'Ecole Complete', 'Fondamental et Secondaire', 'Prescolaire et Fondamental'];
          $vacation = ['AM', 'PM', 'Double Vacation', 'Triple Vacation'];
          return view('adminView.listeEcole',['zones'=>$zones, 'communes'=>$communes,'ecoles'=>$ecoles,
          'sectioncommunales'=>$sectioncommunales, 'districts'=>$districts, 'categories'=>$categories, 'niveau'=>$niveau, 'vacation'=>$vacation,
                'form'=>$form, 'groupes'=>$groupes, 'options'=>$options, 'questions'=>$questions]);

        }


        public function listeEcole(){
          $districts = District::all();
          $communes = Commune::all();
          $zones = Zone::all();
         $listes = get_liste_ecole(0,0,0,-1,0);
        $niveau = get_niveau(1);

          return response()->json(['listes'=>$listes,'districts'=>$districts,'zones'=>$zones, 'communes'=>$communes, 'niveau'=>$niveau]);

        }

        public function get_info_ecole(){
          return ecole_api();
        }

        public function get_text(){
          $texte= get_text_ecole($data[0], $data[1], $data[2], $data[3], $data[4]);
            return response()->json($texte);
        }


        public function rapportEcole($id){
             $data = explode('|', $id);
                 $listecole = get_liste_ecole($data[0], $data[1], $data[2], $data[3], $data[4]);                   
              return Response::json($listecole);

        }

        public function get_enseignant(){
                    return response()->json(get_enseignant());
        }

         public function rapportEnseignant($id){
             $data = explode('|', $id);
              $listecole = get_liste_enseignant($data[0], $data[1], $data[2], $data[3], $data[4]);                
              return Response::json($listecole);

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

        /**
         * Store a newly created resource in storage.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function store(Request $request)
        {
          $msg_error ='';
          $idp = $request->get('idp');

          $data = ['nom' =>strtoupper($request->get('nom')),
                    'adresse'=>$request->get('adresse'),
                    'code'=>$request->get('code'),
                    'zone_id' =>$request->get('zone_id'),
                    'secteur' =>$request->get('secteur'),
                    'fondateur' =>$request->get('fondateur'),
                    'milieu' =>$request->get('milieu'),
                    'tel' =>$request->get('tel'),
                    'sigle'=>$request->get('sigle'),
                    'telephone' =>$request->get('telephone'),
                    'statut' =>$request->get('statut'),
                    'location' =>$request->get('location'),
                    'latitude' =>$request->get('latitude'),
                    'longitude' =>$request->get('longitude'),
                    'email' =>$request->get('email'),
                    'acces' =>$request->get('acces'),
                    'section_communale_id' =>$request->get('section_communale_id')
                  ];

                if($idp != ''){
                    $ecole = update_data('Ecole', $data, $idp);
                    if($ecole['status']==0)
                    $msg_error = $msg_error.'Opération échouée!'.'->Message:'.$ecole['message'];
                    $ecole_id = $idp;
                  }
                else{
                    $ecole = store_data('Ecole', $data);
                    if($ecole['status']==0)
                    $msg_error = $msg_error.'Opération échouée!'.'->Message:'.$ecole['message'].'%'.$ecole['codeError'];
                    $ec = Ecole::orderBy('created_at', 'desc')->first();
                    $ecole_id = $ec->id;
                  }


          if($ecole['status']==1){

            $vac = ['ecole_id'=>$ecole_id,
                    'vacation'=>$request->get('vacation')
                    ];
                $vac_id = check_existe('Vacation', 'ecole_id', $ecole_id);
                if($vac_id != 0 && $idp != '' ){
                    $vacation = update_data('Vacation', $vac, $vac_id);
                    if($vacation['status']==0)
                    $msg_error = $msg_error.'Vacation erreur!'.'->Message:'.$vacation['message'];
                  }
                  else{
                    $vacation = store_data('Vacation', $vac);
                    if($vacation['status']==0)
                    $msg_error = $msg_error.'Vacation erreur!'.'->Message:'.$vacation['message'];
                  }


            $direct = ['ecole_id'=>$ecole_id,
                  'nomd'=>$request->get('nomd'),
                  'prenom'=>$request->get('prenom'),
                  'teld'=>$request->get('teld'),
                  'cin'=>$request->get('cin'),
                  'sexe'=>$request->get('sexe'),
                  'nif'=>$request->get('nif'),
                  'lieunais'=>$request->get('lieunais'),
                  'datenais'=>$request->get('datenais'),
                  'telephoned'=>$request->get('telephoned'),
                  'emaild'=>$request->get('emaild'),
                  'adressed'=>$request->get('adressed'),
                  'section_communaled_id'=>$request->get('section_communaled_id')
                  ];
                $direc_id = check_existe('Directeur', 'ecole_id', $idp);
                if($direc_id != 0 && $idp != '' ){
                    $directeur = update_data('Directeur', $direct, $direc_id);
                    if($directeur['status']==0)
                    $msg_error = $msg_error.'Directeur erreur!'.'->Message:'.$directeur['message'];
                  }
                else{
                  $directeur = store_data('Directeur', $direct);
                  if($directeur['status']==0)
                  $msg_error = $msg_error.'Directeur erreur!'.'->Message:'.$directeur['message'];
                }

            $cat = ['ecole_id'=>$ecole_id,
                    'categorie'=>$request->get('categorie'),
                  ];
          $cat_id = check_existe('Categorie', 'ecole_id', $idp);
          if($cat_id != 0 && $idp != '' ){
            $categorie = update_data('Categorie', $cat, $cat_id);
            if($categorie['status']==0)
            $msg_error = $msg_error.'Categorie erreur!'.'->Message:'.$categorie['message'];
          }
          else{
            $categorie = store_data('Categorie', $cat);
            if($directeur['status']==0)
            $msg_error = $msg_error.'Categorie erreur!'.'->Message:'.$categorie['message'];
          }

              $niv = ['ecole_id'=>$ecole_id,
                      'niveau'=>$request->get('niveau'),
                      'niveau1'=>get_niveau(0)[$request->get('niveau')]

              ];
              $niv_id = check_existe('Niveauenseignement', 'ecole_id', $idp);
              if($niv_id != 0 && $idp != '' ){
            $niveau = update_data('Niveauenseignement', $niv, $niv_id);
            if($niveau['status']==0)
            $msg_error = $msg_error.'Niveau erreur!'.'->Message:'.$niveau['message'];
          }
            else{
            $niveau = store_data('Niveauenseignement', $niv);
            if($niveau['status']==0)
            $msg_error = $msg_error.'Niveau erreur!'.'->Message:'.$niveau['message'];
          }


            //--------------Etat Batiment--------------
            $res=supprime_existe_lastdate_etat($ecole_id);
              $eb = $request->get('etat_batiment');
            if(gettype($res)!='integer') {
            for($i=0; $i<count($eb); $i++){
                  $q = explode('%',$eb[$i]);
                  $rep =['questionnaire_id'=>$q[0],
                        'reponse'=>$q[1],
                        'ecole_id'=>$ecole_id,
                        'dateEvaluation'=>$res
                        ];
                    $reponse = store_data('Structurebatiment',$rep);
                    // if($reponse['status']==0)
                    // $msg_error = $msg_error.'Etat batiment erreur!'.'->Message:'.$reponse['message'];

                  }
                }else{
                  for($i=0; $i<count($eb); $i++){
                        $q = explode('%',$eb[$i]);
                        $rep =['questionnaire_id'=>$q[0],
                              'reponse'=>$q[1],
                              'ecole_id'=>$ecole_id,
                              'dateEvaluation'=>Carbon::now()->toDateString()
                              ];
                          $reponse = store_data('Structurebatiment',$rep);
                          // if($reponse['status']==0)
                          // $msg_error = $msg_error.'Etat batiment erreur!'.'->Message:'.$reponse['message'];
                        }
                }

           }
           if($msg_error == '')
           $msg_error = 1;
          // \Log::debug(implode('*', $produits));
           \Log::debug($msg_error);
          return Response::json($msg_error);

      }



    public function modifyEcole($id){
      $ecole = Ecole::where('id',$id)->get();
      $categorie = \App\Categorie::where('ecole_id', $id)->get();
      $vacation = \App\Vacation::where('ecole_id', $id)->get();
      $niveau = \App\Niveauenseignement::where('ecole_id', $id)->get();
      $directeur = \App\Directeur::where('ecole_id', $id)->get();
      $etat = last_info_batiment($id);
        if(gettype($etat) != 'integer')
        return Response::json(['ecole'=>$ecole, 'categorie'=>$categorie,
                              'vacation'=>$vacation, 'niveau'=>$niveau,
                            'directeur'=>$directeur, 'etat'=>$etat]);

        return Response::json(['ecole'=>$ecole, 'categorie'=>$categorie,
                              'vacation'=>$vacation, 'niveau'=>$niveau,
                            'directeur'=>$directeur, 'etat'=>0]);
    }




public function supprime_ecole($ecole_id){
    $etat= supprime_existe_lastdate_etat($ecole_id);
    $id = Categorie::where('ecole_id', $ecole_id)->pluck('id')[0];
    $categorie = delete_data('Categorie', $id);
    $id = Niveauenseignement::where('ecole_id', $ecole_id)->pluck('id')[0];
    $niveau = delete_data('Niveauenseignement', $id);
    $id = Vacation::where('ecole_id', $ecole_id)->pluck('id')[0];
    $vacation = delete_data('Vacation', $id);
    $id = Directeur::where('ecole_id', $ecole_id)->pluck('id')[0];
    $directeur = delete_data('Directeur', $id);
    $ecole = delete_data('Ecole', $ecole_id);
    return $ecole;
}
    /**
     * Display the specified resource.
     *
     * @param  \App\Ecole  $ecole
     * @return \Illuminate\Http\Response
     */
    public function show(Ecole $ecole)
    {
        //
    }

    public function ecole_edit(){
      $communes = Commune::orderBy('nom', 'asc')->get();
      $zones = Zone::orderBy('nom', 'asc')->get();
      $sections = Section_communale::all();
      $districts= District::all();
      $categories = ['Laique', 'Communautaire', 'Congreganiste Anglicane', 'Congreganiste Romaine',
                      'Presbyterale', 'Protestante','Nationale', 'Lycée','Mission Baptiste', 'Mission Adventiste',
                    'Mission Methodiste', 'Autres'];
    $niveau = get_niveau(0);

      $vacation = ['AM', 'PM', 'Soir','Double Vacation', 'Triple Vacation'];
      $acces = ['Facile', 'Dificile', 'Très Dificile'];
      $ecoles = modifEcole();
        return view('x-editable.ecole_update', compact(['ecoles', 'categories','niveau', 'vacation', 'districts',
      'zones','communes', 'sections','acces']));
    }

    public function updateEcole(Request $request){
       Ecole::find($request->pk)->update([$request->name => $request->value]);
       return response()->json(['success'=>'done']);
   }
    public function updateDirecteur(Request $request){
       Directeur::find($request->pk)->update([$request->name => $request->value]);
       return response()->json(['success'=>'done']);
   }
    public function updateCategorie(Request $request){
       Categorie::find($request->pk)->update([$request->name => $request->value]);
       return response()->json(['success'=>'done']);
   }
    public function updateVacation(Request $request){
       Vacation::find($request->pk)->update([$request->name => $request->value]);
       return response()->json(['success'=>'done']);
   }
    public function updateNiveau(Request $request){
      $niveau = get_niveau(0);
       Niveauenseignement::find($request->pk)->update([$request->name => $request->value]);
       Niveauenseignement::find($request->pk)->update(['niveau' => array_search($request->value, $niveau)]);
       return response()->json(['success'=>'done']);
   }
    public function updateDistrict(Request $request){
       District::find($request->pk)->update([$request->name => $request->value]);
       return response()->json(['success'=>'done']);
   }
    public function updateCommune(Request $request){
       Commune::find($request->pk)->update([$request->name => $request->value]);
       return response()->json(['success'=>'done']);
   }
    public function updateZone(Request $request){
       Zone::find($request->pk)->update([$request->name => $request->value]);
       return response()->json(['success'=>'done']);
   }
    public function updateSection(Request $request){
       Section_communale::find($request->pk)->update([$request->name => $request->value]);
       return response()->json(['success'=>'done']);
   }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Ecole  $ecole
     * @return \Illuminate\Http\Response
     */
    public function edit(Ecole $ecole)
    {
        //
    }

    public function generatePdfEcole($id)
            {
               $da = explode('|', $id);
               $district =$da[0];
              $commune=$da[1];
               $zone =$da[2];
              $niveau=$da[4];
              $secteur=$da[3];
              // $district =$request->get('district');
              // $zone =$request->get('zone');
              // $commune=$request->get('commune');
              // $niveau=$request->get('niveau');
              // $secteur=$request->get('secteur');

              $liste_ecole = get_liste_ecole($district,$commune, $zone,  $secteur, $niveau);

              if($secteur == -1 || $secteur ==1)
                $sect = nb_secteur_pub($district, $commune, $zone, $niveau);
              else
                $sect = 0;

                $listes = json_decode($liste_ecole);
                $texte= get_text_ecole($district,$commune, $zone, $secteur, $niveau);
                $data = ['listes'=>$listes, 'texte'=>$texte, 'secteur'=>$sect];
                if(count($listes)==0)
                  dd($listes);
            return view('PdfView.listeEcole',compact(['listes','texte','secteur']));
                $pdf = \PDF::loadView('PdfView.listeEcole', $data)->setPaper('legal', 'landscape');
                //$filename = storage_path('listeEcole.pdf');

            $filename = public_path('listeEcole.pdf');
                $pdf->save($filename);
                return Response::download($filename);
            }


   public function generateExcelEcole(Request $request)
      {

              // $da = explode('|', $id);
              //  $district =$da[0];
              // $commune=$da[1];
              //  $zone =$da[2];
              // $niveau=$da[4];
              // $secteur=$da[3];
          $district =$request->get('district');
              $zone =$request->get('zone');
              $commune=$request->get('commune');
              $niveau=$request->get('niveau');
              $secteur=$request->get('secteur');
        $data = get_liste_ecole($district,$commune, $zone,  $secteur, $niveau);

              if($secteur == -1 || $secteur ==1)
                $sect = nb_secteur_pub($district, $commune, $zone, $niveau);
              else
                $sect = 0;

                //$data = json_decode($liste_ecole);
                //$texte= get_text_ecole($district,$commune, $zone, $secteur, $niveau);
             // $data = ['listes'=>$listes, 'texte'=>$texte, 'secteur'=>$sect];
               return Response::json($data);
            if(count($data)==0)
                  dd($data);
                $heading = [
                    'Ecole',
                    'Code',
                    'Directeur',
                    'Telephone',
                    'Adresse',
                    'Section Communale',
                    'Zone',
                    'Acces',
                    'Niveau Enseignement',
                    'Secteur'
                ];

   //return Excel::download(new EcolesViewExport($data),'listeEcole.xlsx' );
            }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ecole  $ecole
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ecole $ecole)
    {
        //
    }

    public function get_etat(){
    $form_id = 1;
          $form = Form::find($form_id);
          $groupes = Groupe::where('form_id', $form_id)->get();
          $options = get_form_option($form_id);
          $questions = get_form_question($form_id);
          $sectioncommunales= Section_communale::select('id as value',  'nom as text')->get();
      return  response()->json(compact('form','groupes','options','questions','sectioncommunales'));

  }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Ecole  $ecole
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ecole $ecole)
    {
        //
    }
}
