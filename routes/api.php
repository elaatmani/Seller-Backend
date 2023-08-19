<?php

use App\Events\NewNotification;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Services\RoadRunnerService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cookie;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Admin\SaleController;
use App\Http\Controllers\Api\Admin\ShopController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\SheetController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Public\AgentController;
use App\Http\Controllers\Api\Public\OrderController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\InventoryController;
use App\Http\Controllers\Api\Admin\WarehouseController;
use App\Http\Controllers\Api\Public\FollowUpController;
use App\Http\Controllers\Api\Public\DashboardController;
use App\Http\Controllers\Api\Admin\GoogleSheetController;
use App\Http\Controllers\Api\Admin\FactorisationController;

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
Route::post('/auth/login', [AuthController::class, 'loginUser']);

//Sanctum Use
Route::group(['middleware' => ['auth:sanctum']], function () {



    //Roles
    Route::get('/auth/roles', [UserController::class, 'roles']);
    Route::get('/roles/{id}', [UserController::class, 'showRole']);
    Route::post('/roles/new', [UserController::class, 'createRole']);
    Route::post('/roles/update/{id}', [UserController::class, 'updateRole']);
    Route::delete('/roles/delete/{id}', [UserController::class, 'deleteRole']);



    //Main AccountshowRole
    Route::get('/user', [AuthController::class, 'showUserAccount']);
    Route::post('/user/update', [AuthController::class, 'updateAccount']);
    Route::get('/auth/permission', [AuthController::class, 'userPermission']);



    //All Users
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/profile', [UserController::class, 'profile']);
    Route::post('/users/new', [UserController::class, 'create']);
    Route::post('/users/update/{id}', [UserController::class, 'update']);
    Route::post('/users/profile', [UserController::class, 'updateProfile']);
    Route::delete('/users/delete/{id}', [UserController::class, 'delete']);
    Route::post('/users/status/{id}', [UserController::class, 'updateUserStatus']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::get('/cities', [UserController::class, 'allCities']);
    // Route::get('/cities', [RoadRunnerService::class, 'cities']);
    Route::get('/delevries', [UserController::class, 'delevries']);
    Route::get('/deliveries', [UserController::class, 'deliveries']);
    Route::get('/agents', [UserController::class, 'agents']);
    Route::get('/online', [UserController::class, 'onlineUsers']);



    //Factorisation
    Route::get('/factorisations', [FactorisationController::class, 'index']);
    Route::get('/factorisations/{id}', [FactorisationController::class, 'show']);
    Route::post('/factorisations/update/{id}', [FactorisationController::class, 'update']);
    Route::post('/factorisations/update/comment/{id}', [FactorisationController::class, 'updateComment']);
    Route::post('/factorisations/update/closing/{id}', [FactorisationController::class, 'updateClosing']);
    Route::post('/factorisations/update/payment/{id}', [FactorisationController::class, 'updatePayment']);
    Route::delete('/factorisations/delete/{id}', [FactorisationController::class, 'destroy']);
    Route::get('/factorisations/generate-pdf/{id}', [FactorisationController::class,'generatePDF']);


    // Google Sheet
    Route::get('/sheets', [SheetController::class, 'index']);
    Route::post('/sheets', [SheetController::class, 'store']);
    Route::post('/sheets/{id}', [SheetController::class, 'update']);
    Route::delete('/sheets/{id}', [SheetController::class, 'destroy']);
    Route::post('/sheets/{id}/auto-fetch', [SheetController::class, 'updateAutoFetch']);

    // Fetching orders from sheets
    Route::get('/sync', [GoogleSheetController::class, 'index']);
    Route::get('/sync/{id}', [GoogleSheetController::class, 'sync_orders']);
    Route::get('/sync/{id}/save', [GoogleSheetController::class, 'save_orders']);



    //Warehouse
    Route::get('/warehouses', [WarehouseController::class, 'index']);
    Route::get('/warehouses/{id}', [WarehouseController::class, 'show']);
    Route::post('/warehouses/new', [WarehouseController::class, 'store']);
    Route::post('/warehouses/update/{id}', [WarehouseController::class, 'update']);
    Route::delete('/warehouses/delete/{id}', [WarehouseController::class, 'destroy']);



    //Shop
    Route::get('/shops', [ShopController::class, 'index']);
    Route::get('/shops/{id}', [ShopController::class, 'show']);
    Route::post('/shops/new', [ShopController::class, 'store']);
    Route::post('/shops/update/{id}', [ShopController::class, 'update']);
    Route::delete('/shops/delete/{id}', [ShopController::class, 'destroy']);



    //Product
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/for-order', [ProductController::class, 'productsForOrder']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products/new', [ProductController::class, 'create']);
    Route::post('/products/update/{id}', [ProductController::class, 'update']);
    Route::delete('/products/delete/{id}', [ProductController::class, 'delete']);



    //Notification
    Route::get('/notifications', [NotificationController::class, 'notifications']);
    Route::get('/notifications/agente', [NotificationController::class, 'agenteNotifications']);
    Route::get('/notifications/delivery', [NotificationController::class, 'deliveryNotifications']);



    //Sales For Admin
    Route::get('/sales', [SaleController::class, 'index']);
    Route::post('/sales/fresh', [SaleController::class, 'newSales']);
    Route::post('/sales/new', [SaleController::class, 'create']);
    Route::post('/sales/reset', [SaleController::class, 'saleReset']);
    Route::post('/sales/scan' , [OrderController::class , 'orderScanner']);



    //Orders For Agente
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/toconfirmate', [OrderController::class, 'orderToConfirme']);
    Route::get('/orders/confirmer', [OrderController::class, 'confirmedOrders']);
    Route::get('/orders/add', [OrderController::class, 'addOrder']);
    Route::post('/orders/update/{id}', [OrderController::class, 'updateOrder']);

    Route::get('/orders/show/{id}', [OrderController::class, 'showOrder']);

    Route::get('/orders/history/show/{id}', [OrderController::class, 'orderHistory']);

    Route::get('/orders/count',[OrderController::class , 'orderCount']);


    //Orders For Delivery
    Route::get('/orders/todelivery', [OrderController::class, 'orderToDelivery']);
    Route::get('/orders/delivred', [OrderController::class, 'orderDelivered']);
    Route::post('/orders/update/delivery/note/{id}', [OrderController::class, 'updateDeliveryNote']);



    //Orders update Confirmation Affectation Upsell Note
    Route::post('/orders/update/confirmationandnote/{id}', [OrderController::class, 'updateConfirmationAndNote']);
    Route::post('/orders/update/confirmation/{id}', [OrderController::class, 'updateConfirmation']);
    Route::post('/orders/update/affectation/{id}', [OrderController::class, 'updateAffectation']);
    Route::post('/orders/update/delivery/{id}', [OrderController::class, 'updatedelivery']);
    Route::post('/orders/update/upsell/{id}', [OrderController::class, 'updateUpsell']);
    Route::post('/orders/update/note/{id}', [OrderController::class, 'updateNote']);



    //Inventory
        //Inventory State
        Route::get('/inventorystates', [InventoryController::class, 'inventoryState']);


        //Inventory Movement
        Route::get('/inventorymovements', [InventoryController::class, 'inventoryMovement']);
        Route::post('/inventorymovements/new', [InventoryController::class, 'createInventoryMovement']);
        Route::post('/inventorymovements/update/{id}', [InventoryController::class, 'updateInventoryMovement']);
        Route::delete('/inventorymovements/delete/{id}', [InventoryController::class, 'deleteInventoryMovement']);
        Route::get('/inventorymovements/{id}', [InventoryController::class, 'showInventoryMovement']);


        //Is_Received
        Route::post('/delivery/inventorymovements/update/isreceived/{id}', [InventoryController::class, 'updateReceivedInventoryMovement']);
        Route::post('/delivery/inventorymovements/update/note/{id}', [InventoryController::class, 'updateNoteInventoryMovement']);
        Route::post('/delivery/inventorymovements/update/{id}', [InventoryController::class, 'updateReceivedNoteInventoryMovement']);


    // Dashboard
    Route::get('/dashboard/delivery', [DashboardController::class, 'delivery']);
    Route::get('/dashboard/agente', [DashboardController::class, 'agente']);

    // Follow Up
    Route::post('/v1/admin/statistics', [AdminController::class, 'statistics']);
    Route::post('/v1/admin/orders', [AdminController::class, 'index']);
    Route::post('/v1/admin/orders/create', [AdminController::class, 'create']);
    Route::post('/v1/admin/orders/{id}/update', [AdminController::class, 'update']);

    // Follow Up
    Route::post('/v1/followup/statistics', [FollowUpController::class, 'statistics']);
    Route::post('/v1/followup/orders', [FollowUpController::class, 'index']);
    Route::post('/v1/followup/orders/{id}/update', [FollowUpController::class, 'update']);

    // Agent orders
    Route::post('/v1/agent/statistics', [AgentController::class, 'statistics']);
    Route::post('/v1/agent/orders', [AgentController::class, 'index']);
    Route::get('/v1/agent/orders/counts', [AgentController::class, 'counts']);
    Route::post('/v1/agent/orders/{id}/update', [AgentController::class, 'update']);
});

// Auto fetch
Route::post('/sync', [GoogleSheetController::class, 'sync']);
//User Logout
Route::get('/auth/logout', [AuthController::class, 'logoutUser']);
Route::get('/auth/clear', function() {
    foreach ($_COOKIE as $key => $value) {
        Cookie::queue(Cookie::forget($key));
    }
});
