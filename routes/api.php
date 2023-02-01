<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Models\User;
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

    //All Roles
    Route::get('/auth/roles',[UserController::class,'roles']);
    
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
    Route::post('/users/status/{id}',[UserController::class,'updateUserStatus']);
    
    //Main Account
    Route::get('/user', [UserController::class,'showUserAccount']);
    Route::post('/user/update', [UserController::class,'updateAccount']);


});

//User Logout
Route::get('/auth/logout',[AuthController::class,'logoutUser']);