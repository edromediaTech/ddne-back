<?php

namespace App\Http\Controllers;

use App\Ecoleresponsable;
use Illuminate\Http\Request;
use Response;

class EcoleresponsableController extends Controller
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

    public function check_responsable($user_id){
        $resp =  responsable_exist($user_id);
        return Response::json($resp);
    }

    public function liste_classe($niveau){
        return liste_classe_responsable($niveau);
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
    public function store_responsable(Request $request)
    {

          $msg_error = '';
        $data = json_decode($request->getContent());
        $niveau = respons_convert($data->niveau);
        $data->niveau = $niveau;
            $responsab= store_data('Ecoleresponsable',$data);
         if($responsab['status']==0){
            $msg_error = $msg_error.'Responsable erreur!'.'->Message:'.$responsab['message'];
             \Log::debug($msg_error);
             return $responsab['message'];
          }
              $user_id = \Auth::user()->id;         
            return Response::json(compact('responsab', 'user_id'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Ecoleresponsable  $ecoleresponsable
     * @return \Illuminate\Http\Response
     */
    public function show(Ecoleresponsable $ecoleresponsable)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Ecoleresponsable  $ecoleresponsable
     * @return \Illuminate\Http\Response
     */
    public function edit(Ecoleresponsable $ecoleresponsable)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ecoleresponsable  $ecoleresponsable
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ecoleresponsable $ecoleresponsable)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Ecoleresponsable  $ecoleresponsable
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ecoleresponsable $ecoleresponsable)
    {
        //
    }
}
