<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Revolution\Google\Sheets\Facades\Sheets;

class GoogleSheetController extends Controller
{
    public function index() {
        $sheet_id = "1Ua71zLgcQZwLhfAzWGNqGs3ZIvlx8YxbDZnbeXoqARk";
        $sheet_id = "1Q_F_IctzZzFLDqMjhw9ZR1C0bbRG84vxr1cBROU0Xz0";
        $sheet = "Sheet1";
        $sheet = "Youcan-Orders";

        $data = Sheets::spreadsheet($sheet_id)->sheet($sheet)->get();

        $headers = $data->pull(0);
        $values = Sheets::collection($headers, $data);
        $rows = array_values($values->toArray());

        $rows = array_map(function($item) {
            return [
                ...$item,
                "Total charge" => (float) $item["Total charge"]
            ];
        }, $rows);

        $test = $this->sync_sheet_orders($rows);

        // $id = $this->order_sheet_id($sheet_id, $sheet, $rows[0]['ID']);

        return response()->json($test);
    }

    public function sync_sheet_orders($orders) {
        $ids = collect($orders)->pluck('Order ID');
        $all_ids = Order::whereIn($ids)->get()->pluck('sheets_id');

        $new_orders = array_filter($orders, function($order) use($all_ids) {
            return in_array($order['Order ID'], $all_ids);
        });

        return $new_orders;
    }

    public function order_sheet_id($sheet_id, $sheet_name, $order_id) {
        return $sheet_id . '-' . Str::slug($sheet_name) . '-' . $order_id;
    }
}
