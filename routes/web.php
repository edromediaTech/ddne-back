<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------s
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('hash', function () {
    return Hash::make('lakaypam');
});

Route::get('test', function () {
    return 'Serveur ok';
});


Route::get('/test-niv',function () {
 $liste = \App\Niveauenseignement::orderBy('ecole_id','asc')->pluck('ecole_id');
        // return($liste);
        return  \DB::table('ecoles')
        ->whereNotIn('id',$liste)
        ->select('id','ecoles.nom','ecoles.created_at')
        ->get();
});


Route::get('test-help', function () {  
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
        return getKey($niveau, '1000');
        // formationU($data, 'J0J03092');  
     
});