<?php

namespace App\Http\Controllers\Api\Charts;

use App\Http\Controllers\Controller;
use App\Models\History;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class KpiagenteController extends Controller
{
    private $startDate;
    private $endDate;
    private $model;

    private function calculateData(Request $request, $modelType)
    {
        $agenteId = $request->input("selectedAgenteId");

        $this->startDate = $request->input("startDate") ? Carbon::parse($request->input("startDate")) : Carbon::now()->subDays(7);
        $this->endDate = $request->input("endDate") ? Carbon::parse($request->input("endDate")) : Carbon::now();

        if ($this->startDate->eq($this->endDate)) {
            $this->endDate->addDay(); // Add one day to the end date
        }


        if ($modelType === 'order') {
            $this->model = Order::query();
        } else {
            $this->model = History::query();
        }

        $field = $modelType === 'order' ? 'agente_id' : 'actor_id';

        $this->model->with('user')
            ->when($agenteId != 0, function ($query) use ($agenteId, $field) {
                $query->whereIn($field, $agenteId);
            }, function ($q) use ($field) {
                $q->where($field, '!=', null);
            });

        return $this->model;
    }


    public function TreatedOrHandledOrders(Request $request, $isAgente = false)
    {
        $orders = $this->calculateData($request, 'history');

        $orders
            ->join('orders as o', 'history.trackable_id', '=', 'o.id')
            ->where('history.trackable_type', 'like', '%Order%')
            ->where('history.fields', 'like', '%confirmation%')
            ->whereBetween('history.created_at', [$this->startDate, $this->endDate])
            ->where('o.confirmation', '!=', 'double')
            ->select('history.created_at', 'history.trackable_id', 'history.actor_id', 'o.confirmation');

        $droppedOrdersByDate =  DB::table(DB::raw("({$orders->toSql()}) as sub"))
            ->mergeBindings($orders->getQuery())
            ->when($isAgente, function ($query) {
                $query->join('users', 'sub.actor_id', '=', 'users.id');
            })
            ->selectRaw('DATE(sub.created_at) as date, count(DISTINCT sub.trackable_id) as count' . ($isAgente ? ', CONCAT(users.firstname, " ", users.lastname) as agente_fullname' : ''))
            ->groupBy('date')
            ->when($isAgente, function ($query) {
                $query->groupBy('agente_fullname');  // Group by 'agente_fullname' only if $isAgente is true
            })
            ->get();

        $droppedOrdersCount = $orders->count(DB::raw('DISTINCT history.trackable_id'));

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'card' => [
                    'id' => 1,
                    'title' => 'Treated Or Handled Orders',
                    'value' => $droppedOrdersCount,
                    'icon' => 'mdi-swap-horizontal',
                    'color' => '#ffcc00'
                ],
                'ordersTreatedOrHandledByDate' => $droppedOrdersByDate,
            ],
        ]);
    }

   public function DroppedOrders(Request $request, $isAgente = false)
{
    $orders = $this->calculateData($request, 'history');

    $orders
        ->join('orders as o', 'history.trackable_id', '=', 'o.id')
        ->where('history.trackable_type', 'like', '%Order%')
        ->where('history.fields', 'like', '%agente_id%')
        ->whereBetween('history.created_at', [$this->startDate, $this->endDate])
        ->where('o.confirmation', '!=', 'double')
        ->select('history.created_at', 'history.trackable_id', 'history.actor_id', 'o.confirmation');

    $droppedOrdersByDate =  DB::table(DB::raw("({$orders->toSql()}) as sub"))
        ->mergeBindings($orders->getQuery())
        ->when($isAgente, function ($query) {
            $query->join('users', 'sub.actor_id', '=', 'users.id');
        })
        ->selectRaw('DATE(sub.created_at) as date, count(DISTINCT sub.trackable_id) as count' . ($isAgente ? ', CONCAT(users.firstname, " ", users.lastname) as agente_fullname' : ''))
        ->groupBy('date')
        ->when($isAgente, function ($query) {
            $query->groupBy('agente_fullname');  // Group by 'agente_fullname' only if $isAgente is true
        })
        ->get();

    $droppedOrdersCount = $orders->count(DB::raw('DISTINCT history.trackable_id'));

    return response()->json([
        'code' => 'SUCCESS',
        'data' => [
            'card' => [
                'id' => 2,
                'title' => 'Dropped Orders',
                'value' => $droppedOrdersCount,
                'icon' => 'mdi-parachute-outline',
                'color' => '#FECDAA'
            ],
            'droppedOrdersByDate' => $droppedOrdersByDate,
        ],
    ]);
}



    public function ConfirmedOrders(Request $request, $isAgente = false)
    {
        $orders = $this->calculateData($request, 'order');
        $orders->whereBetween('orders.dropped_at', [$this->startDate, $this->endDate]);

        $allOrders = $orders->where('confirmation', '!=', null)->where('confirmation', '!=', 'double')->count();

        $orders->where('confirmation', 'confirmer');

        $confirmedOrdersCount = $orders->count();

        $confirmedOrdersByDate =  $orders
            ->when($isAgente, function ($query) {
                $query->join('users', 'orders.agente_id', '=', 'users.id');
            })
            ->selectRaw('DATE(orders.dropped_at) as date, count(*) as count' . ($isAgente ? ', CONCAT(users.firstname, " ", users.lastname) as agente_fullname' : ''))
            ->groupBy('date')
            ->when($isAgente, function ($query) {
                $query->groupBy('agente_fullname');  // Group by 'agente_fullname' only if $isAgente is true
            })
            ->get();

        $confirmedPourcentage = $allOrders > 0 ? ($confirmedOrdersCount / $allOrders) * 100 : 0;

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'card' => [
                    'id' => 3,
                    'title' => 'Confirmed Orders',
                    'value' => $confirmedOrdersCount,
                    'total' => $allOrders,
                    'percentage' => $confirmedPourcentage,
                    'icon' => 'mdi-check-all',
                    'color' => '#06b6d4'
                ],
                'confirmedOrdersByDate' => $confirmedOrdersByDate,
            ],
        ]);
    }





    public function DeliveredOrders(Request $request, $isAgente = false)
    {
        $orders = $this->calculateData($request, 'order');
        $ordersConfirmed = $this->ConfirmedOrders($request, true)->getData(true)['data']['card']['value'];
        $allOrdersConfirmedOrders = $orders->where('confirmation', 'confirmer')->whereBetween('dropped_at', [$this->startDate, $this->endDate])->count();


        $orders
            ->whereBetween('delivery_date', [$this->startDate, $this->endDate])
            ->where('confirmation', 'confirmer')
            ->where('delivery', 'livrer');

        $deliveredOrdersCount = $orders->count();

        $deliveredOrdersByDate =  DB::table(DB::raw("({$orders->toSql()}) as sub"))
            ->mergeBindings($orders->getQuery())
            ->when($isAgente, function ($query) {
                $query->join('users', 'sub.agente_id', '=', 'users.id');
            })
            ->selectRaw('DATE(sub.created_at) as date, count(*) as count' . ($isAgente ? ', CONCAT(users.firstname, " ", users.lastname) as agente_fullname' : ''))
            ->groupBy('date')
            ->when($isAgente, function ($query) {
                $query->groupBy('agente_fullname');
            })
            ->get();

        $deliveredOrdersPourcentage = $ordersConfirmed > 0 ? ($deliveredOrdersCount / $ordersConfirmed) * 100 : 0;

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'card' => [
                    'id' => 4,
                    'title' => 'Delivered Orders',
                    'value' => $deliveredOrdersCount,
                    'percentage' => $deliveredOrdersPourcentage,
                                        'val' => $allOrdersConfirmedOrders,

                    'icon' => 'mdi-truck-check',
                    'color' => '#10b981'
                ],
                'deliveredOrdersByDate' => $deliveredOrdersByDate,
            ],
        ]);
    }

    public function getAllKpis(Request $request)
    {
        // Get the filter parameter from the request
        $kpisToReturn = $request->input('kpis', ['ordersTreatedOrHandled', 'droppedOrders', 'confirmedOrders', 'deliveredOrders', 'all']);

        $result = [];

        // Check and call each KPI method based on the filter
        if (in_array('all', $kpisToReturn) || in_array('ordersTreatedOrHandled', $kpisToReturn)) {
            $result['ordersTreatedOrHandled'] = $this->TreatedOrHandledOrders($request , true)->getData(true)['data'];
        }

        if (in_array('all', $kpisToReturn) || in_array('droppedOrders', $kpisToReturn)) {
            $result['droppedOrders'] = $this->DroppedOrders($request, true)->getData(true)['data'];
        }

        if (in_array('all', $kpisToReturn) || in_array('confirmedOrders', $kpisToReturn)) {
            $result['confirmedOrders'] = $this->ConfirmedOrders($request, true)->getData(true)['data'];
        }

        if (in_array('all', $kpisToReturn) || in_array('deliveredOrders', $kpisToReturn)) {
            $result['deliveredOrders'] = $this->DeliveredOrders($request, true)->getData(true)['data'];
        }

        // Return the combined result
        return response()->json([
            'code' => 'SUCCESS',
            'data' => $result,
        ]);
    }

    public function TopAgentesInConfirmation(Request $request)
    {
        // Calculate the data using the existing calculateData function
        $orders = $this->calculateData($request, 'order');

        // Filter the orders to only include confirmed ones
        $orders->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('confirmation', 'confirmer');

        // Get the count of confirmed orders per agent
        $topAgents = $orders->select('agente_id', DB::raw('count(*) as confirmed_count'))
            ->groupBy('agente_id')
            ->orderByDesc('confirmed_count')
            ->get();

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $topAgents,
        ]);
    }
}
