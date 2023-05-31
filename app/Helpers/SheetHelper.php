<?php

namespace App\Helpers;

use App\Models\Order;
use App\Models\OrderItem;
use Exception;
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
                $newRows = $this->insert_sheet_orders($newRows, $sheet);
                return $newRows;
            }

            return $newRows;
        } catch (\Throwable $th) {
            $message = json_decode($th->getMessage());
            throw new Exception('PERMISSION_DENIED');
            // return $message;
        }
    }


    public function insert_sheet_orders($orders, $sheet) {


        $newOrders = [];
        foreach($orders as $o) {

            $fullname = array_key_exists('Full name', $o) ? $o['Full name'] : '';
            $phone = array_key_exists('Phone', $o) ? $o['Phone'] : '';
            $city = array_key_exists('city', $o) ? $o['city'] : '';
            $adresse = array_key_exists('ADRESS', $o) ? $o['ADRESS'] : '';
            $price = array_key_exists('Total charge', $o) ? $o['Total charge'] : '';

            $order = Order::create([
                'fullname' => $fullname,
                'phone' => $phone,
                'city' => $city,
                'adresse' => $adresse,
                'price' => (float) $price,
                'sheets_id' => self::order_sheet_id($sheet, $o['Order ID']),
                'counts_from_warehouse' => true,
            ]);
            $relationship = ['items' => ['product_variation.warehouse', 'product'], 'factorisations'];
            $newOrders[] = $order->fresh()->load($relationship);
        }
        return $newOrders;
    }

    public static function order_sheet_id($sheet, $order_id) {
        return $sheet->sheet_id . '***' . $sheet->sheet_name . '***' . $order_id;
    }
}
