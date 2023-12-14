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
use App\Helpers\SheetHelper;
use App\Http\Controllers\Api\Admin\AdsController;
use App\Models\RoadRunnerRequest;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Services\RoadRunnerService;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Api\Public\OrderController;
use App\Http\Controllers\Api\Admin\GoogleSheetController;
use App\Http\Controllers\Api\Admin\FactorisationController;
use App\Models\Sheet;
use App\Models\SupplyRequest;
use App\Services\OrderItemHistoryService;
use App\Services\RoadRunner;
use App\Services\RoadRunnerCODSquad;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Auth;

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
Route::get('/ads',  [AdsController::class, 'index']);

Route::get('/fix', function() {
    // $supply = SupplyRequest::create([
    //     'product_id' => 3,
    //     'product_variation_id' => 3,
    //     'quantity' => 100,
    //     'note' => 'No note',
    //     'seller_id' => 5
    // ]);
    // $supply->fresh();

    // return $supply;

    // Auth::loginUsingId(1);
    $supply = SupplyRequest::find(1);

    // $supply->status = 'chesdxd';
    // $supply->save();

    return $supply->load('seller');

});

// Route::get('/', function () {
//     return Order::with('advertisements')->get();
// });

Route::get('check-sheet/{id}', [GoogleSheetController::class, 'save_orders']);

Route::get('/road-orders', function() {
    return RoadRunnerCODSquad::orders();
});

Route::get('/road', function() {
    $order_ids = OrderHistory::whereDate('created_at', '2023-08-16')
    ->whereNotIn('user_id', array(4, 8, 18, 12, 17, 13, 11, 15))
    ->get()->pluck('order_id')->toArray();



    $order = Order::whereIn('id', $order_ids)->where('affectation', 4)->first();
    if(!$order) return 'not found';
    return RoadRunner::insert($order);
});

// Route::get('/test', function() {
//     return storage_path(Product::find(35)->image);
// });

Route::get('storage/productImages/{filename}', function ($filename) {
    $path = "/home/u594122495/domains/codesquad.net/public_html/seller/api/storage/app/public/productImages/{$filename}";
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


Route::get('/synoldorders' , function(){
       $statuses = [
        'New',
        'Picked Up',
        'Transfer',
        'Delay',
        'Delivered',
        'Canceled',
        'Returned',
        'Delivered & Return',
        'Paid'

    ];

     $references = [
        'New' => 'dispatch',
        'Picked Up' => 'expidier',
        'Transfer' => 'transfer',
        'Delay' => 'pas-de-reponse',
        'Delivered' => 'livrer',
        'Canceled' => 'annuler',
        'Returned' => 'retourner',
        'Delivered & Return' => 'livrer-et-retourner',
        'Paid' => 'livrer',
    ];

    $orders = RoadRunnerCODSquad::orders()['response'];

    $success = [];
    $failed = [];

    foreach($orders as $res){

        try {

            $id = substr($res['reference_id'], 4);


            $prefix = strtolower(substr($res['reference_id'], 0, 4));



             if($prefix == 'cods' && is_numeric($id)) {
                $order = Order::where('id', (int) $id)->first();
            } else {
                $order = null;
            }

            $roadrunner = RoadRunnerRequest::create([
                'reference_id' => $res['reference_id'],
                'status' => $res['status']
            ]);

            if (!$order) {
                $roadrunner->success = false;
                $roadrunner->message = "Order not found";
                $roadrunner->save();

                $failed[] = [
                    'reference_id' => $res['reference_id'],
                    'error' => 'Order not found'
                ];
                continue;
            }


            $roadrunner->success = true;
            $roadrunner->message = "Order delivery status has changed to '" . $res['status'] . "'.";

            if (!in_array($res['status'], $statuses)) {
                $newStatus = $order->delivery;
                $roadrunner->message = "The state '" . $res['status'] . "' was not found. order delivery stays in '" . $order->delivery . "'.";

                $failed[] = [
                    'reference_id' => $res['reference_id'],
                    'error' => "'Status not found: '" . $res['status'] . "'"
                ];
                continue;

            } else {
                $newStatus = $references[$res['status']];

            }

            $roadrunner->save();

            $order->delivery = $newStatus;

            $order->save();

            $success[] = $res['reference_id'];

        } catch (\Throwable $th) {
            $failed[] = [
                'reference_id' => $res['reference_id'],
                'error' => $th->getMessage()
            ];
            continue;
        }
    }



    DB::commit();

    return response()->json([
        'code' => 'SUCCESS',
        'success' => $success,
        'failed' => $failed,
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


Route::get('get-token', function() {
    $abilities = ["delivery:update"];
    $user = User::where('id', 4)->first();
    $token = $user->createToken('API', $abilities)->plainTextToken;

    return response()->json(['user' => $user, 'token' => $token]);
});
Route::get('/history',function(){
    $order = Order::find(1);
    return OrderItemHistoryService::observe($order);
});

