<?php

use App\Events\NewNotification;
use App\Helpers\ProductHelper;
use App\Http\Controllers\Api\Admin\FactorisationController;
use App\Http\Controllers\Api\Admin\GoogleSheetController;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Helpers\SteHelper;

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

Route::get('/test', [GoogleSheetController::class, 'test']);

Route::get('/fact/{id}',[FactorisationController::class,'generatePDF']);


Route::get('/test-helper', function () {
    $data = array(
        'company' => 'Voldo',
    );
    $endpoint = 'getcities/';
    SteHelper::apiSte($data, $endpoint);
});

