<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderHistory;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\RoadRunnerService;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface {

    public $confirmations = [
        null => 'New',
        'day-one-call-one'=> 'No reply 1 / day1',
        'day-one-call-two' =>'No reply 2 / day1',
        'day-one-call-three' =>'No reply 3 / day1',
        'day-two-call-one' =>'No reply 1 / day2',
        'day-two-call-two' =>'No reply 2 / day2',
        'day-two-call-three' =>'No reply 3 / day2',
        'day-three-call-one' =>'No reply 1 / day3',
        'day-three-call-two' =>'No reply 2 / day3',
        'day-three-call-three' =>'No reply 3 / day3',
        'reporter' =>'Reported',
        'annuler' =>'Canceled',
        'wrong-number' =>'Wrong number',
        'confirmer' =>'Confirmed',
        'double' =>'Double',
    ];

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

        $total = $orders->sum('total');

        $statistics = $orders->map(function($c) use($total) {
            return [
                'name' => $this->confirmations[$c->confirmation],
                'confirmation' => $c->confirmation,
                'total' => $c->total,
                'percent' => round(($c->total * 100) / $total, 2),
            ];
        });

        $show = [null, 'confirmer', 'annuler', 'double'];
        $response = [
            'data' => $statistics,
            'show' => $show
        ];

        return $response;

    }

}
