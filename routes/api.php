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

//Snippet CRUD Operations
Route::post('/create_snippet', [CodeController::class, 'create_snippet']);
Route::get('/update_snippet', [CodeController::class, 'update_snippet']);
Route::get('/delete_snippet/{language}/{snippet_id}', [CodeController::class, 'delete_snippet']);

//Snippet related APIs
Route::get('/display', [CodeController::class, 'display']);
Route::get('/short_form', [CodeController::class, 'short_form']);
Route::get('/thumbnail', [CodeController::class, 'thumbnail']);

//User related APIs
Route::get('/author_details', [UserController::class, 'author_details']);
Route::post('/author_login', [UserController::class, 'author_login']);