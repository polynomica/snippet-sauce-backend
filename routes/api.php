<?php

use App\Http\Controllers\CodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/display', [CodeController::class, 'display']);
Route::get('/filter', [CodeController::class, 'filter']);
Route::get('/author_details', [CodeController::class, 'author_details']);
Route::get('/author_login', [CodeController::class, 'author_login']);