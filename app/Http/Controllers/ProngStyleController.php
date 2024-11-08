<?php

namespace App\Http\Controllers;

use App\Models\ProngStyle;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProngStyleRequest;
use App\Http\Requests\UpdateProngStyleRequest;

class ProngStyleController extends Controller
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
     * @param  \App\Http\Requests\StoreProngStyleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProngStyleRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProngStyle  $prongStyle
     * @return \Illuminate\Http\Response
     */
    public function show(ProngStyle $prongStyle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProngStyle  $prongStyle
     * @return \Illuminate\Http\Response
     */
    public function edit(ProngStyle $prongStyle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProngStyleRequest  $request
     * @param  \App\Models\ProngStyle  $prongStyle
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProngStyleRequest $request, ProngStyle $prongStyle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProngStyle  $prongStyle
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProngStyle $prongStyle)
    {
        //
    }
}
