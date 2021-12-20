<?php
function user_level(){
  $users = \DB::table("users")->select("*", \DB::raw('(CASE 
           WHEN users.user_level = "0" THEN "User" 
           WHEN users.user_level = "1" THEN "Admin"
           ELSE "SuperAdmin"  END) AS status_lable'))->get();

    dd($users);
}


function get_enseignant(){
  $enseignants = DB::table('enseignants')
              ->join('affectations','affectations.enseignant_id','enseignants.id')
              ->join('funiversitaires','funiversitaires.enseignant_id','enseignants.id')
              ->join('classes','affectations.classe_id','classes.id')
              ->join('ecoles','affectations.ecole_id','ecoles.id')
              ->join('zones','ecoles.zone_id','zones.id')
              ->join('communes','zones.commune_id','communes.id')
              ->join('districts','communes.district_id','districts.id')
              ->select('enseignants.id','enseignants.nom','ecoles.nom as ecole','communes.nom as commune','nomclasse','enseignants.prenom','classes.id as classe_id','enseignants.telephone','sexe', 'nif', 'districts.nom as district','nomf')
              ->get();
              return count($enseignants);

}


function get_redoublant($anac){
  return \DB::table('classeleves')
         ->join('eleves', 'classeleves.eleve_id', 'eleves.id')
          ->join('classes', 'classeleves.classe_id', 'classes.id')
           ->join('ecoles', 'classeleves.ecole_id', 'ecoles.id')
          ->where('status', '=', 'Redoublant')
          ->where('anac',$anac)
          ->select('eleve_id', 'status','eleves.nom','prenom','nomclasse', 'ecoles.nom as ecole')
          ->orderBy('ecoles.nom')
          ->get();
}

function get_classe($type){
  if($type == -1) // adm
    return \App\Classe::select('id as value','nomclasse as text')->get();
  if($type == 0) // prescolaire
    return \App\Classe::whereIn('id',[1,2,3])->select('id as value','nomclasse as text')->get();
  if($type == 1) // principal
    return \App\Classe::whereIn('id',[4,5,6,7,8,9,10,11,12])->select('id as value','nomclasse as text')->get();
  if($type == 2) // secondaire
    return \App\Classe::whereIn('id',[13,14,15,16])->select('id as value','nomclasse as text')->get();
  
}

function hasSecondary($niveau){

  if(substr($niveau,0,1) == '1')
    return 1;
  return 0;
  
}

function hasPrescolaire($niveau){

  if(substr($niveau,3,1) == '1')
    return 1;
  return 0;
  
}

function hasFondamental($niveau){

  if(substr($niveau,1,2) == '11')
    return 1;
  return 0;
  
}

function get_performance_op(){
 return DB::table('eleves')
          ->join('users', 'eleves.user_id','users.id')
          ->whereNotIn('email', ['sironel2002@gmail.com', 'djenicarubes@gmail.com', 'rubesdjenica@yahoo.com','sironel@gmail.com', 'djenrubes@gmail.com'])
          ->select('name', 'email', DB::raw('count(eleves.id) as saisie'))
          ->groupBy('name', 'email')
          ->orderBy('saisie','desc')->get();

}
function get_performance_decisions(){
 return DB::table('decisions')
          ->join('users', 'decisions.user_id','users.id')
          ->whereNotIn('email', ['sironel2002@gmail.com', 'djenicarubes@gmail.com', 'rubesdjenica@yahoo.com','sironel@gmail.com', 'djenrubes@gmail.com'])
          ->select('name', 'email', DB::raw('count(classeleve_id) as saisie'))
          ->groupBy('name', 'email')
          ->orderBy('saisie','desc')->get();
}


function get_eleveSecteur(){
  return DB::table('classeleves')
             ->select(DB::raw("count(CASE WHEN ecoles.secteur = '0' THEN 1 END ) AS  Prive"),
                 DB::raw("count(CASE WHEN ecoles.secteur = '1' THEN 1  END) AS Public"))
                   ->join('ecoles', 'classeleves.ecole_id', 'ecoles.id')
          ->where('anac',session_new_year())
          ->get();

}


function stat_Elevepar_district($user_id, $type){
  $district_id = \App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];
  $req =  DB::table('classeleves')
                ->select('districts.nom','districts.id',
                 DB::raw("count(CASE WHEN eleves.sexe = '0' THEN 1 END ) AS  nb_fille"),
                 DB::raw("count(CASE WHEN eleves.sexe = '1' THEN 1  END) AS nb_garcon"),
                 DB::raw('count(eleves.id) as total'))
                 ->join('eleves','classeleves.eleve_id','eleves.id')
                 ->join('ecoles','classeleves.ecole_id','ecoles.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('niveauenseignements','niveauenseignements.ecole_id','ecoles.id')
                 ->join('communes','zones.commune_id','communes.id')
                ->join('districts','communes.district_id','districts.id')
                ->where('anac', session_new_year())
                ->where('districts.id',$district_id);                
                 if($type ==  0)
                 $req->where('niveau1', 'like', '%%%1')->whereIn('classe_id',[1,2,3]);                   
               if($type ==  1)
                 $req->where('niveau1', 'like', '%11%')->whereIn('classe_id',[4,5,6,7,8,9,10,11,12]);
              if($type ==  2)
                 $req->where('niveau1', 'like', '1%%%')->whereIn('classe_id',[13,14,15,16]); 
               return $req->groupBy('districts.nom','districts.id')
                ->orderBy('districts.nom')
                ->get();
               }

function stat_ecolepar_district($user_id, $type){
  $district_id = \App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];
 $req  =   DB::table('ecoles')
                ->select('districts.nom','districts.id',                
                 DB::raw('count(ecoles.id) as total_ecole'))
                  ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                  ->join('niveauenseignements','niveauenseignements.ecole_id','ecoles.id')
                ->join('districts','communes.district_id','districts.id')
                ->where('districts.id',$district_id);
                 if($type ==  0)
                $req->where('niveau1', 'like', '%%%1');
               if($type ==  1)
                 $req->where('niveau1', 'like', '%11%');
              if($type ==  2)
                 $req->where('niveau1', 'like', '1%%%'); 
               return  $req->groupBy('districts.nom','districts.id')
                ->orderBy('districts.nom')
                ->get();
           return $nb_ecole;
    }

  function stat_ecoleZone_district($user_id, $type){
  $district_id = \App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];
 $req  =   DB::table('ecoles')
                ->select('zones.nom','zones.id',                
                 DB::raw('count(ecoles.id) as total_ecole'))
                  ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                  ->join('niveauenseignements','niveauenseignements.ecole_id','ecoles.id')
                ->join('districts','communes.district_id','districts.id')
                ->where('communes.district_id',$district_id);
                 if($type ==  0)
                 $req->where('niveau1', 'like', '%%%1');
               if($type ==  1)
                 $req->where('niveau1', 'like', '%11%');
              if($type ==  2)
                 $req->where('niveau1', 'like', '1%%%');                 
               return  $req->groupBy('zones.nom','zones.id')
                ->orderBy('zones.nom')
                ->get();
           return $nb_ecole;
    }

function get_inspect(){
   $user_id = \Auth::user()->id;
         $insp = \App\Inspecteur::where('user_id', $user_id)->count();
        if($insp < 1)
             return 0;
         return $user_id;
}

function get_ip($user_id){
       $insp = \App\Insprincipal::where('user_id', $user_id)->get();
       
        if(count($insp) < 1)
             return 0;
         return $insp[0]->id;
}

function liste_ecole_zone_insp($user_id){
  $insp_id = \App\Inspecteur::where('user_id', $user_id)->pluck('id');
  $zone_id = \App\Inspecteur_zone::where('inspecteur_id', $insp_id)->pluck('zone_id')[0];
  
  $nb_ecole = DB::table('ecoles')
                ->join('zones','ecoles.zone_id','zones.id')
                ->join('communes','zones.commune_id','communes.id')
                ->join('directeurs','directeurs.ecole_id','ecoles.id')
                ->join('niveauenseignements','niveauenseignements.ecole_id','ecoles.id')
                 ->where('ecoles.zone_id',$zone_id)
                 ->where('niveau1','like','%11%')
                ->select('ecoles.nom as ecole', 'ecoles.id', 'secteur', 'adresse', 'teld', 'nomd', 'prenom')
                //->groupBy('ecoles.nom','ecoles.id')
                ->orderBy('ecoles.nom', 'asc')
                ->get();
           return ($nb_ecole);
}

function select_ecole_zone_insp($user_id){
  $insp_id = \App\Inspecteur::where('user_id', $user_id)->pluck('id');
  $zone_id = \App\Inspecteur_zone::where('inspecteur_id', $insp_id)->pluck('zone_id')[0];
  
  $nb_ecole = DB::table('ecoles')
                ->join('zones','ecoles.zone_id','zones.id')
                ->join('communes','zones.commune_id','communes.id')
                ->join('directeurs','directeurs.ecole_id','ecoles.id')
                ->join('niveauenseignements','niveauenseignements.ecole_id','ecoles.id')
                 ->where('ecoles.zone_id',$zone_id)
                 ->where('niveau1','like', '%11%')
                ->select('ecoles.nom as text', 'ecoles.id as value')
                //->groupBy('ecoles.nom','ecoles.id')
                ->orderBy('ecoles.nom', 'asc')
                ->get();
           return ($nb_ecole);
}

function get_district_inspecteur($user_id){
  $insp_id = \App\Inspecteur::where('user_id', $user_id)->pluck('id');
  $zone_id = \App\Inspecteur_zone::where('inspecteur_id', $insp_id)->pluck('zone_id')[0];
  
  $zoneinsp = DB::table('districts')                
                 ->join('communes','communes.district_id','districts.id') 
                  ->join('zones','zones.commune_id','communes.id')              
                  ->where('zones.id',$zone_id)                
                ->select('districts.nom')               
                 ->get()[0]->nom;
           return  $zoneinsp;
}


function stat_ecole_zone_insp($user_id){
  $insp_id = \App\Inspecteur::where('user_id', $user_id)->pluck('id')[0]; 
  $zone_id = \App\Inspecteur_zone::where('inspecteur_id', $insp_id)->pluck('zone_id')[0];
  $nb = \App\Ecole::where('zone_id', $zone_id)->count();
  return $nb;
}

function stat_eleve_zone_insp($user_id){
  $insp_id = \App\Inspecteur::where('user_id', $user_id)->pluck('id')[0];
  $zone_id = \App\Inspecteur_zone::where('inspecteur_id', $insp_id)->pluck('zone_id')[0];
   $anac = session_new_year(); 
  $nb_ecole =   DB::table('eleves')
                ->select('zones.id','zones.nom',  DB::raw('count(eleves.id) as nb_eleve'))
                 ->join('classeleves','classeleves.eleve_id','eleves.id')
                 ->join('ecoles','classeleves.ecole_id','ecoles.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                  ->join('niveauenseignements','niveauenseignements.ecole_id','ecoles.id')
                 ->where('ecoles.zone_id',$zone_id)
                ->where('anac', $anac)
                ->where('niveau1', 'like', '%11%')
                ->whereIn('classe_id',[4,5,6,7,8,9,10,11,12])
                ->groupBy('zones.nom','zones.id')
                 ->get();

           return($nb_ecole);

}

function stat_eleveTotal(){
  $anac = session_new_year();
   $stat =   DB::table('classeleves')
               ->select('ecoles.nom as ecole', 'ecoles.id','section_communales.nom','directeurs.prenom','teld','nomd','secteur',
                 DB::raw("count(CASE WHEN eleves.sexe = '0' THEN 1 END ) AS  nb_fille"),
                 DB::raw("count(CASE WHEN eleves.sexe = '1' THEN 1  END) AS nb_garcon"),
                 DB::raw('count(eleves.id) as total'))                
                  ->join('eleves','classeleves.eleve_id','eleves.id')
                 ->join('ecoles','classeleves.ecole_id','ecoles.id')
                 ->join('directeurs','directeurs.ecole_id','ecoles.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                  ->join('section_communales','ecoles.section_communale_id','section_communales.id')
                  ->join('communes','zones.commune_id','communes.id')
                 ->where('anac', $anac)
                ->groupBy('ecoles.nom', 'ecoles.id', 'section_communales.nom','nomd', 'directeurs.prenom','teld','secteur')
               ->orderBy('ecoles.nom', 'asc' )
                ->get();
           return ($stat);

}

function stat_eleveTotal_district($user_id, $type){
  $district_id = \App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];
$anac = session_new_year();
   $req =  DB::table('classeleves')
               ->select('ecoles.nom as ecole', 'ecoles.id','section_communales.nom','directeurs.prenom','teld','nomd','secteur',
                 DB::raw("count(CASE WHEN eleves.sexe = '0' THEN 1 END ) AS  nb_fille"),
                 DB::raw("count(CASE WHEN eleves.sexe = '1' THEN 1  END) AS nb_garcon"),
                 DB::raw('count(eleves.id) as total'))                
                 ->join('eleves','classeleves.eleve_id','eleves.id')
                 ->join('ecoles','classeleves.ecole_id','ecoles.id')
                 ->join('niveauenseignements','niveauenseignements.ecole_id','ecoles.id')
                   ->join('directeurs','directeurs.ecole_id','ecoles.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                  ->join('communes','zones.commune_id','communes.id')
                 ->join('districts','communes.district_id','districts.id')                
                  ->join('section_communales','ecoles.section_communale_id','section_communales.id')
                  ->where('districts.id',$district_id)
                  ->where('anac', $anac);
                    if($type ==  0) {               
                    $req->where('niveau1', 'like', '%%%1');
                     $req->whereIn('classe_id',[1,2,3]);
                    }
                    
               if($type ==  1){
                 $req->where('niveau1', 'like', '%11%');
                  $req->whereIn('classe_id',[4,5,6,7,8,9,10,11,12]);
               }
              if($type ==  2){
                 $req->where('niveau1', 'like', '1%%%'); 
                $req->whereIn('classe_id',[13,14,15,16]);
              }
               return $req ->groupBy('ecoles.nom', 'ecoles.id', 'section_communales.nom','nomd', 'directeurs.prenom','teld','secteur')
               ->orderBy('ecoles.nom', 'asc' )
                ->get();
           

}


function stat_eleveTotal_zone_insp($user_id){
   $insp_id = \App\Inspecteur::where('user_id', $user_id)->pluck('id')[0];
    $zone_id = \App\Inspecteur_zone::where('inspecteur_id', $insp_id)->pluck('zone_id')[0];
  $anac = session_new_year();
  $stat =   DB::table('classeleves')
               ->select('ecoles.nom as ecole', 'ecoles.id', 
                 DB::raw("count(CASE WHEN sexe = '0' THEN 1 END ) AS  nb_fille"),
                 DB::raw("count(CASE WHEN sexe = '1' THEN 1  END) AS nb_garcon"),
                 DB::raw('count(eleves.id) as total'))                
                  ->join('eleves','classeleves.eleve_id','eleves.id')
                 ->join('ecoles','classeleves.ecole_id','ecoles.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                  ->join('niveauenseignements','niveauenseignements.ecole_id','ecoles.id')
                 ->where('ecoles.zone_id', $zone_id)
                ->where('anac', $anac)
                ->where('niveau1', 'like', '%11%')
                ->whereIn('classe_id',[4,5,6,7,8,9,10,11,12])
                ->groupBy('ecoles.nom', 'ecoles.id')
               ->orderBy('ecoles.nom', 'asc' )
                ->get();
           return ($stat);

}

function stat_fille_zone_insp($user_id){
  $insp_id = \App\Inspecteur::where('user_id', $user_id)->pluck('id')[0];
  $zone_id = \App\Inspecteur_zone::where('inspecteur_id', $insp_id)->pluck('zone_id')[0];
   $anac = session_new_year(); 
  $nb_ecole =   DB::table('eleves')
                ->select('zones.id','zones.nom',  DB::raw('count(eleves.id) as nb_fille'))
                 ->join('classeleves','classeleves.eleve_id','eleves.id')
                 ->join('ecoles','classeleves.ecole_id','ecoles.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                  ->join('niveauenseignements','niveauenseignements.ecole_id','ecoles.id')
                 ->where('ecoles.zone_id',$zone_id)
                ->where('sexe',0)
                ->where('niveau1', 'like', '%11%')
                ->whereIn('classe_id',[4,5,6,7,8,9,10,11,12])
                  ->where('anac', $anac)
                ->groupBy('zones.nom','zones.id')
                ->orderBy('zones.nom', 'asc' )
                ->get();

           return($nb_ecole);

}

function stat_filleEcole_zone_insp($user_id){
  $insp_id = \App\Inspecteur::where('user_id', $user_id)->pluck('id')[0];
  $zone_id = \App\Inspecteur_zone::where('inspecteur_id', $insp_id)->pluck('zone_id')[0];
  $anac = session_new_year();
  $nb_ecole =   DB::table('eleves')
                ->select('ecoles.id','ecoles.nom',  DB::raw('count(eleves.id) as nb_fille'))
                 ->join('classeleves','classeleves.eleve_id','eleves.id')
                 ->join('ecoles','classeleves.ecole_id','ecoles.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                 ->where('ecoles.zone_id',$zone_id)
                ->where('sexe',0)
                  ->where('anac', $anac)
                ->groupBy('ecoles.nom','ecoles.id')
                ->orderBy('ecoles.nom', 'asc' )
                ->get();

           return($nb_ecole);

}

function get_dept(){
    return \App\Departement::select('id as value', 'nom as text')->get();
}
function get_district_by_dept($dept_id){
    return \App\District::where('departement_id', $dept_id)->select('id as value', 'nom as text')->get();
}

function get_commune_by_district($district_id){
    return \App\Commune::where('district_id', $district_id)->select('id as value', 'nom as text')->get();
}


function get_zone_by_commune($commune_id){
    return \App\Zone::where('commune_id', $commune_id)->select('id as value', 'nom as text')->get();
}

function get_ecole_by_zone($zone_id){
    return \App\Ecole::where('zone_id', $zone_id)->select('id as value', 'nom as text')->get();
}

function get_ecole_by_inspecteur($zone_id, $id){
    $type = \App\Insprincipal::find($id)->type;
     $req =  DB::table('ecoles')
               ->select('ecoles.nom as text', 'ecoles.id as value')            
                 ->join('niveauenseignements','niveauenseignements.ecole_id','ecoles.id')                  
                 ->join('zones','ecoles.zone_id','zones.id')
                  ->join('communes','zones.commune_id','communes.id')
                ->join('districts','communes.district_id','districts.id')                
                  ->join('section_communales','ecoles.section_communale_id','section_communales.id')
                  ->where('ecoles.zone_id',$zone_id);
                       if($type ==  0)
                      $req->where('niveau1', 'like', '%%%1');               
                    if($type ==  1)
                      $req->where('niveau1', 'like', '%11%');
                      if($type ==  2)
                      $req->where('niveau1', 'like', '1%%%');               
               return $req->orderBy('ecoles.nom', 'asc' )
                ->get();   
}


function get_niveau_by_ecole($ecole_id){
    return \App\Niveauenseignement::where('ecole_id', $ecole_id)->select('niveau1 as text')->get()[0];
}

function get_suggestions(){
  $sug  =  DB::table('suggestions')
          ->join('ecoleresponsables', 'suggestions.responsable_id', 'ecoleresponsables.id')
          ->join('users', 'ecoleresponsables.user_id', 'users.id')
          ->join('ecoles','ecoleresponsables.ecole_id', 'ecoles.id')
          ->select('name', 'message','ecoles.nom', 'lu', 'users.email','suggestions.created_at','suggestions.id')
          ->orderBy('suggestions.created_at', 'desc')
          ->get();
        return $sug;
}


function respons_convert($data){
  $tab = explode('|', $data);
  $rep = '';
  foreach($tab as $t){
    if($t == 'true')
      $rep = $rep.'1';    
    else
      $rep = $rep.'0';
  }
  return $rep;
}

function niveau_classe($niveau){
  $classe = [];
   $pres = [1,2,3];
   $prim = [4, 5, 6, 7,8,9];
  $fond = [10,11, 12];
  $sec = [13,14,15,16];  
 
 if(substr($niveau, 0, 1) == '1')
    $classe = array_merge($classe, $sec);
  if(substr($niveau, 1, 1) == '1')
    $classe = array_merge($classe, $fond);
  if(substr($niveau, 2,1) == '1')
    $classe = array_merge($classe, $prim);
  if(substr($niveau, 3, 1) == '1')
    $classe = array_merge($classe, $pres);
  return $classe;  
}

function liste_classe_responsable($niveau){
  $classe = niveau_classe($niveau);
  $liste = DB::table('classes')
  ->whereIn('id', $classe)
  ->select('id as value', 'nomclasse as text')
  ->orderBy('id','asc')
  ->get();
  return $liste;
}

function responsable_exist($user_id){
  $resp = \App\Ecoleresponsable::where('user_id', $user_id)->get();
  if(count($resp) > 0)
    if($resp[0]->valider == 1)
      return $resp[0]->id;
    else
      return -1;
  return 0;
}

function get_data_ecole($ecoleresp_id){
  $data = DB::table('ecoleresponsables')
          ->join('ecoles','ecoleresponsables.ecole_id', 'ecoles.id')
          ->where('ecoleresponsables.id', $ecoleresp_id)
          ->select('nom as ecole', 'ecoles.id as ecole_id', 'niveau')
          ->get()[0];
      return $data;
}

function valider_responsable(){
  $resp = DB::table('ecoleresponsables')
          ->join('users', 'ecoleresponsables.user_id', 'users.id')
          ->join('ecoles','ecoleresponsables.ecole_id','ecoles.id')
          ->select('name', 'users.email', 'ecoles.nom', 'niveau','valider', 'ecoleresponsables.id')
          ->get();

          $rep = [];
        foreach($resp as $r){
           if($r->valider == 0)
               $r->valider = false;
            else
             $r->valider = true;

              array_push($rep, $r);
            }
      return $rep;
}

function update_responsable($id){
  $rep = \App\Ecoleresponsable::find($id);  
  
  if($rep->valider == 1)
    $rep->valider = false;
  else
    $rep->valider = true;
    $rep->save();
   
    return true;
}


// recuperation de la derniere annee dans la table annees

  function get_current_year(){
    $year = \App\Annee::latest()->first();
    return $year->annee;
  }

// calcul de la nouvelle annee

    function annee_nouvelle(){
    $annees = explode('-', get_current_year());
    $an = $annees[1];
    $ansuiv = $an + 1;
    $anv= ($an.'-'.$ansuiv);  
    return $anv;
  }

// store nouvelle annee
  
  function store_new_year(){
    $anv = annee_nouvelle();
      $data = store_data('Annee',['annee'=>$anv]);
      return $anv;
  }

// creation session avec nouvelle annee

  function session_new_year(){
    $nv = get_current_year();
    session(['anac' => $nv]);
    return session('anac');
  }

function get_last_decision($classeleve_id){ 
  $dec = get_data('Decision', 'classeleve_id', $classeleve_id);
  return $dec;
}

function get_prec_decision($classeleve_id){ 
  $dec = get_data('Decision', 'classeleve_id', $classeleve_id);
  return $dec;
}

function get_tranfert_pendant($ecole_id){
  $sortant = DB::table('transferts')
          ->join('classeleves', 'transferts.classeleve_id', 'classeleves.id')
           ->join('eleves','classeleves.eleve_id','eleves.id')
          ->join('classes','classeleves.classe_id','classes.id')
           ->join('ecoles','classeleves.ecole_id','ecoles.id')
           ->join('directeurs','directeurs.ecole_id','ecoles.id')
            ->join('section_communales','ecoles.section_communale_id','section_communales.id')
           ->where('classeleves.ecole_id', $ecole_id)
           ->where('etat',0)
           ->where('accepter',0)
           ->select('eleves.id as eleve_id','classeleves.id as classeleve_id','transferts.id','eleves.nom','eleves.prenom','eleves.datenais','eleves.lieunais','eleves.sexe','prenom_mere','tel_persrep','deficience','dept_n','nomclasse', 'ecoles.id as ecole_id', 'transferts.created_at','ecoles.nom as ecole','section_communales.nom as sectioncom', 'nomd', 'directeurs.prenom as prenomd','directeurs.sexe')
           ->orderbyRaw("concat(eleves.nom,' ',eleves.prenom)" , 'asc')
           ->get();
       return $sortant;
}


function get_id_transfert($id){
 
  $trans = DB::table('transferts')
          ->join('classeleves', 'transferts.classeleve_id', 'classeleves.id')
           ->join('eleves','classeleves.eleve_id','eleves.id')
          ->join('classes','classeleves.classe_id','classes.id')
           ->join('ecoles','classeleves.ecole_id','ecoles.id')          
            ->where('transferts.id', $id)
            ->where('etat', 0)
           ->select('eleves.id as eleve_id','classeleves.id as classeleve_id','transferts.id','eleves.nom','prenom','datenais','lieunais','sexe','prenom_mere','tel_persrep','deficience','dept_n','nomclasse', 'ecoles.id as ecole_id', 'ecoles.nom as ecole', 'accepter','ecolecible')
            ->get();
       return $trans;
}

function get_eleve_accepter($ecole_id){
    $trans = DB::table('transferts')
          ->join('classeleves', 'transferts.classeleve_id', 'classeleves.id')
           ->join('eleves','classeleves.eleve_id','eleves.id')
          ->join('classes','classeleves.classe_id','classes.id')
           ->join('ecoles','classeleves.ecole_id','ecoles.id')
           ->join('section_communales','ecoles.section_communale_id','section_communales.id')
          ->where('etat',0)         
           ->where('ecolecible', $ecole_id)           
           ->select('eleves.id as eleve_id','classeleves.id as classeleve_id','transferts.id','eleves.nom','prenom','datenais','lieunais','sexe','prenom_mere','tel_persrep','deficience','dept_n','nomclasse', 'ecoles.id as ecole_id', 'ecoles.nom as ecole', 'accepter','ecolecible', 'transferts.created_at','section_communales.nom as sectioncom')
            ->get();
       return $trans;
}

function get_eleve_valider($tabecole){
    $trans = DB::table('transferts')
          ->join('classeleves', 'transferts.classeleve_id', 'classeleves.id')
           ->join('eleves','classeleves.eleve_id','eleves.id')
          ->join('classes','classeleves.classe_id','classes.id')
           ->join('ecoles','classeleves.ecole_id','ecoles.id')
          ->where('etat',0)         
           ->whereIn('ecolecible', $tabecole)           
           ->select('eleves.id as eleve_id','classeleves.id as classeleve_id','transferts.id','eleves.nom','prenom','datenais','lieunais','sexe','prenom_mere','tel_persrep','deficience','dept_n','nomclasse', 'ecoles.id as ecole_id', 'ecoles.nom as ecole', 'accepter','ecolecible', 'valider')
            ->get();
       return $trans;
}


function get_eleve_classe($classe_id,$ecole_id, $anac=null){
  $cle = \App\Transfert::where('etat', 0)->pluck('classeleve_id');
    
  return DB::table('classeleves')
  ->join('eleves','classeleves.eleve_id','eleves.id')
  ->join('classes','classeleves.classe_id','classes.id')
  ->join('ecoles','classeleves.ecole_id','ecoles.id')
  ->where('anac',$anac)
  ->where('classe_id',$classe_id)
  ->where('ecole_id',$ecole_id)
  ->whereNotIn('classeleves.id', $cle)
  ->select('eleves.id','classeleves.id as classeleve_id','eleves.nom','prenom','datenais','lieunais','sexe','prenom_mere','tel_persrep','deficience','dept_n','nomclasse', 'ecoles.id as ecole_id ')
   ->orderbyRaw("concat(eleves.nom,' ',eleves.prenom)" , 'asc')
  ->get();
 }


function stat_eleve_par_classe($ecole_id, $niveau){
  $classe_id = niveau_classe($niveau);
  $anac = session_new_year();
    $nb_eleve =   DB::table('classeleves')
                 ->select('nomclasse','classes.id', DB::raw('count(eleve_id) as nb_eleve'))
                 ->join('ecoles','classeleves.ecole_id','ecoles.id')
                 ->join('classes', 'classeleves.classe_id', 'classes.id')
                 ->join('eleves','classeleves.eleve_id','eleves.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                 ->join('districts','communes.district_id','districts.id')
                ->where('anac', $anac)
                ->where('ecoles.id', $ecole_id)
                ->whereIn('classes.id',$classe_id)              
                ->groupBy('classes.nomclasse','classes.id','communes.nom')
                ->get();

           return($nb_eleve);
}

function get_id_niveau_tab($niveau){
  $niv = [];
  for($i=1; $i<5; $i++)
  if(substr($niveau, $i-1, 1) == '1')
    array_push($niv, $i);
  return $niv;
}

function stat_fille_par_niveau($ecole_id, $niveau, $anac){
    
        $nb_eleve =   DB::table('classeleves')
                 ->select('niveaux.libelle', DB::raw('count(eleve_id) as nb_eleve'))
                 ->join('ecoles','classeleves.ecole_id','ecoles.id')                 
                 ->join('classes', 'classeleves.classe_id', 'classes.id')
                 ->join('niveaux','classes.niveau_id', 'niveaux.id')
                 ->join('eleves','classeleves.eleve_id','eleves.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                 ->join('districts','communes.district_id','districts.id')
                ->where('anac', $anac)
                ->where('ecoles.id', $ecole_id)
                ->where('sexe', 0)
                ->whereIn('niveau_id',  get_id_niveau_tab($niveau))                        
                ->groupBy('niveaux.libelle')
                ->get();

           return($nb_eleve);
}

function total_eleve_par_classe($ecole_id, $niveau, $anac){
      
        $nb_eleve =   DB::table('classeleves')
                 ->select('nomclasse','classes.id', DB::raw('count(eleve_id) as nb_eleve'))
                 ->join('ecoles','classeleves.ecole_id','ecoles.id')
                  ->join('classes', 'classeleves.classe_id', 'classes.id')
                 ->join('eleves','classeleves.eleve_id','eleves.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                 ->join('districts','communes.district_id','districts.id')               
                ->where('ecoles.id',$ecole_id)
                ->where('anac',$anac)
               // ->where('sexe',1)
                ->whereIn('classe_id',niveau_classe($niveau))
                ->groupBy('nomclasse','classes.id')
                ->orderBy('nomclasse', 'asc' )
                ->get();
           return($nb_eleve);
}

function total_fille_par_classe($ecole_id, $niveau, $anac){      
        $nb_eleve =   DB::table('classeleves')
                 ->select('nomclasse','classes.id', DB::raw('count(eleve_id) as nb_fille'))
                 ->join('ecoles','classeleves.ecole_id','ecoles.id')
                  ->join('classes', 'classeleves.classe_id', 'classes.id')
                 ->join('eleves','classeleves.eleve_id','eleves.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                 ->join('districts','communes.district_id','districts.id')               
                ->where('ecoles.id',$ecole_id)
                ->where('anac',$anac)
                ->where('sexe',0)
                ->whereIn('classe_id',niveau_classe($niveau))
                ->groupBy('nomclasse','classes.id')
                ->orderBy('nomclasse', 'asc' )
                ->get();
           return($nb_eleve);
}

// function stat_global_par_ecole($ecole_id, $niveau, $anac){ 
//   $total = total_eleve_par_classe($ecole_id, $niveau, $anac);
//   $filles = total_fille_par_classe($ecole_id, $niveau, $anac);
//   $classes = niveau_classe($niveau);  
//   return $classes; 
//     $stat = collect();
//     $tab = [];
//     for($i=0; $i<count($classes); $i++){
//       $tab = array_add($tab, 'id', $classes[$i]);

//       $trouve = 0;
//       foreach($total as $t){
//         if($t->id == $classes[$i]){        
//            $trouve= 1;
//             break;
//         }
//       }
        // if($trouve == 0)
        // $tab = array_add($tab, 'id', $c);
        // $tab = array_add($tab, 'nomclasse', \App\Classe::find($c)->nomclasse);
        // $tab = array_add($tab, 'nb_eleve', 0);
        
        //  $stat->push($tab);
//     }
//     return $tab;
//   return $stat;

// }

function stat_eleve_ecole($ecole_id, $anac){ 
$anac = session_new_year();   
        $nb_eleve =   DB::table('classeleves')
                 ->select('ecoles.id', DB::raw('count(eleve_id) as nb_eleve'))
                 ->join('ecoles','classeleves.ecole_id','ecoles.id')                 
                 ->join('classes', 'classeleves.classe_id', 'classes.id')
                // ->join('niveaux','classes.niveau_id', 'niveaux.id')
                 ->join('eleves','classeleves.eleve_id','eleves.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                 ->join('districts','communes.district_id','districts.id')
                ->where('anac', $anac)
                ->where('ecoles.id', $ecole_id)
               // ->where('sexe', 0)
                ->whereIn('niveau_id',  get_id_niveau_tab($niveau))                        
                ->groupBy('niveaux.libelle')
                ->get();

           return($nb_eleve);
}


function stat_eleve_par_ecole(){
    $ecole_id = \App\Ecoleresponsable::where('user_id', $user_id)->pluck('ecole_id')[0];

   $nb_eleve =   DB::table('classeleves')
                 ->select('ecoles.nom as etab','ecoles.id', 'communes.nom as commune', DB::raw('count(eleve_id) as nb_eleve'))
                 ->join('ecoles','classeleves.ecole_id','ecoles.id')
                 ->join('eleves','classeleves.eleve_id','eleves.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                 ->join('districts','communes.district_id','districts.id')
                //->where('districts.id',42)
                ->where('ecoles.id',$ecole_id)
               // ->where('sexe',0)
              //  ->whereBetween('classe_id',[4,12])
                ->groupBy('ecoles.nom','ecoles.id','communes.nom')
                ->orderBy('ecoles.nom', 'asc' )
                ->get();

           return($nb_eleve);
    }






function imploadValue($types){
    $strTypes = implode(",", $types);
    return $strTypes;
  }

  function explodeValue($types){
    $strTypes = explode(",", $types);
    return $strTypes;
  }

  function random_code($val_deb, $val_end){

    return rand($val_deb, $val_end);
  }

  function annee_scol_suiv(){
    $annees = explode('-',  session_new_year());
    $an = $annees[1];
    $ansuiv = $an + 1;
    return ($an.'-'.$ansuiv);
  }

  function annee_suiv($anac){
    $annees = explode('-', $anac);
    $an = $annees[1];
    $ansuiv = $an + 1;
    return ($an.'-'.$ansuiv);
  }

  function ecolePncs(){
    $nb_ecole =   DB::table('ecoles')
                ->select('ecoles.nom as Etablissement','nomd as Nom','directeurs.prenom as Prenom','directeurs.teld as Telephone','communes.nom as Commune',  DB::raw('count(eleves.id) as Effectif'))
                 ->join('directeurs','directeurs.ecole_id','ecoles.id')
                 ->join('niveauenseignements','niveauenseignements.ecole_id','ecoles.id')
                  ->join('classeleves','classeleves.ecole_id','ecoles.id')
                 ->join('eleves','classeleves.eleve_id','eleves.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                ->join('section_communales','ecoles.section_communale_id','section_communales.id')
                ->where('secteur',1)
                ->where('niveau','<>','Secondaire')
                ->groupBy('communes.nom','directeurs.nomd','directeurs.prenom','directeurs.teld','ecoles.nom')
                ->orderBy('ecoles.nom', 'asc' )
                ->get();
                return $nb_ecole;
  }


    function get_listeID($classe_id,$ecole_id){
     $listeId = \App\Classeleve::where('classe_id',$classe_id)
                ->where('ecole_id',$ecole_id)->pluck('eleve_id');

        $sup_clas_ecole= \App\Classeleve::where('classe_id',$classe_id)
               ->where('ecole_id',$ecole_id)->delete();
        $sup_eleve = \App\Eleve::destroy($listeId);
        return count($listeId);

     }

  function code_format($nom,$prenom,$sexe){
     //$modelname = 'App\\'.$model;
    if(\App\Eleve::all()->count()>0){
            $lastrecord=DB::table('eleves')->latest()->first();
            $last=$lastrecord->id;
          }
          else{
            $last="00000000";
          }

          $lastid=Str::substr($last, 3, 5);
          $enscount=(int) $lastid;
          $trouve = false;
          do {
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

          if(\App\Eleve::find($frmt) == null )
            $trouve = true;
        } while (!$trouve);
            return $frmt;

  }

  function store_eleve($data){

     $msg_error ='';
          $data->nom =strtoupper($data->nom);
        if($data->tel_persrep=='')
          $data->tel_persrep='0000-0000';
        if($data->prenom_mere=='')
          $data->prenom_mere='Ma00';
          $data->prenom = ucfirst($data->prenom);
          $sexe = $data->sexe;
          $frmt = code_format( $data->nom, $data->prenom, $data->sexe);

        $datael = ['id'=>$frmt,'nom'=>$data->nom,'prenom'=>$data->prenom,'datenais'=>$data->datenais,'lieunais'=>$data->lieunais,'dept_n'=>$data->dept_n,'sexe'=>$data->sexe,'prenom_mere'=>ucfirst($data->prenom_mere), 'deficience'=>$data->deficience,'tel_persrep'=>$data->tel_persrep, 'user_id'=>\Auth::user()->id];
             $eleve= store_data('Eleve',$datael);
            if($eleve['status'] == 1){
                $datacl =['ecole_id'=>$data->ecole_id,'classe_id'=>$data->classe_id,'eleve_id'=>$frmt,'anac'=>'2020-2021','status'=>'Nouveau'];
                $classesel = store_data('Classeleve',$datacl);
                if($classesel['status']==0){
            $msg_error = $msg_error.'classeeleve erreur!'.'->Message:'.$classesel['message'];

            return 0;
            }
        }
     if($eleve['status']==0){
           $msg_error = $msg_error.'eleve erreur!'.'->Message:'.$eleve['message'];
             \Log::debug($msg_error);
            return ($eleve);
        }

          return ($eleve);

  }


 // function ligne_par_budget(){
 //  return DB::table('lignebudgetaires')
 //  ->join('budgets', 'lignebudgetaires.budget_id','budgets.id')
 //   ->select('libelle', 'lignebudgetaires.id as ligne_id','budgets.id as budget_id','lignebudgetaires.montant')
 //  ->get();
 // }

 function get_ecole_eleve_non_saisie(){
   $liste = \App\Classeleve::orderBy('ecole_id','asc')->groupBy('ecole_id')->pluck('ecole_id');
       $ecoles=  \DB::table('ecoles')
      ->join('directeurs','directeurs.ecole_id','ecoles.id')
      ->join('zones','ecoles.zone_id','zones.id')
      ->join('communes','zones.commune_id','communes.id')
       ->join('districts','communes.district_id','districts.id')
        ->whereNotIn('ecoles.id',$liste)
        ->select('ecoles.id','ecoles.nom','ecoles.created_at','adresse','districts.nom as district','communes.nom as commune','zones.nom as zone','zones.id as zone_id','nomd', 'prenom','teld')
        ->get();
  return $ecoles;
 }

 function get_ecole_eleve_saisie(){
   $liste = \App\Classeleve::orderBy('ecole_id','asc')->groupBy('ecole_id')->pluck('ecole_id');

  return $liste;
 }


function liste_eleve_abandonne(){
  $abandons = DB::table('abandons')
  ->join('classeleves', 'abandons.classeleve_id', 'classeleves.id')
   ->join('eleves','classeleves.eleve_id','eleves.id')
  ->join('classes','classeleves.classe_id','classes.id')
  ->join('ecoles','classeleves.ecole_id','ecoles.id')
  ->select('eleves.id as ID Elève','eleves.nom as Nom', 'prenom as Prenom', 'nomclasse as Classe', 'ecoles.nom as Ecole', 'anac as Année')
  ->get();
  return $abandons;
}

function liste_eleve_expulse(){
  $expulses = DB::table('expulses')
  ->join('classeleves', 'expulses.classeleve_id', 'classeleves.id')
   ->join('eleves','classeleves.eleve_id','eleves.id')
  ->join('classes','classeleves.classe_id','classes.id')
  ->join('ecoles','classeleves.ecole_id','ecoles.id')
  ->select('eleves.id as ID Elève','eleves.nom as Nom', 'prenom as Prenom', 'nomclasse as Classe', 'ecoles.nom as Ecole', 'anac as Année')
  ->get();
  return $expulses;
}



 function get_eleve_ecole($commune_id){
  return DB::table('classeleves')
  ->join('eleves','classeleves.eleve_id','eleves.id')
  ->join('classes','classeleves.classe_id','cclasses.id')
  ->join('ecoles','classeleves.ecole_id','ecoles.id')
  ->join('zones','ecoles.zone_id','zones.id')
  ->join('communes','zones.commune_id','communes.id')
  ->where('anac', session_new_year())
  //->where('classe_id',$classe_id)
  ->where('communes.id',$commune_id)
  ->select('eleves.id','eleve_id','classeleves.id as classeleve_id','eleves.nom','prenom','datenais','lieunais','sexe','prenom_mere','tel_persrep','deficience','dept_n','nomclasse','ecoles.id as ecole_id','ecoles.nom as ecole')
  ->orderBy('classes.nomclasse','asc')
  ->get();

 }

//  function get_operationDate($op_id){
//     return \App\operationb::where('id',$op_id)->pluck('date')[0]->toString();
//  }

//  function get_nextOp($compteb_id,$operation_date){
//       $operations = \App\operationb::whereDate('date','>',new DateTime($operation_date))->where('compteb_id',$compteb_id)->get();
//       return $operations;
//  }
// function update_solde($lastSolde,$compteb_id,$operation_date){
//   $operations = get_nextOp($compteb_id,$operation_date);
//   foreach ($operations as $op) {
//      $opt = \App\operationb::find($op->id);
//       if($op->type == 0)
//        $lastSolde = $lastSolde + $op->montant;
//       else
//         $lastSolde = $lastSolde - $op->montant;
//         $opt->solde = $lastSolde;
//         $opt->save();
//   }
//   $compte = \App\Compteb::find($compteb_id);
//   $compte->solde = $lastSolde;
//   $compte->save();
//   return $lastSolde;
// }
  // function get_cheque_data($depense_id){
  //   $cheque = DB::table('depenses')
  //   ->join('prestataires','depenses.prestataire_id','prestataires.id')
  //   ->where('depenses.id',$depense_id)
  //   ->select('depenses.id','nom','montant','memo')
  //   ->get();
  //   return $cheque;
  // }

  //  function get_cheque_data($id){
  //   $cheque = DB::table('impressioncheques')
  //   ->join('depenses','impressioncheques.depense_id','depenses.id')
  //   ->join('prestataires','depenses.prestataire_id','prestataires.id')
  //   ->where('impressioncheques.id',$id)
  //   ->select('impressioncheques.id','depense_id','nom','impressioncheques.montant','memo','dategeneratecheque')
  //   ->get();
  //   return $cheque;
  // }

  // function generate_cheque($id){
  //   $donnees = collect();
  //       $data = explode('|', $id);
  //       for($i=0; $i<count($data); $i++) {
  //         $cheque = get_cheque_data($data[$i]);
  //           $donnees->push($cheque[0]);
  //       }
  //           return $donnees;
  // }


  // function liste_activite(){
  //   $data = DB::table('budgets')
  //   ->join('lignebudgetaires','lignebudgetaires.budget_id','lignebudgetaires.id')
  //   ->select('budgets.id','activite','libelle')
  //   ->get();
  //   return $data;
  // }

// function depense_somme(){
//   $depenses = \App\Depense::select(DB::raw('SUM(depenses.montant) as somme'),DB::raw("DATE_FORMAT(date,'%c') as months"))->whereRaw('year(`date`) = ?', array(date('Y')))->groupBy('months')->get();
//   return $depenses;
// }

// function rapportdep(){
//   $sommedepligne = somme_depense_ligne();
//        $budgetligne= ligne_par_budget();
//        $rapportdep= collect();
//        $dep =[];
//        foreach($budgetligne as $bl){
//         $tr = false;
//         foreach ($sommedepligne as $rd) {
//           if($bl->ligne_id ==$rd->lignebudgetaire_id){
//             $tr = true;
//             $rapportdep->push($rd);
//           }

//           }
//             if($tr ==false){
//               $dep = array_add($dep,'budget_id',$bl->budget_id);
//               $dep = array_add($dep,'lignebudgetaire_id',$bl->ligne_id);
//               $dep = array_add($dep,'libelle',$bl->libelle);
//               $dep = array_add($dep,'montant',$bl->montant);
//               $dep = array_add($dep,'somme',0);
//               $rapportdep->push($dep);
//               $dep =[];

//         }
//            }

//       return json_decode($rapportdep);
// }



//   function somme_depense_ligne(){
//   $montant = DB::table('depenses')
//   ->join('lignebudgetaires','depenses.lignebudgetaire_id','lignebudgetaires.id')
//   ->join('budgets','lignebudgetaires.budget_id','budgets.id')
//   ->select('lignebudgetaire_id','budgets.id as budget_id','lignebudgetaires.libelle','lignebudgetaires.montant',DB::raw('SUM(depenses.montant) as somme'))
//   ->whereRaw('year(`date`) = ?', array(date('Y')))
//   ->groupBy('lignebudgetaire_id','lignebudgetaires.montant')
//   ->get();
//     return $montant;
//   }

//   function somme_operationb(){
//   $montant = DB::table('operationb')
//   ->select('compteb_id',DB::raw('SUM(montant) as somme'))
//   ->groupBy('compteb_id')
//   ->get();
//     return $montant;
//   }

//   function get_etat_paiement($depense_id, $impression_id,$montantchk){
//   $montant = DB::table('impressioncheques')
//   ->select('depense_id',DB::raw('SUM(montant) as somme'))
//   ->where('depense_id',$depense_id)
//   ->where('id','<>',$impression_id)
//   ->groupBy('depense_id')
//   ->get();

// if($montant->count()<1)
//   $mt = 0;
// else
//   $mt = $montant[0]->somme;
//   $depmontant = \App\Depense::where('id',$depense_id)->pluck('montant')[0];
//     $result = $depmontant - ($montantchk + $mt);
//     if($result > 0)
//       return 'Partiel';
//     if($result == 0)
//       return 'Total';
//     if($result < 0)
//       return 'Erreur';

//   }

//   function get_sommemontant_ligne(){
//   $montant = DB::table('lignebudgetaires')
//   ->select('budget_id',DB::raw('SUM(montant) as somme'))
//   ->groupBy('budget_id')
//   ->get();
//     return $montant;
//   }


  // function Etat_paiement($depense_id,$etatp){
  //   $depense = \App\Depense::find($depense_id);
  //   $depense->statutP = $etatp;
  //   $depense->save();
  //   return $etatp;
  // }

  //  function check_montant_dispo_budget($budget_id, $montant){
  // $montantalloue = \App\Budget::where('id',$budget_id)->pluck('montant')[0];
  //   $montantutilise = \App\Lignebudgetaire::where('budget_id',$budget_id)->sum('montant');
  //   if($montantalloue - $montantutilise < $montant)
  //     return 0;
  //   return 1;

  // }

  // function check_dispo_budget($lignebudgetaire_id,$montant){
  //   $ligne = \App\lignebudgetaire::find($lignebudgetaire_id);
  //   $somme = \App\Lignebudgetaire::where('budget_id',$ligne->budget_id)->where('id','<>',$lignebudgetaire_id)->sum('montant');
  //   $montanb= \App\Budget::find($ligne->budget_id);
  //   if($montanb->montant - ($somme+$montant) < 0)
  //       return 0;
  //     return 1;

  //     }

  // function check_depense_ligne($lignebudgetaire_id,$montant){
  //  $montantdepligne = \App\Depense::where('lignebudgetaire_id',$lignebudgetaire_id)->sum('montant');
  //  if($montant -$montantdepligne <0 )
  //      return 0;
  //     return 1;
  //     }



  function prof_exist($nif){
    $prof = \App\Enseignant::where('nif',$nif)->get();
        if(count($prof) != 0 )
    return 1;
 return 0;
  }

  function get_matiere_classe($classe_id){
    return DB::table('matieres')
    ->join('classe_matieres','classe_matieres.matiere_id', 'matieres.id')
    ->join('classes','classe_matieres.classe_id', 'classes.id')
    ->where('classes.id', $classe_id )
    ->select('libelle','classe_matieres.id as id')
    ->get();
  }

  function get_matiere_nclasse($classe_id){
    $matieres = \App\Classe_matiere::where('classe_id', $classe_id)->pluck('matiere_id');
    return DB::table('matieres')
    ->whereNotIn('matieres.id', $matieres )
    ->select('libelle','matieres.id as id')
    ->groupBy('matieres.id')
     ->get();
  }

  function prof_nonAffect(){
    $affectations = \App\Affectation::all()->pluck('enseignant_id');
    $enseignants = \App\Enseignant::whereNotIn('id',$affectations)->get();
    return $enseignants;
  }


//   function get_rapport_budget_par_ligne(){
//     $query = DB::table('depenses')
//      ->join('lignebudgetaires','.depenses.lignebudgetaire_id','lignebudgetaires.id')
//   ->join('budgets','lignebudgetaires.budget_id','budgets.id')
//   ->where('anneefisc','2020-2021')
//   ->select('budget_id','lignebudgetaire_id','depenses.id','budgets.montant as montantb','lignebudgetaires.montant as montantl','depenses.montant as montantd','budgets.description','depenses.description as descriptiond', 'date','datefin','datedeb','activite','lignebudgetaires.libelle as libellel', 'depenses.libelle as libelled','auteur','demande', 'allow', 'statutP','prestataire_id')
//   ->orderBy('date','asc')
//   ->get();
//     return $query;
// }


  function classe_matiere(){
    return DB::table('classe_matieres')
    ->join('matieres','classe_matieres.matiere_id', 'matieres.id')
    ->join('classes','classe_matieres.classe_id', 'classes.id')
    ->select('libelle', 'matieres.id as id', 'classes.id as classe_id')
    ->get();
  }

  // function operationSolde($tr_type, $montant, $compte_id,$data){
  //    $cmp= \App\Compteb::find($compte_id);

  //      if($cmp->solde < $montant && $tr_type == 0)
  //         return false;

  //        if($tr_type == 1)
  //           $solde= $cmp->solde+$montant;
  //        else
  //          $solde=$cmp->solde-$montant;


  //       $data->solde = $solde;
  //       $cmp->solde = $solde;
  //      $operation= store_data('operationb',$data);
  //      if($operation['status'] == 1)
  //       $cmp->save();
  //      return $operation;
  // }

// function operationSupprime($tr_type, $montant, $compte_id,$data){
//      $cmp= \App\Compteb::find($compte_id)->first();
//        if($cmp->solde < $montant && $tr_type == 0)
//           return false;

//          if($tr_type == 1)
//             $solde= $cmp->solde+$montant;
//          else
//            $solde=$cmp->solde-$montant;


//         $data->solde = $solde;
//         $cmp->solde = $solde;
//        $operation= store_data('operationb',$data);
//         $cmp->save();
//       return $operation;
//   }


  function get_commune_district($district){
    return \App\Commune::where('district_id', $district)->orderBy('nom','asc')->get();

  }

  function suppression_enseignant($enseignant_id){
     $chaire_id = \App\Chaire::where('enseignant_id',$enseignant_id)->pluck('id'); if(count($chaire_id) > 0)
             $chaire = delete_data('Chaire', $chaire_id[0]);

   $finance_id = \App\Finance::where('enseignant_id',$enseignant_id)->pluck('id'); if(count($finance_id) > 0)
             $finance = delete_data('Finance', $finance_id[0]);

    $statut_id = \App\Statut::where('enseignant_id',$enseignant_id)->pluck('id'); if(count($statut_id) > 0)
             $statut = delete_data('Statut', $statut_id[0]);

   $fsouhait_id = \App\Fsouhaitee::where('enseignant_id',$enseignant_id)->pluck('id'); if(count($fsouhait_id) > 0)
             $fsouhaite = delete_data('Fsouhaitee', $fsouhait_id[0]);

      $racadem_id = \App\Racademique::where('enseignant_id',$enseignant_id)->pluck('id'); if(count($racadem_id) > 0)
             $racademique = delete_data('Racademique', $racadem_id[0]);

$funiv = \App\Funiversitaire::where('enseignant_id',$enseignant_id)->pluck('id');
   if(count($funiv) > 0)
    for($i=0; $i<count($funiv); $i++)
             $funiversite = delete_data('Funiversitaire', $funiv[$i]);

 $fprof = \App\Fprofessionnelle::where('enseignant_id',$enseignant_id)->pluck('id');
   if(count($fprof) > 0)
    for($i=0; $i<count($fprof); $i++)
             $fprofessionnel = delete_data('Fprofessionnelle', $fprof[$i]);

   $ensniv_id = \App\Enseignantniveau::where('enseignant_id',$enseignant_id)->pluck('id');
   if(count($ensniv_id) > 0)
    for($i=0; $i<count($ensniv_id); $i++)
             $ensniveau = delete_data('Enseignantniveau', $ensniv_id[$i]);

      $affectation_id = \App\Affectation::where('enseignant_id', $enseignant_id)->pluck('id');
      if(count($affectation_id) > 0)
        for ($i=0; $i <count($affectation_id) ; $i++) {
       $ensmat_id = \App\Enseignantmatiere::where('affectation_id',$affectation_id[$i])->pluck('id');
        if(count($ensmat_id) > 0)
          for($j=0; $j<count($ensmat_id); $j++)
             $ensmatiere = delete_data('Enseignantmatiere', $ensmat_id[$j]);
     }


   if(count($affectation_id) > 0)
    for($i=0; $i<count($affectation_id); $i++)
             $affectation = delete_data('Affectation', $affectation_id[$i]);
      $enseignant = delete_data('Enseignant', $enseignant_id);
          return 1;

  }


  function get_array_data($type){

if($type == 'formation')
    return ['Sciences de l\'Education','ENS', 'ENI','FIA', 'Capiste', 'CEFEF', 'Jardiniere', 'Autres'];

if($type == 'scolarite')
    return ['Niveau secondaire (3e a Philo)', 'Niveau Fondamental 3e cycle (7e a 9e)','Niveau Fondamental 1er et 2e cycles'];

if($type == 'universite')
  return ['Certificat - Attestation', 'Diplome', 'Licence', 'Maitrise', 'Doctorat'];

if($type == 'chaire')
  return ['1 Chaire', '2 Chaires', '3 Chaires', 'Temps plein'];

if($type == 'niveau')
  return ['Prescolaire', 'Fondamental 1er et 2e Cycles', 'Fondamental 3e Cycle', 'Secondaire'];
  }



  function get_liste_prof(){
    $enseignant = DB::table('affectations')
    ->join('enseignants', 'affectations.enseignant_id', 'enseignants.id')
    ->select('enseignants.id', 'nom', 'prenom', 'ecole_id', 'classe_id')
    ->orderBy('nom', 'asc')
    ->get();
    return $enseignant;
  }
  function get_liste_question(){
    $question = DB::table('qmclasses')
    ->join('questions', 'qmclasses.question_id', 'questions.id')
    ->join('classe_matieres', 'qmclasses.classe_matiere_id', 'classe_matieres.id')
    ->select('questions.id', 'libelle', 'classe_id', 'matiere_id', 'qmclasses.id as qmc')
     ->get();
    return $question;
  }

  function get_nb_question($classe_id, $matiere_id){
    $nb_question = DB::table('qmclasses')
    ->join('questions', 'qmclasses.question_id', 'questions.id')
    ->join('classe_matieres', 'qmclasses.classe_matiere_id', 'classe_matieres.id')
    ->where('classe_id', $classe_id)
    ->where('matiere_id', $matiere_id)
    ->select('questions.id', 'libelle', 'classe_id', 'matiere_id', 'qmclasses.id as qmc')
     ->count();
    return $nb_question;
  }

  function get_liste_matiere(){
    $liste_matiere = DB::table('classe_matieres')
    ->join('matieres', 'classe_matieres.matiere_id', 'matieres.id')
    ->join('classes', 'classe_matieres.classe_id', 'classes.id')
    ->select('classes.id as classe_id', 'matieres.id as matiere_id' ,'libelle', 'nomclasse')
    ->orderBy('libelle', 'asc')
    ->get();
    return $liste_matiere;
  }


  function modifEcole(){
    $ecole = DB::table('ecoles')
    ->join('zones','ecoles.zone_id','zones.id')
    ->join('section_communales','ecoles.section_communale_id','section_communales.id')
    ->join('communes','zones.commune_id','communes.id')
   ->join('districts','communes.district_id','districts.id')
    ->join('categories', 'categories.ecole_id', 'ecoles.id')
    ->join('niveauenseignements', 'niveauenseignements.ecole_id', 'ecoles.id')
    ->join('vacations', 'vacations.ecole_id', 'ecoles.id')
    ->join('directeurs', 'directeurs.ecole_id', 'ecoles.id')
    //->where('ecoles.id',$id)
    ->select('ecoles.id', 'districts.nom as district', 'communes.nom as commune', 'zones.nom as zone'
              ,'section_communales.nom as section', 'ecoles.nom', 'tel', 'teld', 'adresse', 'adressed', 'email',
              'nomd', 'prenom', 'sexe', 'niveau', 'milieu', 'secteur', 'location', 'statut', 'vacation', 'categorie',
              'ecoles.latitude', 'ecoles.longitude', 'sigle', 'code','fondateur','cin', 'nif', 'datenais', 'lieunais',
                'categories.id as categorie_id', 'niveauenseignements.id as niveau_id', 'vacations.id as vacation_id',
                'directeurs.id as directeur_id', 'districts.id as district_id', 'zone_id', 'communes.id as commune_id',
                'section_communale_id', 'acces')
    ->get();
    return $ecole;
  }

 function get_date(){
     $jour = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $mois = ['Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre','Octobre', 'Novembre', 'Decembre'];
       $df = $jour[date('w')]. " ".date('d')." ".$mois[date('n')-1]." ".date('Y');
                return ($df);
   }


function salut(){
    if(date('H') >= 00 AND date('H') < 13)
      return 'Bonjour !';
    elseif(date('H') >= 13 AND date('H') < 18)
      return 'Bonne après-midi !';

      return 'Bonsoir !';

 }

  function remove_special_char($text) {

        $t = $text;

        $specChars = array(
            ' ' => '-',    '!' => '',    '"' => '',
            '#' => '',    '$' => '',    '%' => '',
            '&amp;' => '',    '\'' => '',   '(' => '',
            ')' => '',    '*' => '',    '+' => '',
            ',' => '',    '₹' => '',    '.' => '',
            '/-' => '',    ':' => '',    ';' => '',
            '<' => '',    '=' => '',    '>' => '',
            '?' => '',    '@' => '',    '[' => '',
            '\\' => '',   ']' => '',    '^' => '',
            '_' => '',    '`' => '',    '{' => '',
            '|' => '',    '}' => '',    '~' => '',
            '-----' => '-',    '----' => '-',    '---' => '-',
            '/' => '',    '--' => '-',   '/_' => '-',

        );

        foreach ($specChars as $k => $v) {
            $t = str_replace($k, $v, $t);
        }

        return $t;
  }

 function date_check($input,$devider){
      $output = false;

      $input = explode($devider, $input);
      $year = $input[0];
      $month = $input[1];
      $day = $input[2];

      if (is_numeric($year) && is_numeric($month) && is_numeric($day)) {
        if (strlen($year) == 4 && strlen($month) == 2 && strlen($day) == 2) {
          $output = true;
        }
      }
      return $output;
    }

  function arraytoString($array){
        $string='';
     foreach ($array as $value){
        $string .=  $value.',';
     }
     return ($string);
  }

function format_id($nom, $sexe, $prenom, $idfac, $last_id) {
          $last_id = Str::substr($last_id, 4, 4);
          $etucount = (int) $last_id;
          $etucount++;
          $frmt = $etucount;

          if ($etucount<10){
             $frmt = "000".$frmt;
          }elseif ($etucount<100)
             {
                $frmt = "00".$frmt;
             }elseif ($etucount<1000)
               {
                  $frmt = "0".$frmt;
               }elseif ($etucount<10000)
                   {
                      $frmt = $frmt;
                   }


        return (Str::substr($nom, 0, 1).$sexe.Str::substr($prenom, 0, 1).$idfac.$frmt);

}

//inserer des donnees dans une table : $champfill : champs a remplir et champval: valeur des champs a remplir
function insert($ModelName, $champfill, $champval){
  try{
    $mn = new $ModelName;
    for($i=0; $i<count($champfill);$i++){
      if ($champfill[$i]=='password') {
        $champval[$i]= Hash::make('$champval[$i]', ['rounds' => 12]);
      }
       $mn->{$champfill[$i]} = $champval[$i];
    }
    $mn->save();
    return ($mn->id);
  }catch(\Illuminate\Database\QueryException $ex){
     return false;
  //   return($ex->getMessage());
  }
}

function stat_decision(){
    $dec = DB::table('decisions')
    ->join('classeleves', 'decisions.classeleve_id', 'classeleves.id')
    ->where('anac',session_new_year())->count();

    $el = \App\Classeleve::where('anac', session_new_year())->count();

    $percent =number_format(( $dec / $el)*100, 2);
    return  ['percent'=>$percent, 'dec'=>$dec];
  }

function decision_stat(){
 $dec= DB::table('decisions')
       ->select('anac',  DB::raw('count(decisions.id) as nb_decision'))
       ->join('classeleves', 'decisions.classeleve_id', 'classeleves.id')
       ->where('anac', session_new_year())
       ->groupBy('anac')->get();

   // $el = \App\Classeleve::select('anac',  DB::raw('count(id) as nb_el'))
   //      ->where('anac',session_new_year())->groupBy('anac')->get();;

      return $dec;
}


function stat_ecole_par_district(){
  $nb_ecole =   DB::table('ecoles')
                ->select('districts.nom','districts.id',  DB::raw('count(ecoles.id) as nb_ecole'))
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                ->join('districts','communes.district_id','districts.id')
                ->groupBy('districts.nom','districts.id')
                ->orderBy('districts.nom', 'asc' )
                ->get();

           return($nb_ecole);

}

function stat_eleve_par_ecole_district(){
  $nb_eleve =   DB::table('classeleves')
                ->select('ecoles.nom as etab','ecoles.id', 'communes.nom as commune', DB::raw('count(eleve_id) as nb_eleve'))
                 ->join('ecoles','classeleves.ecole_id','ecoles.id')
                 ->join('eleves','classeleves.eleve_id','eleves.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                ->join('districts','communes.district_id','districts.id')
                ->where('districts.id',42)
                ->where('ecoles.secteur',0)
                ->where('sexe',0)
                ->whereBetween('classe_id',[4,12])
                ->groupBy('ecoles.nom','ecoles.id','communes.nom')
                ->orderBy('ecoles.nom', 'asc' )
                ->get();

           return($nb_eleve);

}

function perform_operat($dated, $datef){
  $saisie_ope =   DB::table('eleves')
                ->select('name','user_id as id',  DB::raw('count(user_id) as nb_saisie'))
                 ->join('users','eleves.user_id','users.id')
                 ->whereBetween('eleves.created_at', [$dated, $datef])
                ->groupBy('name','user_id')
                ->orderBy('name', 'asc' )
                ->get()->toArray();

           return($saisie_ope);

}

function stat_eleve_par_district(){
  $anac = session_new_year();
  $nb_ecole =   DB::table('classeleves')
                ->select('districts.nom','districts.id',  DB::raw('count(eleves.id) as nb_eleve'))
                 ->join('eleves','classeleves.eleve_id','eleves.id')
                 ->join('ecoles','classeleves.ecole_id','ecoles.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                ->join('districts','communes.district_id','districts.id')
                ->where('anac', $anac)
                ->groupBy('districts.nom','districts.id')
                ->orderBy('districts.nom', 'asc' )
                ->get();
           return($nb_ecole);
}

function get_eleve_info(){
  $nb_ecole =  DB::table('classeleves')
            ->join('eleves','classeleves.eleve_id','eleves.id')
            ->join('classes','classeleves.classe_id','classes.id')
            ->join('ecoles','classeleves.ecole_id','ecoles.id')
            ->join('zones','ecoles.zone_id','zones.id')
            ->join('communes','zones.commune_id','communes.id')
            ->join('districts','communes.district_id','districts.id')
            ->join('departements','districts.departement_id','departements.id')
            ->where('departements.id','04')
             ->where('anac', session_new_year())
            ->select('districts.nom as district','districts.id as district_id','communes.id as commune_id','zones.id as zone_id','zones.nom as zone','communes.nom as commune','ecoles.id as ecole_id','ecoles.nom as ecole','secteur','milieu','sexe','eleves.id as eleve_id','eleves.nom','prenom','datenais','prenom_mere','tel_persrep','deficience','dept_n','classes.id as classe_id','nomclasse','departements.id as departement_id','lieunais', 'classeleves.id as classeleve_id')
            ->orderBy('districts.nom', 'asc' )
             ->orderbyRaw("concat(eleves.nom,' ',eleves.prenom)")
            ->get();
           return ($nb_ecole);
}


function getID_decision($ecole_id,$classe_id){
  $anac = session_new_year();
  //liste eleve par classe pour une ecole
       $decision =  DB::table('classeleves')
            ->join('eleves','classeleves.eleve_id','eleves.id')
            ->join('classes','classeleves.classe_id','classes.id')
            ->join('ecoles','classeleves.ecole_id','ecoles.id')                   
            ->where('anac',$anac)
            ->where('ecoles.id',$ecole_id)
            ->where('classes.id',$classe_id)
            ->select('classeleves.id')
            ->get();

      //conversion liste en tableau id
              $tab_id = [];
        foreach($decision as $deci){
          array_push($tab_id,$deci->id);
        }
       return json_encode($tab_id);
      }

function getID_initialised($ecole_id,$classe_id,$anac){ 
  //liste eleve par classe pour une ecole donnee
   $decision =  DB::table('classeleves')
            ->join('eleves','classeleves.eleve_id','eleves.id')
            ->join('classes','classeleves.classe_id','classes.id')
            ->join('ecoles','classeleves.ecole_id','ecoles.id')                   
            ->where('anac',$anac)
            ->where('ecoles.id',$ecole_id)
            ->where('classes.id',$classe_id)
            ->select('classeleves.id')
            ->get();
//conversion liste en tableau
       $tab_id = [];
        foreach($decision as $deci){
           array_push($tab_id,$deci->id);
        }
  // liste eleve deja initialise 
      $el = DB::table('decisions')
          ->whereIn('classeleve_id',$tab_id)
          ->select('classeleve_id')
          ->get();
//conversion liste en tableau
          $liste = [];
        foreach($el as $dec){
          array_push($liste, $dec->classeleve_id);
        } 

    //liste eleve non initialise
         $liste_ni =  DB::table('classeleves')
            ->join('eleves','classeleves.eleve_id','eleves.id')
            ->join('classes','classeleves.classe_id','classes.id')
            ->join('ecoles','classeleves.ecole_id','ecoles.id')                   
            ->where('anac',$anac)
            ->where('ecoles.id',$ecole_id)
            ->where('classes.id',$classe_id)
            ->whereNotIn('classeleves.id',$liste)
            ->select('classeleves.id')
            ->get();

           return ($liste_ni);
}

function initialisation_decision($ecole_id,$classe_id,$anac){
  $liste_ni = getID_initialised($ecole_id,$classe_id, $anac);
  $n =0;
    foreach($liste_ni as $ls)
    {
      $data = ['mention'=>'Select mention','moyenne'=>0.00, 'classeleve_id'=>$ls->id];
      $dec = store_data('Decision', $data);
      if($dec['status']!=1){          
          break;
           \Log::debug($dec['message']);
          return false;
       }
       if($classe_id > 12){
            $info = ['decision_id'=>$dec['data']['id'], 'annee'=>getanneeNeuv($classe_id),'nordre'=>'00000000'];
            $inf = store_data('Infoneuf',$info);
         if($inf['status'] !=1){
           break;
             \Log::debug($inf['message']);
          return false;
         }
       }
        $n++;
    }
  return $n;
}


function get_decision($ecole_id,$classe_id, $anac ){
      $init = initialisation_decision($ecole_id,$classe_id,$anac);
      if($classe_id < 13)
       $decision =  DB::table('decisions')
            ->join('classeleves','decisions.classeleve_id','classeleves.id')
            ->join('eleves','classeleves.eleve_id','eleves.id')
            ->join('classes','classeleves.classe_id','classes.id')
            ->join('ecoles','classeleves.ecole_id','ecoles.id')                   
            ->where('anac',$anac)
            ->where('ecoles.id',$ecole_id)
            ->where('classes.id',$classe_id)
            ->select('decisions.id as decision_id','classeleves.id as id','eleves.id as eleve_id','eleves.nom','prenom', 'mention','moyenne','prenom_mere', 'tel_persrep','lieunais','datenais','ecoles.id as ecole_id', 'classes.id as classe_id', 'sexe')
            ->orderbyRaw("concat(eleves.nom,' ',eleves.prenom)")
            ->get();
          
          else
             $decision =  DB::table('decisions')
            ->join('infoneufs','infoneufs.decision_id','decisions.id')
            ->join('classeleves','decisions.classeleve_id','classeleves.id')
            ->join('eleves','classeleves.eleve_id','eleves.id')
            ->join('classes','classeleves.classe_id','classes.id')
            ->join('ecoles','classeleves.ecole_id','ecoles.id')                   
            ->where('anac',$anac)
            ->where('ecoles.id',$ecole_id)
            ->where('classes.id',$classe_id)
            ->select('decisions.id as decision_id','classeleves.id as id','eleves.id as eleve_id','eleves.nom','prenom', 'mention','moyenne','nordre','annee','decision_id','prenom_mere', 'tel_persrep','lieunais','datenais','ecoles.id as ecole_id', 'classes.id as classe_id', 'sexe')
             ->orderbyRaw("concat(eleves.nom,' ',eleves.prenom)")
            ->get();
           return ($decision); 
      
}

function update_decision($data){
    $id = \App\Decision::where('classeleve_id', $data->classeleve_id)->pluck('id')[0];
    $dec = ['mention'=>$data->mention, 'moyenne'=>$data->moyenne, 'classeleve_id'=>$data->classeleve_id];
     if(update_data('Decision', $dec, $id)['status'] ==1){
        $classe_id = (\App\Classeleve::find($data->classeleve_id))->classe_id;
      if($classe_id>12){
        $info_id = \App\Infoneuf::where('decision_id', $id)->pluck('id')[0];
         $info = ['annee'=>$data->annee, 'nordre'=>$data->nordre, 'decision_id'=>$id];   
           if(update_data('Infoneuf', $info, $info_id)['status'] ==1)
             return 1;
      }
      return 1;
    }
   
  return 0;
}


function getanneeNeuv($classe_id){ 
  $anac = session_new_year();
    $n= $classe_id - 12;
    $annees = explode('-', $anac);
  return (($annees[0]-$n) .'-'. ($annees[1]-$n));  

}

function get_eleve_info1($commune){
       $nb_ecole =  DB::table('classeleves')
            ->join('eleves','classeleves.eleve_id','eleves.id')
            ->join('classes','classeleves.classe_id','classes.id')
            ->join('ecoles','classeleves.ecole_id','ecoles.id')
            ->join('zones','ecoles.zone_id','zones.id')
            ->join('communes','zones.commune_id','communes.id')
            ->join('districts','communes.district_id','districts.id')
            ->join('departements','districts.departement_id','departements.id')
            ->where('departements.id','04')
            ->where('communes.id',$commune)
           ->where('anac',  session_new_year())
            ->select('districts.nom as district','districts.id as district_id','communes.id as commune_id','zones.id as zone_id','zones.nom as zone','communes.nom as commune','ecoles.id as ecole_id','ecoles.nom as ecole','secteur','milieu','sexe','eleves.id as eleve_id','eleves.nom','prenom','datenais','prenom_mere','tel_persrep','deficience','dept_n','classes.id as classe_id','nomclasse','departements.id as departement_id','lieunais')
          //  ->orderBy('districts.nom', 'asc' )
             ->orderbyRaw("concat(eleves.nom,' ',eleves.prenom)")
            ->get();
           return ($nb_ecole);
}



function check_eleve($eleve_id, $anac){
    $classeleve = \App\Classeleve::where('eleve_id',$eleve_id)->where('anac', $anac)->get();
    if($classeleve->count() > 0)
      return $classeleve[0]->id; 
    return 0;
    }

function promotion_eleve_admis($decision, $anac){
    $classeleve = \App\Classeleve::where('eleve_id',$decision->eleve_id)->where('anac', $anac)->get();   
  $data = ['eleve_id' =>$decision->eleve_id,
            'ecole_id'=> $decision->ecole_id,            
            'anac' =>$anac
];
  if(count($classeleve)  == 1){
             $data['status'] = 'Nouveau';
            $data['classe_id'] = $decision->classe_id + 1;
            $pro = update_data('Classeleve', $data, $classeleve[0]->id);
              if($pro['status'] == 0){
                 \Log::debug($pro['message']);
                 return 0;           
              }
                 
      }
  else{
        $data['status'] = 'Nouveau';
        $data['classe_id'] = $decision->classe_id + 1;
        $pro = store_data('Classeleve', $data);
        if($pro['status'] == 0){
           \Log::debug($pro['message']); 
           return 0;         
        }
         $expulse = \App\Expulse::where('classeleve_id', $decision->id)->get();
          if($expulse->count() == 1){
            $result = delete_data('Expulse', $expulse[0]->id);
            \Log::debug($result['message']); 
          }
        else{
          $abandon = \App\Abandon::where('classeleve_id', $decision->id)->get();
          if($abandon->count() == 1){
            $result = delete_data('Abandon', $abandon[0]->id);
            \Log::debug($result['message']); 
          }
       }

     }
  return 1;
}

function promotion_eleve_arefaire($decision, $anac){
   $classeleve = \App\Classeleve::where('eleve_id',$decision->eleve_id)->where('anac', $anac)->get(); 
   
  $data = ['eleve_id' =>$decision->eleve_id,
            'ecole_id'=> $decision->ecole_id,            
            'anac' =>$anac
];
  if($classeleve->count()  == 1){
     $id = $classeleve[0]->id;
    $data['status'] = 'Redoublant';
            $data['classe_id'] = $decision->classe_id;
            $pro = update_data('Classeleve', $data, $id);
            if($pro['status'] == 0){
               \Log::debug($pro['message']); 
               return 0;          
            }
           }
  else{
     $data['status'] = 'Redoublant';
        $data['classe_id'] = $decision->classe_id;
        $pro = store_data('Classeleve', $data);
        if($pro['status'] == 0){
           \Log::debug($pro['message']); 
           return 0;          
        }
         $expulse = \App\Expulse::where('classeleve_id', $decision->id)->get();
          if($expulse->count() == 1){
            $result = delete_data('Expulse', $expulse[0]->id);
            \Log::debug($result['message']); 
          }
        else{
          $abandon = \App\Abandon::where('classeleve_id', $decision->id)->get();
          if($abandon->count() == 1){
            $result = delete_data('Abandon', $abandon[0]->id);
            \Log::debug($result['message']); 
          }
       }
       }
    return 1; 
}


function promotion_eleve_ailleur($decision, $anac){
   $ailleur = \App\Expulse::where('classeleve_id', $decision->id)->get();
      $dataExp = ['classeleve_id' => $decision->id,
                  'decision_id' => $decision->decision_id];
                    
      if($ailleur->count() > 0){
           $exp = update_data('Expulse', $dataExp, $ailleur[0]->id);
        if($exp['status'] == 0){
           \Log::debug($exp['message']); 
           return 0;          
        }
       
    }
    else{
       $exp = store_data('Expulse', $dataExp);
        if($exp['status'] == 0){
           \Log::debug($exp['message']);  
           return 0;         
        }
          $classeleve = \App\Classeleve::where('eleve_id',$decision->eleve_id)->where('anac', $anac)->get();
        if($classeleve->count() == 1){
          $result = delete_data('Classeleve', $classeleve[0]->id);
          \Log::debug($result['message']); 
        }
        else{
          $abandon = \App\Abandon::where('classeleve_id', $decision->id)->get();
          if($abandon->count() == 1){
            $result = delete_data('Abandon', $abandon[0]->id);
            \Log::debug($result['message']); 
          }
        }

      
    }
    return 1;
}

function eleve_abandon($decision, $anac){
   $abandon = \App\Abandon::where('classeleve_id', $decision->id)->get();
      $dataAb = ['classeleve_id' => $decision->id ];
                 
      if($abandon->count() == 1){
           $ab = update_data('Abandon', $dataAb, $abandon[0]->id);
        if($ab['status'] == 0){
           \Log::debug($ab['message']);
           return 0;           
        }
      
       }
     else{
       $ab = store_data('Abandon', $dataAb);
        if($ab['status'] == 0){
           \Log::debug($ab['message']); 
           return 0;          
        }

        $classeleve = \App\Classeleve::where('eleve_id',$decision->eleve_id)->where('anac', $anac)->get();
        if($classeleve->count() == 1){
          $result = delete_data('Classeleve', $classeleve[0]->id);
          \Log::debug($result['message']); 
        }
        else{
          $expulse = \App\Expulse::where('classeleve_id', $decision->id)->get();
          if($expulse->count() == 1){
            $result = delete_data('Expulse', $expulse[0]->id);
            \Log::debug($result['message']); 
          }
        }
       
    }
    return 1;
}


function update_select_mention($decision, $anac){

        $classeleve = \App\Classeleve::where('eleve_id',$decision->eleve_id)->where('anac', $anac)->get();
        if($classeleve->count() == 1){
          $result = delete_data('Classeleve', $classeleve[0]->id);
          \Log::debug($result['message']); 
          return 1;
        }
        else{
          $expulse = \App\Expulse::where('classeleve_id', $decision->id)->get();
          if($expulse->count() == 1){
            $result = delete_data('Expulse', $expulse[0]->id);
            \Log::debug($result['message']); 
            return 1;
          }
        
      else{
          $abandon = \App\Abandon::where('classeleve_id', $decision->id)->get();
          if($abandon->count() == 1){
            $result = delete_data('Abandon', $abandon[0]->id);
            \Log::debug($result['message']); 
            return 1;
          }
       }
        }
        return 1;
}





function promotion_classe($ecole_id, $classe_id, $anac){
  $anacsuiv = annee_suiv($anac);
  $listedec = get_decision($ecole_id, $classe_id,$anac);

  $n=0;
  foreach($listedec as $dec){
    if($dec->mention == 'Admis')
      $pro =  promotion_eleve_admis($dec, $anacsuiv); 
    if($dec->mention == 'A refaire')
      $pro =  promotion_eleve_arefaire($dec, $anacsuiv);
    if($dec->mention == 'A refaire ailleurs' || $dec->mention == 'Admis ailleurs')
      $pro =  promotion_eleve_ailleur($dec, $anacsuiv);
    if($dec->mention == 'Abandon')
      $pro =  eleve_abandon($dec, $anacsuiv);
    if($dec->mention == 'Select mention')
      $pro = update_select_mention($dec, $anacsuiv);

    
    if($pro == '0'){
      break;
      return 0; 
    }
    $n++;
  }
  return $n;
}

function inspecteur_par_ecole($user_id){
$inspecteur_id = \App\Inspecteur::where('user_id', $user_id)->pluck('id')[0];
$ecoles= DB::table('zones')
->join('ecoles','ecoles.zone_id','zones.id')
->join('inspecteur_zones','inspecteur_zones.zone_id','zones.id')
->join('inspecteurs','inspecteur_zones.inspecteur_id','inspecteurs.id')
->where('inspecteurs.id', $inspecteur_id)
->select('*')
->get();
return $ecoles;
}
function get_form_question($form_id){
    $questions = DB::table('questionnaires')
    ->join('groupes', 'questionnaires.groupe_id','groupes.id')
    ->join('forms', 'groupes.form_id','forms.id')
    ->where('forms.id', $form_id)
    ->select('groupes.id as groupe_id', 'libelle','type_q', 'questionnaires.id')
    ->get();
    return($questions);
}

function get_form_option($form_id){
    $options = DB::table('options')
    ->join('questionnaires', 'options.questionnaire_id', 'questionnaires.id')
   ->join('groupes', 'questionnaires.groupe_id','groupes.id')
    ->join('forms', 'groupes.form_id','forms.id')
    ->where('forms.id', $form_id)
    ->select('options.id','options.libelle','questionnaires.id as question_id')
    ->get();
    return($options);
}

function stat_ecole_par_zone($user_id){
  $district_id = \App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];

  $nb_ecole =   DB::table('ecoles')
                ->select('districts.nom','districts.id','zones.nom',  DB::raw('count(ecoles.id) as nb_ecole'))
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                ->join('districts','communes.district_id','districts.id')
                ->where('communes.district_id',$district_id)
                ->groupBy('zones.nom','zones.id')
                ->orderBy('zones.nom', 'asc' )
                ->get();

           return($nb_ecole);
      }

  function zone_par_district($user_id){
  $district_id = \App\Insprincipal::where('user_id',$user_id)->get()[0]->district_id;

  $zone_dis =   DB::table('zones')
                ->select('zones.id as value','zones.nom as text') 
                ->join('communes','zones.commune_id','communes.id')
                ->join('districts','communes.district_id','districts.id')
                ->where('communes.district_id',$district_id)
                ->orderBy('zones.nom', 'asc' )
                ->get();

           return($zone_dis);
      }


function modif($ModelName, $champfill, $champval, $id){
  try{
    $mn=$ModelName::find($id);
    for($i=0; $i<count($champfill);$i++){
      if ($champfill[$i]=='password') {
        $champval[$i]= Hash::make('$champval[$i]', ['rounds' => 12]);
      }
       $mn->{$champfill[$i]} = $champval[$i];
    }
    $mn->save();
    return ($id);
  }catch(\Illuminate\Database\QueryException $ex){
    return false;
  }
}

function nb_inspecteur(){
  return( \App\Inspecteur::all()->count());
}


function nb_enseignant(){
  return( \App\Enseignant::all()->count());
}

function nb_ecole(){
    return(\App\Ecole::all()->count());
}

function nb_supervision(){
  return(\App\Observation::all()->count());
}

function nb_zone(){
  return( \App\Zone::all()->count());
}
function nb_eleve(){
  return( \App\Eleve::all()->count());
}

function nb_ecole_supervise(){
  return(\App\Observation::distinct('ecole_id'))->count();
}

function nb_enseignant_by_sexef(){
  return(\App\Enseignant::where('sexe',0)->count());
}
function nb_enseignant_by_sexeg(){
  return(\App\Enseignant::where('sexe',1)->count());
}

function nb_eleve_by_sexef(){
  return(\App\Eleve::where('sexe',0)->count());
}

function nb_eleve_by_sexeg(){
  return(\App\Eleve::where('sexe',1)->count());
}

function nb_prive(){
   return DB::table('classeleves')
  ->join('eleves','classeleves.eleve_id','eleves.id')
  ->join('ecoles','classeleves.ecole_id','ecoles.id')
  ->where('secteur',0)
  ->count();
}
function nb_public(){
  return DB::table('classeleves')
  ->join('eleves','classeleves.eleve_id','eleves.id')
  ->join('ecoles','classeleves.ecole_id','ecoles.id')
  ->where('secteur',1)
  ->count();
}


function get_enseignant_by_discom(){
  $enseignants = DB::table('affectations')
  ->join('enseignants', 'affectations.enseignant_id','enseignants.id')
  ->join('ecoles', 'affectations.ecole_id','ecoles.id')
  ->join('section_communales', 'ecoles.section_communale_id','section_communales.id')
  ->join('communes', 'section_communales.commune_id','communes.id')
  ->join('districts', 'communes.district_id','districts.id')
 // ->where('districts.id',$district)
  ->select('enseignants.nom', 'enseignants.id', 'prenom', 'nif','sexe', 'date_naissance','enseignants.telephone','date_EFonction', 'district_id')
  ->orderBy('nom','asc')
  ->distinct('affectations.enseignant_id')
  ->get();
  return count($enseignants);
}



function get_enseignant_pdf($district){
  $enseignants = DB::table('affectations')
  ->join('enseignants', 'affectations.enseignant_id','enseignants.id')
  ->join('ecoles', 'affectations.ecole_id','ecoles.id')
  ->join('section_communales', 'ecoles.section_communale_id','section_communales.id')
  ->join('communes', 'section_communales.commune_id','communes.id')
  ->join('districts', 'communes.district_id','districts.id')
  ->where('districts.id',$district)
  ->select('enseignants.nom', 'enseignants.id', 'prenom', 'nif','sexe', 'date_naissance','enseignants.telephone','date_EFonction', 'district_id')
  ->orderBy('nom','asc')
  ->distinct('affectations.enseignant_id')
  ->get();
  return $enseignants;
}


function last_info_batiment($ecole_id){
$batiment =  \App\Structurebatiment::where('ecole_id', $ecole_id)->latest('dateEvaluation')->first();
if($batiment != null){
$last_date = $batiment->dateEvaluation;
 return \App\Structurebatiment::where('ecole_id', $ecole_id)->where('dateEvaluation', $last_date)->get();
}
return 0;
}

function delete_etat_batiment($ecole_id){
    $res=supprime_existe_lastdate_etat($ecole_id);
    return $res;
}

function nb_secteur_pub($district=0,$commune=null, $zone=null,  $niv=null){
 $query = DB::table('ecoles');
$query->select('districts.id','communes.id','zones.id');
$query->join('directeurs','directeurs.ecole_id','ecoles.id')
->join('section_communales','ecoles.section_communale_id','section_communales.id')
->join('zones','ecoles.zone_id','zones.id')
->join('communes','zones.commune_id','communes.id')
->join('districts','communes.district_id','districts.id')
->join('niveauenseignements','niveauenseignements.ecole_id','ecoles.id');


    if($district == 0){
      //$query->get();
    }
    elseif($commune ==0){
      $query->where('districts.id', $district);
    }
      elseif($zone ==0){
          $query->where('districts.id', $district)->where('communes.id', $commune);
      }

          else{
                 $query->where('districts.id', $district)->where('communes.id', $commune)->where('zones.id', $zone);
              }


       if($niv !=0){
          if(strlen($niv) != 5)
             $query->where('niveau1', $niv);
          else{
            $c_niveau = check_niveau($niv);
              $query->where('niveau1' ,'like', $c_niveau);
            }
      }

      $query->where('secteur', 1);

     $listecole = $query->count();
    return $listecole;
}



function nb_fille_by_district($user_id){
  $district_id = \App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];
  $nb_ecole =   DB::table('enseignants')
                ->select('districts.nom','districts.id','zones.nom','sexe',  DB::raw('count(enseignants.id) as nb_ecole'))
                  ->join('affectations','affectations.enseignant_id','enseignants.id')
                 ->join('ecoles','affectations.ecole_id','ecoles.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                ->join('districts','communes.district_id','districts.id')
                ->where('communes.district_id',$district_id)
                ->where('sexe',0)
                ->count();

           return($nb_ecole);

}
function nb_garcon_by_district($user_id){
  $district_id = \App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];
  $nb_ecole =   DB::table('enseignants')
                ->select('districts.nom','districts.id','zones.nom','sexe',  DB::raw('count(enseignants.id) as nb_ecole'))
                  ->join('affectations','affectations.enseignant_id','enseignants.id')
                 ->join('ecoles','affectations.ecole_id','ecoles.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                ->join('districts','communes.district_id','districts.id')
                ->where('communes.district_id',$district_id)
                ->where('sexe',1)
                ->count();

           return($nb_ecole);
}
function nb_prof_by_district($user_id){
  $district_id = \App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];
  $nb_ecole =   DB::table('enseignants')
                ->select('districts.nom','districts.id','zones.nom','enseignants.id',  DB::raw('count(enseignants.id) as nb_ecole'))
                  ->join('affectations','affectations.enseignant_id','enseignants.id')
                 ->join('ecoles','affectations.ecole_id','ecoles.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                ->join('districts','communes.district_id','districts.id')
                ->where('communes.district_id',$district_id)
                ->count();

           return($nb_ecole);
}
function nb_supervision_by_district($user_id){
  $district_id = \App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];
  $nb_ecole =   DB::table('observations')
                ->select('districts.nom','districts.id','zones.nom','observations.id',  DB::raw('count(observations.id) as nb_ecole'))
                  ->join('ecoles','observations.ecole_id','ecoles.id')
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                ->join('districts','communes.district_id','districts.id')
                ->where('communes.district_id',$district_id)
                ->count();

           return($nb_ecole);
}

function stat_zone_par_district($user_id){
  $district_id = \App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];
  $nb_ecole =   DB::table('zones')
                ->select('districts.nom','districts.id','zones.nom',  DB::raw('count(zones.id) as nb_ecole'))
                 ->join('communes','zones.commune_id','communes.id')
                ->join('districts','communes.district_id','districts.id')
                ->where('communes.district_id',$district_id)
                ->count();

           return($nb_ecole);

}
function nb_ecole_par_district($user_id){
  $district_id = \App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];
  $nb_ecole =   DB::table('ecoles')
                ->select('districts.nom','districts.id','zones.nom','ecoles.nom',  DB::raw('count(ecoles.id) as nb_ecole'))
                 ->join('zones','ecoles.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                ->join('districts','communes.district_id','districts.id')
                ->where('communes.district_id',$district_id)
                ->count();
           return($nb_ecole);
    }

function est_ecole_par_district($user_id){//estimation
  $district_id = \App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];
  $est_par_district = [
                        '41'=>110,
                        '42'=>199,
                        '43'=>105,
                        '44'=>48,
                        '45'=>40,
                        '46'=>48
                    ];
                if($district_id > 41 && $district_id >46)
                return 0;
             return $est_par_district[$district_id];
    }

function nb_inspecteur_par_district($user_id){
  $district_id = \App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];
  $nb_ecole =   DB::table('inspecteur_zones')
                ->select('districts.nom','districts.id','zones.nom','inspecteur_id',  DB::raw('count(inspecteur_id) as nb_ecole'))
                 ->join('zones','inspecteur_zones.zone_id','zones.id')
                 ->join('communes','zones.commune_id','communes.id')
                ->join('districts','communes.district_id','districts.id')
                ->where('communes.district_id',$district_id)
                ->count();
           return($nb_ecole);

}
function get_all_data_ecole($ecole_id=0){
  $req = DB::table('ecoles');
  $req->join('categories', 'categories.ecole_id', 'ecoles.id');
  $req->join('vacations', 'vacations.ecole_id', 'ecoles.id');
  $req->join('niveauenseignements', 'niveauenseignements.ecole_id', 'ecoles.id');
  $req->join('directeurs', 'directeurs.ecole_id', 'ecoles.id');

  $req->select('*');
   if($ecole_id != 0)
  $req->where('ecoles.id', $ecole_id);
$ecole =  $req->get();

  $reque = DB::table('structurebatiments');
  $reque->join('questionnaires', 'structurebatiments.questionnaire_id', 'questionnaires.id');
  $reque->join('options', 'options.questionnaire_id', 'questionnaires.id');

  $reque->select('ecole_id', 'questionnaires.libelle as question' ,'options.libelle as reponse');
  if($ecole_id != 0)
  $reque->where('structurebatiments.ecole_id', $ecole_id);
  $reque->orderBy('ecole_id');
$etat_batiment=  $reque->get();
  $data = ['ecole'=>$ecole, 'etat_physique'=>$etat_batiment];
  return  response()->json($data);

}


function get_liste_enseignant_ip($user_id, $commune, $zone=null,  $ecole = null, $classe = null, $formation = null){
    $district_id = \App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];
$query = DB::table('enseignants');
$query->join('affectations','affectations.enseignant_id','enseignants.id')->join('funiversitaires','funiversitaires.enseignant_id','enseignants.id')->join('classes','affectations.classe_id','classes.id')->join('ecoles','affectations.ecole_id','ecoles.id')->join('zones','ecoles.zone_id','zones.id')->join('communes','zones.commune_id','communes.id')->join('districts','communes.district_id','districts.id');

$query->select('enseignants.id','enseignants.nom','ecoles.nom as ecole','communes.nom as commune','nomclasse','enseignants.prenom','classes.id as classe_id','enseignants.telephone','sexe', 'nif', 'districts.nom as district','nomf');

    if($commune == 0){
      //$query->get();
    }
    elseif($zone ==0){
      $query->where('communes.id', $commune);
    }
      elseif($ecole ==0){
          $query->where('communes.id', $commune)->where('zones.id', $zone);
      }
    elseif($classe ==0){
          $query->where('communes.id', $commune)->where('zones.id', $zone)->where('ecoles.id', $ecole);
      }
              else{
                 $query->where('communes.id', $commune)->where('zones.id', $zone)->where('ecoles.id', $ecole)->where('classes.id', $classe);
              }

    if($classe != 0)
      $query->where('classes.id',$classe);

    if($formation != '0' && $formation != 1)
      $query->where('funiversitaires.nomf',$formation);
    elseif($formation == 1)
      $query->whereNotIn('funiversitaires.nomf',['ENI','FIA','ENS','Sces de l\'Education']);
      $query->where('communes.district_id',$district_id);
     $listeprof = $query->get();
    return $listeprof;
}


function get_liste_enseignant($district, $commune=null, $zone=null, $secteur= -1, $statut =0){
$query = DB::table('affectations');

 $query->join('enseignants','affectations.enseignant_id','enseignants.id')->join('statuts','statuts.enseignant_id','enseignants.id')->join('ecoles','affectations.ecole_id','ecoles.id')->join('zones','ecoles.zone_id','zones.id')->join('communes','zones.commune_id','communes.id')->join('districts','communes.district_id','districts.id');

$query->select('enseignants.id','enseignants.nom','enseignants.prenom','enseignants.telephone','sexe', 'nif','date_affectation as date')->distinct('affectations.enseignant_id')->orderBy('enseignants.nom','asc');

  

    if($district == 0){
      //$query->get();
    }
    elseif($commune ==0){
      $query->where('districts.id', $district);

    }
      elseif($zone ==0){
          $query->where('districts.id', $district)->where('communes.id', $commune);
      }
      
              else{
                 $query->where('districts.id', $district)->where('communes.id', $commune)->where('zones.id', $zone);
              }

      if ($secteur != -1) {
         $query->where('secteur', $secteur);
      }

   

      if ($statut != 0) {
         $query->where('statuts.statut', $statut);
      }

     
    // if($niv !=0){
    //       if(strlen($niv) != 5)
    //          $query->where('niveau1', $niv);
    //       else{
    //         $c_niveau = check_niveau($niv);
    //           $query->where('niveau1' ,'like', $c_niveau);
    //         }
    //   }


     $listeprof = $query->get();   
     
      return $listeprof;

}


function get_commune_dept(){
   return DB::table('communes')
   ->join('districts','communes.district_id','districts.id')
   ->join('departements','districts.departement_id','departements.id')
   ->select('communes.nom','departement_id','communes.id')
   ->orderBy('nom','ASc')
   ->get();
}



function get_listecole(){
$listecole = DB::table('ecoles')
//->join('section_communales','ecoles.section_communale_id','ecoles.id')
// ->join('categories','categories.ecole_id','ecoles.id')
// ->join('niveauenseignements','niveauenseignements.ecole_id','ecoles.id')
// ->join('vacations','vacations.ecole_id','ecoles.id')
->join('zones','ecoles.zone_id','zones.id')
->join('communes','zones.commune_id','communes.id')
->join('districts','communes.district_id','districts.id')
->select('ecoles.id','ecoles.nom as ecole','adresse','milieu','fondateur','communes.nom as commune', 'secteur', 'districts.nom as district'
  //'categorie','vacation',  'niveau'
)
->get();
return $listecole;
}

function get_liste_ecole($district=0,$commune=null, $zone=null,  $secteur = null, $niv=0){
$query = DB::table('ecoles');
$query->join('directeurs','directeurs.ecole_id','ecoles.id')
->join('section_communales','ecoles.section_communale_id','section_communales.id')
->join('zones','ecoles.zone_id','zones.id')
->join('communes','zones.commune_id','communes.id')
->join('districts','communes.district_id','districts.id')
->join('niveauenseignements','niveauenseignements.ecole_id','ecoles.id');

$query->select('ecoles.id','ecoles.nom as Ecole','code as Code','Adresse', 'tel as tel','email as Email Ecole','districts.nom as District','communes.nom as Commune', 'zones.nom as Zone','section_communales.nom as Section_Communale', 'sigle as Sigle', 'fondateur as Fondateur','acces as Acces', 'niveau as Niveau_Enseignement','secteur as Secteur', 'ecoles.longitude as Longitude','ecoles.latitude as Latitude', 'nomd as Nom_Directeur', 'prenom as Prenom_Directeur', 'teld as Tel_Directeur','telephoned as Tel Directeur2','emaild as Email', 'cin as CIN', 'nif as NIF', 'datenais as Date Naissance', 'lieunais as Lieu de Naissance','sexe as Sexe','adressed as Adresse Directeur');


    if($district == 0){
      //$query->get();
    }
    elseif($commune ==0){
      $query->where('districts.id', $district);
    }
      elseif($zone ==0){
          $query->where('districts.id', $district)->where('communes.id', $commune);
      }

          else{
                 $query->where('districts.id', $district)->where('communes.id', $commune)->where('zones.id', $zone);
              }

      if ($secteur != -1) {
         $query->where('secteur', $secteur);
      }
      if($niv !=0){
          if(strlen($niv) != 5)
             $query->where('niveau1', $niv);
          else{
            $c_niveau = check_niveau($niv);
              $query->where('niveau1' ,'like', $c_niveau);
            }
      }

     $listecole = $query->get();
     $i=0;
     foreach ($listecole as $liste) {
       if($liste->Sexe == 1)
        $listecole[$i]->Sexe = 'M';
      else
         $listecole[$i]->Sexe = 'F';
       $i++;
     }
      $i=0;
      foreach ($listecole as $liste) {
       if($liste->Secteur == 1)
        $listecole[$i]->Secteur = 'Public';
      else
         $listecole[$i]->Secteur = 'Non Public';
       $i++;

     }
    return $listecole;
}



function get_dir(){
  $l = '_1__';
  $nom = \App\Niveauenseignement::where('niveau1', 'like', $l)->pluck('niveau');
  return $nom;
}

function niveau_format(){
   $niveau = ['Prescolaire'=>'0001',
    'Fondamental'=>'0110',
    'Secondaire'=>'1000',
    'Ecole Complete'=>'1111',
    'Fondamental 1er et 2eme cycle'=>'0010',
    'Prescolaire et Fondamental 1er et 2eme cycle'=>'0101',
    'Fondamental 3eme Cycle et Secondaire'=>'0011',
    'Fondamental et Secondaire'=>'1110',
     'Prescolaire et Fondamental'=>'0111'];

    $listecole = \App\Niveauenseignement::all();
    foreach ($listecole as $ecole) {
      $ecole->niveau1 = $niveau[$ecole->niveau];
      $ecole->save();
    }
    return \App\Niveauenseignement::all();
}

function check_niveau($niveau){
  $c_niveau = substr($niveau, 1);
  $joker = '';
  for($i=0; $i<strlen($c_niveau); $i++){
    if(substr($niveau, $i+1, 1) ==0)
           $joker = $joker.'_';
    else
      $joker = $joker.'1';
  }
  return $joker;
}

// function check_niveau($niveau){
//   $c_niveau = substr($niveau, 1);
//   if($c_niveau  >= 1000)
//     $joker = '1***';
//   else if($c_niveau >=100)
//     $joker = '*1**';
//   else if($c_niveau >=10)
//     $joker = '**1*';
//   else
//     $joker ='***1';

//   return $joker;
// }


function get_text($id, $district,$commune=null, $zone=null,  $ecole = null, $classe = null, $formation = null){
    if($id == 1)
    $text = 'Liste des Enseignants ';
  else
    $text = 'Liste des Enseignants Supervisés ';
    if($formation != '0'){
       if($formation != '1'){
      $text =$text.' ayant une formation de '.$formation;
    }
      if($formation == '1'){
      $text =$text.' n\'ayant pas de formation specialiée ';
    }

}


     if($classe != 0 ){
       if($id ==2){
        if($classe == '-1'  )
          $text =$text.' travaillant en 1e et 2e AF';
        if($classe == '20')
         $text =$text.' travaillant en 3e et 4e AF';
         }
        else $text =$text.' travaillant en '.get_nom($classe,'Classe');
    }

     if($ecole != 0){
      $text =$text.' à  '.get_nom($ecole,'Ecole');
    }

   if($zone != 0){
      $text =$text.', '.get_nom($zone,'Zone');
    }

   if($commune != 0){
      $text =$text.', Commune de '.get_nom($commune,'Commune');
    }

   if($district != 0){
      $text =$text.', District Scolaire de '.get_nom($district,'District');
    }

  return $text;
}

function get_niveau($n){

 $niveau= ['Prescolaire' =>'0001',
           'Fondamental'=>'0110',
           'Secondaire'=>'1000',
           'Ecole Complete'=>'1111',
            'Fondamental 1er et 2eme cycle'=>'0010',
            'Prescolaire et Fondamental 1er et 2eme cycle'=>'0011',
            'Prescolaire et Fondamental complet'=>'0111',
           'Fondamental 3eme Cycle et Secondaire'=>'1100',
           'Fondamental et Secondaire'=>'1110',
           '3e Cycle'=>'0100'];

    $niveau2 = [  'Prescolaire inclus'=>'-0001',
                   '1e et 2e  Cycle inclus'=>'-0010',
                   '3e Cycle inclus'=>'-0100',
                   'Secondaire inclus'=>'-1000'];
       if($n==0)
            return $niveau;
            return array_merge($niveau, $niveau2);
}


function get_text_ecole($district,$commune=null, $zone=null, $secteur=null, $niveau=null){
     $lniveau =get_niveau(1);


    $text = 'Liste des Ecoles ';


   if($secteur != -1){
      if($secteur == 1)
      $text =$text.'publiques ';
      else
         $text =$text.'non publiques ';
    }

    if($niveau !=0)
      $text = $text.','.array_search($niveau, $lniveau);

    if($zone != 0){
      $text =$text.', '.get_nom($zone,'Zone');
    }

   if($commune != 0){
      $text =$text.', Commune de '.get_nom($commune,'Commune');
    }

   if($district != 0){
      $text =$text.', District Scolaire de '.get_nom($district,'District');
    }
  return $text;
}



function get_list_enseignant(){
$query = DB::table('enseignants');
$query->join('affectations','affectations.enseignant_id','enseignants.id')->join('funiversitaires','funiversitaires.enseignant_id','enseignants.id')->join('classes','affectations.classe_id','classes.id')->join('ecoles','affectations.ecole_id','ecoles.id')->join('zones','ecoles.zone_id','zones.id')->join('communes','zones.commune_id','communes.id')->join('districts','communes.district_id','districts.id');

$query->select('enseignants.id','enseignants.nom','ecoles.nom as ecole','communes.nom as commune','nomclasse','enseignants.prenom','classes.id as classe_id','enseignants.telephone','sexe', 'nif', 'districts.nom as district','nomf');

 $listeprof = $query->get();
    return $listeprof;
}




function test(){
  $query = DB::table('enseignants')
  ->join('affectations','affectations.enseignant_id','enseignants.id')
  ->join('funiversitaires as fun','fun.enseignant_id','enseignants.id')
   ->join('classes','affectations.classe_id','classes.id')
  ->join('ecoles','affectations.ecole_id','ecoles.id')
  ->join('zones','ecoles.zone_id','zones.id')
  ->join('communes','zones.commune_id','communes.id')
  ->join('districts','communes.district_id','districts.id')
  ->select('enseignants.id','enseignants.nom','ecoles.nom as ecole','communes.nom as commune','nomclasse','enseignants.prenom','classes.id as classe_id','telephone','sexe', 'nif', 'districts.nom as district','nomf')
  ->get();

  return $query;
}

function get_nom($id,$model){
   $modelname = 'App\\'.$model;
   if($model != 'Classe'){
       $nom=$modelname::find($id)->nom;
   }else
   { $nom=$modelname::find($id)->nomclasse;}
  return $nom;
}

function get_observ_sup($classe){
  $req = DB::table('supervisions');
  if($classe <=5){

  $req->select('enseignants.id','enseignants.nom','ecoles.nom as ecole','communes.nom as commune','zones.nom as zone','date_observation','nomclasse','libelle as matiere','enseignants.prenom','section_communales.nom as section','classes.id as classe_id','matieres.id as matiere_id',  DB::raw('count(mention) as qteA'));}
  else{$req->select('enseignants.id','enseignants.nom','ecoles.nom as ecole','communes.nom as commune','zones.nom as zone','date_observation','nomclasse','libelle as matiere','enseignants.prenom','section_communales.nom as section','classes.id as classe_id','matieres.id as matiere_id',  DB::raw('SUM(mention) as total'));}

    $req->join('observations','supervisions.observation_id','observations.id')->join('enseignants','observations.enseignant_id','enseignants.id')->join('ecoles','observations.ecole_id','ecoles.id')    ->join('section_communales','ecoles.section_communale_id','section_communales.id')->join('qmclasses','supervisions.qmclasse_id','qmclasses.id')->join('classe_matieres','qmclasses.classe_matiere_id','classe_matieres.id')
    ->join('classes','classe_matieres.classe_id','classes.id')->join('matieres','classe_matieres.matiere_id','matieres.id')->join('zones','ecoles.zone_id','zones.id')->join('communes','zones.commune_id','communes.id');

 if($classe <=5){
  if($classe != -1)
    $req->where('mention', 1 )->where('classes.id', $classe );
  else
    $req->where('mention', 1 )->whereIn('classes.id', [4,5] );
}else{
  if($classe !=20)
    $req->where('classes.id', $classe );
  else
    $req->whereIn('classes.id', [6,7] );

}
    $req->groupBy('enseignants.id','enseignants.nom','enseignants.prenom','ecoles.nom','communes.nom','zones.nom','date_observation','nomclasse','libelle','section_communales.nom','classes.id','matieres.id');
    $obs= $req->get();
    return $obs;
}


function nb_question($classe, $matiere ){
  $nbq = DB::table('qmclasses')
  ->join('classe_matieres','qmclasses.classe_matiere_id','classe_matieres.id')
  ->where('classe_id', $classe)
  ->where('matiere_id', $matiere)
  ->count();
  return $nbq;
}

function supprime($ModelName, $id){
  try{
    $mn=$ModelName::find($id);
        $mn->delete();
    return true;
  }catch(\Illuminate\Database\QueryException $ex){
    return false;
  }
}

function get_ecole_incomp(){
  $liste = \App\Directeur::orderBy('ecole_id','asc')->pluck('ecole_id');
        // return($liste);
        return  \DB::table('ecoles')
        ->whereNotIn('id',$liste)
        ->select('id','ecoles.nom','ecoles.created_at')
        ->get();
}

function recuperation_ecole(){
  $liste_ecole = get_ecole_incomp();
  if($liste_ecole->count()>0){
    foreach ($liste_ecole as $le) {
      //table directeur
      // \App\Personne::where('date_naissance','>', date('Y-m-d',strtotime('-18 years')))->count();
      $directeur = \App\Directeur::where('created_at', '-', $le->created_at)->pluck('id');
      if($directeur->count() > 0){
        $direct = update_data('Directeur',['ecole_id'=>$le->id], $directeur[0]);
      }
      $categorie = \App\Categorie::where('created_at', $le->created_at)->pluck('id');
      if($categorie->count() > 0){
        $cate = update_data('Categorie',['ecole_id'=>$le->id], $categorie[0]);
      }
      $niveauens = \App\Niveauenseignement::where('created_at', $le->created_at)->pluck('id');
      if($niveauens->count() > 0){
        $niveau = update_data('Niveauenseignement',['ecole_id'=>$le->id], $niveauens[0]);
      }
      $vacation = \App\Vacation::where('created_at', $le->created_at)->pluck('id');
      if($vacation->count() > 0){
        $vaca = update_data('Vacation',['ecole_id'=>$le->id], $vacation[0]);
      }
      $structurebati = \App\Structurebatiment::where('created_at', $le->created_at)->pluck('id');
      if($structurebati->count() > 0){
        foreach ($structurebati as $sb) {
        $structure = update_data('Structurebatiment',['ecole_id'=>$le->id], $sb);
          }
      }
    }
  }
  return($directeur);
}

function get_id_inspecteur($user_id){
 return (\App\Inspecteur::where('user_id',$user_id)->pluck('id'))[0];
}


 function uploadfile ($file,$path){
        $nomfic = rand() . '.' . $file->getClientOriginalExtension();
          $fileChem= $file->storeAs($path,$nomfic);
           return $fileChem;

 }

  function delete_data($model, $id) {
      $modelname = 'App\\'.$model;
      $message ='';
      $status = 1;

      if($modelname::find($id)!= null)  {
            try {
              $modelname::destroy($id);
            }
            catch(\Illuminate\Database\QueryException $ex){
                 $message = $ex->getMessage();
                 $status = 0;
                }
       }
      else{
          $message = 'Donnée introuvable...';
          $status = 0;
        }
      return (compact(['id','status','message']));
   }


function update_data($model, $donnee, $id) {
      $modelname = 'App\\'.$model;
      $message ='';
      $status = 1;
      $codeError ='';
      $data = collect();
      try {
         $data = $modelname::find($id);

          foreach ($donnee as $key => $value) {
            $data->$key = $value;
          }
          $data->save();
      }
      catch(\Illuminate\Database\QueryException $ex){
           $message = $ex->getMessage();
            $codeError = $ex->getCode();
           $status = 0;
          }
      return (compact(['data','status','message','codeError']));
   }

   function get_data($model, $colonne, $data) {
      $modelname = 'App\\'.$model;
      $result=$modelname::where($colonne, $data)->get();

      if($result == null)
        return 0;
        return $result[0]->id;
}

   function check_existe($model, $colonne, $data) {
      $modelname = 'App\\'.$model;
      $message ='';
      $status = 1;
      $result=$modelname::where($colonne, $data)->orderBy('created_at', 'desc')->first();

      if($result == null)
        return 0;
        return $result->id;
}

   function check_existe_etat($ecole_id, $q_id, $rep) {
     $batiment =  \App\Structurebatiment::where('ecole_id', $ecole_id)->latest('dateEvaluation')->first();
      if($batiment != null){
     $last_date = $batiment->dateEvaluation;
      $result = \App\Structurebatiment::where('ecole_id', $ecole_id)
      ->where('dateEvaluation', $last_date)
      ->where('questionnaire_id', $q_id)
      ->where('reponse', $rep)
      ->get();
    }else
    return 0;
      if($result->count() < 1)
        return 0;
        return $result[0]->id;
}

   function supprime_existe_lastdate_etat($ecole_id) {
     $batiment =  \App\Structurebatiment::where('ecole_id', $ecole_id)->latest('dateEvaluation')->get();
     $ldate = 0;
      if($batiment->count() != 0){
        $ldate = $batiment[0]->dateEvaluation;
        foreach ($batiment as $bat) {
          \App\Structurebatiment::destroy($bat->id);
        }

}
return $ldate;
}


   function get_date_etat($ecole_id, $q_id) {
     $batiment =  \App\Structurebatiment::where('ecole_id', $ecole_id)->latest('dateEvaluation')->first();
     $last_date = $batiment->dateEvaluation;
      $result = \App\Structurebatiment::where('ecole_id', $ecole_id)
      ->where('dateEvaluation', $last_date)
      ->where('questionnaire_id', $q_id)
      ->get();

      if($result->count() < 1)
        return 0;
        return $result[0]->dateEvaluation;
}

function store_data($model, $donnee) {
      $modelname = 'App\\'.$model;
      $message ='';
      $status = 1;
      $codeError = '';
      $data = collect();
      try {
         $data =new $modelname;

          foreach ($donnee as $key => $value) {
            $data->$key = $value;
          }
          $data->save();
      }
      catch(\Illuminate\Database\QueryException $ex){
           $message = $ex->getMessage();
           $codeError = $ex->getCode();
           $status = 0;
          }

      return (compact(['data','status','message', 'codeError']));
   }
