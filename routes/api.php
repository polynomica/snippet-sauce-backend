<?php

// use Illuminate\Http\Request;
use App\Http\Controllers\CodeController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\LangController;
use App\Http\Controllers\UserController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Admin related APIs
Route::post('/admin_login', [UserController::class, 'admin_login']);
Route::get('/author_details/{git_username}', [UserController::class, 'author_details']);

// Snippet related APIs
Route::post('/filter', [DisplayController::class, 'filter']);
Route::get('/display', [DisplayController::class, 'display']);
Route::get('/total', [DisplayController::class, 'total_snippets']);
Route::get('/title/{title}', [DisplayController::class, 'title_search']);
Route::get('/search/{snippet_id}', [DisplayController::class, 'search']);
Route::get('/similar/{language}', [DisplayController::class, 'similar_snippets']);

// Language CRUD Operations
Route::get('/languages', [LangController::class, 'get_languages']);
Route::get('/language_detail/{language}', [LangController::class, 'language_details']);

// Routes with Middleware
Route::middleware('is_admin')->group(function () {
    // Language CRUD Operations
    Route::post('/add_language', [LangController::class, 'add_language']);
    Route::post('/update_language/{previous_language}', [LangController::class, 'update_language']);

    // Snippet CRUD Operations
    Route::post('/create_snippet', [CodeController::class, 'create_snippet']);
    Route::post('/update_snippet/{snippet_id}', [CodeController::class, 'update_snippet']);
    Route::post('/delete_snippet/{snippet_id}', [CodeController::class, 'delete_snippet']);
});
