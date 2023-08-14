<?php

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use App\Helpers\SteHelper;
use App\Models\OrderHistory;
use Illuminate\Http\Request;
use App\Helpers\ProductHelper;
use App\Events\NewNotification;
use App\Models\RoadRunnerRequest;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Services\RoadRunnerService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Api\Public\OrderController;
use App\Http\Controllers\Api\Admin\GoogleSheetController;
use App\Http\Controllers\Api\Admin\FactorisationController;
use App\Services\RoadRunner;

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

// Route::get('/test', function() {
//     return storage_path(Product::find(35)->image);
// });

Route::get('storage/productImages/{filename}', function ($filename) {
    $path = "/home/u594122495/domains/vldo.shop/public_html/api/storage/app/public/productImages/{$filename}";
    if (file_exists($path)) {
        return response()->file($path);
    } else {
        abort(404);
    }
});

Route::get('/sync', [GoogleSheetController::class, 'index']);



Route::get('/fact/{id}',[FactorisationController::class,'generatePDF']);


// Route::get('/test-helper', function () {
//     $data = array(
//         'company' => 'Voldo',
//     );
//     $endpoint = 'getcities/';
//     $response = SteHelper::apiSte($data, $endpoint);

//     return response()->json($response);
// });
Route::get('/cities', function () {

    $response = RoadRunner::cities();

    return response()->json($response);
});
Route::get('/orders', function () {
    $response = RoadRunnerService::orders();

    return response()->json($response);
});

Route::get('/inserto', function () {
    $order = Order::find(29);
    $response = RoadRunner::insert($order);
    // $response = $order;
    return response()->json([
        'status' => true,
        'data' => $response
    ]);
});



Route::get('/citi' , function(){
    $data = array(
        'company' => 'Voldo',
    );
    $endpoint = 'getcities';
    $response = SteHelper::apiSte($data, $endpoint);

    return response()->json($response);
});

// Route::get('/insert', function () {
//     $data = array(
//         'company' => 'Voldo',
//         "firstName" => "Road Runner",
//         "lastName" => "Delivery",
//         "countryPhoneCode" => "961",
//         "phoneNumber" => "70123456",
//         "reference_id" => "CMD-123",
//         "totalLbpPrice" => "100000",
//         "totalUsdPrice" => "50",
//         "orderSize" => "1",
//         "zone_id" => "5",
//         "address" => "Beirut",
//         "note" => "Any note"
//     );
//     $endpoint = 'insert/';
//     SteHelper::apiSte($data, $endpoint);
// });

// Route::get('/deleteId' , function(){
//     $response = RoadRunnerService::delete(246);
//     return response()->json($response);
// });


// Route::get('/formatS', function(){
//     $orderData = Order::with('items.product_variation')->where('refere',246)->first();
//     $order = json_decode($orderData, true);

//     $result = RoadRunnerService::formatProductString($order);
//     return response()->json($result);

// });



// Route::get('/fix', function() {

//     $updated_orders = [];
//     $not_updated_orders = [];
//     // DB::beginTransaction();
//     try {
//         $requests = RoadRunnerRequest::distinct('reference_id')->where('reference_id', 'like', 'vld%')->get(['reference_id', 'status']);

//         // $id = substr("vld2228", 3);

//         // foreach($requests as $request) {

//         // }

//         $references = [
//             'New' => 'dispatch',
//             'Picked Up' => 'expidier',
//             'Transfer' => 'transfer',
//             'Delay' => 'pas-de-reponse',
//             'Delivered' => 'livrer',
//             'Cancel' => 'annuler',
//             'Returned' => 'retourner',
//             'Delivered & Return' => 'livrer-et-retourner',
//             'Paid' => 'paid'
//         ];


//         foreach($requests as $req) {
//             $max_id = RoadRunnerRequest::where('reference_id', $req->reference_id)->max('id');
//             $r = RoadRunnerRequest::where('id', $max_id)->first(['reference_id', 'status', 'created_at']);

//             $valid_status = in_array($r->status, array_flip($references));
//             $valid_prefix = strtolower(substr($r->reference_id, 0, 3)) == 'vld';
//             $id = substr($r->reference_id, 3);

//             if($valid_status && $valid_prefix && is_numeric($id)) {
//                 $status = $references[$r->status];

//                 $order =  Order::where('id', $id)->first();
//                 if(!$order) {
//                     throw new Error($id);
//                 }
//                 if($order->id == $id) {
//                     $order->delivery = $status;
//                     $updated_orders[] = ['id' => $order->id, 'status' => $status];
//                     $orderHistory = new OrderHistory();
//                     $orderHistory->order_id = $id;
//                     $orderHistory->user_id = 4;
//                     $orderHistory->type = 'delivery';
//                     $orderHistory->historique = $status;
//                     $orderHistory->note = 'Updated Status of Delivery';
//                     $orderHistory->save();
//                     $order->save();
//                 } else {
//                     $not_updated_orders[] = ['id' => $order->id];
//                 }
//             } else {
//                 $not_updated_orders[] = ['id' => $id];
//             }
//         }

//         // DB::commit();

//         // return collect($updated_orders)->where('status', 'Delay')->count();
//     } catch (\Throwable $th) {
//         $not_updated_orders[] = $th->getMessage();
//     }
//     return [ 'not' => $not_updated_orders, 'updated' => $updated_orders];


// });


Route::get('add-user', function() {
    $role = Role::create([
        'name' => 'follow-up'
    ]);

    $permission = Permission::create(['name' => 'follow_up_orders']);
    $role->givePermissionTo($permission);

    $user = User::create([
        'firstname' => 'followup',
        'lastname' => 'followup',
        'email' => 'followup@gmail.com',
        'phone' => '12345678',
        'password' => Hash::make('followup'),
        'status' => 1
    ]);

    $user->assignRole('follow-up');
});
