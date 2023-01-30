<?php

use App\Http\Controllers\Api\Auth\AuthController;
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




//User Login
Route::post('/auth/login',[AuthController::class,'loginUser']);

//Sanctum Use
Route::group(['middleware' => ['auth:sanctum']], function(){
    //User Permission
    Route::get('/auth/permission',[AuthController::class,'userPermission']);

    //User Register
    Route::post('/auth/register',[AuthController::class,'createUser']);

    Route::get('/user', function(Request $request) {
        return $request->user();
    });

    //User Logout
});
Route::get('/auth/logout',[AuthController::class,'logoutUser']);