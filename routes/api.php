<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CodeController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Snippet related APIs
Route::get('/display', [CodeController::class, 'display']);
Route::get('/filter', [CodeController::class, 'filter']);

//User related APIs
Route::get('/author_details', [UserController::class, 'author_details']);
Route::post('/author_login', [UserController::class, 'author_login']);