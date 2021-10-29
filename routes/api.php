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
Route::post('/update_snippet', [CodeController::class, 'update_snippet']);
Route::post('/delete_snippet/{language}/{snippet_id}', [CodeController::class, 'delete_snippet']);

//Add new Language
Route::post('/add_language', [CodeController::class, 'add_language']);

//Snippet related APIs
Route::get('/display', [CodeController::class, 'display']);
Route::get('/search/{snipptet_id}', [CodeController::class, 'search']);

//User related APIs
Route::get('/author_details', [UserController::class, 'author_details']);
Route::post('/author_login', [UserController::class, 'author_login']);