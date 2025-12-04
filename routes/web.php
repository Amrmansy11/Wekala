<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::middleware('auth:admin-api')->get('/', function () {
   return response()->json(['message' => 'Welcome to Wekala API!']);
});
