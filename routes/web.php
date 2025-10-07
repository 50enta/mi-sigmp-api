<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('newNearMiss',['type' => 'Near Miss']);
});
