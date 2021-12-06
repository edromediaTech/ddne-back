<?php

namespace App\Http\Controllers;
use Auth;
use App\insprincipal;
use App\Zone;
use App\Commune;
use App\Classe;
use App\Ecole;
use App\District;
use Illuminate\Http\Request;

class InsprincipalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    public function liste( $id)
    {
      $user_id = Auth::user()->id;
      $districts = \App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];
      $zones= Zone::all();
      $enseignants= get_list_enseignant();
      $communes= Commune::where('district_id', $districts)->get();
      $ecoles= Ecole::all();
      $classe= -1;
      $classes= Classe::all();


    if($id == 1){
      return view('IP.liste',['zones'=>$zones, 'communes'=>$communes, 'enseignants'=>$enseignants, 'ecoles'=>$ecoles, 'classes'=>$classes, 'id'=>$id, 'supe'=>-1, 'nb'=>$enseignants->count()]);
    }

        $enseignants = get_observ_sup($classe);
        if($classe <= 5)
         $supe = 0;
        else
            $supe = 1;
        $nb_questions = nb_question(5,1);
    return view('IP.liste',['zones'=>$zones, 'communes'=>$communes, 'ecoles'=>$ecoles, 'classes'=>$classes, 'districts'=>$districts, 'enseignants'=>$enseignants,'id'=>$id, 'nb_questions'=>$nb_questions, 'supe'=>$supe]);
    }


    public function get_data_enseignant(Request $request, $id)
    { $user_id = Auth::user()->id;
      $district =\App\Insprincipal::where('user_id', $user_id)->pluck('district_id')[0];
      //$district =$request->get('district');
      $zone =$request->get('zone');
      $commune =$request->get('commune');
      $ecole=$request->get('ecole');
      $classe=$request->get('classe');
      $matiere=$request->get('matiere');
      $formation=$request->get('formation');

    if($id == 1){
        $enseignants = get_liste_enseignant_ip($user_id, $commune ,$zone, $ecole, $classe, $formation);


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
        $msg_error= '';
        $data = json_decode($request->getContent());
        //$data['user_id'] = Auth::user()->id;
        $ip = store_data('Insprincipal', $data);
            if($ip['status']==0){
            $msg_error = $msg_error.'Ip erreur!'.'->Message:'.$ip['message'];
             \Log::debug($msg_error);
             }  
             $user_id = \Auth::user()->id; 
             $id =  Insprincipal::latest()->first()->id;       
            return response()->json(compact('ip','user_id','id'));


      // $insprincipal = new Insprincipal;
      //     $insprincipal->user_id = Auth::user()->id;
      //   $insprincipal->district_id = $request->input('district');
      //   $insprincipal->nom = $request->input('nom');
      //   $insprincipal->prenom = $request->input('prenom');
      //   $insprincipal->nif = $request->input('nif');
      // $insprincipal->datefonction = $request->input('datefonction');
      // $insprincipal->save();
      // return redirect('/principal');
    }


    public function generatePDF(Request $request, $id)
    { $district =$request->get('district');
     $zone =$request->get('zone');
     $commune =$request->get('commune');
     $ecole=$request->get('ecole');
     $classe=$request->get('classe');
     $formation=$request->get('formation');
     if($id == 1){
         $enseignants = get_liste_enseignant_ip( $commune ,$zone, $ecole, $classe, $formation);
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


    /**
     * Display the specified resource.
     *
     * @param  \App\insprincipal  $insprincipal
     * @return \Illuminate\Http\Response
     */
    public function show(insprincipal $insprincipal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\insprincipal  $insprincipal
     * @return \Illuminate\Http\Response
     */
    public function edit(insprincipal $insprincipal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\insprincipal  $insprincipal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, insprincipal $insprincipal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\insprincipal  $insprincipal
     * @return \Illuminate\Http\Response
     */
    public function destroy(insprincipal $insprincipal)
    {
        //
    }
}
