<?php

use App\Http\Controllers\Api\Auth\AuthController;
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

// Route::get('/users/{id}', [AuthController::class,'show']);
// Route::get('/posts',[PostController::class,'posts'])->middleware('auth:sanctum');


Route::post('/auth/login',[AuthController::class,'loginUser']);

Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::post('/auth/register',[AuthController::class,'createUser']);
    Route::post('/auth/logout',[AuthController::class,'logoutUser']);
});