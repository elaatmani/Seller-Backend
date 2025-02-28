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

        // Apply filters
        $this->model->with('user')
            ->when($agenteId != 0, function ($query) use ($agenteId, $field) {
                $query->whereIn($field, $agenteId);
            }, function ($q) use ($field) {
                $q->where($field, '!=', null);
            })
            ->when($request->has('selectedProductId') && !empty($request->input('selectedProductId')), function ($query) use ($request) {
                // Filter by multiple product_ids
                $query->join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->whereIn('order_items.product_id', $request->input('selectedProductId'));
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
            $result['ordersTreatedOrHandled'] = $this->TreatedOrHandledOrders($request, true)->getData(true)['data'];
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
        $orders->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->where('confirmation', 'confirmer');

        // Get the count of confirmed orders per agent
        $topAgents = $orders
            ->join('users', 'users.id', '=', 'orders.agente_id')
            ->select('orders.agente_id', 'users.firstname', 'users.lastname', DB::raw('count(*) as confirmed_count'))
            ->groupBy('orders.agente_id', 'users.firstname', 'users.lastname')
            ->orderByDesc('confirmed_count')
            ->get();

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $topAgents,
        ]);
    }

    public function AgentPerformance(Request $request)
    {
        // Calculate the data using the existing calculateData function
        $orders = $this->calculateData($request, 'order');

        // Filter the orders to only include confirmed and delivered ones
        $orders->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->where('confirmation', 'confirmer')
            ->whereIn('delivery', ["livrer", "paid"]);

        $topAgents = $orders->select(
            'orders.agente_id', // Select the agente_id from orders
            'users.firstname', // Select firstname from users
            'users.lastname',  // Select lastname from users
            DB::raw('count(*) as confirmed_count'),
            DB::raw('sum(case when delivery in ("livrer", "paid") then 1 else 0 end) as delivered_count')
        )
            ->join('users', 'orders.agente_id', '=', 'users.id') // Join orders with users table on agente_id
            ->groupBy('orders.agente_id', 'users.firstname', 'users.lastname') // Group by agent and user name
            ->orderByDesc('confirmed_count')
            ->get();

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $topAgents,
        ]);
    }

    public function agentsByConfirmation(Request $request)
    {
        // SELECT agente_id, confirmation, count(*) FROM `orders` GROUP by agente_id, confirmation;

        $results = DB::table('orders')
            ->join('users', 'orders.agente_id', '=', 'users.id')
            ->select(
                DB::raw('CONCAT(users.firstname, " ", users.lastname) as fullname'),
                'orders.agente_id',
                'orders.confirmation',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('orders.agente_id', 'orders.confirmation', 'users.firstname', 'users.lastname')
            ->whereNotNull('orders.agente_id')
            ->where('orders.agente_id', '!=', 1)
            ->get();

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $results,
        ]);
    }

    public function agentsByDelivery(Request $request)
    {
        $results = DB::table('orders')
            ->join('users', 'orders.agente_id', '=', 'users.id')
            ->select(
                DB::raw('CONCAT(users.firstname, " ", users.lastname) as fullname'),
                'orders.agente_id',
                'orders.delivery',
                DB::raw('COUNT(*) as count')
            )
            ->where('orders.confirmation', 'confirmer')
            ->where('orders.agente_id', '!=', 1)
            ->whereNotNull('orders.agente_id')
            ->groupBy('orders.agente_id', 'orders.delivery', 'users.firstname', 'users.lastname')
            ->get();


        return response()->json([
            'code' => 'SUCCESS',
            'data' => $results,
        ]);
    }

    public function agentsPerformanceLifetime(Request $request)
    {
        $products = $request->input('products', []);

        $agents = DB::table('users as u')
            ->join('model_has_roles as r', 'u.id', '=', 'r.model_id') // Joining instead of whereRaw
            ->where('u.status', 1)
            ->when(!empty($request->input('agents', [])), function ($query) use ($request) {
                $query->whereIn('u.id', $request->input('agents'));
            })
            ->where('r.role_id', 2) // Filtering by role directly
            ->select(
                'u.id',
                'u.firstname',
                'u.lastname',
                DB::raw('(SELECT count(*) FROM orders o 
                  ' . (!empty($products) ? 'JOIN order_items oi ON o.id = oi.order_id' : '') . ' 
                  WHERE o.agente_id = u.id 
                  AND o.confirmation != "double" 
                  ' . (!empty($products) ? 'AND oi.product_id IN (' . implode(',', $products) . ')' : '') . ') as treated'),
                DB::raw('(SELECT count(*) FROM orders o 
                  ' . (!empty($products) ? 'JOIN order_items oi ON o.id = oi.order_id' : '') . ' 
                  WHERE o.agente_id = u.id 
                  AND o.confirmation = "confirmer" 
                  ' . (!empty($products) ? 'AND oi.product_id IN (' . implode(',', $products) . ')' : '') . ') as confirmed'),
                DB::raw('(SELECT count(*) FROM orders o 
                  ' . (!empty($products) ? 'JOIN order_items oi ON o.id = oi.order_id' : '') . ' 
                  WHERE o.agente_id = u.id 
                  AND o.confirmation = "confirmer" 
                  AND o.delivery IN ("livrer", "paid") 
                  ' . (!empty($products) ? 'AND oi.product_id IN (' . implode(',', $products) . ')' : '') . ') as delivered')
            )
            ->get();

        foreach ($agents as $agent) {
            // Assigning values to the agent object
            $agent->treated = (int) $agent->treated ?? 0;
            $agent->confirmed = (int) $agent->confirmed ?? 0;
            $agent->delivered = (int) $agent->delivered ?? 0;

            $agent->confirmation_rate = $agent->treated == 0 ? 0 : round(($agent->confirmed * 100) / $agent->treated, 2);
            $agent->delivery_rate = $agent->confirmed == 0 ? 0 : round(($agent->delivered * 100) / $agent->confirmed, 2);
        }

        $agents = $agents->sortByDesc('confirmation_rate')->values();


        return [
            'data' => $agents
        ];
    }

    public function agentsPerformanceByTime(Request $request)
    {
        // $start = \Carbon\Carbon::now()->subDays(7)->startOfDay();
        // $end = \Carbon\Carbon::now()->endOfDay();

        // $start = Carbon::parse($request->input('dates.0', Carbon::now()->subDays(7)->startOfDay()));
        // $end = Carbon::parse($request->input('dates.1', Carbon::now()->endOfDay()));

        $start = $request->input('dates.0') ? Carbon::parse($request->input('dates.0')) : null;
        $end = $request->input('dates.1') ? Carbon::parse($request->input('dates.1')) : null;
        $products = $request->input('products', []);

        if (!$start && !$end) {
            return response()->json($this->agentsPerformanceLifetime($request));
        }

        $agents = DB::table('users as u')
            ->join('model_has_roles as r', 'u.id', '=', 'r.model_id') // Joining instead of whereRaw
            ->where('u.status', 1)
            ->where('r.role_id', 2) // Filtering by role directly
            ->select(
                'u.id',
                'u.firstname',
                'u.lastname'
            )
            ->when(!empty($request->input('agents', [])), function ($query) use ($request) {
                $query->whereIn('u.id', $request->input('agents'));
            })
            ->get();

        foreach ($agents as $agent) {
            $data = DB::table('history as h')
                ->join('orders as o', 'h.trackable_id', '=', 'o.id')
                ->when(!empty($products), function ($query) use ($products) {
                    $query->join('order_items as oi', 'o.id', '=', 'oi.order_id');
                    $query->whereIn('oi.product_id', $products);
                })
                ->selectRaw('
                SUM(CASE WHEN h.fields LIKE \'%"field":"agente_id"%\' THEN 1 ELSE 0 END) AS treated,
                SUM(CASE WHEN h.fields LIKE \'%"field":"confirmation","new_value":"confirmer"%\' THEN 1 ELSE 0 END) AS confirmed,
                SUM(CASE WHEN h.fields LIKE \'%"field":"confirmation","new_value":"confirmer"%\' AND o.delivery IN ("livrer", "paid") THEN 1 ELSE 0 END) AS delivered
            ')
                ->where('h.actor_id', $agent->id)
                ->when($start, function ($query) use ($start) {
                    $query->whereDate('h.created_at', '>=', $start);
                })
                ->when($end, function ($query) use ($end) {
                    $query->whereDate('h.created_at', '<=', $end);
                })
                ->first();

            // Assigning values to the agent object
            $agent->treated = (int) $data->treated ?? 0;
            $agent->confirmed = (int) $data->confirmed ?? 0;
            $agent->delivered = (int) $data->delivered ?? 0;

            $agent->confirmation_rate = $agent->treated == 0 ? 0 : round(($agent->confirmed * 100) / $agent->treated, 2);
            $agent->delivery_rate = $agent->confirmed == 0 ? 0 : round(($agent->delivered * 100) / $agent->confirmed, 2);
        }

        $agents = $agents->sortByDesc('confirmation_rate')->values();

        return response()->json([
            'data' => $agents,
            'start' => $start,
            'end' => $end
        ]);
    }
}
