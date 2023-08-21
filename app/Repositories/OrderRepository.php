<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderHistory;
use Illuminate\Support\Facades\DB;
use App\Services\RoadRunnerService;
use App\Repositories\Interfaces\OrderRepositoryInterface;

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

    public function all($options = []) {
        $query = Order::query();

        foreach(data_get($options, 'orWhere', []) as $w) {
            $query->orWhere($w[0], $w[1], $w[2]);
        }

        foreach(data_get($options, 'whereDate', []) as $wd) {
            if(!$wd[2]) continue;
            $query->whereDate($wd[0], $wd[1], Carbon::make($wd[2])->toDate());
        }

        foreach(data_get($options, 'orderBy', []) as $wd) {
            $query->orderBy($wd[0], $wd[1]);
        }

        foreach(data_get($options, 'where', []) as $w ) {
            $query->where($w[0], $w[1], $w[2]);
        }

        if(data_get($options, 'get', true)) {
            return $query->get();
        }

        if(data_get($options, 'reported_first', false)) {
            $this->reportedFirst($query);
        }


        return $query;

    }

    public function reportedFirst($query) {
        $query->select('*',
        DB::raw('TIMESTAMPDIFF(day, DATE_FORMAT(now(), "%Y-%m-%d"), DATE_FORMAT(reported_agente_date, "%Y-%m-%d")) as reported_diff')
        , DB::raw('
            CASE
                WHEN confirmation = "reporter" AND TIMESTAMPDIFF(day, DATE_FORMAT(now(), "%Y-%m-%d"), DATE_FORMAT(reported_agente_date, "%Y-%m-%d")) <= 0 THEN 1
                ELSE 0
            END AS show_first')
        );

        $query->orderBy('show_first', 'DESC');
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
        $options['get'] = false;

        // Get the query builder instance for the 'users' table
        $query = $this->all($options);

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


    public function update($id, $data) {
        $order = Order::where('id', $id)->first();

        if($data['affectation'] != null && $data['delivery'] == null) {
            $data['delivery'] = 'dispatch';
        }

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


    public function create($data) {

        $order = Order::create([
            ...$data,
            'delivery' => data_get($data, 'affectation') != null ? 'dispatch' : null
        ]);


        $items = $data['items'];

        foreach($items as $item) {
            OrderItem::create([
                ...$item,
                'order_id' => $order->id
            ]);
        }
        $order = $order->fresh();
        return $order;
    }


    public function adminStatistics()
    {
        $orders = DB::table('orders')->groupBy('confirmation')->selectRaw("confirmation, count('confirmation') as total")->get();

        $total = $orders->sum('total');

        $noAnswers = [
            'day-one-call-one',
            'day-one-call-two',
            'day-one-call-three',
            'day-two-call-one',
            'day-two-call-two',
            'day-two-call-three',
            'day-three-call-one',
            'day-three-call-two',
            'day-three-call-three',
        ];

        $noAnswer = $orders->whereIn('confirmation', $noAnswers);

        $statistics = $orders->map(function($c) use($total) {
            return [
                'name' => $this->confirmations[$c->confirmation],
                'confirmation' => $c->confirmation,
                'total' => $c->total,
                'percent' => round(($c->total * 100) / $total, 2),
            ];
        })->whereNotIn('confirmation', $noAnswers);

        $statistics[] = [
                'name' => 'No Answer',
                'confirmation' => 'day-one-call-one',
                'total' => $noAnswer->sum('total'),
                'percent' => round(($noAnswer->sum('total') * 100) / $total, 2),
        ];

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
