<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    public function index(Request $request) {
        // $orders = Order::paginate(5);


        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10); // Number of records per page

        // Get the query builder instance for the 'users' table
        $query = Order::query();

        $validSortOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $validSortOrders)) {
            $sortOrder = 'desc'; // Set default if the provided sort order is invalid
        }

        // Apply the sorting to the query
        $query->orderBy($sortBy, $sortOrder);

        // Retrieve the paginated results
        $orders = $query->paginate($perPage);

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'orders' => $orders
            ]
            ]);
    }
}
