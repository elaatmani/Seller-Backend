<?php

namespace App\Helpers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Revolution\Google\Sheets\Facades\Sheets;

// 1 - order can have multiple items with same id in diffrent rows;
// 2 - order can have multiple items in the same row;

class SheetHelper {

    public $orders = [];

    public function __construct()
    {
    }

    public function sync_orders($sheet, $save = true) {

        try {
            $sheet_id = $sheet->sheet_id;
            $sheet_name = $sheet->sheet_name;


            $data = Sheets::spreadsheet($sheet_id)->sheet($sheet_name)->get();
            $headers = $data->pull(0);
            $values = Sheets::collection($headers, $data);
            $rows = array_values($values->toArray());

            $orders_ids = array_values(Order::where('sheets_id', 'like', $sheet_id . '***' . $sheet_name . "%")->get()->pluck('sheets_id')->toArray());

            $newRows = array_filter($rows, function ($row) use ($orders_ids, $sheet) {
                return !in_array( self::order_sheet_id($sheet, $row['Order ID']), $orders_ids)
                && !!data_get($row, 'SKU')
                && data_get($row, 'SKU') != 'N/A'
                && !!data_get($row, 'Full name')
                && !!data_get($row, 'Phone');
            });

            if($save) {
                $newRows = $this->insert_sheet_orders(array_values($newRows), $sheet);
                return $newRows;
            }

            return array_values($newRows);
        } catch (\Throwable $th) {
            $message = json_decode($th->getMessage(), true);

            if(isset($message['error']) && $message['error']['status'] == 'PERMISSION_DENIED') {
                throw new Exception('PERMISSION_DENIED');
            } else {
                throw new Exception($th->getMessage());
            }
        }
    }


    public function insert_sheet_orders($orders, $sheet) {

        DB::beginTransaction();
        $newOrders = [];
        $productNotFoundOrders = [];
        $alreadyExistsOrders = [];
        $affiliateProductIds = DB::table('user_product')->select('product_id')->where('user_id', $sheet->user_id)->get()->pluck('product_id')->toArray();

        foreach($orders as $o) {
            try {
                $fullname = array_key_exists('Full name', $o) ? $o['Full name'] : '';
                $phone = array_key_exists('Phone', $o) ? $o['Phone'] : '';
                $city = array_key_exists('City', $o) ? $o['City'] : '';
                $adresse = array_key_exists('ADRESS', $o) ? $o['ADRESS'] : '';
                $price = array_key_exists('Total charge', $o) ? $o['Total charge'] : '';
                $quantity = array_key_exists('Total quantity', $o) ? $o['Total quantity'] : '';
                $sku = array_key_exists('SKU', $o) ? $o['SKU'] : '';
                $product_name = array_key_exists('Product name', $o) ? $o['Product name'] : '';
                $source = array_key_exists('Source', $o) ? $o['Source'] : '';

                if(!$sku || !$phone || !$fullname) continue;

                

                $product = Product::where([['ref', $sku], ['user_id', $sheet->user_id], ['status', 1]])->orWhere(function($query) use($sheet, $affiliateProductIds) {
                    $query->whereIn('id', $affiliateProductIds)->where('status', 1);
                })->first();

                if(!$product) {
                    $productNotFoundOrders[] = $o;
                    continue;
                };

                // $order_exists = DB::table('orders')->where('sheets_id', self::order_sheet_id($sheet, $o['Order ID']))->exists();
                // if(!!$order_exists) {
                //     $alreadyExistsOrders[] = $o;
                //     continue;
                // };

                $order = Order::create([
                    'user_id' => $sheet->user_id,
                    'fullname' => $fullname,
                    'phone' => $phone,
                    'city' => $city,
                    'adresse' => $adresse,
                    'price' => 0,
                    'sheets_id' => self::order_sheet_id($sheet, $o['Order ID']),
                    'counts_from_warehouse' => true,
                    'product_name' => $product_name,
                    'source' => $source,
                    'is_affiliate' => $product->product_type == 'affiliate',
                ]);

                if(isset($product)) {
                    $arr = explode('\n', $quantity);

                    // return $product->variations->first()->id;
                    $check = 0;
                    foreach($arr as $q) {
                            OrderItem::create([
                                'order_id' => $order->id,
                                'product_id' => $product->id,
                                'product_ref' => $product->ref,
                                'product_variation_id' => $product->variations->first()->id,
                                'quantity' => (int) $q,
                                'price' => $check == 0 ? (float) $price : 0
                            ]);
                            $check = 1;
                    }
                }

                $relationship = ['items' => ['product_variation.warehouse', 'product'], 'factorisations'];
                $newOrders[] = $order->fresh()->load($relationship);

            } catch (\Throwable $th) {
                throw new \Exception($th->getMessage());
            }

        }
        DB::commit();
        return $newOrders;
    }

    public static function order_sheet_id($sheet, $order_id) {
        return $sheet->sheet_id . '***' . $sheet->sheet_name . '***' . $order_id;
    }
}
