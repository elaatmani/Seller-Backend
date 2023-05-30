<?php

namespace App\Helpers;

use App\Models\Order;
use App\Models\OrderItem;
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
    }


    public function insert_sheet_orders($orders, $sheet) {

        $newOrders = [];
        foreach($orders as $o) {
            $order = Order::create([
                'fullname' => $o['Full name'],
                'phone' => $o['Phone'],
                'city' => $o['City'],
                'adresse' => $o['ADRESS'],
                'price' => (float) $o['Total charge'],
                'sheets_id' => self::order_sheet_id($sheet, $o['Order ID']),
            ]);
            $newOrders[] = $order;
        }
        return $newOrders;
    }

    public static function order_sheet_id($sheet, $order_id) {
        return $sheet->sheet_id . '***' . $sheet->sheet_name . '***' . $order_id;
    }
}
