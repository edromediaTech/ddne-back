<?php

namespace App\Http\Controllers;

use App\Transfert;
use Illuminate\Http\Request;
use Response;

class TransfertController extends Controller
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
        $msg_error = '';
          $data = json_decode($request->getContent());  
            foreach ($data as $da) {
              $tr = store_data('Transfert', $da);
                if($tr['status']==0){
               $msg_error = $msg_error.'Transfert erreur!'.'->Message:'.$tr['message'];
                   \Log::debug($msg_error); 
                   return Response::json(0);                     
              }  

             } 
         
            return Response::json(1);  
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Transfert  $transfert
     * @return \Illuminate\Http\Response
     */
    public function show(Transfert $transfert)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Transfert  $transfert
     * @return \Illuminate\Http\Response
     */
    public function edit(Transfert $transfert)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Transfert  $transfert
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $data = explode('|', $id);
        
        $trans = Transfert::find($data[0]); 
        if($trans->accepter == 1)  {    
        $trans->accepter = 0;
        $trans->ecolecible = null;
         }
        else {
             $trans->accepter = 1;
        $trans->ecolecible =$data[1];
           }
         $trans->save();
        return Response::json(1);
    } 

    public function valider($id)
    {
    $data = explode('|', $id);
    for($i=0; $i < count($data); $i++){
        $trans = Transfert::find($data[$i]); 
        $trans->valider = 1 ;  
        $trans->etat = 1;   
        $trans->anacne = session_new_year();   
         $trans->save();
        $cle = \App\Classeleve::find($trans->classeleve_id);
        $cle->ecole_id = $trans->ecolecible;
        $cle->save();
     } 
       
        return Response::json(1);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Transfert  $transfert
     * @return \Illuminate\Http\Response
     */
     public function destroy_group($id)
    {
        $group = explode('|', $id);
        $n= 0;
        for($i=0; $i<count($group); $i++){
         $sug = delete_data('Transfert', $group[$i]);
         $n++;
     }
         return Response::json($n);
    }

    public function destroy(Transfert $transfert)
    {
        //
    }
}
