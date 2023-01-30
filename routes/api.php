<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\ProductController;

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

    //All Rules
    Route::get('/auth/roles',[UserController::class,'roles']);
    
    //Auth User
    Route::get('/user', function(Request $request) {
        return $request->user();
    });
    
    //Product
    Route::get('/products',[ProductController::class,'index']);
    Route::get('/products/{id}',[ProductController::class,'show']);
    Route::post('/products/new', [ProductController::class,'create']);
    Route::post('/products/update/{id}',[ProductController::class,'update']);
    Route::delete('/products/delete/{id}',[ProductController::class,'delete']);
    
    //All Users
    Route:: get('/users',[UserController::class,'index']);
    Route:: get('/users/{id}',[UserController::class,'show']);
    Route::post('/auth/register',[AuthController::class,'createUser']);
    Route:: post('/users/update/{id}',[UserController::class,'update']);
    Route::delete('/users/delete/{id}',[UserController::class,'delete']);
});

//User Logout
Route::get('/auth/logout',[AuthController::class,'logoutUser']);