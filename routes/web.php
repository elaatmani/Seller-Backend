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
use App\Services\RoadRunnerService;

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
    $response = SteHelper::apiSte($data, $endpoint);

    return response()->json($response);
});
Route::get('/cities', function () {
    $response = RoadRunnerService::cities();

    return response()->json($response);
});

Route::get('/insert', function () {
    $data = array(
        'company' => 'Voldo',
        "firstName" => "Road Runner",
        "lastName" => "Delivery",
        "countryPhoneCode" => "961",
        "phoneNumber" => "70123456",
        "reference_id" => "CMD-123",
        "totalLbpPrice" => "100000",
        "totalUsdPrice" => "50",
        "orderSize" => "1",
        "zone_id" => "5",
        "address" => "Beirut",
        "note" => "Any note"
    );
    $endpoint = 'insert/';
    SteHelper::apiSte($data, $endpoint);
});

