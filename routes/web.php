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
      return  get_ecole_by_commune(29);
});