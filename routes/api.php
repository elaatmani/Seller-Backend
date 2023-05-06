<?php

use App\Http\Controllers\Api\Admin\InventoryController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\SaleController;
use App\Http\Controllers\Api\Admin\ShopController;
use App\Http\Controllers\Api\Public\OrderController;
use App\Http\Controllers\Api\NotificationController;


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
    Route::post('/roles/new',[UserController::class,'createRole']); 
    Route::post('/roles/update/{id}',[UserController::class,'updateRole']);
    Route::delete('/roles/delete/{id}',[UserController::class,'deleteRole']);



    //Main AccountshowRole
    Route::get('/user', [AuthController::class,'showUserAccount']);
    Route::post('/user/update', [AuthController::class,'updateAccount']);
    Route::get('/auth/permission',[AuthController::class,'userPermission']);



    //All Users
    Route:: get('/users',[UserController::class,'index']);
    Route:: get('/users/profile',[UserController::class,'profile']);
    Route::post('/users/new',[UserController::class,'create']);
    Route:: post('/users/update/{id}',[UserController::class,'update']);
    Route:: post('/users/profile',[UserController::class,'updateProfile']);
    Route::delete('/users/delete/{id}',[UserController::class,'delete']);
    Route::post('/users/status/{id}',[UserController::class,'updateUserStatus']);
    Route:: get('/users/{id}',[UserController::class,'show']);
    Route::get('/cities',[UserController::class,'allCities']);
    Route::get('/delevries',[UserController::class,'delevries']);
    Route::get('/agents',[UserController::class,'agents']);
    Route::get('/online',[UserController::class,'onlineUsers']);

    //Shop
    Route::get('/shops',[ShopController::class,'index']);
    Route::get('/shops/{id}',[ShopController::class,'show']);
    Route::post('/shops/new', [ShopController::class,'store']);
    Route::post('/shops/update/{id}',[ShopController::class,'update']);
    Route::delete('/shops/delete/{id}',[ShopController::class,'destroy']);


    //Product
    Route::get('/products',[ProductController::class,'index']);
    Route::get('/products/{id}',[ProductController::class,'show']);
    Route::post('/products/new', [ProductController::class,'create']);
    Route::post('/products/update/{id}',[ProductController::class,'update']);
    Route::delete('/products/delete/{id}',[ProductController::class,'delete']);
    
   //Notification
   Route::get('/notifications',[NotificationController::class,'notifications']);
   Route::get('/notifications/agente',[NotificationController::class,'agenteNotifications']);
   Route::get('/notifications/delivery',[NotificationController::class,'deliveryNotifications']);
   


    //Sales For Admin
    Route::get('/sales',[SaleController::class,'index']);
    Route::post('/sales/new',[SaleController::class,'create']);
    Route::post('/sales/reset',[SaleController::class,'saleReset']);


    //Orders For Agente
    Route::get('/orders',[OrderController::class,'index']);
    Route::get('/orders/toconfirmate',[OrderController::class,'orderToConfirme']);
    Route::get('/orders/confirmer',[OrderController::class,'confirmedOrders']);
    Route::get('/orders/add',[OrderController::class,'addOrder']);
    Route::post('/orders/update/{id}',[OrderController::class,'updateOrder']);
  
    Route::get('/orders/show/{id}',[OrderController::class,'showOrder']);

    Route::get('/orders/history/show/{id}',[OrderController::class,'orderHistory']);
    
    //Orders For Delivery
    Route::get('/orders/todelivery',[OrderController::class,'orderToDelivery']);
    Route::get('/orders/delivred',[OrderController::class,'orderDelivered']);
    Route::post('/orders/update/delivery/note/{id}',[OrderController::class,'updateDeliveryNote']);

    
    //Orders update Confirmation Affectation Upsell Note
    Route::post('/orders/update/confirmationandnote/{id}',[OrderController::class,'updateConfirmationAndNote']);
    Route::post('/orders/update/confirmation/{id}',[OrderController::class,'updateConfirmation']);
    Route::post('/orders/update/affectation/{id}',[OrderController::class,'updateAffectation']);
    Route::post('/orders/update/delivery/{id}',[OrderController::class,'updatedelivery']);
    Route::post('/orders/update/upsell/{id}',[OrderController::class,'updateUpsell']);
    Route::post('/orders/update/note/{id}',[OrderController::class,'updateNote']);


    //Inventory
        //Inventory State
        Route::get('/inventorystates',[InventoryController::class,'inventoryState']);

        //Inventory Movement
        Route::get('/inventorymovements',[InventoryController::class,'inventoryMovement']);
        Route::post('/inventorymovements/new',[InventoryController::class,'createInventoryMovement']);
        Route::post('/inventorymovements/update/{id}',[InventoryController::class,'updateInventoryMovement']);
        Route::delete('/inventorymovements/delete/{id}',[InventoryController::class,'deleteInventoryMovement']);
        Route::get('/inventorymovements/{id}',[InventoryController::class,'showInventoryMovement']);
        
        //Is_Received
        Route::post('/delivery/inventorymovements/update/isreceived/{id}',[InventoryController::class,'updateReceivedInventoryMovement']);
        Route::post('/delivery/inventorymovements/update/note/{id}',[InventoryController::class,'updateNoteInventoryMovement']);
        Route::post('/delivery/inventorymovements/update/{id}',[InventoryController::class,'updateReceivedNoteInventoryMovement']);
        
 
        
        
});

//User Logout
Route::get('/auth/logout',[AuthController::class,'logoutUser']);
