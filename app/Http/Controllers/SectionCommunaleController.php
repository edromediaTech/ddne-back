<?php

namespace App\Http\Controllers;

use App\Section_communale;
use Illuminate\Http\Request;

class SectionCommunaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $secom = \DB::table('section_communales')
        ->select('id as value','nom as text')
        ->orderBy('nom','asc')
        ->get();

        return ($secom);

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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Section_communale  $section_communale
     * @return \Illuminate\Http\Response
     */
    public function show(Section_communale $section_communale)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Section_communale  $section_communale
     * @return \Illuminate\Http\Response
     */
    public function edit(Section_communale $section_communale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Section_communale  $section_communale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Section_communale $section_communale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Section_communale  $section_communale
     * @return \Illuminate\Http\Response
     */
    public function destroy(Section_communale $section_communale)
    {
        //
    }
}
