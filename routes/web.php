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
    $ids = "CODS19645;CODS19591;CODS19512;CODS19503;CODS19504;CODS19498;CODS19473;CODS19448;CODS19413;CODS19357;CODS19328;CODS19305;CODS19306;CODS19252;CODS18354;CODS19211;CODS19003;CODS19201;CODS19163;CODS19132;CODS19113;CODS19110;CODS19080;CODS18968;CODS18688;CODS18897;CODS18344;CODS18886;CODS18818;CODS18270;CODS18862;CODS18850;CODS18692;CODS14843;CODS14895;CODS17579;CODS17921;CODS18834;CODS18819;CODS18719;CODS18713;CODS18691;CODS18644;CODS18601;CODS18546;CODS18523;CODS18520;CODS18266;CODS18508;CODS18470;CODS18277;CODS18411;CODS18280;CODS18499;CODS18509;CODS18290;CODS18480;CODS18334;CODS18402;CODS18468;CODS18365;CODS18394;CODS18376;CODS18366;CODS18350;CODS18345;CODS18339;CODS18305;CODS18297;CODS18289;CODS18291;CODS18283;CODS18249;CODS18220;CODS18244;CODS18209;CODS18203;CODS18202;CODS16685;CODS18183;CODS18170;CODS18163;CODS17838;CODS17897;CODS17851;CODS17950;CODS18000;CODS18041;CODS18130;CODS18129;CODS17992;CODS18056;CODS17926;CODS18118;CODS18110;CODS18093;CODS18094;CODS18083;CODS18068;CODS18055;CODS18038;CODS17984;CODS17978;CODS17975;CODS17943;CODS17933;CODS17906;CODS17900;CODS17853;CODS17855;CODS17835;CODS17824;CODS17805;CODS17819;CODS17743;CODS17810;CODS17707;CODS17803;CODS17756;CODS17792;CODS17781;CODS17696;CODS17754;CODS17747;CODS17745;CODS17732;CODS15645;CODS9446;CODS17582;CODS15025;CODS16993;CODS17705;CODS17654;CODS17699;CODS17702;CODS17678;CODS17674;CODS17658;CODS17650;CODS17649;CODS17641;CODS17633;CODS17625;CODS17616;CODS17568;CODS15393;CODS16182;CODS16429;CODS17560;CODS6870;CODS7700;CODS9791;CODS12337;CODS14349;CODS17141;CODS17140;CODS17531;CODS17120;CODS17541;CODS17159;CODS17536;CODS17534;CODS17533;CODS17521;CODS17158;CODS16638;CODS17495;CODS17485;CODS17470;CODS17466;CODS17280;CODS16407;CODS10950;CODS12388;CODS12858;CODS13186;CODS16231;CODS16934;CODS17144;CODS17407;CODS17263;CODS17422;CODS17387;CODS17252;CODS17251;CODS17244;CODS17240;CODS17149;CODS17210;CODS17188;CODS17281;CODS17125;CODS17109;CODS17082;CODS16749;CODS16861;CODS17093;CODS16718;CODS16721;CODS16783;CODS16692;CODS16846;CODS16992;CODS16480;CODS16473;CODS16519;CODS17068;CODS15285;CODS14686;CODS16660;CODS17052;CODS17011;CODS16994;CODS16970;CODS16960;CODS16961;CODS16942;CODS16938;CODS16929;CODS16930;CODS16924;CODS16916;CODS16912;CODS16908;CODS16896;CODS16894;CODS16890;CODS16876;CODS16874;CODS16866;CODS16868;CODS16859;CODS16824;CODS16808;CODS16803;CODS16789;CODS16781;CODS16779;CODS16764;CODS16758;CODS16756;CODS16715;CODS16708;CODS16675;CODS16673;CODS16674;CODS16649;CODS16648;CODS16467;CODS16605;CODS16571;CODS16400;CODS16406;CODS16381;CODS16499;CODS16634;CODS16631;CODS16629;CODS16642;CODS16018;CODS16007;CODS16491;CODS16483;CODS16251;CODS16600;CODS16587;CODS16589;CODS16585;CODS16572;CODS16568;CODS16558;CODS16538;CODS16536;CODS16531;CODS16521;CODS16515;CODS16512;CODS16505;CODS16510;CODS16472;CODS16478;CODS16464;CODS16440;CODS16436;CODS16412;CODS16370;CODS16373;CODS16358;CODS16352;CODS16348;CODS16347;CODS16339;CODS16334;CODS16333;CODS16329;CODS16324;CODS16314;CODS16307;CODS16309;CODS16304;CODS16302;CODS16291;CODS16290;CODS16263;CODS16257;CODS16252;CODS16244;CODS16199;CODS16197;CODS16179;CODS16177;CODS16176;CODS16172;CODS16161;CODS16158;CODS16154;CODS15700;CODS16073;CODS16118;CODS16130;CODS16128;CODS15718;CODS16111;CODS16110;CODS16104;CODS16092;CODS16074;CODS16067;CODS16058;CODS16051;CODS16036;CODS16024;CODS16011;CODS15997;CODS15988;CODS15967;CODS15946;CODS15944;CODS15923;CODS15933;CODS15927;CODS15928;CODS15918;CODS15864;CODS15885;CODS15882;CODS15874;CODS15875;CODS15849;CODS15836;CODS15676;CODS14708;CODS14968;CODS15313;CODS15811;CODS15809;CODS12676;CODS15793;CODS15785;CODS15776;CODS15771;CODS15753;CODS15747;CODS15737;CODS15736;CODS15733;CODS15732;CODS15721;CODS15719;CODS15694;CODS15322;CODS15347;CODS15675;CODS15441;CODS15669;CODS15289;CODS15254;CODS15662;CODS15660;CODS15654;CODS15651;CODS15649;CODS15750;CODS15630;CODS15623;CODS15611;CODS15579;CODS15526;CODS15518;CODS15514;CODS15509;CODS15505;CODS15499;CODS15502;CODS15501;CODS15500;CODS15433;CODS15469;CODS15294;CODS15452;CODS15427;CODS15442;CODS15435;CODS15413;CODS15403;CODS15394;CODS15387;CODS15308;CODS15363;CODS15351;CODS15346;CODS15197;CODS15318;CODS15306;CODS14896;CODS15297;CODS15291;CODS14388;CODS14389;CODS14943;CODS15275;CODS15271;CODS14382;CODS14195;CODS15247;CODS14031;CODS15243;CODS15239;CODS15231;CODS15228;CODS15226;CODS15211;CODS15202;CODS15193;CODS15184;CODS15180;CODS14266;CODS14261;CODS15156;CODS15151;CODS14595;CODS15122;CODS14294;CODS14901;CODS14735;CODS14668;CODS14354;CODS14626;CODS14361;CODS14583;CODS14372;CODS14378;CODS14933;CODS14937;CODS15112;CODS15104;CODS15000;CODS15084;CODS15081;CODS15069;CODS15064;CODS14759;CODS15040;CODS15052;CODS15047;CODS15045;CODS15037;CODS14848;CODS15022;CODS14377;CODS15019;CODS14997;CODS14824;CODS15002;CODS14999;CODS14994;CODS14989;CODS14991;CODS14966;CODS14964;CODS14953;CODS14960;CODS14740;CODS14952;CODS14944;CODS14930;CODS14923;CODS14922;CODS14027;CODS14902;CODS15015;CODS14893;CODS14891;CODS14880;CODS14873;CODS14872;CODS14868;CODS14865;CODS14861;CODS14852;CODS14851;CODS14838;CODS14834;CODS14731;CODS14792;CODS14818;CODS14774;CODS14785;CODS14752;CODS14743;CODS14199;CODS14450;CODS14727;CODS14714;CODS14712;CODS14688;CODS14679;CODS13714;CODS14660;CODS14656;CODS14647;CODS14635;CODS14636;CODS14616;CODS14611;CODS14606;CODS14608;CODS14540;CODS14605;CODS14539;CODS14579;CODS14564;CODS14457;CODS14309;CODS14480;CODS14417;CODS14332;CODS14423;CODS14511;CODS14508;CODS14502;CODS14451;CODS14470;CODS14455;CODS14448;CODS14447;CODS14445;CODS14442;CODS14434;CODS14431;CODS14427;CODS14426;CODS12424;CODS14330;CODS14322;CODS14316;CODS14312;CODS14310;CODS14302;CODS14021;CODS14311;CODS14297;CODS14220;CODS14140;CODS4843;CODS14248;CODS14180;CODS14221;CODS14210;CODS14181;CODS14168;CODS14151;CODS14150;CODS14137;CODS14135;CODS14039;CODS14129;CODS14126;CODS14125;CODS14087;CODS14114;CODS14119;CODS13863;CODS13829;CODS13812;CODS13891;CODS14025;CODS13409;CODS14106;CODS13483;CODS14091;CODS13657;CODS13428;CODS13307;CODS13048;CODS13997;CODS13970;CODS13505;CODS14078;CODS13872;CODS14065;CODS14057;CODS14049;CODS14054;CODS14042;CODS14043;CODS14041;CODS14034;CODS14032;CODS14015;CODS14002;CODS14001;CODS13993;CODS13992;CODS13990;CODS13980;CODS13966;CODS13937;CODS13935;CODS13926;CODS10596;CODS13914;CODS13911;CODS13910;CODS13888;CODS14004;CODS13878;CODS13877;CODS13850;CODS13846;CODS13844;CODS13830;CODS13827;CODS13826;CODS13815;CODS13811;CODS13807;CODS13805;CODS13793;CODS13769;CODS13768;CODS13760;CODS13756;CODS13754;CODS13747;CODS13736;CODS13729;CODS13731;CODS13722;CODS12870;CODS13703;CODS13669;CODS13710;CODS13708;CODS13711;CODS13201;CODS13704;CODS13667;CODS13700;CODS13699;CODS13698;CODS13695;CODS13694;CODS13693;CODS12739;CODS13680;CODS13671;CODS13670;CODS13668;CODS13662;CODS13663;CODS13658;CODS13653;CODS13646;CODS13620;CODS13592;CODS13604;CODS13588;CODS13579;CODS13573;CODS13568;CODS13564;CODS13563;CODS13543;CODS13542;CODS13412;CODS13512;CODS13493;CODS12721;CODS13465;CODS13532;CODS11492;CODS13496;CODS13525;CODS13479;CODS13521;CODS13518;CODS13511;CODS13509;CODS13508;CODS13497;CODS13484;CODS13482;CODS13481;CODS13480;CODS13471;CODS13462;CODS13439;CODS13432;CODS13514;CODS13423;CODS13414;CODS13396;CODS13394;CODS13383";
    
    Auth::loginUsingId(1);
    
    $result = array_map(function ($ref) {
        $id = substr($ref, 4);
        
        $order = Order::where('id', $id)->first();
        
        if(!$order) return 'not-found: '. $id;
        
        $order->delivery = "annuler";
        
        return $order->save();
        
        
    }, explode(";", $ids));
    
    return ($result);
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

