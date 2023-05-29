<?php

use App\Events\NewNotification;
use App\Helpers\ProductHelper;
use App\Http\Controllers\Api\Admin\FactorisationController;
use App\Http\Controllers\Api\Admin\GoogleSheetController;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', [GoogleSheetController::class, 'index']);

Route::get('/fact/{id}',[FactorisationController::class,'generatePDF']);
