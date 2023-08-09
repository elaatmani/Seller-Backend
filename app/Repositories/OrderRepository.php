<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderHistory;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\RoadRunnerService;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface {

    public function all() {

    }

    public function paginate(int $perPage = 10, string $sortBy = 'created_at', string $sortOrder = 'desc') {
         // Number of records per page

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

        return $orders;
    }

    public function create($data) {

    }


    public function followUpStatistics($userId)
    {
        $orders = DB::table('orders')->groupBy('confirmation')->selectRaw("confirmation, count('confirmation') as total")->get();

        // $confirmed = $orders->where('confirmation', 'confirmer')->count();
        // $cancelled = $orders->where('confirmation', 'annuler')->count();

        return $orders;

        // return [
        //     'confirmed' => $confirmed,
        //     'cancelled' => $cancelled
        // ];
    }

}
