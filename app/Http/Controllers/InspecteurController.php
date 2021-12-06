<?php

namespace App\Http\Controllers;

use App\Inspecteur;
use App\Observation;
use App\Supervision;
use App\Commune;
use App\Zone;
use App\Ecole;
use App\Inspecteur_zone;
use Response, Validator;
use Illuminate\Http\Request;
use Charts;

class InspecteurController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         $user_id = \Auth::user()->id;
         $insp = Inspecteur::where('user_id', $user_id)->count();
        if($insp < 1)
             return 0;
         return 1;    

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

    public function storeInspect(Request $request)
    {
       
       
          $msg_error = '';
        $data = json_decode($request->getContent()); 
        $data_insp =[
        'nom' => $data->nom,    
        'prenom' => $data->prenom,    
        'nif' => $data->nif,    
        'telephone' => $data->telephone,    
        'user_id' =>  $data->user_id
        ];    
       
            $inspect= store_data('Inspecteur',$data_insp);
         if($inspect['status']==0){
            $msg_error = $msg_error.'Inspecteur erreur!'.'->Message:'.$inspect['message'];
             \Log::debug($msg_error);
             return $inspect['message'];
          }
          else
            $dataIz = json_decode($request->getContent());
        $dataIz = [
            'inspecteur_id' => Inspecteur::latest()->first()->id,
            'zone_id' => $data->zone_id,
            'date_affectation' => $data->date_affectation
        ];
            $inspectzone = store_data('Inspecteur_zone', $dataIz);
                if($inspectzone['status']==0){
            $msg_error = $msg_error.'Inspecteur_zone erreur!'.'->Message:'.$inspectzone['message'];
             \Log::debug($msg_error);
             return $inspectzone['message'];
             }  
              $user_id = $data->user_id;       
            return Response::json(compact('inspect', 'user_id'));
    }

 public function storeInspecteur(Request $request)
    {
       
        $user_id = \Auth::user()->id;
        $inspecteur = new Inspecteur;
        $inspecteur->nom = $request->get('nom');
        $inspecteur->prenom = $request->get('prenom');
        $inspecteur->nif = $request->get('nif');
        $inspecteur->telephone = $request->get('telephone');
        $inspecteur->user_id = $user_id;
        $inspecteur->save();
        $insp = Inspecteur::latest()->first();
        $inspecteurzone = new Inspecteur_zone;
        $inspecteurzone->zone_id = $request->get('zone');
        $inspecteurzone->inspecteur_id = $insp->id;
        $inspecteurzone->date_affectation = $request->get('date_affectation');
        $inspecteurzone->save();
        return Response::json('succes');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        $user_id = \Auth::user()->id;

      // try{
        $observation = new Observation;
      //  $observation->id = $request->input('');
        $observation->enseignant_id = $request->get('enseignant');
        $observation->inspecteur_id =get_id_inspecteur($user_id);
        $observation->ecole_id = $request->get('ecole');
        $observation->date_observation = $request->get('date_observation');
        $observation->heure_debut = $request->get('heure_debut');
        $observation->heure_fin = $request->get('heure_fin');
        $observation->effectif_fille = $request->get('effectif_fille');
        $observation->effectif_garcon = $request->get('effectif_garcon');
        $observation->present_fille = $request->get('present_fille');
        $observation->present_garcon = $request->get('present_garcon');
        $observation->materiel_fille = $request->get('materiel_fille');
        $observation->materiel_garcon = $request->get('materiel_garcon');
        $observation->numero_lecon = $request->get('numero_lecon');
        $observation->video = $request->get('video');
        $observation->save();

        $indice = ['A'=>1,'B'=>2,'C'=>3];
        $rep = explode('|', $request->get('rep'));
        for($i=0; $i<count($rep); $i++ ){
            $info = explode('-',$rep[$i]);
        $supervision = new Supervision;
        $supervision->observation_id = $observation->id;
        $supervision->qmclasse_id = $info[1];
        $supervision->mention = $indice[$info[0]];
        $supervision->save();
       }
         // return view ('supervision.succes');
          return Response::json('succes');

      // }
      // catch (\Exception $e) {
      //   if($e->getcode() ==23000){
      //   return view (' supervision.erreur');
      // }


}


// upload video


 public  function upload(Request $request)
    {
     $rules = array(
      'file'  => 'required|mimes:mp4|max:105048'
     );

     $error = Validator::make($request->all(), $rules);

     if($error->fails())
     {
      return response()->json(['errors' => $error->errors()->all()]);
     }

     $image = $request->file('file');

     $new_name = rand() . '.' . $image->getClientOriginalExtension();
     $image->move(public_path('images'), $new_name);

     $output = array(
         'success' => 'Image uploaded successfully',
         'image'  => '<img src="/images/'.$new_name.'" class="img-thumbnail" />',
         'path'  => '/images/'.$new_name,
        );

        return response()->json($output);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Inspecteur  $inspecteur
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Inspecteur  $inspecteur
     * @return \Illuminate\Http\Response
     */
    public function edit(Inspecteur $inspecteur)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Inspecteur  $inspecteur
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Inspecteur $inspecteur)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Inspecteur  $inspecteur
     * @return \Illuminate\Http\Response
     */
    public function destroy(Inspecteur $inspecteur)
    {
        //
    }
}
