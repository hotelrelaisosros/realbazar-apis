<?php

namespace App\Http\Controllers;

use App\Models\SettingHeight;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSettingHeightRequest;
use App\Http\Requests\UpdateSettingHeightRequest;

class SettingHeightController extends Controller
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
     * @param  \App\Http\Requests\StoreSettingHeightRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSettingHeightRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SettingHeight  $settingHeight
     * @return \Illuminate\Http\Response
     */
    public function show(SettingHeight $settingHeight)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SettingHeight  $settingHeight
     * @return \Illuminate\Http\Response
     */
    public function edit(SettingHeight $settingHeight)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSettingHeightRequest  $request
     * @param  \App\Models\SettingHeight  $settingHeight
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSettingHeightRequest $request, SettingHeight $settingHeight)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SettingHeight  $settingHeight
     * @return \Illuminate\Http\Response
     */
    public function destroy(SettingHeight $settingHeight)
    {
        //
    }
}
