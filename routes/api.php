<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Public\OrderController;
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

// Route::get('/test',[ProductController::class,'test']);


//User Login
Route::post('/auth/login',[AuthController::class,'loginUser']);

//Sanctum Use
Route::group(['middleware' => ['auth:sanctum']], function(){
    
    //Roles
    Route::get('/auth/roles',[UserController::class,'roles']);
    Route::get('/roles/{id}',[UserController::class,'showRole']);
    Route::post('/roles/add',[UserController::class,'createRole']);
    Route::post('/roles/update/{id}',[UserController::class,'updateRole']);
    Route::delete('/roles/delete/{id}',[UserController::class,'deleteRole']);
    
    //Main AccountshowRole
    Route::get('/user', [UserController::class,'showUserAccount']);
    Route::post('/user/update', [UserController::class,'updateAccount']);
    Route::get('/auth/permission',[AuthController::class,'userPermission']);
    
    //All Users
    Route:: get('/users',[UserController::class,'index']);
    Route:: get('/users/{id}',[UserController::class,'show']);
    Route::post('/auth/register',[AuthController::class,'createUser']);
    Route:: post('/users/update/{id}',[UserController::class,'update']);
    Route::delete('/users/delete/{id}',[UserController::class,'delete']);
    Route::post('/users/status/{id}',[UserController::class,'updateUserStatus']);

 
    
    
    //Product
    Route::get('/products',[ProductController::class,'index']);
    Route::get('/products/{id}',[ProductController::class,'show']);
    Route::post('/products/new', [ProductController::class,'create']);
    Route::post('/products/update/{id}',[ProductController::class,'update']);
    Route::delete('/products/delete/{id}',[ProductController::class,'delete']);
    

    //Order
    Route::get('/orders',[OrderController::class,'index']);

});

//User Logout
Route::get('/auth/logout',[AuthController::class,'logoutUser']);