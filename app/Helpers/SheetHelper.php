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
        $this->orders = Order::all();
    }

    public function sync_orders($sheet, $save = true) {

        try {
            $sheet_id = $sheet->sheet_id;
            $sheet_name = $sheet->sheet_name;

            $data = Sheets::spreadsheet($sheet_id)->sheet($sheet_name)->get();
            $headers = $data->pull(0);
            $values = Sheets::collection($headers, $data);
            $rows = array_values($values->toArray());
            $sheets_ids = array_map(fn($r) => self::order_sheet_id($sheet, $r['Order ID']), $rows);
            $orders_ids = array_values($this->orders->whereIn('sheets_id', $sheets_ids)->pluck('sheets_id')->toArray());

            $newRows = array_filter($rows, function ($row) use ($orders_ids, $sheet) {
                return !in_array( self::order_sheet_id($sheet, $row['Order ID']), $orders_ids);
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
        foreach($orders as $o) {

            $fullname = array_key_exists('Full name', $o) ? $o['Full name'] : '';
            $phone = array_key_exists('Phone', $o) ? $o['Phone'] : '';
            $city = array_key_exists('City', $o) ? $o['City'] : '';
            $adresse = array_key_exists('ADRESS', $o) ? $o['ADRESS'] : '';
            $price = array_key_exists('Total charge', $o) ? $o['Total charge'] : '';
            $quantity = array_key_exists('Total quantity', $o) ? $o['Total quantity'] : '';
            $sku = array_key_exists('SKU', $o) ? $o['SKU'] : '';
            $product_name = array_key_exists('Product name', $o) ? $o['Product name'] : '';

            $check = Order::where('sheets_id', self::order_sheet_id($sheet, $o['Order ID']))->first();

            if(!$sku) continue;
            if(!!$check) continue;

            $product = Product::where('ref', $sku)->first();

            $order = Order::create([
                'fullname' => $fullname,
                'phone' => $phone,
                'city' => $city,
                'adresse' => $adresse,
                'price' => (float) $price,
                'sheets_id' => self::order_sheet_id($sheet, $o['Order ID']),
                'counts_from_warehouse' => true,
                'product_name' => $product_name
            ]);

            if(isset($product)) {
                $arr = explode('\n', $quantity);

                // return $product->variations->first()->id;

                foreach($arr as $q) {
                    try {

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'product_ref' => $product->ref,
                            'product_variation_id' => $product->variations->first()->id,
                            'quantity' => (int) $q
                        ]);
                    } catch (\Exception $th) {
                        return $th->getMessage();
                    }
                }
            }

            $relationship = ['items' => ['product_variation.warehouse', 'product'], 'factorisations'];
            $newOrders[] = $order->fresh()->load($relationship);
        }
        DB::commit();
        return $newOrders;
    }

    public static function order_sheet_id($sheet, $order_id) {
        return $sheet->sheet_id . '***' . $sheet->sheet_name . '***' . $order_id;
    }
}
