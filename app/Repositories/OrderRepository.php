<?php

namespace App\Repositories;

use App\Models\Order;
use Carbon\Carbon;
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
        'reconfirmer' => 'Reconfirmed'
    ];

    public function all() {
        return Order::query()->get();
    }


    public function whereCount($where, $callbak = null) {
        $query = Order::query();
        $query->where($where);

        if($callbak != null) {
            $callbak($query);
        }

        return $query->count();
    }

    public function paginate(
        int $perPage = 10,
        string $sortBy = 'created_at',
        string $sortOrder = 'desc',
        array $options = []
    ) {
         // Number of records per page

        // Get the query builder instance for the 'users' table
        $query = Order::query();

        $validSortOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $validSortOrders)) {
            $sortOrder = 'desc'; // Set default if the provided sort order is invalid
        }

        foreach(data_get($options, 'orWhere', []) as $w) {
            $query->orWhere($w[0], $w[1], $w[2]);
        }

        foreach(data_get($options, 'whereDate', []) as $wd) {
            if(!$wd[2]) continue;
            $query->whereDate($wd[0], $wd[1], Carbon::make($wd[2])->toDate());
        }

        foreach(data_get($options, 'where', []) as $w ) {
            $query->where($w[0], $w[1], $w[2]);
        }

        // Apply the sorting to the query
        $query->orderBy($sortBy, $sortOrder);

        // Retrieve the paginated results
        $orders = $query->paginate($perPage);

        return $orders;
    }


    public function update($id, $data) {
        $order = Order::where('id', $id)->first();

        $order->update($data);

        $items = $data['items'];
        $itemsIds = collect($items)->map(fn($i) => $i['id'])->values()->toArray();

        OrderItem::where('order_id', $id)->whereNotIn('id', $itemsIds)->delete();

        foreach($items as $item) {
            OrderItem::updateOrCreate(
                [
                    'id' => $item['id'],
                    'order_id' => $id,
                ],
                $item
            );
        }
        $order = $order->fresh();
        return $order;
    }


    public function adminStatistics()
    {
        return null;
        $orders = DB::table('orders');
        $newOrders = $orders->where('confirmation', null)->count();

        // ->selectRaw("followup_confirmation, count('followup_confirmation') as total")->get();

        $total = $orders->sum('total');

        $statistics = $orders->map(function($c) use($total) {
            return [
                'name' => $this->confirmations[$c->followup_confirmation],
                'confirmation' => $c->followup_confirmation,
                'total' => $c->total,
                'percent' => round(($c->total * 100) / $total, 2),
            ];
        });

        $show = [ '*' ];
        $response = [
            'data' => $statistics,
            'show' => $show
        ];

        return $response;
    }


    public function followUpStatistics($userId)
    {
        $orders = DB::table('orders')
        // ->where('followup_id', $userId)
        ->groupBy('followup_confirmation')
        ->selectRaw("followup_confirmation, count('followup_confirmation') as total")->get();

        $total = $orders->sum('total');

        $statistics = $orders->map(function($c) use($total) {
            return [
                'name' => $this->confirmations[$c->followup_confirmation],
                'confirmation' => $c->followup_confirmation,
                'total' => $c->total,
                'percent' => round(($c->total * 100) / $total, 2),
            ];
        });

        $show = [ '*' ];
        $response = [
            'data' => $statistics,
            'show' => $show
        ];

        return $response;
    }


    public function agentStatistics($userId)
    {
        $orders = DB::table('orders')
        ->where('agente_id', $userId)
        ->groupBy('confirmation')
        ->selectRaw("confirmation, count('confirmation') as total")->get();

        $total = $orders->sum('total');

        $statistics = $orders->map(function($c) use($total) {
            return [
                'name' => $this->confirmations[$c->confirmation],
                'confirmation' => $c->confirmation,
                'total' => $c->total,
                'percent' => round(($c->total * 100) / $total, 2),
            ];
        });

        $show = [ '*' ];
        $response = [
            'data' => $statistics,
            'show' => $show
        ];

        return $response;
    }


}
